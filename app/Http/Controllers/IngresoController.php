<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IngresoController extends Controller
{
    private function tipos(): array
    {
        return [
            'venta'         => ['label' => 'Venta',              'emoji' => '💰'],
            'cosecha_propia'=> ['label' => 'Cosecha / Producción','emoji' => '🌾'],
            'animal'        => ['label' => 'Venta de animal',    'emoji' => '🐄'],
            'subproducto'   => ['label' => 'Subproducto',        'emoji' => '🥚'],
            'subsidio'      => ['label' => 'Subsidio / Ayuda',   'emoji' => '🏛️'],
            'servicio'      => ['label' => 'Servicio prestado',  'emoji' => '🔧'],
            'alquiler'      => ['label' => 'Alquiler de tierra/equipo','emoji'=>'🏡'],
            'otro'          => ['label' => 'Otro',               'emoji' => '📦'],
        ];
    }

    private function guardarImagen($file, string $subdir = 'ingresos'): string
    {
        $carpeta = public_path("img/{$subdir}");
        if (!file_exists($carpeta)) mkdir($carpeta, 0775, true);
        $nombre = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($carpeta, $nombre);
        return "img/{$subdir}/{$nombre}";
    }

    private function eliminarImagen(?string $ruta): void
    {
        if ($ruta) { $f = public_path($ruta); if (file_exists($f)) unlink($f); }
    }

    /* ── LISTADO ── */
    public function index(Request $request)
    {
        $uid = session('usuario_id');

        $query = DB::table('ingresos as i')
            ->leftJoin('cultivos as c',  'c.id',  '=', 'i.cultivo_id')
            ->leftJoin('animales as a',  'a.id',  '=', 'i.animal_id')
            ->leftJoin('cosechas as co', 'co.id', '=', 'i.cosecha_id')
            ->where('i.usuario_id', $uid)
            ->select(
                'i.*',
                'c.nombre as cultivo_nombre',
                'a.nombre_lote as animal_nombre', 'a.especie as animal_especie',
                'co.producto as cosecha_nombre'
            );

        if ($request->q) {
            $q = $request->q;
            $query->where(fn($w) => $w
                ->where('i.descripcion','like',"%$q%")
                ->orWhere('i.comprador','like',"%$q%"));
        }
        if ($request->mes)  $query->whereRaw("DATE_FORMAT(i.fecha,'%Y-%m') = ?", [$request->mes]);
        if ($request->tipo) $query->where('i.tipo', $request->tipo);
        if ($request->asociado) {
            if ($request->asociado === 'cultivo')  $query->whereNotNull('i.cultivo_id');
            if ($request->asociado === 'animal')   $query->whereNotNull('i.animal_id');
            if ($request->asociado === 'cosecha')  $query->whereNotNull('i.cosecha_id');
        }

        $ingresos  = $query->orderBy('i.fecha','desc')->get();

        $totalMes  = DB::table('ingresos')->where('usuario_id',$uid)
            ->whereMonth('fecha',now()->month)->whereYear('fecha',now()->year)->sum('valor_total');
        $totalAnio = DB::table('ingresos')->where('usuario_id',$uid)
            ->whereYear('fecha',now()->year)->sum('valor_total');

        // Stats por tipo este mes
        $statsTipo = DB::table('ingresos')->where('usuario_id',$uid)
            ->whereMonth('fecha',now()->month)->whereYear('fecha',now()->year)
            ->selectRaw('tipo, SUM(valor_total) as total, COUNT(*) as cnt')
            ->groupBy('tipo')->orderByDesc('total')->get();

        // Top compradores
        $topCompradores = DB::table('ingresos')->where('usuario_id',$uid)
            ->whereYear('fecha',now()->year)
            ->whereNotNull('comprador')
            ->selectRaw('comprador, SUM(valor_total) as total, COUNT(*) as cnt')
            ->groupBy('comprador')->orderByDesc('total')->limit(5)->get();

        $cultivos = DB::table('cultivos')->where('usuario_id',$uid)->orderBy('nombre')->get();
        $animales = DB::table('animales')->where('usuario_id',$uid)->where('estado','activo')->orderBy('nombre_lote')->get();
        $cosechas = DB::table('cosechas')->where('usuario_id',$uid)->orderBy('fecha_cosecha','desc')->limit(30)->get();
        $tipos    = $this->tipos();

        try {
            $clientes = DB::table('personas')->where('usuario_id',$uid)->where('tipo','comprador')->where('activo',1)->orderBy('nombre')->get();
        } catch (\Exception $e) { $clientes = collect(); }

        return view('pages.ingresos', compact(
            'ingresos','totalMes','totalAnio','statsTipo','topCompradores',
            'cultivos','animales','cosechas','tipos','clientes'
        ));
    }

    /* ── CREAR ── */
    public function store(Request $request)
    {
        $request->validate(['descripcion'=>'required','valor_total'=>'required|numeric']);
        $uid  = session('usuario_id');
        $cant = $request->cantidad;
        $pu   = $request->precio_unitario;
        $total = ($cant && $pu) ? $cant * $pu : $request->valor_total;

        $foto = null;
        if ($request->hasFile('foto_soporte')) {
            $foto = $this->guardarImagen($request->file('foto_soporte'), 'ingresos/soportes');
        }

        // Si seleccionó cliente guardado, usar su nombre
        $comprador = $request->comprador;
        if ($request->cliente_id) {
            $cli = DB::table('clientes')->find($request->cliente_id);
            if ($cli) $comprador = $cli->nombre;
        }

        DB::table('ingresos')->insert([
            'usuario_id'     => $uid,
            'descripcion'    => $request->descripcion,
            'cantidad'       => $cant ?: null,
            'unidad'         => $request->unidad,
            'precio_unitario'=> $pu ?: null,
            'valor_total'    => $total,
            'fecha'          => $request->fecha ?? now()->toDateString(),
            'comprador'      => $comprador,
            'persona_id' => $request->persona_id ?: null,
            'tipo'           => $request->tipo ?? 'venta',
            'cultivo_id'     => $request->cultivo_id ?: null,
            'animal_id'      => $request->animal_id ?: null,
            'cosecha_id'     => $request->cosecha_id ?: null,
            'notas'          => $request->notas,
            'foto_soporte'   => $foto,
            'creado_en'      => now()->toDateTimeString(),
        ]);

        return redirect()->route('ingresos.index')->with('msg','Ingreso registrado.')->with('msgType','success');
    }

    /* ── ACTUALIZAR ── */
    public function update(Request $request, $id)
    {
        $request->validate(['descripcion'=>'required','valor_total'=>'required|numeric']);
        $uid    = session('usuario_id');
        $ingreso = DB::table('ingresos')->where('id',$id)->where('usuario_id',$uid)->first();
        if (!$ingreso) abort(404);

        $data = [
            'descripcion'    => $request->descripcion,
            'cantidad'       => $request->cantidad ?: null,
            'unidad'         => $request->unidad,
            'precio_unitario'=> $request->precio_unitario ?: null,
            'valor_total'    => $request->valor_total,
            'fecha'          => $request->fecha,
            'comprador'      => $request->comprador,
            'persona_id' => $request->persona_id ?: null,
            'tipo'           => $request->tipo ?? 'venta',
            'cultivo_id'     => $request->cultivo_id ?: null,
            'animal_id'      => $request->animal_id ?: null,
            'cosecha_id'     => $request->cosecha_id ?: null,
            'notas'          => $request->notas,
        ];

        if ($request->hasFile('foto_soporte')) {
            $this->eliminarImagen($ingreso->foto_soporte ?? null);
            $data['foto_soporte'] = $this->guardarImagen($request->file('foto_soporte'), 'ingresos/soportes');
        }

        DB::table('ingresos')->where('id',$id)->where('usuario_id',$uid)->update($data);
        return redirect()->route('ingresos.index')->with('msg','Ingreso actualizado.')->with('msgType','success');
    }

    /* ── ELIMINAR ── */
    public function destroy($id)
    {
        $uid    = session('usuario_id');
        $ingreso = DB::table('ingresos')->where('id',$id)->where('usuario_id',$uid)->first();
        if ($ingreso) $this->eliminarImagen($ingreso->foto_soporte ?? null);
        DB::table('ingresos')->where('id',$id)->where('usuario_id',$uid)->delete();
        return redirect()->route('ingresos.index')->with('msg','Ingreso eliminado.')->with('msgType','warning');
    }

    /* ── CLIENTES ── */
    public function storeCliente(Request $request)
    {
        $request->validate(['nombre'=>'required|string|max:150']);
        try {
            DB::table('personas')->insert([
                'usuario_id' => session('usuario_id'),
                'nombre'     => $request->nombre,
                'telefono'   => $request->telefono,
                'email'      => $request->email,
                'direccion'  => $request->direccion,
                'tipo'       => $request->tipo,
                'notas'      => $request->notas,
                'activo'     => 1,
                'creado_en'  => now()->toDateTimeString(),
            ]);
        } catch (\Exception $e) {
            return redirect()->route('ingresos.index')
                ->with('msg','⚠️ Ejecuta el archivo ingresos_upgrade.sql en tu base de datos primero.')
                ->with('msgType','error');
        }
        return redirect()->route('ingresos.index')->with('msg','Cliente guardado.')->with('msgType','success');
    }

    public function destroyCliente($id)
    {
        DB::table('personas')->where('id',$id)->where('tipo','comprador')->where('usuario_id',session('usuario_id'))->update(['activo'=>0]);
        return redirect()->route('ingresos.index')->with('msg','Cliente eliminado.')->with('msgType','warning');
    }
}