<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GastoController extends Controller
{
    /** Categorías completas */
    private function categorias(): array
    {
        return [
            '🌱 Siembra'          => ['Semillas','Trasplante','Preparación de suelo'],
            '🌿 Nutrición'        => ['Fertilizantes','Abonos orgánicos','Correctivos de suelo'],
            '🧴 Sanidad vegetal'  => ['Plaguicidas','Herbicidas','Fungicidas','Insecticidas'],
            '💧 Agua y riego'     => ['Riego','Infraestructura hídrica'],
            '🐄 Ganadería'        => ['Alimento animal','Veterinario','Medicamentos animales','Vacunas'],
            '👷 Personal'         => ['Mano de obra','Jornales','Administración'],
            '🚛 Logística'        => ['Transporte','Combustible','Fletes'],
            '🔧 Infraestructura'  => ['Herramientas','Maquinaria','Mantenimiento','Arriendo de maquinaria'],
            '🏡 Finca'            => ['Arriendo de tierra','Servicios públicos','Seguros','Impuestos'],
            '📦 Otros'            => ['Otros'],
        ];
    }

    private function categoriasPlanas(): array
    {
        $lista = [];
        foreach ($this->categorias() as $items) {
            foreach ($items as $item) $lista[] = $item;
        }
        return $lista;
    }

    private function guardarImagen($file, string $subdir = 'gastos'): string
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

        $query = DB::table('gastos as g')
            ->leftJoin('cultivos as c',  'c.id',  '=', 'g.cultivo_id')
            ->leftJoin('animales as a',  'a.id',  '=', 'g.animal_id')
            ->leftJoin('cosechas as co', 'co.id', '=', 'g.cosecha_id')
            ->leftJoin('personas as p','p.id','=','g.persona_id')
            ->where('g.usuario_id', $uid)
            ->select(
                'g.*',
                'c.nombre  as cultivo_nombre',
                'a.nombre_lote as animal_nombre', 'a.especie as animal_especie',
                'co.producto as cosecha_nombre',
                'p.nombre as proveedor_nombre_bd','p.tipo as proveedor_tipo'
            );

        if ($request->q) {
            $q = $request->q;
            $query->where(fn($w) => $w
                ->where('g.descripcion','like',"%$q%")
                ->orWhere('g.categoria','like',"%$q%")
                ->orWhere('g.proveedor','like',"%$q%"));
        }
        if ($request->mes) $query->whereRaw("DATE_FORMAT(g.fecha,'%Y-%m') = ?", [$request->mes]);
        if ($request->cat) $query->where('g.categoria', $request->cat);
        if ($request->asociado) {
            if ($request->asociado === 'cultivo')  $query->whereNotNull('g.cultivo_id');
            if ($request->asociado === 'animal')   $query->whereNotNull('g.animal_id');
            if ($request->asociado === 'cosecha')  $query->whereNotNull('g.cosecha_id');
            if ($request->asociado === 'ninguno')  $query->whereNull('g.cultivo_id')->whereNull('g.animal_id')->whereNull('g.cosecha_id');
        }

        $gastos    = $query->orderBy('g.fecha','desc')->get();
        $totalMes  = DB::table('gastos')->where('usuario_id',$uid)->whereMonth('fecha',now()->month)->whereYear('fecha',now()->year)->sum('valor');
        $totalAnio = DB::table('gastos')->where('usuario_id',$uid)->whereYear('fecha',now()->year)->sum('valor');
        $statsCat  = DB::table('gastos')->where('usuario_id',$uid)
            ->whereMonth('fecha',now()->month)->whereYear('fecha',now()->year)
            ->selectRaw('categoria, SUM(valor) as total')->groupBy('categoria')
            ->orderByDesc('total')->limit(5)->get();

        $cultivos     = DB::table('cultivos')->where('usuario_id',$uid)->where('estado','activo')->orderBy('nombre')->get();
        $animales     = DB::table('animales')->where('usuario_id',$uid)->where('estado','activo')->orderBy('nombre_lote')->get();
        $cosechas     = DB::table('cosechas')->where('usuario_id',$uid)->orderBy('fecha_cosecha','desc')->limit(20)->get();
        $tareas       = DB::table('tareas')->where('usuario_id',$uid)->where('completada',0)->orderBy('fecha')->get();
        $categorias   = $this->categorias();
        $categoriasPlanas = $this->categoriasPlanas();

        try {
            $proveedores = DB::table('personas')->where('usuario_id',$uid)->where('tipo','proveedor')->where('activo',1)->orderBy('nombre')->get();
        } catch (\Exception $e) { $proveedores = collect(); }

        try {
            $recurrentes = DB::table('gastos_recurrentes')->where('usuario_id',$uid)->where('activo',1)->orderBy('proximo_vencimiento')->get();
        } catch (\Exception $e) { $recurrentes = collect(); }

        return view('pages.gastos', compact(
            'gastos','totalMes','totalAnio','statsCat',
            'cultivos','animales','cosechas','tareas',
            'categorias','categoriasPlanas','proveedores','recurrentes'
        ));
    }

    /* ── CREAR ── */
    public function store(Request $request)
    {
        $request->validate(['categoria'=>'required','descripcion'=>'required','valor'=>'required|numeric']);
        $uid = session('usuario_id');

        $foto = null;
        if ($request->hasFile('foto_factura')) {
            $foto = $this->guardarImagen($request->file('foto_factura'), 'gastos/facturas');
        }

        // Si seleccionó proveedor de la BD, usar su nombre
        $proveedorNombre = $request->proveedor;
        if ($request->proveedor_id) {
            $prov = DB::table('personas')->find($request->persona_id);
            if ($prov) $proveedorNombre = $prov->nombre;
        }

        DB::table('gastos')->insert([
            'usuario_id'     => $uid,
            'categoria'      => $request->categoria,
            'descripcion'    => $request->descripcion,
            'cantidad'       => $request->cantidad ?: null,
            'unidad_cantidad'=> $request->unidad_cantidad,
            'valor'          => $request->valor,
            'fecha'          => $request->fecha ?? now()->toDateString(),
            'proveedor'      => $proveedorNombre,
            'proveedor_id'   => $request->proveedor_id ?: null,
            'cultivo_id'     => $request->cultivo_id ?: null,
            'animal_id'      => $request->animal_id ?: null,
            'cosecha_id'     => $request->cosecha_id ?: null,
            'tarea_id'       => $request->tarea_id ?: null,
            'factura_numero'  => $request->factura_numero,
            'notas'          => $request->notas,
            'foto_factura'   => $foto,
            'es_recurrente'  => 0,
            'creado_en'      => now()->toDateTimeString(),
        ]);

        return redirect()->route('gastos.index')->with('msg','Gasto registrado.')->with('msgType','success');
    }

    /* ── ACTUALIZAR ── */
    public function update(Request $request, $id)
    {
        $request->validate(['categoria'=>'required','descripcion'=>'required','valor'=>'required|numeric']);
        $uid   = session('usuario_id');
        $gasto = DB::table('gastos')->where('id',$id)->where('usuario_id',$uid)->first();
        if (!$gasto) abort(404);

        $data = [
            'categoria'      => $request->categoria,
            'descripcion'    => $request->descripcion,
            'cantidad'       => $request->cantidad ?: null,
            'unidad_cantidad'=> $request->unidad_cantidad,
            'valor'          => $request->valor,
            'fecha'          => $request->fecha,
            'proveedor'      => $request->proveedor,
            'proveedor_id'   => $request->proveedor_id ?: null,
            'cultivo_id'     => $request->cultivo_id ?: null,
            'animal_id'      => $request->animal_id ?: null,
            'cosecha_id'     => $request->cosecha_id ?: null,
            'tarea_id'       => $request->tarea_id ?: null,
            'factura_numero'  => $request->factura_numero,
            'notas'          => $request->notas,
        ];

        if ($request->hasFile('foto_factura')) {
            $this->eliminarImagen($gasto->foto_factura ?? null);
            $data['foto_factura'] = $this->guardarImagen($request->file('foto_factura'), 'gastos/facturas');
        }

        DB::table('gastos')->where('id',$id)->where('usuario_id',$uid)->update($data);
        return redirect()->route('gastos.index')->with('msg','Gasto actualizado.')->with('msgType','success');
    }

    /* ── ELIMINAR ── */
    public function destroy($id)
    {
        $uid   = session('usuario_id');
        $gasto = DB::table('gastos')->where('id',$id)->where('usuario_id',$uid)->first();
        if ($gasto) $this->eliminarImagen($gasto->foto_factura ?? null);
        DB::table('gastos')->where('id',$id)->where('usuario_id',$uid)->delete();
        return redirect()->route('gastos.index')->with('msg','Gasto eliminado.')->with('msgType','warning');
    }

    /* ── PROVEEDORES ── */
    public function storeProveedor(Request $request)
    {
        $request->validate(['nombre'=>'required|string|max:150']);
        try {
            DB::table('personas')->insert([
                'usuario_id' => session('usuario_id'),
                'nombre'     => $request->nombre,
                'telefono'   => $request->telefono,
                'email'      => $request->email,
                'direccion'  => $request->direccion,
                'categoria'  => $request->categoria,
                'notas'      => $request->notas,
                'activo'     => 1,
                'creado_en'  => now()->toDateTimeString(),
            ]);
        } catch (\Exception $e) {
            return redirect()->route('gastos.index')
                ->with('msg', '⚠️ Error: La tabla "proveedores" no existe. Ejecuta el archivo gastos_upgrade.sql en tu base de datos.')
                ->with('msgType', 'error');
        }
        return redirect()->route('gastos.index')->with('msg','Proveedor guardado.')->with('msgType','success');
    }

    public function destroyProveedor($id)
    {
        DB::table('personas')->where('id',$id)->where('tipo','proveedor')->where('usuario_id',session('usuario_id'))->update(['activo'=>0]);
        return redirect()->route('gastos.index')->with('msg','Proveedor eliminado.')->with('msgType','warning');
    }

    /* ── GASTOS RECURRENTES ── */
    public function storeRecurrente(Request $request)
    {
        $request->validate(['categoria'=>'required','descripcion'=>'required','valor'=>'required|numeric','frecuencia'=>'required']);
        $uid  = session('usuario_id');
        $dia  = $request->dia_del_mes ?? 1;

        $proximo = now()->day($dia);
        if ($proximo->isPast()) $proximo->addMonth();

        try {
            DB::table('gastos_recurrentes')->insert([
                'usuario_id'         => $uid,
                'categoria'          => $request->categoria,
                'descripcion'        => $request->descripcion,
                'valor'              => $request->valor,
                'proveedor'          => $request->proveedor,
                'proveedor_id'       => $request->proveedor_id ?: null,
                'cultivo_id'         => $request->cultivo_id ?: null,
                'animal_id'          => $request->animal_id ?: null,
                'frecuencia'         => $request->frecuencia,
                'dia_del_mes'        => $dia,
                'activo'             => 1,
                'proximo_vencimiento'=> $proximo->toDateString(),
                'notas'              => $request->notas,
                'creado_en'          => now()->toDateTimeString(),
            ]);
        } catch (\Exception $e) {
            return redirect()->route('gastos.index')
                ->with('msg', '⚠️ Error: La tabla "gastos_recurrentes" no existe. Ejecuta el archivo gastos_upgrade.sql en tu base de datos.')
                ->with('msgType', 'error');
        }
        return redirect()->route('gastos.index')->with('msg','Gasto recurrente creado.')->with('msgType','success');
    }

    public function generarRecurrente($id)
    {
        $uid = session('usuario_id');
        $r   = DB::table('gastos_recurrentes')->where('id',$id)->where('usuario_id',$uid)->first();
        if (!$r) abort(404);

        // Crear el gasto real
        DB::table('gastos')->insert([
            'usuario_id'    => $uid,
            'categoria'     => $r->categoria,
            'descripcion'   => $r->descripcion . ' (recurrente)',
            'valor'         => $r->valor,
            'fecha'         => now()->toDateString(),
            'proveedor'     => $r->proveedor,
            'proveedor_id'  => $r->proveedor_id,
            'cultivo_id'    => $r->cultivo_id,
            'animal_id'     => $r->animal_id,
            'es_recurrente' => 1,
            'recurrente_id' => $r->id,
            'creado_en'     => now()->toDateTimeString(),
        ]);

        // Calcular próximo
        $frecuencias = ['semanal'=>7,'quincenal'=>15,'mensual'=>1,'bimestral'=>2,'trimestral'=>3,'anual'=>12];
        $proximo = now();
        if ($r->frecuencia === 'semanal')   $proximo->addDays(7);
        elseif ($r->frecuencia === 'quincenal') $proximo->addDays(15);
        elseif ($r->frecuencia === 'mensual') $proximo->addMonth();
        elseif ($r->frecuencia === 'bimestral') $proximo->addMonths(2);
        elseif ($r->frecuencia === 'trimestral') $proximo->addMonths(3);
        elseif ($r->frecuencia === 'anual') $proximo->addYear();

        DB::table('gastos_recurrentes')->where('id',$id)->update([
            'ultimo_generado'    => now()->toDateString(),
            'proximo_vencimiento'=> $proximo->toDateString(),
        ]);

        return redirect()->route('gastos.index')->with('msg','Gasto generado correctamente.')->with('msgType','success');
    }

    public function destroyRecurrente($id)
    {
        DB::table('gastos_recurrentes')->where('id',$id)->where('usuario_id',session('usuario_id'))->update(['activo'=>0]);
        return redirect()->route('gastos.index')->with('msg','Gasto recurrente desactivado.')->with('msgType','warning');
    }
}