<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersonaController extends Controller
{
    private function tipos(): array {
        return [
            'trabajador' => ['label'=>'Trabajador/Jornalero', 'emoji'=>'👷'],
            'proveedor'  => ['label'=>'Proveedor',            'emoji'=>'🏪'],
            'comprador'  => ['label'=>'Comprador/Cliente',    'emoji'=>'🛒'],
            'vecino'     => ['label'=>'Vecino',               'emoji'=>'🏘️'],
            'familiar'   => ['label'=>'Familiar',             'emoji'=>'👨‍👩‍👧'],
            'contacto'   => ['label'=>'Contacto general',     'emoji'=>'📋'],
            'otro'       => ['label'=>'Otro',                 'emoji'=>'👤'],
        ];
    }

    private function guardarImagen($file, string $sub='personas'): string {
        $dir = public_path("img/{$sub}");
        if (!file_exists($dir)) mkdir($dir, 0775, true);
        $n = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
        $file->move($dir, $n);
        return "img/{$sub}/{$n}";
    }

    private function eliminarImagen(?string $ruta): void {
        if ($ruta) { $f = public_path($ruta); if (file_exists($f)) unlink($f); }
    }

    /* ── LISTADO ── */
    public function index(Request $request)
    {
        $uid   = session('usuario_id');
        $tab   = $request->tab ?? 'todos';
        $query = DB::table('personas')->where('usuario_id',$uid)->where('activo',1);

        if ($request->q)      $query->where('nombre','like',"%{$request->q}%");
        if ($tab !== 'todos') $query->where('tipo',$tab);

        $personas = $query->orderBy('favorito','desc')->orderBy('nombre')->get();

        $statsPorTipo = DB::table('personas')->where('usuario_id',$uid)->where('activo',1)
            ->selectRaw('tipo, COUNT(*) as c')->groupBy('tipo')->pluck('c','tipo');

        $pagadoMes = DB::table('persona_pagos as p')
            ->join('personas as pe','pe.id','=','p.persona_id')
            ->where('p.usuario_id',$uid)
            ->whereMonth('p.fecha',now()->month)
            ->whereYear('p.fecha',now()->year)
            ->sum('p.valor');

        $ultimasLabores = DB::table('persona_labores as l')
            ->join('personas as p','p.id','=','l.persona_id')
            ->leftJoin('cultivos as c','c.id','=','l.cultivo_id')
            ->where('l.usuario_id',$uid)
            ->select('l.*','p.nombre as persona_nombre','c.nombre as cultivo_nombre')
            ->orderBy('l.fecha','desc')->limit(5)->get();

        $cultivos = DB::table('cultivos')->where('usuario_id',$uid)->where('estado','activo')->orderBy('nombre')->get();
        $animales = DB::table('animales')->where('usuario_id',$uid)->where('estado','activo')->orderBy('nombre_lote')->get();
        $tipos    = $this->tipos();

        return view('pages.personas', compact(
            'personas','statsPorTipo','pagadoMes','ultimasLabores',
            'cultivos','animales','tipos','tab'
        ));
    }

    /* ── DETALLE ── */
    public function show($id)
    {
        $uid     = session('usuario_id');
        $persona = DB::table('personas')->where('id',$id)->where('usuario_id',$uid)->first();
        if (!$persona) abort(404);

        $pagos = DB::table('persona_pagos as p')
            ->leftJoin('cultivos as c','c.id','=','p.cultivo_id')
            ->leftJoin('animales as a','a.id','=','p.animal_id')
            ->where('p.persona_id',$id)->where('p.usuario_id',$uid)
            ->select('p.*','c.nombre as cultivo_nombre','a.nombre_lote as animal_nombre')
            ->orderBy('p.fecha','desc')->get();

        $labores = DB::table('persona_labores as l')
            ->leftJoin('cultivos as c','c.id','=','l.cultivo_id')
            ->leftJoin('animales as a','a.id','=','l.animal_id')
            ->where('l.persona_id',$id)->where('l.usuario_id',$uid)
            ->select('l.*','c.nombre as cultivo_nombre','a.nombre_lote as animal_nombre')
            ->orderBy('l.fecha','desc')->get();

        $totalPagado    = $pagos->sum('valor');
        $totalPagadoMes = $pagos->filter(fn($p) => \Carbon\Carbon::parse($p->fecha)->isCurrentMonth())->sum('valor');
        $totalDias      = $pagos->where('tipo_pago','jornal')->sum('dias');

        $cultivos = DB::table('cultivos')->where('usuario_id',$uid)->where('estado','activo')->orderBy('nombre')->get();
        $animales = DB::table('animales')->where('usuario_id',$uid)->where('estado','activo')->orderBy('nombre_lote')->get();
        $tipos    = $this->tipos();

        return view('pages.persona-detalle', compact(
            'persona','pagos','labores',
            'totalPagado','totalPagadoMes','totalDias',
            'cultivos','animales','tipos'
        ));
    }

    /* ── CREAR ── */
    public function store(Request $request)
    {
        $request->validate(['nombre'=>'required','tipo'=>'required']);
        $uid  = session('usuario_id');
        $foto = null;
        if ($request->hasFile('foto')) $foto = $this->guardarImagen($request->file('foto'));

        DB::table('personas')->insert([
            'usuario_id'    => $uid,
            'tipo'          => $request->tipo,
            'nombre'        => $request->nombre,
            'telefono'      => $request->telefono,
            'email'         => $request->email,
            'documento'     => $request->documento,
            'direccion'     => $request->direccion,
            'foto'          => $foto,
            'cargo'         => $request->cargo,
            'tipo_contrato' => $request->tipo_contrato ?: null,
            'valor_jornal'  => $request->valor_jornal  ?: null,
            'valor_mensual' => $request->valor_mensual ?: null,
            'fecha_ingreso' => $request->fecha_ingreso ?: null,
            'labores'       => $request->labores,
            'notas'         => $request->notas,
            'activo'        => 1,
            'favorito'      => 0,
            'creado_en'     => now()->toDateTimeString(),
            'actualizado_en'=> now()->toDateTimeString(),
        ]);

        return redirect()->route('personas.index')
            ->with('msg','Persona registrada.')->with('msgType','success');
    }

    /* ── ACTUALIZAR ── */
    public function update(Request $request, $id)
    {
        $request->validate(['nombre'=>'required','tipo'=>'required']);
        $uid     = session('usuario_id');
        $persona = DB::table('personas')->where('id',$id)->where('usuario_id',$uid)->first();
        if (!$persona) abort(404);

        $data = [
            'tipo'          => $request->tipo,
            'nombre'        => $request->nombre,
            'telefono'      => $request->telefono,
            'email'         => $request->email,
            'documento'     => $request->documento,
            'direccion'     => $request->direccion,
            'cargo'         => $request->cargo,
            'tipo_contrato' => $request->tipo_contrato ?: null,
            'valor_jornal'  => $request->valor_jornal  ?: null,
            'valor_mensual' => $request->valor_mensual ?: null,
            'fecha_ingreso' => $request->fecha_ingreso ?: null,
            'labores'       => $request->labores,
            'notas'         => $request->notas,
            'actualizado_en'=> now()->toDateTimeString(),
        ];

        if ($request->hasFile('foto')) {
            $this->eliminarImagen($persona->foto ?? null);
            $data['foto'] = $this->guardarImagen($request->file('foto'));
        }

        DB::table('personas')->where('id',$id)->where('usuario_id',$uid)->update($data);

        $back = $request->input('back','list');
        if ($back === 'detalle') {
            return redirect()->route('personas.show',$id)
                ->with('msg','Persona actualizada.')->with('msgType','success');
        }
        return redirect()->route('personas.index')
            ->with('msg','Persona actualizada.')->with('msgType','success');
    }

    /* ── ELIMINAR ── */
    public function destroy($id)
    {
        $uid     = session('usuario_id');
        $persona = DB::table('personas')->where('id',$id)->where('usuario_id',$uid)->first();
        if ($persona) $this->eliminarImagen($persona->foto ?? null);
        DB::table('personas')->where('id',$id)->where('usuario_id',$uid)->update(['activo'=>0]);
        return redirect()->route('personas.index')
            ->with('msg','Persona eliminada.')->with('msgType','warning');
    }

    /* ── TOGGLE FAVORITO ── */
    public function toggleFavorito($id)
    {
        $uid = session('usuario_id');
        $p   = DB::table('personas')->where('id',$id)->where('usuario_id',$uid)->first();
        if ($p) DB::table('personas')->where('id',$id)->update(['favorito'=> $p->favorito ? 0 : 1]);
        return back();
    }

    /* ── REGISTRAR PAGO ── */
    public function storePago(Request $request, $id)
    {
        $request->validate([
            'valor'    => 'required|numeric|min:0.01',
            'fecha'    => 'required|date',
            'tipo_pago'=> 'required',
        ]);

        $uid     = session('usuario_id');
        $persona = DB::table('personas')->where('id',$id)->where('usuario_id',$uid)->first();
        if (!$persona) abort(404);

        // 1. Guardar el pago
        DB::table('persona_pagos')->insert([
            'persona_id' => $id,
            'usuario_id' => $uid,
            'tipo_pago'  => $request->tipo_pago,
            'dias'       => $request->dias ?: null,
            'valor'      => $request->valor,
            'fecha'      => $request->fecha,
            'cultivo_id' => $request->cultivo_id ?: null,
            'animal_id'  => $request->animal_id  ?: null,
            'concepto'   => $request->concepto,
            'notas'      => $request->notas,
            'creado_en'  => now()->toDateTimeString(),
        ]);

        // 2. Crear gasto automático SIEMPRE — mano de obra es un costo real
        //    independiente de si está vinculado a un cultivo o no.
        DB::table('gastos')->insert([
            'usuario_id'     => $uid,
            'persona_id'     => $id,
            'cultivo_id'     => $request->cultivo_id ?: null,
            'animal_id'      => $request->animal_id  ?: null,
            'categoria'      => 'Mano de obra',
            'descripcion'    => $request->concepto
                                    ? $request->concepto . ' — ' . $persona->nombre
                                    : 'Pago a ' . $persona->nombre,
            'cantidad'       => $request->dias ?: null,
            'unidad_cantidad'=> $request->dias ? 'días' : null,
            'valor'          => $request->valor,
            'fecha'          => $request->fecha,
            'notas'          => 'Generado automáticamente desde pago a persona #' . $id,
            'pendiente_sync' => 0,
            'creado_en'      => now()->toDateTimeString(),
        ]);

        return redirect()->route('personas.show',$id)
            ->with('msg','Pago registrado y gasto de mano de obra creado automáticamente.')
            ->with('msgType','success');
    }

    /* ── ELIMINAR PAGO ── */
    public function destroyPago($personaId, $pagoId)
    {
        $uid = session('usuario_id');

        // Obtener el pago antes de eliminar para ubicar el gasto asociado
        $pago = DB::table('persona_pagos')
            ->where('id', $pagoId)
            ->where('persona_id', $personaId)
            ->where('usuario_id', $uid)
            ->first();

        if ($pago) {
            // Eliminar el gasto automático asociado (mismo valor, fecha y persona_id)
            DB::table('gastos')
                ->where('usuario_id', $uid)
                ->where('persona_id', $personaId)
                ->where('valor', $pago->valor)
                ->where('fecha', $pago->fecha)
                ->where('categoria', 'Mano de obra')
                ->delete();

            DB::table('persona_pagos')
                ->where('id', $pagoId)
                ->where('persona_id', $personaId)
                ->where('usuario_id', $uid)
                ->delete();
        }

        return redirect()->route('personas.show', $personaId)
            ->with('msg','Pago y gasto asociado eliminados.')
            ->with('msgType','warning');
    }

    /* ── REGISTRAR LABOR ── */
    public function storeLabor(Request $request, $id)
    {
        $request->validate(['descripcion'=>'required','fecha'=>'required|date']);

        DB::table('persona_labores')->insert([
            'persona_id'    => $id,
            'usuario_id'    => session('usuario_id'),
            'fecha'         => $request->fecha,
            'descripcion'   => $request->descripcion,
            'cultivo_id'    => $request->cultivo_id ?: null,
            'animal_id'     => $request->animal_id  ?: null,
            'horas'         => $request->horas ?: null,
            'insumos_usados'=> $request->insumos_usados,
            'notas'         => $request->notas,
            'creado_en'     => now()->toDateTimeString(),
        ]);

        return redirect()->route('personas.show',$id)
            ->with('msg','Labor registrada.')->with('msgType','success');
    }

    /* ── ELIMINAR LABOR ── */
    public function destroyLabor($personaId, $laborId)
    {
        DB::table('persona_labores')
            ->where('id', $laborId)
            ->where('persona_id', $personaId)
            ->where('usuario_id', session('usuario_id'))
            ->delete();

        return redirect()->route('personas.show', $personaId)
            ->with('msg','Labor eliminada.')->with('msgType','warning');
    }
}