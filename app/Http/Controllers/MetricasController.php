<?php

namespace App\Http\Controllers;

use App\Models\Ingreso;
use App\Models\Gasto;
use App\Models\Cosecha;
use App\Models\Cultivo;
use App\Models\Animal;
use App\Models\Tarea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MetricasController extends Controller
{
    private function supabaseUrl(): string
    {
        return env('SUPABASE_URL') . '/rest/v1/metricas_periodo';
    }

    private function headers(): array
    {
        return [
            'apikey'        => env('SUPABASE_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_KEY'),
            'Content-Type'  => 'application/json',
            'Prefer'        => 'return=minimal',
        ];
    }

    /**
     * Recopila métricas reales del sistema y las envía a Supabase.
     *
     * Puede recibir el parámetro ?periodo=2026-03 (por defecto el mes anterior).
     * Se llama desde el botón en la página de Reportes.
     */
    public function enviarSnapshot(Request $request)
    {
        $uid     = session('usuario_id');
        $periodo = $request->periodo ?? Carbon::now()->subMonth()->format('Y-m');

        [$anio, $mes] = explode('-', $periodo);
        $anio = (int) $anio;
        $mes  = (int) $mes;

        // ── Financiero ────────────────────────────────────────────
        $totalIngresos = Ingreso::delUsuario($uid)
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio)
            ->sum('valor_total');

        $totalGastos = Gasto::delUsuario($uid)
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio)
            ->sum('valor');

        $balance    = $totalIngresos - $totalGastos;
        $roi        = $totalGastos > 0
            ? round(($totalIngresos - $totalGastos) / $totalGastos * 100, 2)
            : null;

        // ── Actividad registrada ──────────────────────────────────
        $numCosechas = Cosecha::delUsuario($uid)
            ->whereMonth('fecha_cosecha', $mes)
            ->whereYear('fecha_cosecha', $anio)
            ->count();

        $numCultivosActivos = Cultivo::delUsuario($uid)
            ->where('estado', 'activo')
            ->count();

        $numAnimales = Animal::delUsuario($uid)
            ->activos()
            ->count();

        $numGastos = Gasto::delUsuario($uid)
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio)
            ->count();

        $numIngresos = Ingreso::delUsuario($uid)
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio)
            ->count();

        $numTareas = Tarea::delUsuario($uid)
            ->where('completada', 1)
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio)
            ->count();

        // ── Envío a Supabase ──────────────────────────────────────
        $datos = [
            'usuario_id'               => (string) $uid,
            'instancia'                => config('app.name'),
            'periodo'                  => $periodo,
            'tipo_periodo'             => 'mensual',
            'total_ingresos'           => (float) $totalIngresos,
            'total_gastos'             => (float) $totalGastos,
            'balance'                  => (float) $balance,
            'roi_promedio'             => $roi,
            'num_cosechas'             => $numCosechas,
            'num_cultivos_activos'     => $numCultivosActivos,
            'num_animales'             => $numAnimales,
            'num_gastos_registrados'   => $numGastos,
            'num_ingresos_registrados' => $numIngresos,
            'num_tareas_completadas'   => $numTareas,
        ];

        $response = Http::withOptions(['verify' => false])->withHeaders($this->headers())
            ->post($this->supabaseUrl(), $datos);

        if ($response->successful()) {
            return redirect()->back()
                ->with('msg', "✅ Métricas de $periodo enviadas correctamente a la base de investigación.")
                ->with('msgType', 'success');
        }

        return redirect()->back()
            ->with('msg', '⚠️ No se pudieron enviar las métricas. Revisa la conexión a Supabase.')
            ->with('msgType', 'error');
    }

    /**
     * Envía snapshots de TODOS los meses desde que el usuario se registró.
     * Útil para cargar el historial completo la primera vez.
     */
    public function enviarHistorial(Request $request)
    {
        $uid      = session('usuario_id');
        $enviados = 0;
        $errores  = 0;

        // Obtener el primer mes con registros
        $primerIngreso = Ingreso::delUsuario($uid)->min('fecha');
        $primerGasto   = Gasto::delUsuario($uid)->min('fecha');

        $primero = collect([$primerIngreso, $primerGasto])
            ->filter()
            ->min();

        if (!$primero) {
            return redirect()->back()
                ->with('msg', 'No hay datos suficientes para generar el historial.')
                ->with('msgType', 'warning');
        }

        $inicio = Carbon::parse($primero)->startOfMonth();
        $fin    = Carbon::now()->subMonth()->startOfMonth();

        while ($inicio->lte($fin)) {
            $periodo = $inicio->format('Y-m');
            [$anio, $mes] = explode('-', $periodo);

            $datos = [
                'usuario_id'               => (string) $uid,
                'instancia'                => config('app.name'),
                'periodo'                  => $periodo,
                'tipo_periodo'             => 'mensual',
                'total_ingresos'           => (float) Ingreso::delUsuario($uid)->whereMonth('fecha', (int)$mes)->whereYear('fecha', (int)$anio)->sum('valor_total'),
                'total_gastos'             => (float) Gasto::delUsuario($uid)->whereMonth('fecha', (int)$mes)->whereYear('fecha', (int)$anio)->sum('valor'),
                'balance'                  => (float) (Ingreso::delUsuario($uid)->whereMonth('fecha', (int)$mes)->whereYear('fecha', (int)$anio)->sum('valor_total') - Gasto::delUsuario($uid)->whereMonth('fecha', (int)$mes)->whereYear('fecha', (int)$anio)->sum('valor')),
                'num_cosechas'             => Cosecha::delUsuario($uid)->whereMonth('fecha_cosecha', (int)$mes)->whereYear('fecha_cosecha', (int)$anio)->count(),
                'num_cultivos_activos'     => Cultivo::delUsuario($uid)->where('estado', 'activo')->count(),
                'num_animales'             => Animal::delUsuario($uid)->activos()->count(),
                'num_gastos_registrados'   => Gasto::delUsuario($uid)->whereMonth('fecha', (int)$mes)->whereYear('fecha', (int)$anio)->count(),
                'num_ingresos_registrados' => Ingreso::delUsuario($uid)->whereMonth('fecha', (int)$mes)->whereYear('fecha', (int)$anio)->count(),
                'num_tareas_completadas'   => Tarea::delUsuario($uid)->where('completada', 1)->whereMonth('fecha', (int)$mes)->whereYear('fecha', (int)$anio)->count(),
            ];

            $response = Http::withOptions(['verify' => false])->withHeaders($this->headers())
                ->post($this->supabaseUrl(), $datos);

            $response->successful() ? $enviados++ : $errores++;

            $inicio->addMonth();
        }

        $msg = "Historial enviado: $enviados períodos.";
        if ($errores > 0) $msg .= " ($errores con error)";

        return redirect()->back()
            ->with('msg', $msg)
            ->with('msgType', $errores === 0 ? 'success' : 'warning');
    }
}