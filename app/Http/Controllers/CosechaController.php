<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CosechaController extends Controller
{
    public function index(Request $request)
    {
        $uid = session('usuario_id');

        // Filtros opcionales
        $q        = $request->q;
        $mesF     = $request->mes;
        $calidadF = $request->calidad;

        $query = DB::table('cosechas as cs')
            ->leftJoin('cultivos as c', 'c.id', '=', 'cs.cultivo_id')
            ->where('cs.usuario_id', $uid)
            ->select('cs.*', 'c.nombre as cultivo_nombre');

        if ($q)        $query->where(fn($w) => $w->where('cs.producto','like',"%$q%")->orWhere('cs.comprador','like',"%$q%"));
        if ($mesF)     $query->whereRaw("DATE_FORMAT(cs.fecha_cosecha,'%Y-%m') = ?", [$mesF]);
        if ($calidadF) $query->where('cs.calidad', $calidadF);

        $cosechas = $query->orderBy('cs.fecha_cosecha', 'desc')->get();

        // ── Estadísticas ──────────────────────────────────────────
        $totalMes = DB::table('cosechas')
            ->where('usuario_id', $uid)
            ->whereMonth('fecha_cosecha', now()->month)
            ->whereYear('fecha_cosecha', now()->year)
            ->sum('valor_estimado');

        $totalAnio = DB::table('cosechas')
            ->where('usuario_id', $uid)
            ->whereYear('fecha_cosecha', now()->year)
            ->sum('valor_estimado');

        $cantidadMes = DB::table('cosechas')
            ->where('usuario_id', $uid)
            ->whereMonth('fecha_cosecha', now()->month)
            ->whereYear('fecha_cosecha', now()->year)
            ->count();

        // Top productos del año
        $topProductos = DB::table('cosechas')
            ->where('usuario_id', $uid)
            ->whereYear('fecha_cosecha', now()->year)
            ->selectRaw('producto, SUM(cantidad) as total_qty, SUM(valor_estimado) as total_valor')
            ->groupBy('producto')
            ->orderByDesc('total_valor')
            ->limit(5)
            ->get();

        $cultivos = DB::table('cultivos')
            ->where('usuario_id', $uid)
            ->orderBy('nombre')
            ->get();

        return view('pages.cosechas', compact(
            'cosechas','totalMes','totalAnio','cantidadMes',
            'topProductos','cultivos'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'producto'       => 'required|min:2',
            'cantidad'       => 'required|numeric|min:0.01',
            'unidad'         => 'required',
            'fecha_cosecha'  => 'required|date',
        ], [
            'producto.required'      => 'El nombre del producto es obligatorio.',
            'cantidad.required'      => 'La cantidad cosechada es obligatoria.',
            'cantidad.min'           => 'La cantidad debe ser mayor a cero.',
            'unidad.required'        => 'La unidad de medida es obligatoria.',
            'fecha_cosecha.required' => 'La fecha de cosecha es obligatoria.',
        ]);

        $cant  = (float) $request->cantidad;
        $punit = $request->precio_unitario ? (float) $request->precio_unitario : null;
        $valor = ($punit) ? $cant * $punit : null;

        DB::table('cosechas')->insert([
            'usuario_id'      => session('usuario_id'),
            'cultivo_id'      => $request->cultivo_id ?: null,
            'producto'        => $request->producto,
            'cantidad'        => $cant,
            'unidad'          => $request->unidad,
            'precio_unitario' => $punit,
            'valor_estimado'  => $valor,
            'fecha_cosecha'   => $request->fecha_cosecha,
            'calidad'         => $request->calidad ?? 'buena',
            'destino'         => $request->destino,
            'comprador'       => $request->comprador,
            'observaciones'   => $request->observaciones,
            'creado_en'       => now()->toDateTimeString(),
            'actualizado_en'  => now()->toDateTimeString(),
        ]);

        // Actualizar estado del cultivo a "cosechado" si se indicó
        if ($request->cultivo_id && $request->marcar_cosechado) {
            DB::table('cultivos')
                ->where('id', $request->cultivo_id)
                ->where('usuario_id', session('usuario_id'))
                ->update(['estado' => 'cosechado', 'actualizado_en' => now()->toDateTimeString()]);
        }

        return redirect()->route('cosechas.index')
            ->with('msg', 'Cosecha registrada correctamente.')
            ->with('msgType', 'success');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'producto'      => 'required|min:2',
            'cantidad'      => 'required|numeric|min:0.01',
            'unidad'        => 'required',
            'fecha_cosecha' => 'required|date',
        ]);

        $cant  = (float) $request->cantidad;
        $punit = $request->precio_unitario ? (float) $request->precio_unitario : null;
        $valor = $punit ? $cant * $punit : null;

        DB::table('cosechas')
            ->where('id', $id)
            ->where('usuario_id', session('usuario_id'))
            ->update([
                'cultivo_id'      => $request->cultivo_id ?: null,
                'producto'        => $request->producto,
                'cantidad'        => $cant,
                'unidad'          => $request->unidad,
                'precio_unitario' => $punit,
                'valor_estimado'  => $valor,
                'fecha_cosecha'   => $request->fecha_cosecha,
                'calidad'         => $request->calidad,
                'destino'         => $request->destino,
                'comprador'       => $request->comprador,
                'observaciones'   => $request->observaciones,
                'actualizado_en'  => now()->toDateTimeString(),
            ]);

        return redirect()->route('cosechas.index')
            ->with('msg', 'Cosecha actualizada.')
            ->with('msgType', 'success');
    }

    public function destroy($id)
    {
        DB::table('cosechas')
            ->where('id', $id)
            ->where('usuario_id', session('usuario_id'))
            ->delete();

        return redirect()->route('cosechas.index')
            ->with('msg', 'Cosecha eliminada.')
            ->with('msgType', 'warning');
    }
}