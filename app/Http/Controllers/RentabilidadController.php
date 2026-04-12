<?php

/**
 * RentabilidadController
 *
 * Controlador para gestionar la rentabilidad de los cultivos, incluyendo el cálculo de ingresos, gastos,
 * rentabilidad, ROI y métricas relacionadas, así como la visualización de detalles por cultivo.
 *
 * Métodos:
 * - index(Request $request): Muestra el resumen de rentabilidad de todos los cultivos del usuario.
 * - detalle(Request $request, $id): Muestra el detalle de rentabilidad de un cultivo específico.
 *
 * @package App\Http\Controllers
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RentabilidadController extends Controller
{
    public function index(Request $request)
{
    $uid = session('usuario_id');
    
    // Verificar autenticación
    if (!$uid) {
        return redirect()->route('login')->with('msg', 'Por favor inicie sesión.')->with('msgType', 'error');
    }
    
    // Validar y obtener el año
    $anioInput = $request->anio;
    $anio = (is_numeric($anioInput) && (int)$anioInput >= 1900 && (int)$anioInput <= (int)now()->year + 1)
        ? (int)$anioInput
        : (int)now()->year;
    
    $orden = $request->orden ?? 'rentabilidad'; // rentabilidad | nombre | ingresos | gastos

    // ── Consulta principal: costos e ingresos por cultivo ──────
    $cultivos = DB::table('cultivos as c')
        ->where('c.usuario_id', $uid)
        ->select('c.*')
        ->get();

    $datos = $cultivos->map(function ($cultivo) use ($uid, $anio) {

        // Gastos asociados a este cultivo en el año
        $gastos = DB::table('gastos')
            ->where('usuario_id', $uid)
            ->where('cultivo_id', $cultivo->id)
            ->whereYear('fecha', $anio)
            ->sum('valor');

        // Ingresos directos del cultivo
        $ingresos = DB::table('ingresos')
            ->where('usuario_id', $uid)
            ->where('cultivo_id', $cultivo->id)
            ->whereYear('fecha', $anio)
            ->sum('valor_total');

        // Cosechas valoradas del cultivo
        $valorCosechas = DB::table('cosechas')
            ->where('usuario_id', $uid)
            ->where('cultivo_id', $cultivo->id)
            ->whereYear('fecha_cosecha', $anio)
            ->sum('valor_estimado');

        // Usar ingresos reales si existen, si no usar valor de cosechas
        $ingresoTotal = $ingresos > 0 ? $ingresos : $valorCosechas;

        $rentabilidad = $ingresoTotal - $gastos;
        $roi = $gastos > 0
            ? round(($rentabilidad / $gastos) * 100, 1)
            : ($ingresoTotal > 0 ? 100 : 0);

        $margen = ($ingresoTotal > 0)
            ? round(($rentabilidad / $ingresoTotal) * 100, 1)
            : 0;

        // Desglose de gastos por categoría
        $gastosPorCategoria = DB::table('gastos')
            ->where('usuario_id', $uid)
            ->where('cultivo_id', $cultivo->id)
            ->whereYear('fecha', $anio)
            ->selectRaw('categoria, SUM(valor) as total')
            ->groupBy('categoria')
            ->orderByDesc('total')
            ->get();

        // Número de tareas completadas vs total
        $tareasTotal = DB::table('tareas')->where('usuario_id', $uid)->where('cultivo_id', $cultivo->id)->count();
        $tareasCompletadas = DB::table('tareas')->where('usuario_id', $uid)->where('cultivo_id', $cultivo->id)->where('completada', 1)->count();

        return (object) [
            'id'                  => $cultivo->id,
            'nombre'              => $cultivo->nombre,
            'tipo'                => $cultivo->tipo,
            'estado'              => $cultivo->estado,
            'area'                => $cultivo->area,
            'unidad'              => $cultivo->unidad,
            'fecha_siembra'       => $cultivo->fecha_siembra,
            'gastos'              => (float) $gastos,
            'ingresos'            => (float) $ingresoTotal,
            'rentabilidad'        => (float) $rentabilidad,
            'roi'                 => $roi,
            'margen'              => $margen,
            'gastosPorCategoria'  => $gastosPorCategoria,
            'tareasTotal'         => $tareasTotal,
            'tareasCompletadas'   => $tareasCompletadas,
            'costoPorHectarea'    => ($cultivo->area > 0) ? round($gastos / $cultivo->area, 0) : 0,
            'ingresoPorHectarea'  => ($cultivo->area > 0) ? round($ingresoTotal / $cultivo->area, 0) : 0,
        ];
    });

    // ── Ordenamiento (usando switch en lugar de match) ──
    switch($orden) {
        case 'nombre':
            $datos = $datos->sortBy('nombre');
            break;
        case 'ingresos':
            $datos = $datos->sortByDesc('ingresos');
            break;
        case 'gastos':
            $datos = $datos->sortByDesc('gastos');
            break;
        default: // rentabilidad
            $datos = $datos->sortByDesc('rentabilidad');
            break;
    }
    
    $datos = $datos->values();

    // ── Totales generales ─────────────────────────────────────
    $totalGastos     = $datos->sum('gastos');
    $totalIngresos   = $datos->sum('ingresos');
    $totalBalance    = $totalIngresos - $totalGastos;
    $mejorCultivo    = $datos->sortByDesc('rentabilidad')->first();
    $peorCultivo     = $datos->filter(function($d) { 
        return $d->gastos > 0 || $d->ingresos > 0; 
    })->sortBy('rentabilidad')->first();

    // Datos para gráfica de barras agrupadas (Chart.js)
    $chartLabels    = $datos->pluck('nombre')->toArray();
    $chartGastos    = $datos->pluck('gastos')->toArray();
    $chartIngresos  = $datos->pluck('ingresos')->toArray();
    $chartBalance   = $datos->map(function($d) { 
        return $d->rentabilidad; 
    })->toArray();

    return view('pages.rentabilidad', compact(
        'datos', 'anio', 'orden',
        'totalGastos', 'totalIngresos', 'totalBalance',
        'mejorCultivo', 'peorCultivo',
        'chartLabels', 'chartGastos', 'chartIngresos', 'chartBalance'
    ));
}
    

    public function detalle(Request $request, $id)
    {
        $uid = session('usuario_id');
        
        if (!$uid) {
            return redirect()->route('login')->with('msg', 'Por favor inicie sesión.')->with('msgType', 'error');
        }
        
        $cultivo = DB::table('cultivos')->where('id', $id)->where('usuario_id', $uid)->first();

        if (!$cultivo) {
            return redirect()->route('rentabilidad.index')
                ->with('msg', 'Cultivo no encontrado.')
                ->with('msgType', 'error');
        }

        $anio = (int) ($request->anio ?? now()->year);

        // ── Gastos por mes ────────────────────────────────────────
        $gastosMes = DB::table('gastos')
            ->where('usuario_id', $uid)
            ->where('cultivo_id', $id)
            ->whereYear('fecha', $anio)
            ->selectRaw('MONTH(fecha) as mes, SUM(valor) as total')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get()
            ->keyBy('mes');

        $ingresosMes = DB::table('ingresos')
            ->where('usuario_id', $uid)
            ->where('cultivo_id', $id)
            ->whereYear('fecha', $anio)
            ->selectRaw('MONTH(fecha) as mes, SUM(valor_total) as total')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get()
            ->keyBy('mes');

        // Arrays de 12 meses para Chart.js
        $gastosArr   = array_map(fn($m) => (float)($gastosMes[$m]->total ?? 0), range(1, 12));
        $ingresosArr = array_map(fn($m) => (float)($ingresosMes[$m]->total ?? 0), range(1, 12));
        $balanceArr  = array_map(fn($i) => $ingresosArr[$i] - $gastosArr[$i], range(0, 11));

        // ── Gastos por categoría ──────────────────────────────────
        $gastosCat = DB::table('gastos')
            ->where('usuario_id', $uid)
            ->where('cultivo_id', $id)
            ->whereYear('fecha', $anio)
            ->selectRaw('categoria, SUM(valor) as total')
            ->groupBy('categoria')
            ->orderByDesc('total')
            ->get();

        // ── Todos los gastos del cultivo ──────────────────────────
        $gastos = DB::table('gastos')
            ->where('usuario_id', $uid)
            ->where('cultivo_id', $id)
            ->whereYear('fecha', $anio)
            ->orderBy('fecha', 'desc')
            ->get();

        // ── Todos los ingresos del cultivo ────────────────────────
        $ingresos = DB::table('ingresos')
            ->where('usuario_id', $uid)
            ->where('cultivo_id', $id)
            ->whereYear('fecha', $anio)
            ->orderBy('fecha', 'desc')
            ->get();

        // ── Cosechas del cultivo ──────────────────────────────────
        $cosechas = DB::table('cosechas')
            ->where('usuario_id', $uid)
            ->where('cultivo_id', $id)
            ->whereYear('fecha_cosecha', $anio)
            ->orderBy('fecha_cosecha', 'desc')
            ->get();

        // ── Tareas del cultivo ────────────────────────────────────
        $tareas = DB::table('tareas')
            ->where('usuario_id', $uid)
            ->where('cultivo_id', $id)
            ->orderBy('fecha', 'desc')
            ->get();

        // ── Métricas finales ──────────────────────────────────────
        $totalGastos    = $gastos->sum('valor');
        $totalIngresos  = $ingresos->sum('valor_total');
        $totalCosechas  = $cosechas->sum('valor_estimado');
        $ingresoReal    = $totalIngresos > 0 ? $totalIngresos : $totalCosechas;
        $rentabilidad   = $ingresoReal - $totalGastos;
        $roi            = $totalGastos > 0 ? round(($rentabilidad / $totalGastos) * 100, 1) : 0;

        return view('pages.rentabilidad-detalle', compact(
            'cultivo', 'anio',
            'gastosArr', 'ingresosArr', 'balanceArr',
            'gastosCat', 'gastos', 'ingresos', 'cosechas', 'tareas',
            'totalGastos', 'totalIngresos', 'totalCosechas', 'ingresoReal',
            'rentabilidad', 'roi'
        ));
    }
}