<?php
namespace App\Http\Controllers;

use App\Models\LineaProductiva;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $uid  = session('usuario_id');
        $user = DB::table('usuarios')->find($uid);
        if ($user && !isset($user->created_at)) $user->created_at = $user->creado_en ?? null;

        $inicio   = now()->startOfMonth()->toDateString();
        $fin      = now()->endOfMonth()->toDateString();
        $hoy      = now()->toDateString();

        // ── Stats principales (transversales) ─────────────────────
        $cultivosActivos = DB::table('cultivos')->where('usuario_id',$uid)->where('estado','activo')->count();
        $gastosMes       = DB::table('gastos')->where('usuario_id',$uid)->whereBetween('fecha',[$inicio,$fin])->sum('valor');
        $ingresosMes     = DB::table('ingresos')->where('usuario_id',$uid)->whereBetween('fecha',[$inicio,$fin])->sum('valor_total');
        $tareasPend      = DB::table('tareas')->where('usuario_id',$uid)->where('completada',0)->where('fecha','<=',$hoy)->count();
        $animalesActivos = DB::table('animales')->where('usuario_id',$uid)->where('estado','activo')->sum('cantidad');

        // Tareas de hoy y vencidas
        $tareasHoy = DB::table('tareas')->where('usuario_id',$uid)->where('completada',0)
            ->whereDate('fecha',$hoy)->orderByRaw("FIELD(prioridad,'alta','media','baja')")->limit(4)->get();
        $tareasVencidas = DB::table('tareas')->where('usuario_id',$uid)->where('completada',0)
            ->where('fecha','<',$hoy)->count();

        // Últimos cultivos
        $recentCultivos = DB::table('cultivos')->where('usuario_id',$uid)->orderBy('id','desc')->limit(3)->get();

        // Alertas inventario
        $alertasInventario = DB::table('inventario')->where('usuario_id',$uid)->whereRaw('cantidad_actual <= stock_minimo')->count();

        // Próximas dosis animales
        try {
            $proximasDosis = DB::table('animal_eventos as ae')
                ->join('animales as a','a.id','=','ae.animal_id')
                ->where('ae.usuario_id',$uid)->whereNotNull('ae.proxima_dosis')
                ->whereBetween('ae.proxima_dosis',[$hoy, now()->addDays(7)->toDateString()])
                ->select('ae.titulo','ae.proxima_dosis','a.nombre_lote','a.especie')
                ->orderBy('ae.proxima_dosis')->limit(3)->get();
        } catch (\Exception $e) { $proximasDosis = collect(); }

        // Personas: pagado este mes a trabajadores
        try {
            $pagadoMesPersonas = DB::table('persona_pagos')->where('usuario_id',$uid)
                ->whereMonth('fecha',now()->month)->whereYear('fecha',now()->year)->sum('valor');
            $trabajadoresActivos = DB::table('personas')->where('usuario_id',$uid)->where('tipo','trabajador')->where('activo',1)->count();
        } catch (\Exception $e) { $pagadoMesPersonas = 0; $trabajadoresActivos = 0; }

        // Top cultivo rentable
        $topCultivo = DB::table('cultivos as c')->where('c.usuario_id',$uid)
            ->selectRaw('c.id, c.nombre, c.tipo,
                COALESCE((SELECT SUM(valor_total) FROM ingresos WHERE cultivo_id=c.id AND YEAR(fecha)=YEAR(CURDATE())),0)
                - COALESCE((SELECT SUM(valor) FROM gastos WHERE cultivo_id=c.id AND YEAR(fecha)=YEAR(CURDATE())),0)
                AS rentabilidad')
            ->orderByDesc('rentabilidad')->first();

        // Cosechas recientes
        $cosechasRecientes = DB::table('cosechas')->where('usuario_id',$uid)->orderBy('fecha_cosecha','desc')->limit(2)->get();

        // ══════════════════════════════════════════════════════════
        // KPIs ADAPTATIVOS POR LÍNEA PRODUCTIVA
        // Cada bloque solo se calcula si la línea está activa (ahorra
        // queries innecesarias para usuarios que no tienen esa línea).
        // ══════════════════════════════════════════════════════════
        $lineasActivas = LineaProductiva::activasDelUsuario($uid);
        $kpisLineas    = $this->calcularKpisPorLinea($uid, $lineasActivas, $hoy, $inicio, $fin);

        return view('pages.dashboard', compact(
            'user','cultivosActivos','gastosMes','ingresosMes','tareasPend',
            'tareasHoy','tareasVencidas','recentCultivos','alertasInventario',
            'topCultivo','cosechasRecientes','animalesActivos',
            'proximasDosis','pagadoMesPersonas','trabajadoresActivos',
            'kpisLineas'
        ));
    }

    /**
     * Calcula los KPIs específicos de cada línea productiva activa.
     * Devuelve un array asociativo: ['bovino' => [...], 'avicola' => [...], ...]
     *
     * Cada bloque tiene la forma:
     *   ['titulo' => string, 'emoji' => string, 'metricas' => [['valor','label'], ...]]
     *
     * Diseño defensivo: cada try/catch evita que una tabla faltante
     * (ej. animal_produccion no migrada todavía) tumbe el dashboard.
     */
    private function calcularKpisPorLinea(int $uid, array $activas, string $hoy, string $inicio, string $fin): array
    {
        $kpis = [];

        // ── CULTIVOS ──────────────────────────────────────────────
        if (in_array('cultivos', $activas)) {
            $cosechasMes = 0; $valorCosechasMes = 0; $cultivosCosechar = 0;
            try {
                $cosechasMes = DB::table('cosechas')->where('usuario_id',$uid)
                    ->whereBetween('fecha_cosecha',[$inicio,$fin])->count();
                $valorCosechasMes = DB::table('cosechas')->where('usuario_id',$uid)
                    ->whereBetween('fecha_cosecha',[$inicio,$fin])->sum('valor_estimado');
            } catch (\Exception $e) {}
            try {
                // fecha_cosecha_estimada puede no existir en BDs antiguas
                $cultivosCosechar = DB::table('cultivos')->where('usuario_id',$uid)
                    ->where('estado','activo')
                    ->whereNotNull('fecha_cosecha_estimada')
                    ->whereBetween('fecha_cosecha_estimada',[$hoy, now()->addDays(15)->toDateString()])
                    ->count();
            } catch (\Exception $e) { $cultivosCosechar = 0; }

            $kpis['cultivos'] = [
                'titulo'   => 'Cultivos',
                'emoji'    => '🌱',
                'color'    => 'verde',
                'metricas' => [
                    ['valor' => $cosechasMes, 'label' => 'Cosechas del mes', 'sub' => null],
                    ['valor' => '$'.number_format($valorCosechasMes/1000,0).'k',
                     'label' => 'Valor cosechado', 'sub' => null],
                    ['valor' => $cultivosCosechar, 'label' => 'A cosechar (15d)',
                     'sub'   => $cultivosCosechar > 0 ? 'urgente' : null],
                ],
            ];
        }

        // ── BOVINO ────────────────────────────────────────────────
        if (in_array('bovino', $activas)) {
            try {
                $cabezasBovinas = DB::table('animales')->where('usuario_id',$uid)
                    ->where('estado','activo')
                    ->whereIn('especie',['Ganado bovino','Terneros'])->sum('cantidad');

                // Si tiene tabla de producción animal, sacar leche del mes
                $lecheMes = 0;
                try {
                    $lecheMes = DB::table('animal_produccion as ap')
                        ->join('animales as a','a.id','=','ap.animal_id')
                        ->where('ap.usuario_id',$uid)
                        ->whereIn('a.especie',['Ganado bovino'])
                        ->whereBetween('ap.fecha',[$inicio,$fin])
                        ->where('ap.tipo','leche')
                        ->sum('ap.cantidad');
                } catch (\Exception $e) {}

                $kpis['bovino'] = [
                    'titulo'   => 'Vacuno',
                    'emoji'    => '🐄',
                    'color'    => 'marron',
                    'metricas' => [
                        ['valor' => $cabezasBovinas, 'label' => 'Cabezas activas', 'sub' => null],
                        ['valor' => $lecheMes > 0 ? number_format($lecheMes,0).' L' : '—',
                         'label' => 'Leche del mes',  'sub' => null],
                    ],
                ];
            } catch (\Exception $e) {}
        }

        // ── AVÍCOLA ───────────────────────────────────────────────
        if (in_array('avicola', $activas)) {
            try {
                $aves = DB::table('animales')->where('usuario_id',$uid)
                    ->where('estado','activo')
                    ->whereIn('especie',['Gallinas','Patos','Pavos'])->sum('cantidad');

                // Huevos producidos hoy y este mes
                $huevosHoy = 0; $huevosMes = 0;
                try {
                    $huevosHoy = DB::table('animal_produccion as ap')
                        ->join('animales as a','a.id','=','ap.animal_id')
                        ->where('ap.usuario_id',$uid)
                        ->where('ap.tipo','huevos')
                        ->whereDate('ap.fecha',$hoy)
                        ->sum('ap.cantidad');
                    $huevosMes = DB::table('animal_produccion as ap')
                        ->join('animales as a','a.id','=','ap.animal_id')
                        ->where('ap.usuario_id',$uid)
                        ->where('ap.tipo','huevos')
                        ->whereBetween('ap.fecha',[$inicio,$fin])
                        ->sum('ap.cantidad');
                } catch (\Exception $e) {}

                $kpis['avicola'] = [
                    'titulo'   => 'Avícola',
                    'emoji'    => '🐔',
                    'color'    => 'naranja',
                    'metricas' => [
                        ['valor' => $aves, 'label' => 'Aves activas', 'sub' => null],
                        ['valor' => $huevosHoy > 0 ? number_format($huevosHoy,0) : '—',
                         'label' => 'Huevos hoy', 'sub' => null],
                        ['valor' => $huevosMes > 0 ? number_format($huevosMes,0) : '—',
                         'label' => 'Huevos del mes', 'sub' => null],
                    ],
                ];
            } catch (\Exception $e) {}
        }

        // ── PORCINO ───────────────────────────────────────────────
        if (in_array('porcino', $activas)) {
            try {
                $cerdos = DB::table('animales')->where('usuario_id',$uid)
                    ->where('estado','activo')
                    ->whereIn('especie',['Cerdos','Cerdas de cría'])->sum('cantidad');
                $kpis['porcino'] = [
                    'titulo'   => 'Porcícola',
                    'emoji'    => '🐷',
                    'color'    => 'rosa',
                    'metricas' => [
                        ['valor' => $cerdos, 'label' => 'Cerdos activos', 'sub' => null],
                    ],
                ];
            } catch (\Exception $e) {}
        }

        // ── PISCÍCOLA ─────────────────────────────────────────────
        if (in_array('piscicola', $activas)) {
            try {
                $peces = DB::table('animales')->where('usuario_id',$uid)
                    ->where('estado','activo')
                    ->whereIn('especie',['Peces'])->sum('cantidad');
                $kpis['piscicola'] = [
                    'titulo'   => 'Piscícola',
                    'emoji'    => '🐟',
                    'color'    => 'azul',
                    'metricas' => [
                        ['valor' => number_format($peces,0), 'label' => 'Peces activos', 'sub' => null],
                    ],
                ];
            } catch (\Exception $e) {}
        }

        // ── CAPRINO/OVINO ─────────────────────────────────────────
        if (in_array('caprino_ovino', $activas)) {
            try {
                $cabras = DB::table('animales')->where('usuario_id',$uid)
                    ->where('estado','activo')
                    ->whereIn('especie',['Cabras','Ovejas'])->sum('cantidad');
                $kpis['caprino_ovino'] = [
                    'titulo'   => 'Cabras y ovejas',
                    'emoji'    => '🐐',
                    'color'    => 'marron',
                    'metricas' => [
                        ['valor' => $cabras, 'label' => 'Cabezas activas', 'sub' => null],
                    ],
                ];
            } catch (\Exception $e) {}
        }

        // ── APÍCOLA ───────────────────────────────────────────────
        if (in_array('apicola', $activas)) {
            // Aún no tenemos tabla de colmenas; mostramos stub.
            $kpis['apicola'] = [
                'titulo'   => 'Apícola',
                'emoji'    => '🐝',
                'color'    => 'amarillo',
                'metricas' => [
                    ['valor' => '—', 'label' => 'Colmenas (próximamente)', 'sub' => null],
                ],
            ];
        }

        // ── EQUINO ────────────────────────────────────────────────
        if (in_array('equino', $activas)) {
            try {
                $equinos = DB::table('animales')->where('usuario_id',$uid)
                    ->where('estado','activo')
                    ->whereIn('especie',['Caballos'])->sum('cantidad');
                $kpis['equino'] = [
                    'titulo'   => 'Equinos',
                    'emoji'    => '🐴',
                    'color'    => 'marron',
                    'metricas' => [
                        ['valor' => $equinos, 'label' => 'Caballos activos', 'sub' => null],
                    ],
                ];
            } catch (\Exception $e) {}
        }

        // ── CUNÍCOLA ──────────────────────────────────────────────
        if (in_array('cunicola', $activas)) {
            try {
                $conejos = DB::table('animales')->where('usuario_id',$uid)
                    ->where('estado','activo')
                    ->whereIn('especie',['Conejos'])->sum('cantidad');
                $kpis['cunicola'] = [
                    'titulo'   => 'Conejos',
                    'emoji'    => '🐰',
                    'color'    => 'rosa',
                    'metricas' => [
                        ['valor' => $conejos, 'label' => 'Conejos activos', 'sub' => null],
                    ],
                ];
            } catch (\Exception $e) {}
        }

        return $kpis;
    }
}