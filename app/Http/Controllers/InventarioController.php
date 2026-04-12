<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    public function index(Request $request)
    {
        $uid = session('usuario_id');

        $query = DB::table('inventario as i')
            ->where('i.usuario_id', $uid);

        // Filtros
        if ($request->q)        $query->where('i.nombre', 'like', "%{$request->q}%");
        if ($request->cat)      $query->where('i.categoria', $request->cat);
        if ($request->alerta)   $query->whereRaw('i.cantidad_actual <= i.stock_minimo');

        $insumos = $query->orderBy('i.nombre')->get();

        // Métricas globales
        $totalInsumos    = $insumos->count();
        $alertasStock    = $insumos->filter(fn($i) => $i->cantidad_actual <= $i->stock_minimo)->count();
        $porVencer       = $insumos->filter(fn($i) => $i->fecha_vencimiento &&
            \Carbon\Carbon::parse($i->fecha_vencimiento)->diffInDays(now()) <= 30 &&
            \Carbon\Carbon::parse($i->fecha_vencimiento)->isFuture()
        )->count();
        $valorInventario = $insumos->sum(fn($i) => $i->cantidad_actual * ($i->precio_unitario ?? 0));

        // Últimos movimientos
        $movimientos = DB::table('inventario_movimientos as m')
            ->join('inventario as i', 'i.id', '=', 'm.inventario_id')
            ->leftJoin('cultivos as c', 'c.id', '=', 'm.cultivo_id')
            ->where('m.usuario_id', $uid)
            ->select('m.*', 'i.nombre as insumo_nombre', 'i.unidad', 'c.nombre as cultivo_nombre')
            ->orderBy('m.creado_en', 'desc')
            ->limit(10)
            ->get();

        $cultivos   = DB::table('cultivos')->where('usuario_id', $uid)->where('estado', 'activo')->orderBy('nombre')->get();
        $categorias = $this->categorias();

        return view('pages.inventario', compact(
            'insumos', 'totalInsumos', 'alertasStock', 'porVencer',
            'valorInventario', 'movimientos', 'cultivos', 'categorias'
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
        ], [
            'nombre.required'          => 'El nombre del insumo es obligatorio.',
            'categoria.required'       => 'La categoría es obligatoria.',
            'cantidad_actual.required' => 'La cantidad actual es obligatoria.',
            'stock_minimo.required'    => 'El stock mínimo es obligatorio.',
            'unidad.required'          => 'La unidad de medida es obligatoria.',
        ]);

        $uid = session('usuario_id');

        $id = DB::table('inventario')->insertGetId([
            'usuario_id'      => $uid,
            'nombre'          => $request->nombre,
            'categoria'       => $request->categoria,
            'cantidad_actual' => $request->cantidad_actual,
            'stock_minimo'    => $request->stock_minimo,
            'unidad'          => $request->unidad,
            'precio_unitario' => $request->precio_unitario ?: null,
            'proveedor'       => $request->proveedor ?: null,
            'fecha_vencimiento' => $request->fecha_vencimiento ?: null,
            'notas'           => $request->notas ?: null,
            'creado_en'       => now()->toDateTimeString(),
            'actualizado_en'  => now()->toDateTimeString(),
        ]);

        // Registrar como movimiento inicial si hay cantidad
        if ((float) $request->cantidad_actual > 0) {
            DB::table('inventario_movimientos')->insert([
                'inventario_id'   => $id,
                'usuario_id'      => $uid,
                'tipo'            => 'entrada',
                'cantidad'        => $request->cantidad_actual,
                'precio_unitario' => $request->precio_unitario ?: null,
                'motivo'          => 'Stock inicial',
                'fecha'           => now()->toDateString(),
                'creado_en'       => now()->toDateTimeString(),
            ]);
        }

        return redirect()->route('inventario.index')
            ->with('msg', 'Insumo registrado correctamente.')
            ->with('msgType', 'success');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre'      => 'required|min:2',
            'categoria'   => 'required',
            'stock_minimo'=> 'required|numeric|min:0',
            'unidad'      => 'required',
        ]);

        DB::table('inventario')
            ->where('id', $id)
            ->where('usuario_id', session('usuario_id'))
            ->update([
                'nombre'           => $request->nombre,
                'categoria'        => $request->categoria,
                'stock_minimo'     => $request->stock_minimo,
                'unidad'           => $request->unidad,
                'precio_unitario'  => $request->precio_unitario ?: null,
                'proveedor'        => $request->proveedor ?: null,
                'fecha_vencimiento'=> $request->fecha_vencimiento ?: null,
                'notas'            => $request->notas ?: null,
                'actualizado_en'   => now()->toDateTimeString(),
            ]);

        return redirect()->route('inventario.index')
            ->with('msg', 'Insumo actualizado.')
            ->with('msgType', 'success');
    }

    public function destroy($id)
    {
        DB::table('inventario')
            ->where('id', $id)
            ->where('usuario_id', session('usuario_id'))
            ->delete();

        return redirect()->route('inventario.index')
            ->with('msg', 'Insumo eliminado.')
            ->with('msgType', 'warning');
    }

    /**
     * Registrar una entrada o salida de inventario
     */
    public function movimiento(Request $request, $id)
    {
        $request->validate([
            'tipo'     => 'required|in:entrada,salida,ajuste',
            'cantidad' => 'required|numeric|min:0.01',
            'fecha'    => 'required|date',
        ], [
            'tipo.required'     => 'El tipo de movimiento es obligatorio.',
            'cantidad.required' => 'La cantidad es obligatoria.',
            'cantidad.min'      => 'La cantidad debe ser mayor a cero.',
            'fecha.required'    => 'La fecha es obligatoria.',
        ]);

        $uid     = session('usuario_id');
        $insumo  = DB::table('inventario')->where('id', $id)->where('usuario_id', $uid)->first();

        if (!$insumo) {
            return redirect()->route('inventario.index')
                ->with('msg', 'Insumo no encontrado.')
                ->with('msgType', 'error');
        }

        $cantidad = (float) $request->cantidad;
        $nueva    = match($request->tipo) {
            'entrada' => $insumo->cantidad_actual + $cantidad,
            'salida'  => max(0, $insumo->cantidad_actual - $cantidad),
            'ajuste'  => $cantidad,  // ajuste = nueva cantidad exacta
            default   => $insumo->cantidad_actual,
        };

        // Actualizar stock
        DB::table('inventario')->where('id', $id)->update([
            'cantidad_actual' => $nueva,
            'actualizado_en'  => now()->toDateTimeString(),
        ]);

        // Registrar movimiento
        DB::table('inventario_movimientos')->insert([
            'inventario_id'   => $id,
            'usuario_id'      => $uid,
            'cultivo_id'      => $request->cultivo_id ?: null,
            'tipo'            => $request->tipo,
            'cantidad'        => $cantidad,
            'precio_unitario' => $request->precio_unitario ?: null,
            'motivo'          => $request->motivo ?: null,
            'fecha'           => $request->fecha,
            'creado_en'       => now()->toDateTimeString(),
        ]);

        $msg = match($request->tipo) {
            'entrada' => "Entrada registrada. Stock actual: {$nueva} {$insumo->unidad}",
            'salida'  => "Salida registrada. Stock actual: {$nueva} {$insumo->unidad}",
            'ajuste'  => "Stock ajustado a: {$nueva} {$insumo->unidad}",
        };

        // Alerta si quedó bajo mínimo
        if ($nueva <= $insumo->stock_minimo) {
            $msg .= " ⚠️ Stock bajo mínimo.";
        }

        return redirect()->route('inventario.index')
            ->with('msg', $msg)
            ->with('msgType', $nueva <= $insumo->stock_minimo ? 'warning' : 'success');
    }

    /**
     * Página solo de alertas
     */
    public function alertas()
    {
        $uid = session('usuario_id');

        $alertasStock = DB::table('inventario')
            ->where('usuario_id', $uid)
            ->whereRaw('cantidad_actual <= stock_minimo')
            ->orderByRaw('(cantidad_actual / GREATEST(stock_minimo, 0.01)) ASC')
            ->get();

        $porVencer = DB::table('inventario')
            ->where('usuario_id', $uid)
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '>=', now()->toDateString())
            ->whereDate('fecha_vencimiento', '<=', now()->addDays(30)->toDateString())
            ->orderBy('fecha_vencimiento')
            ->get();

        $vencidos = DB::table('inventario')
            ->where('usuario_id', $uid)
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<', now()->toDateString())
            ->orderBy('fecha_vencimiento')
            ->get();

        return view('pages.inventario-alertas', compact('alertasStock', 'porVencer', 'vencidos'));
    }

    private function categorias(): array
    {
        return [
            'Semillas', 'Fertilizantes', 'Plaguicidas', 'Herbicidas',
            'Fungicidas', 'Herramientas', 'Combustible', 'Alimento animal',
            'Medicamentos veterinarios', 'Abonos', 'Equipos', 'Otros',
        ];
    }
}