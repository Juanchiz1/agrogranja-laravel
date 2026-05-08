<?php

namespace App\Http\Controllers;

use App\Models\LineaProductiva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportesController extends Controller
{
    public function index(Request $request)
    {
        $uid   = session('usuario_id');
        $anio  = (int) ($request->anio ?? now()->year);
        $mes   = $request->mes ? (int) $request->mes : null;
        $linea = $request->linea ?? 'todas';

        $lineasActivas = LineaProductiva::activasDelUsuario($uid);

        $inicio = $mes
            ? Carbon::create($anio, $mes, 1)->startOfMonth()->toDateString()
            : Carbon::create($anio, 1, 1)->toDateString();
        $fin = $mes
            ? Carbon::create($anio, $mes, 1)->endOfMonth()->toDateString()
            : Carbon::create($anio, 12, 31)->toDateString();

        // ── RENTABILIDAD POR LÍNEA ────────────────────────────────
        $rentabilidadLineas = $this->calcularRentabilidadPorLinea(
            $uid, $lineasActivas, $inicio, $fin
        );

        // ── KPIs ESPECÍFICOS POR LÍNEA ────────────────────────────
        $kpisEspecificos = $this->calcularKpisEspecificos($uid, $lineasActivas, $inicio, $fin);

        // ── TOTALES GENERALES ─────────────────────────────────────
        $totalIngresos = DB::table('ingresos')
            ->where('usuario_id', $uid)->whereBetween('fecha', [$inicio, $fin])->sum('valor_total');
        $totalGastos = DB::table('gastos')
            ->where('usuario_id', $uid)->whereBetween('fecha', [$inicio, $fin])->sum('valor');
        $balanceTotal = $totalIngresos - $totalGastos;

        // ── DATOS PARA GRÁFICAS ────────────────────────────────────
        // Gráfica mensual del año seleccionado
        $evolucionMensual = $this->evolucionMensual($uid, $anio);

        // Gráfica comparativa por línea (barras)
        $chartLineas      = array_keys($rentabilidadLineas);
        $chartIngresos    = array_column(array_values($rentabilidadLineas), 'ingresos');
        $chartGastos      = array_column(array_values($rentabilidadLineas), 'gastos');
        $chartRentab      = array_column(array_values($rentabilidadLineas), 'rentabilidad');

        // ── TABLA DE DESGLOSE ──────────────────────────────────────
        $detalleIngresos = DB::table('ingresos')
            ->where('usuario_id', $uid)->whereBetween('fecha', [$inicio, $fin])
            ->selectRaw('tipo, SUM(valor_total) as total, COUNT(*) as registros')
            ->groupBy('tipo')->orderByDesc('total')->get();

        $detalleGastos = DB::table('gastos')
            ->where('usuario_id', $uid)->whereBetween('fecha', [$inicio, $fin])
            ->selectRaw('categoria, SUM(valor) as total, COUNT(*) as registros')
            ->groupBy('categoria')->orderByDesc('total')->get();

        // ── AÑOS DISPONIBLES ──────────────────────────────────────
        $aniosDisponibles = DB::table('ingresos')->where('usuario_id', $uid)
            ->selectRaw('YEAR(fecha) as anio')->groupBy('anio')
            ->orderByDesc('anio')->pluck('anio')->toArray();
        if (!in_array(now()->year, $aniosDisponibles)) {
            array_unshift($aniosDisponibles, now()->year);
        }

        return view('pages.reportes.index', compact(
            'rentabilidadLineas', 'kpisEspecificos', 'lineasActivas',
            'totalIngresos', 'totalGastos', 'balanceTotal',
            'evolucionMensual', 'chartLineas', 'chartIngresos',
            'chartGastos', 'chartRentab',
            'detalleIngresos', 'detalleGastos',
            'anio', 'mes', 'linea', 'aniosDisponibles',
            'inicio', 'fin'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    // RENTABILIDAD POR LÍNEA PRODUCTIVA
    // ─────────────────────────────────────────────────────────────────
    private function calcularRentabilidadPorLinea(int $uid, array $activas,
                                                   string $inicio, string $fin): array
    {
        $resultado = [];

        foreach ($activas as $linea) {
            $ingresos = 0; $gastos = 0;

            switch ($linea) {
                case 'cultivos':
                    $ingresos = DB::table('ingresos')
                        ->where('usuario_id', $uid)->whereBetween('fecha', [$inicio, $fin])
                        ->whereNotNull('cultivo_id')->sum('valor_total');
                    $gastos = DB::table('gastos')
                        ->where('usuario_id', $uid)->whereBetween('fecha', [$inicio, $fin])
                        ->whereNotNull('cultivo_id')->sum('valor');
                    break;

                case 'bovino':
                    [$ingresos, $gastos] = $this->rentabilidadEspecie(
                        $uid, ['Ganado bovino', 'Terneros'], $inicio, $fin
                    );
                    break;

                case 'avicola':
                    [$ingresos, $gastos] = $this->rentabilidadEspecie(
                        $uid, ['Gallinas', 'Patos', 'Pavos'], $inicio, $fin
                    );
                    break;

                case 'porcino':
                    [$ingresos, $gastos] = $this->rentabilidadEspecie(
                        $uid, ['Cerdos', 'Cerdas de cría'], $inicio, $fin
                    );
                    break;

                case 'piscicola':
                    // Ingresos de cosechas piscícolas
                    try {
                        $ingresos = DB::table('ingresos')
                            ->where('usuario_id', $uid)->whereBetween('fecha', [$inicio, $fin])
                            ->where('tipo', 'animal')
                            ->whereExists(fn($q) => $q->from('piscicola_cosechas')
                                ->whereColumn('piscicola_cosechas.valor_total_cop', 'ingresos.valor_total')
                                ->where('piscicola_cosechas.usuario_id', $uid))
                            ->sum('valor_total');
                    } catch (\Exception $e) {}
                    [$ingAlt, $gastos] = $this->rentabilidadEspecie(
                        $uid, ['Peces'], $inicio, $fin
                    );
                    if (!$ingresos) $ingresos = $ingAlt;
                    break;

                case 'caprino_ovino':
                    [$ingresos, $gastos] = $this->rentabilidadEspecie(
                        $uid, ['Cabras', 'Ovejas'], $inicio, $fin
                    );
                    break;

                case 'equino':
                    [$ingresos, $gastos] = $this->rentabilidadEspecie(
                        $uid, ['Caballos'], $inicio, $fin
                    );
                    break;

                case 'cunicola':
                    [$ingresos, $gastos] = $this->rentabilidadEspecie(
                        $uid, ['Conejos'], $inicio, $fin
                    );
                    break;

                default:
                    continue 2;
            }

            $rentabilidad = $ingresos - $gastos;
            $margen = $ingresos > 0 ? round(($rentabilidad / $ingresos) * 100, 1) : 0;
            $emoji  = $this->emojiLinea($linea);

            $resultado[$linea] = [
                'nombre'       => ucfirst(str_replace('_', ' ', $linea)),
                'emoji'        => $emoji,
                'ingresos'     => round($ingresos, 0),
                'gastos'       => round($gastos, 0),
                'rentabilidad' => round($rentabilidad, 0),
                'margen'       => $margen,
                'es_rentable'  => $rentabilidad >= 0,
            ];
        }

        // Ordenar por rentabilidad descendente
        uasort($resultado, fn($a, $b) => $b['rentabilidad'] - $a['rentabilidad']);
        return $resultado;
    }

    private function rentabilidadEspecie(int $uid, array $especies,
                                          string $inicio, string $fin): array
    {
        try {
            // IDs de animales de esas especies
            $animalIds = DB::table('animales')
                ->where('usuario_id', $uid)
                ->whereIn('especie', $especies)->pluck('id');

            if ($animalIds->isEmpty()) return [0, 0];

            $ingresos = DB::table('ingresos')
                ->where('usuario_id', $uid)->whereBetween('fecha', [$inicio, $fin])
                ->whereIn('animal_id', $animalIds)->sum('valor_total');

            $gastos = DB::table('gastos')
                ->where('usuario_id', $uid)->whereBetween('fecha', [$inicio, $fin])
                ->whereIn('animal_id', $animalIds)->sum('valor');

            return [(float) $ingresos, (float) $gastos];
        } catch (\Exception $e) { return [0, 0]; }
    }

    // ─────────────────────────────────────────────────────────────────
    // KPIs ESPECÍFICOS POR LÍNEA
    // ─────────────────────────────────────────────────────────────────
    private function calcularKpisEspecificos(int $uid, array $activas,
                                              string $inicio, string $fin): array
    {
        $kpis = [];
        $diasPeriodo = max(1, Carbon::parse($inicio)->diffInDays(Carbon::parse($fin)) + 1);

        // ── KPIs BOVINOS ──────────────────────────────────────────
        if (in_array('bovino', $activas)) {
            try {
                $vacasLecheras = DB::table('animales')->where('usuario_id', $uid)
                    ->where('estado', 'activo')->where('especie', 'Ganado bovino')
                    ->where('produccion', 'like', '%leche%')->sum('cantidad');

                $lechePeriodo = DB::table('animal_produccion as ap')
                    ->join('animales as a', 'a.id', '=', 'ap.animal_id')
                    ->where('ap.usuario_id', $uid)
                    ->where('ap.tipo_produccion', 'leche')
                    ->whereBetween('ap.fecha', [$inicio, $fin])
                    ->whereIn('a.especie', ['Ganado bovino'])->sum('ap.cantidad');

                $kpis['bovino'] = [
                    'titulo' => 'KPIs Bovinos',
                    'emoji'  => '🐄',
                    'items'  => [
                        ['kpi' => 'Litros totales del periodo',
                         'valor' => number_format((float)$lechePeriodo, 0) . ' L'],
                        ['kpi' => 'Litros por vaca por dia',
                         'valor' => $vacasLecheras > 0 && $lechePeriodo > 0
                            ? round($lechePeriodo / ($vacasLecheras * $diasPeriodo), 1) . ' L'
                            : '—',
                         'meta' => '10-25 L/vaca/dia (Holstein)',
                         'meta_criolla' => '3-8 L/vaca/dia (criolla)'],
                        ['kpi' => 'Vacas en produccion',
                         'valor' => $vacasLecheras . ' vacas'],
                    ],
                ];
            } catch (\Exception $e) {}
        }

        // ── KPIs AVÍCOLA ──────────────────────────────────────────
        if (in_array('avicola', $activas)) {
            try {
                $gallinasActivas = DB::table('animales')->where('usuario_id', $uid)
                    ->where('estado', 'activo')
                    ->whereIn('especie', ['Gallinas'])->sum('cantidad');

                $huevosPeriodo = DB::table('animal_produccion as ap')
                    ->join('animales as a', 'a.id', '=', 'ap.animal_id')
                    ->where('ap.usuario_id', $uid)->where('ap.tipo_produccion', 'huevos')
                    ->whereBetween('ap.fecha', [$inicio, $fin])
                    ->whereIn('a.especie', ['Gallinas'])->sum('ap.cantidad');

                $porcentajePostura = ($gallinasActivas > 0 && $huevosPeriodo > 0)
                    ? round(($huevosPeriodo / ($gallinasActivas * $diasPeriodo)) * 100, 1)
                    : 0;

                $kpis['avicola'] = [
                    'titulo' => 'KPIs Avicola',
                    'emoji'  => '🐔',
                    'items'  => [
                        ['kpi'   => 'Huevos totales del periodo',
                         'valor' => number_format((float)$huevosPeriodo, 0) . ' uds'],
                        ['kpi'   => 'Huevos por ave por dia',
                         'valor' => $gallinasActivas > 0 && $huevosPeriodo > 0
                            ? round($huevosPeriodo / ($gallinasActivas * $diasPeriodo), 3) . ' huevos/dia'
                            : '—',
                         'meta'  => '0.85-0.95 huevos/ave/dia'],
                        ['kpi'   => 'Porcentaje de postura',
                         'valor' => $porcentajePostura . '%',
                         'meta'  => '> 85% es excelente'],
                    ],
                ];
            } catch (\Exception $e) {}
        }

        // ── KPIs PORCÍCOLA ────────────────────────────────────────
        if (in_array('porcino', $activas)) {
            try {
                $totalPartos = 0; $lechonesProm = 0; $hembrasActivas = 0;
                try {
                    $totalPartos = DB::table('porcicola_camadas')
                        ->where('usuario_id', $uid)->whereNotNull('fecha_parto_real')
                        ->whereBetween('fecha_parto_real', [$inicio, $fin])->count();
                    $lechonesProm = DB::table('porcicola_camadas')
                        ->where('usuario_id', $uid)->whereNotNull('fecha_parto_real')
                        ->whereBetween('fecha_parto_real', [$inicio, $fin])
                        ->avg('lechones_nacidos_vivos');
                    $hembrasActivas = DB::table('animales')->where('usuario_id', $uid)
                        ->where('estado', 'activo')->where('categoria_porcina', 'hembra_cria')
                        ->count();
                } catch (\Exception $e) {}

                $kpis['porcino'] = [
                    'titulo' => 'KPIs Porcicola',
                    'emoji'  => '🐷',
                    'items'  => [
                        ['kpi'  => 'Partos en el periodo',
                         'valor' => $totalPartos . ' partos'],
                        ['kpi'  => 'Lechones nacidos por camada',
                         'valor' => $lechonesProm ? round($lechonesProm, 1) . ' lechones' : '—',
                         'meta' => '> 10 lechones/camada'],
                        ['kpi'  => 'Hembras de cria activas',
                         'valor' => $hembrasActivas . ' hembras'],
                    ],
                ];
            } catch (\Exception $e) {}
        }

        // ── KPIs PISCÍCOLA ────────────────────────────────────────
        if (in_array('piscicola', $activas)) {
            try {
                $cosechas = DB::table('piscicola_cosechas')
                    ->where('usuario_id', $uid)
                    ->whereBetween('fecha_cosecha', [$inicio, $fin])->get();

                $promCA          = $cosechas->whereNotNull('conversion_alimenticia')->avg('conversion_alimenticia');
                $promSobrev      = $cosechas->whereNotNull('sobrevivencia_pct')->avg('sobrevivencia_pct');
                $promRendimiento = $cosechas->whereNotNull('rendimiento_kg_m2')->avg('rendimiento_kg_m2');

                $kpis['piscicola'] = [
                    'titulo' => 'KPIs Piscicola',
                    'emoji'  => '🐟',
                    'items'  => [
                        ['kpi'  => 'Conversion alimenticia promedio',
                         'valor' => $promCA ? round($promCA, 2) . ' (CA)' : '—',
                         'meta' => '< 1.5 Cachama / < 1.7 Tilapia'],
                        ['kpi'  => 'Sobrevivencia promedio',
                         'valor' => $promSobrev ? round($promSobrev, 1) . '%' : '—',
                         'meta' => '> 90% ideal'],
                        ['kpi'  => 'Rendimiento kg/m2',
                         'valor' => $promRendimiento ? round($promRendimiento, 2) . ' kg/m2' : '—',
                         'meta' => '3-5 kg/m2 cachama / 5-8 tilapia'],
                    ],
                ];
            } catch (\Exception $e) {}
        }

        return $kpis;
    }

    // ─────────────────────────────────────────────────────────────────
    // EVOLUCIÓN MENSUAL DEL AÑO
    // ─────────────────────────────────────────────────────────────────
    private function evolucionMensual(int $uid, int $anio): array
    {
        $meses = [];
        for ($m = 1; $m <= 12; $m++) {
            $ini = Carbon::create($anio, $m, 1)->toDateString();
            $fin = Carbon::create($anio, $m, 1)->endOfMonth()->toDateString();

            $ingresos = DB::table('ingresos')
                ->where('usuario_id', $uid)->whereBetween('fecha', [$ini, $fin])->sum('valor_total');
            $gastos = DB::table('gastos')
                ->where('usuario_id', $uid)->whereBetween('fecha', [$ini, $fin])->sum('valor');

            $meses[] = [
                'mes'          => Carbon::create($anio, $m, 1)->locale('es')->isoFormat('MMM'),
                'ingresos'     => round((float) $ingresos, 0),
                'gastos'       => round((float) $gastos, 0),
                'rentabilidad' => round((float) $ingresos - (float) $gastos, 0),
            ];
        }
        return $meses;
    }

    private function emojiLinea(string $linea): string
    {
        $map = [
            'cultivos'     => '🌱',
            'bovino'       => '🐄',
            'avicola'      => '🐔',
            'porcino'      => '🐷',
            'piscicola'    => '🐟',
            'caprino_ovino'=> '🐐',
            'apicola'      => '🐝',
            'equino'       => '🐴',
            'cunicola'     => '🐰',
        ];
        return $map[$linea] ?? '🌾';
    }
}