<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RentabilidadController extends Controller
{
    public function index(Request $request)
    {
        $uid  = session('usuario_id');
        $anio = (int)($request->anio ?? now()->year);
        $orden = $request->orden ?? 'rentabilidad';

        // ── Traer todos los cultivos ──────────────────────────────
        $cultivos = DB::table('cultivos')
            ->where('usuario_id', $uid)
            ->get();

        // ── Enriquecer cada cultivo con sus métricas ──────────────
        $datos = $cultivos->map(function ($c) use ($uid, $anio) {

            $ingresos = DB::table('ingresos')
                ->where('usuario_id', $uid)
                ->where('cultivo_id', $c->id)
                ->whereYear('fecha', $anio)
                ->sum('valor_total');

            $gastos = DB::table('gastos')
                ->where('usuario_id', $uid)
                ->where('cultivo_id', $c->id)
                ->whereYear('fecha', $anio)
                ->sum('valor');

            // Mano de obra vinculada a este cultivo
            $manoDeObra = DB::table('persona_pagos')
                ->where('usuario_id', $uid)
                ->where('cultivo_id', $c->id)
                ->whereYear('fecha', $anio)
                ->sum('valor');

            $costoTotal   = $gastos + $manoDeObra;
            $rentabilidad = $ingresos - $costoTotal;
            $roi          = $costoTotal > 0
                ? round(($rentabilidad / $costoTotal) * 100, 1) : 0;
            $margen       = $ingresos > 0
                ? round(($rentabilidad / $ingresos) * 100, 1) : 0;

            // Top categorías de gasto (para las pills en la tarjeta)
            $gastosPorCategoria = DB::table('gastos')
                ->where('usuario_id', $uid)
                ->where('cultivo_id', $c->id)
                ->whereYear('fecha', $anio)
                ->selectRaw('categoria, SUM(valor) as total')
                ->groupBy('categoria')
                ->orderByDesc('total')
                ->get();

            $c->ingresos           = $ingresos;
            $c->gastos             = $gastos;
            $c->mano_de_obra       = $manoDeObra;
            $c->costo_total        = $costoTotal;
            $c->rentabilidad       = $rentabilidad;
            $c->roi                = $roi;
            $c->margen             = $margen;
            $c->gastosPorCategoria = $gastosPorCategoria;

            return $c;
        });

        // ── Ordenar ───────────────────────────────────────────────
        $datos = match($orden) {
            'ingresos'     => $datos->sortByDesc('ingresos'),
            'gastos'       => $datos->sortByDesc('costo_total'),
            'nombre'       => $datos->sortBy('nombre'),
            default        => $datos->sortByDesc('rentabilidad'),
        }->values();

        // ── Totales globales ──────────────────────────────────────
        $totalIngresos = $datos->sum('ingresos');
        $totalGastos   = $datos->sum('costo_total');   // gastos + mano de obra
        $totalBalance  = $totalIngresos - $totalGastos;

        // ── Mejores / peores ──────────────────────────────────────
        $mejorCultivo = $datos->sortByDesc('rentabilidad')->first();
        $peorCultivo  = $datos->sortBy('rentabilidad')->first();

        // Solo mostrar peor si realmente es negativo
        if ($peorCultivo && $peorCultivo->rentabilidad >= 0) {
            $peorCultivo = null;
        }

        // ── Datos para gráfica comparativa ───────────────────────
        $chartLabels   = $datos->pluck('nombre')->toArray();
        $chartGastos   = $datos->pluck('costo_total')->toArray();
        $chartIngresos = $datos->pluck('ingresos')->toArray();

        return view('pages.rentabilidad', compact(
            'datos', 'anio', 'orden',
            'totalIngresos', 'totalGastos', 'totalBalance',
            'mejorCultivo', 'peorCultivo',
            'chartLabels', 'chartGastos', 'chartIngresos'
        ));
    }

    public function detalle(Request $request, $id)
    {
        $uid = session('usuario_id');
        if (!$uid) return redirect()->route('login');

        $cultivo = DB::table('cultivos')
            ->where('id', $id)
            ->where('usuario_id', $uid)
            ->first();

        if (!$cultivo) {
            return redirect()->route('rentabilidad.index')
                ->with('msg', 'Cultivo no encontrado.')
                ->with('msgType', 'error');
        }

        $anio = (int)($request->anio ?? now()->year);

        // ── Datos mensuales para gráfica (gastos + mano de obra) ─
        $gastosMes = DB::table('gastos')
            ->where('usuario_id', $uid)->where('cultivo_id', $id)
            ->whereYear('fecha', $anio)
            ->selectRaw('MONTH(fecha) as mes, SUM(valor) as total')
            ->groupBy('mes')->get()->keyBy('mes');

        $manoObraMes = DB::table('persona_pagos')
            ->where('usuario_id', $uid)->where('cultivo_id', $id)
            ->whereYear('fecha', $anio)
            ->selectRaw('MONTH(fecha) as mes, SUM(valor) as total')
            ->groupBy('mes')->get()->keyBy('mes');

        $ingresosMes = DB::table('ingresos')
            ->where('usuario_id', $uid)->where('cultivo_id', $id)
            ->whereYear('fecha', $anio)
            ->selectRaw('MONTH(fecha) as mes, SUM(valor_total) as total')
            ->groupBy('mes')->get()->keyBy('mes');

        // Gráfica: costoMes = gastos + mano de obra del mes
        $gastosArr = array_map(
            fn($m) => (float)($gastosMes[$m]->total ?? 0)
                    + (float)($manoObraMes[$m]->total ?? 0),
            range(1, 12)
        );
        $ingresosArr = array_map(
            fn($m) => (float)($ingresosMes[$m]->total ?? 0),
            range(1, 12)
        );
        $balanceArr = array_map(
            fn($i) => $ingresosArr[$i] - $gastosArr[$i],
            range(0, 11)
        );

        // ── Gastos por categoría ──────────────────────────────────
        $gastosCat = DB::table('gastos')
            ->where('usuario_id', $uid)->where('cultivo_id', $id)
            ->whereYear('fecha', $anio)
            ->selectRaw('categoria, SUM(valor) as total')
            ->groupBy('categoria')->orderByDesc('total')->get();

        // ── Listados ──────────────────────────────────────────────
        $gastos = DB::table('gastos')
            ->where('usuario_id', $uid)->where('cultivo_id', $id)
            ->whereYear('fecha', $anio)->orderBy('fecha', 'desc')->get();

        $ingresos = DB::table('ingresos')
            ->where('usuario_id', $uid)->where('cultivo_id', $id)
            ->whereYear('fecha', $anio)->orderBy('fecha', 'desc')->get();

        $cosechas = DB::table('cosechas')
            ->where('usuario_id', $uid)->where('cultivo_id', $id)
            ->whereYear('fecha_cosecha', $anio)
            ->orderBy('fecha_cosecha', 'desc')->get();

        $tareas = DB::table('tareas')
            ->where('usuario_id', $uid)->where('cultivo_id', $id)
            ->orderBy('fecha', 'desc')->get();

        // ── Mano de obra detallada ────────────────────────────────
        $manoDeObra = DB::table('persona_pagos as pp')
            ->join('personas as per', 'per.id', '=', 'pp.persona_id')
            ->where('pp.usuario_id', $uid)
            ->where('pp.cultivo_id', $id)
            ->whereYear('pp.fecha', $anio)
            ->select(
                'pp.id', 'pp.fecha', 'pp.tipo_pago',
                'pp.dias', 'pp.valor', 'pp.concepto',
                'per.nombre as trabajador', 'per.cargo'
            )
            ->orderBy('pp.fecha', 'desc')
            ->get();

        $totalManoObra = $manoDeObra->sum('valor');

        // ── Totales ───────────────────────────────────────────────
        $totalGastos   = $gastos->sum('valor');
        $totalIngresos = $ingresos->sum('valor_total');
        $totalCosechas = $cosechas->sum('valor_estimado');
        $costoTotal    = $totalGastos + $totalManoObra;
        $ingresoReal   = $totalIngresos > 0 ? $totalIngresos : $totalCosechas;
        $rentabilidad  = $ingresoReal - $costoTotal;
        $roi           = $costoTotal > 0
            ? round(($rentabilidad / $costoTotal) * 100, 1) : 0;

        return view('pages.rentabilidad-detalle', compact(
            'cultivo', 'anio', 'gastosArr', 'ingresosArr', 'balanceArr',
            'gastosCat', 'gastos', 'ingresos', 'cosechas', 'tareas',
            'manoDeObra', 'totalManoObra',
            'totalGastos', 'totalIngresos', 'totalCosechas',
            'costoTotal', 'ingresoReal', 'rentabilidad', 'roi'
        ));
    }

    public function detalleAnimal(Request $request, $id)
    {
        $uid = session('usuario_id');
        if (!$uid) return redirect()->route('login');

        $animal = DB::table('animales')
            ->where('id', $id)->where('usuario_id', $uid)->first();

        if (!$animal) {
            return redirect()->route('rentabilidad.index')
                ->with('msg', 'Animal no encontrado.')
                ->with('msgType', 'error');
        }

        $fechaInicio = $animal->fecha_ingreso ?? $animal->creado_en;
        $fechaFin    = $animal->fecha_venta   ?? now()->toDateString();

        $gastos = DB::table('gastos')
            ->where('usuario_id', $uid)
            ->where('animal_id', $animal->id)
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->orderBy('fecha', 'desc')
            ->get();

        $manoDeObra = DB::table('persona_pagos as pp')
            ->join('personas as per', 'per.id', '=', 'pp.persona_id')
            ->where('pp.usuario_id', $uid)
            ->where('pp.animal_id', $animal->id)
            ->select(
                'pp.id', 'pp.fecha', 'pp.tipo_pago',
                'pp.dias', 'pp.valor', 'pp.concepto',
                'per.nombre as trabajador', 'per.cargo'
            )
            ->orderBy('pp.fecha', 'desc')
            ->get();

        $totalManoObra = $manoDeObra->sum('valor');
        $totalGastos   = $gastos->sum('valor');
        $costoTotal    = $totalGastos + $totalManoObra;
        $rentabilidad  = ($animal->valor_venta ?? 0) - $costoTotal;
        $roi           = $costoTotal > 0
            ? round(($rentabilidad / $costoTotal) * 100, 1) : 0;

        return view('pages.rentabilidad-animal-detalle', compact(
            'animal', 'gastos', 'manoDeObra', 'totalManoObra',
            'totalGastos', 'costoTotal', 'rentabilidad', 'roi',
            'fechaInicio', 'fechaFin'
        ));
    }
}