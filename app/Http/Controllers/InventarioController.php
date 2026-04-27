<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    private function categorias(): array {
        return [
            '🌱 Cultivos'  => ['Semillas','Fertilizantes','Abonos','Plaguicidas','Herbicidas','Fungicidas'],
            '🐄 Animales'  => ['Alimento animal','Medicamentos veterinarios','Vacunas animales','Suplementos'],
            '🔧 Finca'     => ['Herramientas','Equipos','Combustible','Repuestos','Empaques'],
            '📦 General'   => ['Otros'],
        ];
    }

    private function categoriasPlanas(): array {
        $r = [];
        foreach ($this->categorias() as $g => $items) foreach ($items as $c) $r[] = $c;
        return $r;
    }

    private function guardarImagen($file, string $sub='inventario'): string {
        $dir = public_path("img/{$sub}");
        if (!file_exists($dir)) mkdir($dir, 0775, true);
        $nombre = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
        $file->move($dir, $nombre);
        return "img/{$sub}/{$nombre}";
    }

    private function eliminarImagen(?string $ruta): void {
        if ($ruta) { $f = public_path($ruta); if (file_exists($f)) unlink($f); }
    }

    public function index(Request $request)
    {
        $uid   = session('usuario_id');
        $query = DB::table('inventario as i')->where('i.usuario_id', $uid);

        if ($request->q)      $query->where('i.nombre','like',"%{$request->q}%");
        if ($request->cat)    $query->where('i.categoria',$request->cat);
        if ($request->uso)    $query->where('i.uso_principal',$request->uso);
        if ($request->alerta) $query->whereRaw('i.cantidad_actual <= i.stock_minimo');

        $insumos = $query->orderBy('i.nombre')->get();

        $alertasStock    = $insumos->filter(fn($i) => $i->cantidad_actual <= $i->stock_minimo)->count();
        $porVencer       = $insumos->filter(fn($i) => $i->fecha_vencimiento
            && \Carbon\Carbon::parse($i->fecha_vencimiento)->diffInDays(now()) <= 30
            && \Carbon\Carbon::parse($i->fecha_vencimiento)->isFuture())->count();
        $valorInventario = $insumos->sum(fn($i) => $i->cantidad_actual * ($i->precio_unitario ?? 0));
        $totalInsumos    = $insumos->count();

        $movimientos = DB::table('inventario_movimientos as m')
            ->join('inventario as i','i.id','=','m.inventario_id')
            ->leftJoin('cultivos as c','c.id','=','m.cultivo_id')
            ->leftJoin('animales as a','a.id','=','m.animal_id')
            ->leftJoin('personas as per','per.id','=','m.persona_id')
            ->where('m.usuario_id',$uid)
            ->select('m.*','i.nombre as insumo_nombre','i.unidad',
                     'c.nombre as cultivo_nombre',
                     'a.nombre_lote as animal_nombre','a.especie as animal_especie',
                     'per.nombre as persona_nombre')
            ->orderBy('m.creado_en','desc')->limit(15)->get();

        $cultivos     = DB::table('cultivos')->where('usuario_id',$uid)->where('estado','activo')->orderBy('nombre')->get();
        $animales     = DB::table('animales')->where('usuario_id',$uid)->where('estado','activo')->orderBy('nombre_lote')->get();
        $categorias   = $this->categorias();
        $catPlanas    = $this->categoriasPlanas();
        $trabajadores = DB::table('personas')->where('usuario_id',$uid)->where('activo',1)->orderBy('nombre')->get();

        return view('pages.inventario', compact(
            'insumos','totalInsumos','alertasStock','porVencer','valorInventario',
            'movimientos','cultivos','animales','categorias','catPlanas','trabajadores'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'          => 'required|min:2',
            'categoria'       => 'required',
            'cantidad_actual' => 'required|numeric|min:0',
            'stock_minimo'    => 'required|numeric|min:0',
            'unidad'          => 'required',
        ]);

        $uid  = session('usuario_id');
        $foto = null;
        if ($request->hasFile('foto')) $foto = $this->guardarImagen($request->file('foto'),'inventario');

        $id = DB::table('inventario')->insertGetId([
            'usuario_id'       => $uid,
            'nombre'           => $request->nombre,
            'categoria'        => $request->categoria,
            'cantidad_actual'  => $request->cantidad_actual,
            'stock_minimo'     => $request->stock_minimo,
            'unidad'           => $request->unidad,
            'precio_unitario'  => $request->precio_unitario ?: null,
            'proveedor'        => $request->proveedor ?: null,
            'fecha_vencimiento'=> $request->fecha_vencimiento ?: null,
            'notas'            => $request->notas ?: null,
            'foto'             => $foto,
            'ubicacion'        => $request->ubicacion ?: null,
            'uso_principal'    => $request->uso_principal ?? 'general',
            'creado_en'        => now()->toDateTimeString(),
            'actualizado_en'   => now()->toDateTimeString(),
        ]);

        if ((float)$request->cantidad_actual > 0) {
            DB::table('inventario_movimientos')->insert([
                'inventario_id'  => $id,
                'usuario_id'     => $uid,
                'tipo'           => 'entrada',
                'cantidad'       => $request->cantidad_actual,
                'precio_unitario'=> $request->precio_unitario ?: null,
                'motivo'         => 'Stock inicial',
                'persona'        => $request->persona_registro ?: null,
                'fecha'          => now()->toDateString(),
                'creado_en'      => now()->toDateTimeString(),
            ]);
        }

        return redirect()->route('inventario.index')
            ->with('msg','Insumo registrado correctamente.')
            ->with('msgType','success');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre'      => 'required|min:2',
            'categoria'   => 'required',
            'stock_minimo'=> 'required|numeric|min:0',
            'unidad'      => 'required',
        ]);

        $uid    = session('usuario_id');
        $insumo = DB::table('inventario')->where('id',$id)->where('usuario_id',$uid)->first();
        if (!$insumo) abort(404);

        $data = [
            'nombre'           => $request->nombre,
            'categoria'        => $request->categoria,
            'stock_minimo'     => $request->stock_minimo,
            'unidad'           => $request->unidad,
            'precio_unitario'  => $request->precio_unitario ?: null,
            'proveedor'        => $request->proveedor ?: null,
            'fecha_vencimiento'=> $request->fecha_vencimiento ?: null,
            'notas'            => $request->notas ?: null,
            'ubicacion'        => $request->ubicacion ?: null,
            'uso_principal'    => $request->uso_principal ?? 'general',
            'actualizado_en'   => now()->toDateTimeString(),
        ];

        if ($request->hasFile('foto')) {
            $this->eliminarImagen($insumo->foto ?? null);
            $data['foto'] = $this->guardarImagen($request->file('foto'),'inventario');
        }

        DB::table('inventario')->where('id',$id)->where('usuario_id',$uid)->update($data);

        return redirect()->route('inventario.index')
            ->with('msg','Insumo actualizado.')
            ->with('msgType','success');
    }

    public function destroy($id)
    {
        $uid    = session('usuario_id');
        $insumo = DB::table('inventario')->where('id',$id)->where('usuario_id',$uid)->first();
        if ($insumo) $this->eliminarImagen($insumo->foto ?? null);
        DB::table('inventario')->where('id',$id)->where('usuario_id',$uid)->delete();

        return redirect()->route('inventario.index')
            ->with('msg','Insumo eliminado.')
            ->with('msgType','warning');
    }

    public function movimiento(Request $request, $id)
    {
        $request->validate([
            'tipo'     => 'required|in:entrada,salida,ajuste,en_uso,devolucion',
            'cantidad' => 'required|numeric|min:0.01',
            'fecha'    => 'required|date',
        ]);

        $uid    = session('usuario_id');
        $insumo = DB::table('inventario')->where('id',$id)->where('usuario_id',$uid)->first();
        if (!$insumo) abort(404);

        $cantidad = (float) $request->cantidad;

        // en_uso y devolucion NO modifican el stock físico
        $nueva = match($request->tipo) {
            'entrada'    => $insumo->cantidad_actual + $cantidad,
            'salida'     => max(0, $insumo->cantidad_actual - $cantidad),
            'ajuste'     => $cantidad,
            'en_uso'     => $insumo->cantidad_actual,
            'devolucion' => $insumo->cantidad_actual,
            default      => $insumo->cantidad_actual,
        };

        DB::table('inventario')->where('id',$id)->update([
            'cantidad_actual' => $nueva,
            'actualizado_en'  => now()->toDateTimeString(),
        ]);

        $fotoSoporte = null;
        if ($request->hasFile('foto_soporte')) {
            $fotoSoporte = $this->guardarImagen($request->file('foto_soporte'),'inventario/soportes');
        }

        DB::table('inventario_movimientos')->insert([
            'inventario_id'  => $id,
            'usuario_id'     => $uid,
            'cultivo_id'     => $request->cultivo_id ?: null,
            'animal_id'      => $request->animal_id  ?: null,
            'persona_id'     => $request->persona_id ?: null,
            'tipo'           => $request->tipo,
            'cantidad'       => $cantidad,
            'precio_unitario'=> $request->precio_unitario ?: null,
            'motivo'         => $request->motivo ?: null,
            'persona'        => $request->persona ?: null,
            'foto_soporte'   => $fotoSoporte,
            'fecha'          => $request->fecha,
            'creado_en'      => now()->toDateTimeString(),
        ]);

        // ── GASTO AUTOMÁTICO al registrar una entrada con precio ──────────
        // Se crea siempre que haya precio_unitario, sin necesidad de checkbox.
        if ($request->tipo === 'entrada' && $request->precio_unitario) {
            DB::table('gastos')->insert([
                'usuario_id'      => $uid,
                'cultivo_id'      => $request->cultivo_id ?: null,
                'animal_id'       => $request->animal_id  ?: null,
                'categoria'       => $insumo->categoria,
                'descripcion'     => 'Compra de ' . $insumo->nombre,
                'cantidad'        => $cantidad,
                'unidad_cantidad' => $insumo->unidad,
                'valor'           => $cantidad * (float) $request->precio_unitario,
                'fecha'           => $request->fecha,
                'proveedor'       => $request->proveedor_mov ?: $insumo->proveedor,
                'notas'           => 'Generado automáticamente desde inventario',
                'pendiente_sync'  => 0,
                'creado_en'       => now()->toDateTimeString(),
            ]);
        }
        // ─────────────────────────────────────────────────────────────────

        $msg = match($request->tipo) {
            'entrada'    => "Entrada registrada. Stock: {$nueva} {$insumo->unidad}",
            'salida'     => "Salida registrada. Stock: {$nueva} {$insumo->unidad}",
            'ajuste'     => "Stock ajustado a: {$nueva} {$insumo->unidad}",
            'en_uso'     => "Marcado en uso. Stock disponible: {$nueva} {$insumo->unidad}",
            'devolucion' => "Devolución registrada. Stock: {$nueva} {$insumo->unidad}",
            default      => "Movimiento registrado. Stock: {$nueva} {$insumo->unidad}",
        };

        if ($nueva <= $insumo->stock_minimo) $msg .= " ⚠️ Stock bajo mínimo.";

        return redirect()->route('inventario.index')
            ->with('msg', $msg)
            ->with('msgType', $nueva <= $insumo->stock_minimo ? 'warning' : 'success');
    }

    public function alertas()
    {
        $uid = session('usuario_id');

        $alertasStock = DB::table('inventario')
            ->where('usuario_id',$uid)
            ->whereRaw('cantidad_actual <= stock_minimo')
            ->orderByRaw('(cantidad_actual / GREATEST(stock_minimo, 0.01)) ASC')
            ->get();

        $porVencer = DB::table('inventario')
            ->where('usuario_id',$uid)
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento','>=',now()->toDateString())
            ->whereDate('fecha_vencimiento','<=',now()->addDays(30)->toDateString())
            ->orderBy('fecha_vencimiento')
            ->get();

        $vencidos = DB::table('inventario')
            ->where('usuario_id',$uid)
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento','<',now()->toDateString())
            ->orderBy('fecha_vencimiento')
            ->get();

        return view('pages.inventario-alertas', compact('alertasStock','porVencer','vencidos'));
    }
}