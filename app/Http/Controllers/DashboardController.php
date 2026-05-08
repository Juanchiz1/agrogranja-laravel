<?php
namespace App\Http\Controllers;

use App\Models\LineaProductiva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $uid  = session('usuario_id');
        $user = DB::table('usuarios')->find($uid);
        if ($user && !isset($user->created_at)) $user->created_at = $user->creado_en ?? null;

        $inicio = now()->startOfMonth()->toDateString();
        $fin    = now()->endOfMonth()->toDateString();
        $hoy    = now()->toDateString();

        // ── Stats principales ──────────────────────────────────────
        $cultivosActivos = DB::table('cultivos')
            ->where('usuario_id', $uid)->where('estado', 'activo')->count();
        $gastosMes   = DB::table('gastos')
            ->where('usuario_id', $uid)->whereBetween('fecha', [$inicio, $fin])->sum('valor');
        $ingresosMes = DB::table('ingresos')
            ->where('usuario_id', $uid)->whereBetween('fecha', [$inicio, $fin])->sum('valor_total');
        $tareasPend  = DB::table('tareas')
            ->where('usuario_id', $uid)->where('completada', 0)->where('fecha', '<=', $hoy)->count();
        $animalesActivos = DB::table('animales')
            ->where('usuario_id', $uid)->where('estado', 'activo')->sum('cantidad');

        $tareasHoy = DB::table('tareas')
            ->where('usuario_id', $uid)->where('completada', 0)->whereDate('fecha', $hoy)
            ->orderByRaw("FIELD(prioridad,'alta','media','baja')")->limit(4)->get();
        $tareasVencidas = DB::table('tareas')
            ->where('usuario_id', $uid)->where('completada', 0)->where('fecha', '<', $hoy)->count();

        $recentCultivos = DB::table('cultivos')
            ->where('usuario_id', $uid)->orderBy('id', 'desc')->limit(3)->get();

        $alertasInventario = 0;
        try {
            $alertasInventario = DB::table('inventario')
                ->where('usuario_id', $uid)->whereRaw('cantidad_actual <= stock_minimo')->count();
        } catch (\Exception $e) {}

        $topCultivo = DB::table('cultivos as c')->where('c.usuario_id', $uid)
            ->selectRaw('c.id, c.nombre, c.tipo,
                COALESCE((SELECT SUM(valor_total) FROM ingresos
                          WHERE cultivo_id=c.id AND YEAR(fecha)=YEAR(CURDATE())),0)
              - COALESCE((SELECT SUM(valor) FROM gastos
                          WHERE cultivo_id=c.id AND YEAR(fecha)=YEAR(CURDATE())),0)
                AS rentabilidad')
            ->orderByDesc('rentabilidad')->first();

        $cosechasRecientes = DB::table('cosechas')
            ->where('usuario_id', $uid)->orderBy('fecha_cosecha', 'desc')->limit(2)->get();

        try {
            $pagadoMesPersonas = DB::table('persona_pagos')->where('usuario_id', $uid)
                ->whereMonth('fecha', now()->month)->whereYear('fecha', now()->year)->sum('valor');
            $trabajadoresActivos = DB::table('personas')->where('usuario_id', $uid)
                ->where('tipo', 'trabajador')->where('activo', 1)->count();
        } catch (\Exception $e) { $pagadoMesPersonas = 0; $trabajadoresActivos = 0; }

        // ── ALERTAS INTELIGENTES CONTEXTUALES ─────────────────────
        $lineasActivas = LineaProductiva::activasDelUsuario($uid);
        $alertasInteligentes = $this->generarAlertasContextuales($uid, $lineasActivas, $hoy);

        // ── Próximas dosis SOLO de líneas activas ──────────────────
        $proximasDosis = $this->proximasDosisContextuales($uid, $lineasActivas, $hoy);

        // ── KPIs adaptativos ───────────────────────────────────────
        $kpisLineas = $this->calcularKpisPorLinea($uid, $lineasActivas, $hoy, $inicio, $fin);

        return view('pages.dashboard', compact(
            'user', 'cultivosActivos', 'gastosMes', 'ingresosMes', 'tareasPend',
            'tareasHoy', 'tareasVencidas', 'recentCultivos', 'alertasInventario',
            'topCultivo', 'cosechasRecientes', 'animalesActivos',
            'proximasDosis', 'pagadoMesPersonas', 'trabajadoresActivos',
            'kpisLineas', 'lineasActivas', 'alertasInteligentes'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    // ALERTAS INTELIGENTES CONTEXTUALES
    // Solo genera alertas relevantes para las líneas que el usuario tiene
    // ─────────────────────────────────────────────────────────────────
    private function generarAlertasContextuales(int $uid, array $activas, string $hoy): array
    {
        $alertas = [];

        // ── BOVINO: partos próximos, días abiertos excesivos ──────
        if (in_array('bovino', $activas)) {
            try {
                $partosProx = DB::table('animal_reproduccion')
                    ->join('animales as a', 'a.id', '=', 'animal_reproduccion.animal_id')
                    ->where('animal_reproduccion.usuario_id', $uid)
                    ->whereNull('animal_reproduccion.fecha_parto_real')
                    ->whereBetween('animal_reproduccion.fecha_probable_parto',
                        [$hoy, now()->addDays(10)->toDateString()])
                    ->count();
                if ($partosProx > 0) {
                    $alertas[] = [
                        'tipo'   => 'bovino_parto',
                        'nivel'  => 'alta',
                        'icono'  => 'cria',
                        'texto'  => $partosProx . ' parto(s) bovino previsto(s) en los proximos 10 dias',
                        'url'    => route('bovino.hato'),
                        'linea'  => 'bovino',
                    ];
                }
            } catch (\Exception $e) {}

            try {
                // Vacunas sanidad animal vencidas
                $vacunasBov = DB::table('animal_sanidad_programada')
                    ->join('animales as a', 'a.id', '=', 'animal_sanidad_programada.animal_id')
                    ->where('animal_sanidad_programada.usuario_id', $uid)
                    ->whereIn('a.especie', ['Ganado bovino', 'Terneros'])
                    ->where('animal_sanidad_programada.completada', 0)
                    ->where('animal_sanidad_programada.fecha_programada', '<=', $hoy)
                    ->count();
                if ($vacunasBov > 0) {
                    $alertas[] = [
                        'tipo'  => 'bovino_vacuna',
                        'nivel' => 'alta',
                        'icono' => 'vacuna',
                        'texto' => $vacunasBov . ' vacuna(s) bovinas vencidas o pendientes',
                        'url'   => route('bovino.hato'),
                        'linea' => 'bovino',
                    ];
                }
            } catch (\Exception $e) {}
        }

        // ── AVÍCOLA: vacunas, recolección pendiente ───────────────
        if (in_array('avicola', $activas)) {
            try {
                $vacunasAvi = DB::table('avicola_vacunacion')
                    ->where('usuario_id', $uid)
                    ->whereNull('fecha_aplicada')
                    ->where('fecha_programada', '<=', $hoy)
                    ->where('activo', 1)->count();
                if ($vacunasAvi > 0) {
                    $alertas[] = [
                        'tipo'  => 'avicola_vacuna',
                        'nivel' => 'alta',
                        'icono' => 'vacuna',
                        'texto' => $vacunasAvi . ' protocolo(s) sanitarios avicolas vencidos',
                        'url'   => route('avicola.vacunacion'),
                        'linea' => 'avicola',
                    ];
                }
            } catch (\Exception $e) {}

            try {
                // Alta mortalidad avícola (> 2% en los últimos 7 días)
                $mortalidadSem = DB::table('avicola_mortalidad')
                    ->where('usuario_id', $uid)
                    ->where('fecha', '>=', now()->subDays(7)->toDateString())
                    ->sum('cantidad');
                $totalAves = DB::table('animales')->where('usuario_id', $uid)
                    ->where('estado', 'activo')
                    ->whereIn('especie', ['Gallinas', 'Patos', 'Pavos'])->sum('cantidad');
                if ($totalAves > 0 && ($mortalidadSem / $totalAves) > 0.02) {
                    $alertas[] = [
                        'tipo'  => 'avicola_mortalidad',
                        'nivel' => 'critica',
                        'icono' => 'alerta',
                        'texto' => 'Mortalidad avicola alta: ' . $mortalidadSem .
                                   ' aves en 7 dias (' .
                                   round(($mortalidadSem / $totalAves) * 100, 1) . '%)',
                        'url'   => route('avicola.mortalidad'),
                        'linea' => 'avicola',
                    ];
                }
            } catch (\Exception $e) {}
        }

        // ── PORCÍCOLA: partos próximos, destetes pendientes ──────
        if (in_array('porcino', $activas)) {
            try {
                $partosPorcProx = DB::table('porcicola_camadas')
                    ->where('usuario_id', $uid)
                    ->whereNull('fecha_parto_real')
                    ->where('resultado_diagnostico', 'positivo')
                    ->whereBetween('fecha_probable_parto',
                        [$hoy, now()->addDays(10)->toDateString()])
                    ->count();
                if ($partosPorcProx > 0) {
                    $alertas[] = [
                        'tipo'  => 'porcicola_parto',
                        'nivel' => 'alta',
                        'icono' => 'cria',
                        'texto' => $partosPorcProx . ' parto(s) porcino esperado(s) en 10 dias',
                        'url'   => route('porcicola.reproductivo'),
                        'linea' => 'porcino',
                    ];
                }
            } catch (\Exception $e) {}

            try {
                $vacunasPorci = DB::table('porcicola_sanidad')
                    ->where('usuario_id', $uid)
                    ->whereNull('fecha_aplicada')
                    ->where('activo', 1)
                    ->where('fecha_programada', '<=', $hoy)
                    ->count();
                if ($vacunasPorci > 0) {
                    $alertas[] = [
                        'tipo'  => 'porcicola_vacuna',
                        'nivel' => 'alta',
                        'icono' => 'vacuna',
                        'texto' => $vacunasPorci . ' protocolo(s) sanitarios porcicolas vencidos',
                        'url'   => route('porcicola.sanidad'),
                        'linea' => 'porcino',
                    ];
                }
            } catch (\Exception $e) {}
        }

        // ── PISCÍCOLA: calidad de agua, muestreo pendiente ───────
        if (in_array('piscicola', $activas)) {
            try {
                $alertasAgua = DB::table('piscicola_calidad_agua')
                    ->where('usuario_id', $uid)->where('alerta', 1)
                    ->where('fecha', '>=', now()->subDays(2)->toDateString())->count();
                if ($alertasAgua > 0) {
                    $alertas[] = [
                        'tipo'  => 'piscicola_agua',
                        'nivel' => 'critica',
                        'icono' => 'agua',
                        'texto' => $alertasAgua . ' alerta(s) de calidad del agua en estanques',
                        'url'   => route('piscicola.calidad_agua'),
                        'linea' => 'piscicola',
                    ];
                }
            } catch (\Exception $e) {}

            try {
                // Muestreo sin realizar en más de 21 días
                $siembrasAntiguas = DB::table('piscicola_siembras as s')
                    ->leftJoin('piscicola_muestreos as m', function ($j) {
                        $j->on('m.siembra_id', '=', 's.id')
                          ->whereRaw('m.id = (SELECT id FROM piscicola_muestreos
                                             WHERE siembra_id=s.id ORDER BY fecha DESC LIMIT 1)');
                    })
                    ->where('s.usuario_id', $uid)->where('s.activo', 1)
                    ->where(function ($q) {
                        $q->whereNull('m.fecha')
                          ->orWhere('m.fecha', '<', now()->subDays(21)->toDateString());
                    })->count();
                if ($siembrasAntiguas > 0) {
                    $alertas[] = [
                        'tipo'  => 'piscicola_muestreo',
                        'nivel' => 'media',
                        'icono' => 'muestreo',
                        'texto' => $siembrasAntiguas . ' estanque(s) sin muestreo en mas de 21 dias',
                        'url'   => route('piscicola.muestreo'),
                        'linea' => 'piscicola',
                    ];
                }
            } catch (\Exception $e) {}
        }

        // ── INVENTARIO bajo mínimo (transversal) ─────────────────
        try {
            $invBajo = DB::table('inventario')
                ->where('usuario_id', $uid)->whereRaw('cantidad_actual <= stock_minimo')
                ->orderByRaw('(stock_minimo - cantidad_actual) DESC')->limit(3)->get();
            foreach ($invBajo as $inv) {
                $alertas[] = [
                    'tipo'  => 'inventario_bajo_' . $inv->id,
                    'nivel' => 'media',
                    'icono' => 'inventario',
                    'texto' => 'Stock bajo: ' . $inv->nombre .
                               ' (' . $inv->cantidad_actual . ' ' . $inv->unidad . ' restantes)',
                    'url'   => route('inventario.index'),
                    'linea' => 'transversal',
                ];
            }
        } catch (\Exception $e) {}

        // ── CULTIVOS: cosecha próxima ─────────────────────────────
        if (in_array('cultivos', $activas)) {
            try {
                $cosechaProx = DB::table('cultivos')->where('usuario_id', $uid)
                    ->where('estado', 'activo')
                    ->whereNotNull('fecha_cosecha_estimada')
                    ->whereBetween('fecha_cosecha_estimada',
                        [$hoy, now()->addDays(7)->toDateString()])
                    ->count();
                if ($cosechaProx > 0) {
                    $alertas[] = [
                        'tipo'  => 'cultivo_cosecha',
                        'nivel' => 'media',
                        'icono' => 'cosecha',
                        'texto' => $cosechaProx . ' cultivo(s) listos para cosechar esta semana',
                        'url'   => route('cosechas.index'),
                        'linea' => 'cultivos',
                    ];
                }
            } catch (\Exception $e) {}
        }

        // Ordenar: críticas primero, luego altas, luego medias
        usort($alertas, function ($a, $b) {
            $orden = ['critica' => 0, 'alta' => 1, 'media' => 2, 'baja' => 3];
            return ($orden[$a['nivel']] ?? 3) - ($orden[$b['nivel']] ?? 3);
        });

        return $alertas;
    }

    // ─────────────────────────────────────────────────────────────────
    // PRÓXIMAS DOSIS — solo de las especies de líneas activas
    // ─────────────────────────────────────────────────────────────────
    private function proximasDosisContextuales(int $uid, array $activas, string $hoy): object
    {
        // Construir lista de especies permitidas según líneas activas
        $especiesPermitidas = [];
        if (in_array('bovino', $activas))
            $especiesPermitidas = array_merge($especiesPermitidas, ['Ganado bovino', 'Terneros']);
        if (in_array('avicola', $activas))
            $especiesPermitidas = array_merge($especiesPermitidas, ['Gallinas', 'Patos', 'Pavos']);
        if (in_array('porcino', $activas))
            $especiesPermitidas = array_merge($especiesPermitidas, ['Cerdos', 'Cerdas de cría']);
        if (in_array('caprino_ovino', $activas))
            $especiesPermitidas = array_merge($especiesPermitidas, ['Cabras', 'Ovejas']);
        if (in_array('equino', $activas))
            $especiesPermitidas[] = 'Caballos';
        if (in_array('cunicola', $activas))
            $especiesPermitidas[] = 'Conejos';

        if (empty($especiesPermitidas)) return collect();

        try {
            return DB::table('animal_eventos as ae')
                ->join('animales as a', 'a.id', '=', 'ae.animal_id')
                ->where('ae.usuario_id', $uid)
                ->whereNotNull('ae.proxima_dosis')
                ->whereBetween('ae.proxima_dosis', [$hoy, now()->addDays(7)->toDateString()])
                ->whereIn('a.especie', $especiesPermitidas)
                ->select('ae.titulo', 'ae.proxima_dosis', 'a.nombre_lote', 'a.especie')
                ->orderBy('ae.proxima_dosis')->limit(5)->get();
        } catch (\Exception $e) { return collect(); }
    }

    // ─────────────────────────────────────────────────────────────────
    // KPIs POR LÍNEA — con KPIs específicos mejorados
    // ─────────────────────────────────────────────────────────────────
    private function calcularKpisPorLinea(int $uid, array $activas,
                                           string $hoy, string $inicio, string $fin): array
    {
        $kpis = [];

        if (in_array('cultivos', $activas)) {
            $cosechasMes = 0; $valorCosechasMes = 0; $cultivosCosechar = 0;
            try {
                $cosechasMes = DB::table('cosechas')->where('usuario_id', $uid)
                    ->whereBetween('fecha_cosecha', [$inicio, $fin])->count();
                $valorCosechasMes = DB::table('cosechas')->where('usuario_id', $uid)
                    ->whereBetween('fecha_cosecha', [$inicio, $fin])->sum('valor_estimado');
                $cultivosCosechar = DB::table('cultivos')->where('usuario_id', $uid)
                    ->where('estado', 'activo')->whereNotNull('fecha_cosecha_estimada')
                    ->whereBetween('fecha_cosecha_estimada',
                        [$hoy, now()->addDays(15)->toDateString()])->count();
            } catch (\Exception $e) {}
            $kpis['cultivos'] = [
                'titulo' => 'Cultivos', 'emoji' => '🌱', 'color' => 'verde',
                'metricas' => [
                    ['valor' => $cosechasMes,   'label' => 'Cosechas del mes',  'sub' => null],
                    ['valor' => '$' . number_format($valorCosechasMes / 1000, 0) . 'k',
                     'label' => 'Valor cosechado', 'sub' => null],
                    ['valor' => $cultivosCosechar, 'label' => 'A cosechar (15d)',
                     'sub'   => $cultivosCosechar > 0 ? 'urgente' : null],
                ],
                'ruta' => route('cultivos.index'),
            ];
        }

        if (in_array('bovino', $activas)) {
            try {
                $cabezas  = DB::table('animales')->where('usuario_id', $uid)
                    ->where('estado', 'activo')
                    ->whereIn('especie', ['Ganado bovino', 'Terneros'])->sum('cantidad');
                $lecheMes = 0; $kpiLitrosVaca = '—'; $vacasLecheras = 0;
                try {
                    $lecheMes = DB::table('animal_produccion as ap')
                        ->join('animales as a', 'a.id', '=', 'ap.animal_id')
                        ->where('ap.usuario_id', $uid)
                        ->whereIn('a.especie', ['Ganado bovino'])
                        ->whereBetween('ap.fecha', [$inicio, $fin])
                        ->where('ap.tipo_produccion', 'leche')
                        ->sum('ap.cantidad');

                    // KPI: litros/vaca/día
                    $vacasLecheras = DB::table('animales')->where('usuario_id', $uid)
                        ->where('estado', 'activo')
                        ->where('especie', 'Ganado bovino')
                        ->where('produccion', 'like', '%leche%')->count();
                    $diasMes = now()->daysInMonth;
                    if ($vacasLecheras > 0 && $lecheMes > 0) {
                        $kpiLitrosVaca = round($lecheMes / ($vacasLecheras * $diasMes), 1) . ' L/vaca';
                    }
                } catch (\Exception $e) {}
                $kpis['bovino'] = [
                    'titulo' => 'Vacuno', 'emoji' => '🐄', 'color' => 'marron',
                    'metricas' => [
                        ['valor' => $cabezas,   'label' => 'Cabezas activas', 'sub' => null],
                        ['valor' => $lecheMes > 0 ? number_format($lecheMes, 0) . ' L' : '—',
                         'label' => 'Leche del mes', 'sub' => null],
                        ['valor' => $kpiLitrosVaca, 'label' => 'L/vaca/dia', 'sub' => null],
                    ],
                    'ruta' => route('bovino.hato'),
                ];
            } catch (\Exception $e) {}
        }

        if (in_array('avicola', $activas)) {
            try {
                $aves = DB::table('animales')->where('usuario_id', $uid)
                    ->where('estado', 'activo')
                    ->whereIn('especie', ['Gallinas', 'Patos', 'Pavos'])->sum('cantidad');
                $huevosHoy = 0; $huevosMes = 0; $kpiHuevoAve = '—';
                try {
                    $huevosHoy = DB::table('animal_produccion as ap')
                        ->join('animales as a', 'a.id', '=', 'ap.animal_id')
                        ->where('ap.usuario_id', $uid)->where('ap.tipo_produccion', 'huevos')
                        ->whereDate('ap.fecha', $hoy)->sum('ap.cantidad');
                    $huevosMes = DB::table('animal_produccion as ap')
                        ->join('animales as a', 'a.id', '=', 'ap.animal_id')
                        ->where('ap.usuario_id', $uid)->where('ap.tipo_produccion', 'huevos')
                        ->whereBetween('ap.fecha', [$inicio, $fin])->sum('ap.cantidad');
                    // KPI: huevos/ave/día
                    $diasMes = now()->day; // días transcurridos del mes
                    if ($aves > 0 && $huevosMes > 0 && $diasMes > 0) {
                        $kpiHuevoAve = round($huevosMes / ($aves * $diasMes), 2) . '/ave';
                    }
                } catch (\Exception $e) {}
                $kpis['avicola'] = [
                    'titulo' => 'Avicola', 'emoji' => '🐔', 'color' => 'naranja',
                    'metricas' => [
                        ['valor' => $aves, 'label' => 'Aves activas', 'sub' => null],
                        ['valor' => $huevosHoy > 0 ? number_format($huevosHoy, 0) : '—',
                         'label' => 'Huevos hoy', 'sub' => null],
                        ['valor' => $kpiHuevoAve, 'label' => 'Huevos/ave/dia', 'sub' => null],
                    ],
                    'ruta' => route('avicola.galpon'),
                ];
            } catch (\Exception $e) {}
        }

        if (in_array('porcino', $activas)) {
            try {
                $cerdos    = DB::table('animales')->where('usuario_id', $uid)
                    ->where('estado', 'activo')
                    ->whereIn('especie', ['Cerdos', 'Cerdas de cría'])->sum('cantidad');
                $hembrasPreniadas = 0; $partosAnio = 0;
                try {
                    $hembrasPreniadas = DB::table('porcicola_camadas')
                        ->where('usuario_id', $uid)->whereNull('fecha_parto_real')
                        ->where('resultado_diagnostico', 'positivo')->count();
                    $partosAnio = DB::table('porcicola_camadas')
                        ->where('usuario_id', $uid)->whereNotNull('fecha_parto_real')
                        ->whereYear('fecha_parto_real', now()->year)->count();
                } catch (\Exception $e) {}
                $kpis['porcino'] = [
                    'titulo' => 'Porcicola', 'emoji' => '🐷', 'color' => 'rosa',
                    'metricas' => [
                        ['valor' => $cerdos,          'label' => 'Cerdos activos', 'sub' => null],
                        ['valor' => $hembrasPreniadas, 'label' => 'Hembras prenadas', 'sub' => null],
                        ['valor' => $partosAnio,       'label' => 'Partos este anio', 'sub' => null],
                    ],
                    'ruta' => route('porcicola.piara'),
                ];
            } catch (\Exception $e) {}
        }

        if (in_array('piscicola', $activas)) {
            try {
                $estanquesActivos = DB::table('piscicola_estanques')
                    ->where('usuario_id', $uid)->where('estado', 'activo')->count();
                $biomasaTotal = DB::table('piscicola_siembras')
                    ->where('usuario_id', $uid)->where('activo', 1)
                    ->sum('biomasa_actual_kg');
                $kgM2 = '—';
                try {
                    $areaTotal = DB::table('piscicola_estanques as e')
                        ->join('piscicola_siembras as s', 's.estanque_id', '=', 'e.id')
                        ->where('e.usuario_id', $uid)->where('s.activo', 1)
                        ->sum('e.area_m2');
                    if ($areaTotal > 0 && $biomasaTotal > 0) {
                        $kgM2 = round($biomasaTotal / $areaTotal, 2) . ' kg/m2';
                    }
                } catch (\Exception $e) {}
                $kpis['piscicola'] = [
                    'titulo' => 'Piscicola', 'emoji' => '🐟', 'color' => 'azul',
                    'metricas' => [
                        ['valor' => $estanquesActivos, 'label' => 'Estanques activos', 'sub' => null],
                        ['valor' => number_format((float)$biomasaTotal, 1) . ' kg',
                         'label' => 'Biomasa total', 'sub' => null],
                        ['valor' => $kgM2, 'label' => 'kg/m2 actual', 'sub' => null],
                    ],
                    'ruta' => route('piscicola.estanques'),
                ];
            } catch (\Exception $e) {}
        }

        if (in_array('caprino_ovino', $activas)) {
            try {
                $cabras = DB::table('animales')->where('usuario_id', $uid)
                    ->where('estado', 'activo')
                    ->whereIn('especie', ['Cabras', 'Ovejas'])->sum('cantidad');
                $kpis['caprino_ovino'] = [
                    'titulo' => 'Cabras y ovejas', 'emoji' => '🐐', 'color' => 'marron',
                    'metricas' => [['valor' => $cabras, 'label' => 'Cabezas activas', 'sub' => null]],
                    'ruta' => route('animales.index') . '?especie=Cabras',
                ];
            } catch (\Exception $e) {}
        }

        if (in_array('apicola', $activas)) {
            $kpis['apicola'] = [
                'titulo' => 'Apicola', 'emoji' => '🐝', 'color' => 'amarillo',
                'metricas' => [['valor' => '—', 'label' => 'Colmenas (proximo)', 'sub' => null]],
                'ruta' => route('animales.index'),
            ];
        }

        if (in_array('equino', $activas)) {
            try {
                $equinos = DB::table('animales')->where('usuario_id', $uid)
                    ->where('estado', 'activo')->where('especie', 'Caballos')->sum('cantidad');
                $kpis['equino'] = [
                    'titulo' => 'Equinos', 'emoji' => '🐴', 'color' => 'marron',
                    'metricas' => [['valor' => $equinos, 'label' => 'Caballos activos', 'sub' => null]],
                    'ruta' => route('animales.index') . '?especie=Caballos',
                ];
            } catch (\Exception $e) {}
        }

        if (in_array('cunicola', $activas)) {
            try {
                $conejos = DB::table('animales')->where('usuario_id', $uid)
                    ->where('estado', 'activo')->where('especie', 'Conejos')->sum('cantidad');
                $kpis['cunicola'] = [
                    'titulo' => 'Conejos', 'emoji' => '🐰', 'color' => 'rosa',
                    'metricas' => [['valor' => $conejos, 'label' => 'Conejos activos', 'sub' => null]],
                    'ruta' => route('animales.index') . '?especie=Conejos',
                ];
            } catch (\Exception $e) {}
        }

        return $kpis;
    }
}