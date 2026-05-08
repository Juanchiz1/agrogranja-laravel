<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PiscicolaController extends Controller
{
    // Rangos ideales de calidad del agua por especie
    private const RANGOS_AGUA = [
        'default' => [
            'oxigeno'     => ['min' => 5.0,  'max' => 12.0, 'alerta_min' => 3.0],
            'ph'          => ['min' => 6.5,  'max' => 8.5,  'alerta_min' => 6.0, 'alerta_max' => 9.0],
            'temperatura' => ['min' => 24.0, 'max' => 32.0],
            'amonio'      => ['max' => 0.02],
            'nitrito'     => ['max' => 0.10],
        ],
        'Trucha' => [
            'oxigeno'     => ['min' => 7.0,  'max' => 12.0],
            'ph'          => ['min' => 6.5,  'max' => 8.0],
            'temperatura' => ['min' => 10.0, 'max' => 18.0],
        ],
    ];

    // ─────────────────────────────────────────────────────────────────
    // ESTANQUES — Dashboard principal
    // ─────────────────────────────────────────────────────────────────

   public function estanques()
{
    $uid = session('usuario_id');
    $hoy = now()->toDateString();

    $estanques = DB::table('piscicola_estanques')
        ->where('usuario_id', $uid)
        ->orderByRaw("FIELD(estado,'activo','vacio','mantenimiento','cosechado')")
        ->orderBy('nombre')
        ->get();

    $estanques = $estanques->map(function ($e) use ($uid) {
        $siembraActiva = DB::table('piscicola_siembras')
            ->where('estanque_id', $e->id)
            ->where('usuario_id', $uid)
            ->where('activo', 1)
            ->orderByDesc('fecha_siembra')
            ->first();

        $e->siembra_activa = $siembraActiva;

        if ($siembraActiva) {
            $e->dias_cultivo = Carbon::parse($siembraActiva->fecha_siembra)->diffInDays(now());
            $e->ultimo_muestreo = DB::table('piscicola_muestreos')
                ->where('siembra_id', $siembraActiva->id)
                ->orderByDesc('fecha')->first();
            $e->mortalidad_acum = DB::table('piscicola_mortalidad')
                ->where('siembra_id', $siembraActiva->id)->sum('cantidad');
            $e->sobrevivencia_pct = $siembraActiva->cantidad_alevinos > 0
                ? round((($siembraActiva->cantidad_alevinos - $e->mortalidad_acum)
                    / $siembraActiva->cantidad_alevinos) * 100, 1)
                : 100;
            $e->sobrevivencia = $e->sobrevivencia_pct; // ← alias para la vista
        } else {
            $e->dias_cultivo      = null;
            $e->ultimo_muestreo   = null;
            $e->mortalidad_acum   = 0;
            $e->sobrevivencia_pct = null;
            $e->sobrevivencia     = null; // ← también aquí
        }

        $e->ultima_agua = DB::table('piscicola_calidad_agua')
            ->where('estanque_id', $e->id)
            ->orderByDesc('fecha')->orderByDesc('creado_en')->first();

        return $e;
    });

    $totalEstanques = $estanques->count();
    $totalActivos   = $estanques->where('estado', 'activo')->count();
    $totalAreaM2    = $estanques->where('estado', 'activo')->sum('area_m2');
    $totalBiomasa   = $estanques->sum(fn($e) => $e->siembra_activa->biomasa_actual_kg ?? 0);

    $alertasAgua = DB::table('piscicola_calidad_agua')
        ->where('usuario_id', $uid)
        ->where('alerta', 1)
        ->where('fecha', '>=', now()->subDays(2)->toDateString())
        ->orderByDesc('fecha')
        ->get();

    return view('pages.piscicola.estanques', compact(
        'estanques', 'totalEstanques', 'totalActivos',
        'totalAreaM2', 'totalBiomasa', 'alertasAgua'
    ));
}

    public function storeEstanque(Request $request)
    {
        $request->validate([
            'nombre'   => 'required|string|max:120',
            'area_m2'  => 'required|numeric|min:1',
            'especie_cultivada' => 'required',
        ]);

        $uid     = session('usuario_id');
        $volumen = null;
        if ($request->area_m2 && $request->profundidad_m) {
            $volumen = round($request->area_m2 * $request->profundidad_m, 2);
        }

        DB::table('piscicola_estanques')->insert([
            'usuario_id'        => $uid,
            'nombre'            => $request->nombre,
            'tipo'              => $request->tipo ?? 'tierra',
            'especie_cultivada' => $request->especie_cultivada,
            'area_m2'           => $request->area_m2,
            'profundidad_m'     => $request->profundidad_m ?: null,
            'volumen_m3'        => $volumen,
            'ubicacion'         => $request->ubicacion ?: null,
            'estado'            => 'vacio',
            'notas'             => $request->notas ?: null,
            'creado_en'         => now()->toDateTimeString(),
            'actualizado_en'    => now()->toDateTimeString(),
        ]);

        return redirect()->route('piscicola.estanques')
            ->with('msg', 'Estanque "' . $request->nombre . '" registrado.')
            ->with('msgType', 'success');
    }

    public function updateEstanque(Request $request, $id)
    {
        $uid = session('usuario_id');
        $volumen = null;
        if ($request->area_m2 && $request->profundidad_m) {
            $volumen = round($request->area_m2 * $request->profundidad_m, 2);
        }

        DB::table('piscicola_estanques')
            ->where('id', $id)->where('usuario_id', $uid)
            ->update([
                'nombre'            => $request->nombre,
                'tipo'              => $request->tipo ?? 'tierra',
                'especie_cultivada' => $request->especie_cultivada,
                'area_m2'           => $request->area_m2,
                'profundidad_m'     => $request->profundidad_m ?: null,
                'volumen_m3'        => $volumen,
                'ubicacion'         => $request->ubicacion ?: null,
                'estado'            => $request->estado,
                'notas'             => $request->notas ?: null,
                'actualizado_en'    => now()->toDateTimeString(),
            ]);

        return redirect()->route('piscicola.estanques')
            ->with('msg', 'Estanque actualizado.')->with('msgType', 'success');
    }

    // ─────────────────────────────────────────────────────────────────
    // SIEMBRA — Registrar alevinos
    // ─────────────────────────────────────────────────────────────────

    public function siembra()
    {
        $uid = session('usuario_id');

        $estanques = DB::table('piscicola_estanques')
            ->where('usuario_id', $uid)
            ->whereIn('estado', ['vacio', 'activo'])
            ->orderBy('nombre')->get();

        $siembras = DB::table('piscicola_siembras as s')
            ->join('piscicola_estanques as e', 'e.id', '=', 's.estanque_id')
            ->where('s.usuario_id', $uid)
            ->select('s.*', 'e.nombre as nombre_estanque', 'e.area_m2', 'e.especie_cultivada')
            ->orderByDesc('s.fecha_siembra')
            ->get()
            ->map(function ($s) {
                $s->dias_cultivo = Carbon::parse($s->fecha_siembra)->diffInDays(now());
                $mortalidad = DB::table('piscicola_mortalidad')
                    ->where('siembra_id', $s->id)->sum('cantidad');
                $s->mortalidad_total  = $mortalidad;
                $s->sobrevivencia_pct = $s->cantidad_alevinos > 0
                    ? round((($s->cantidad_alevinos - $mortalidad) / $s->cantidad_alevinos) * 100, 1)
                    : 100;
                $s->sobrevivencia     = $s->sobrevivencia_pct;
                $s->especie           = $s->especie_cultivada ?? $s->especie ?? '—';
                // Último muestreo para mostrar ganancia diaria
                $ultiMuest = DB::table('piscicola_muestreos')
                    ->where('siembra_id', $s->id)->orderByDesc('fecha')->first();
                $s->muestreos        = $ultiMuest ? collect([$ultiMuest]) : collect();
                $s->ultimo_muestreo  = $ultiMuest;
                return $s;
            });

        return view('pages.piscicola.siembra', compact('estanques', 'siembras'));
    }

    public function storeSiembra(Request $request)
    {
        $request->validate([
            'estanque_id'             => 'required|integer',
            'fecha_siembra'           => 'required|date',
            'especie'                 => 'required|string',
            'cantidad_alevinos'       => 'required|integer|min:1',
            'peso_promedio_inicial_g' => 'required|numeric|min:0.001',
        ]);

        $uid      = session('usuario_id');
        $estanque = DB::table('piscicola_estanques')
            ->where('id', $request->estanque_id)->where('usuario_id', $uid)->first();
        if (!$estanque) abort(404);

        $biomasaInicial = round($request->cantidad_alevinos * $request->peso_promedio_inicial_g / 1000, 3);
        $densidad       = $estanque->area_m2 > 0
            ? round($request->cantidad_alevinos / $estanque->area_m2, 2)
            : null;

        DB::table('piscicola_siembras')->insert([
            'estanque_id'             => $request->estanque_id,
            'usuario_id'              => $uid,
            'fecha_siembra'           => $request->fecha_siembra,
            'especie'                 => $request->especie,
            'cantidad_alevinos'       => $request->cantidad_alevinos,
            'peso_promedio_inicial_g' => $request->peso_promedio_inicial_g,
            'biomasa_inicial_kg'      => $biomasaInicial,
            'densidad_peces_m2'       => $densidad,
            'proveedor'               => $request->proveedor ?: null,
            'costo_alevinos'          => $request->costo_alevinos ?: null,
            'temperatura_recepcion'   => $request->temperatura_recepcion ?: null,
            'observaciones'           => $request->observaciones ?: null,
            'cantidad_actual'         => $request->cantidad_alevinos,
            'peso_promedio_actual_g'  => $request->peso_promedio_inicial_g,
            'biomasa_actual_kg'       => $biomasaInicial,
            'alimento_acumulado_kg'   => 0,
            'activo'                  => 1,
            'creado_en'               => now()->toDateTimeString(),
            'actualizado_en'          => now()->toDateTimeString(),
        ]);

        // Activar estanque
        DB::table('piscicola_estanques')
            ->where('id', $request->estanque_id)
            ->update(['estado' => 'activo', 'actualizado_en' => now()->toDateTimeString()]);

        return redirect()->route('piscicola.siembra')
            ->with('msg', 'Siembra registrada: ' . number_format($request->cantidad_alevinos) .
                   ' alevinos. Biomasa inicial: ' . $biomasaInicial . ' kg.')
            ->with('msgType', 'success');
    }

    // ─────────────────────────────────────────────────────────────────
    // ALIMENTACIÓN — Registro diario
    // ─────────────────────────────────────────────────────────────────

    public function alimentacion(Request $request)
    {
        $uid   = session('usuario_id');
        $fecha = $request->fecha ?? now()->toDateString();

        // Siembras activas
        $siembrasActivas = DB::table('piscicola_siembras as s')
            ->join('piscicola_estanques as e', 'e.id', '=', 's.estanque_id')
            ->where('s.usuario_id', $uid)->where('s.activo', 1)
            ->select('s.*', 'e.nombre as nombre_estanque', 'e.area_m2', 'e.especie_cultivada')
            ->orderBy('e.nombre')->get()
            ->map(function ($s) {
                $s->especie = $s->especie_cultivada ?? $s->especie ?? '—';
                return $s;
            });

        // Registros del día
        $alimentacionDia = DB::table('piscicola_alimentacion')
            ->where('usuario_id', $uid)->where('fecha', $fecha)
            ->get()->keyBy('siembra_id');

        // Historial últimos 30 días
        $historial = DB::table('piscicola_alimentacion as a')
            ->join('piscicola_estanques as e', 'e.id', '=', 'a.estanque_id')
            ->where('a.usuario_id', $uid)
            ->where('a.fecha', '>=', now()->subDays(30)->toDateString())
            ->select('a.*', 'e.nombre as nombre_estanque')
            ->orderByDesc('a.fecha')->limit(60)->get();

        $totalMes = DB::table('piscicola_alimentacion')
            ->where('usuario_id', $uid)
            ->whereRaw("DATE_FORMAT(fecha,'%Y-%m') = ?", [now()->format('Y-m')])
            ->sum('cantidad_kg');

        $costoMes = DB::table('piscicola_alimentacion')
            ->where('usuario_id', $uid)
            ->whereRaw("DATE_FORMAT(fecha,'%Y-%m') = ?", [now()->format('Y-m')])
            ->sum('costo_alimento');

        $totalAlimentoDia = $alimentacionDia->sum('cantidad_kg');
        $siembras        = $siembrasActivas;
        $alimentosDelDia = $alimentacionDia;

        // Datos para la gráfica de 30 días
        $chartLabels   = $historial->pluck('fecha')
            ->map(fn($f) => \Carbon\Carbon::parse($f)->format('d/m'))
            ->values()->toArray();
        $chartAlimento = $historial->pluck('cantidad_kg')
            ->map(fn($v) => round((float)$v, 2))
            ->values()->toArray();

        return view('pages.piscicola.alimentacion', compact(
            'siembrasActivas', 'siembras', 'alimentacionDia', 'alimentosDelDia', 'fecha',
            'historial', 'totalMes', 'costoMes', 'totalAlimentoDia',
            'chartLabels', 'chartAlimento'
        ));
    }

    public function storeAlimentacion(Request $request)
    {
        $request->validate([
            'siembra_id'  => 'required|integer',
            'fecha'       => 'required|date',
            'cantidad_kg' => 'required|numeric|min:0.001',
        ]);

        $uid     = session('usuario_id');
        $siembra = DB::table('piscicola_siembras')
            ->where('id', $request->siembra_id)->where('usuario_id', $uid)->first();
        if (!$siembra) abort(404);

        // Calcular tasa de alimentación si hay biomasa
        $biomasa = $siembra->biomasa_actual_kg ?? $siembra->biomasa_inicial_kg ?? 0;
        $tasa    = ($biomasa > 0)
            ? round(($request->cantidad_kg / $biomasa) * 100, 2)
            : null;

        DB::table('piscicola_alimentacion')->updateOrInsert(
            ['siembra_id' => $request->siembra_id, 'fecha' => $request->fecha],
            [
                'estanque_id'           => $siembra->estanque_id,
                'usuario_id'            => $uid,
                'tipo_alimento'         => $request->tipo_alimento ?: null,
                'cantidad_kg'           => $request->cantidad_kg,
                'tasa_alimentacion_pct' => $tasa,
                'num_raciones'          => $request->num_raciones ?? 2,
                'biomasa_referencia_kg' => $biomasa ?: null,
                'costo_alimento'        => $request->costo_alimento ?: null,
                'observaciones'         => $request->observaciones ?: null,
                'creado_en'             => now()->toDateTimeString(),
            ]
        );

        // Actualizar alimento acumulado en la siembra
        $totalAlimento = DB::table('piscicola_alimentacion')
            ->where('siembra_id', $request->siembra_id)->sum('cantidad_kg');
        DB::table('piscicola_siembras')->where('id', $request->siembra_id)
            ->update(['alimento_acumulado_kg' => $totalAlimento, 'actualizado_en' => now()->toDateTimeString()]);

        $msg = 'Alimentacion registrada: ' . $request->cantidad_kg . ' kg';
        if ($tasa) $msg .= ' (' . $tasa . '% de la biomasa)';

        return redirect()->route('piscicola.alimentacion', ['fecha' => $request->fecha])
            ->with('msg', $msg)->with('msgType', 'success');
    }

    // ─────────────────────────────────────────────────────────────────
    // MUESTREO — Actualización de biomasa
    // ─────────────────────────────────────────────────────────────────

    public function muestreo()
    {
        $uid = session('usuario_id');

        $siembrasActivas = DB::table('piscicola_siembras as s')
            ->join('piscicola_estanques as e', 'e.id', '=', 's.estanque_id')
            ->where('s.usuario_id', $uid)->where('s.activo', 1)
            ->select('s.*', 'e.nombre as nombre_estanque', 'e.area_m2', 'e.especie_cultivada')
            ->orderBy('e.nombre')->get()
            ->map(function ($s) {
                $s->especie = $s->especie_cultivada ?? $s->especie ?? '—';
                return $s;
            });

        $muestreos = DB::table('piscicola_muestreos as m')
            ->join('piscicola_estanques as e', 'e.id', '=', 'm.estanque_id')
            ->join('piscicola_siembras as s', 's.id', '=', 'm.siembra_id')
            ->where('m.usuario_id', $uid)
            ->select('m.*', 'e.nombre as nombre_estanque', 's.especie',
                     'e.especie_cultivada')
            ->orderByDesc('m.fecha')->limit(50)->get();

        return view('pages.piscicola.muestreo', compact('siembrasActivas', 'muestreos'));
    }

    public function storeMuestreo(Request $request)
    {
        $request->validate([
            'siembra_id'         => 'required|integer',
            'fecha'              => 'required|date',
            'peces_muestreados'  => 'required|integer|min:1',
            'peso_promedio_g'    => 'required|numeric|min:0.1',
        ]);

        $uid     = session('usuario_id');
        $siembra = DB::table('piscicola_siembras')
            ->where('id', $request->siembra_id)->where('usuario_id', $uid)->first();
        if (!$siembra) abort(404);

        $diasCultivo  = Carbon::parse($siembra->fecha_siembra)->diffInDays(Carbon::parse($request->fecha));
        $semanaCultivo = (int) floor($diasCultivo / 7);

        // Calcular GPD vs muestreo anterior
        $anterior = DB::table('piscicola_muestreos')
            ->where('siembra_id', $request->siembra_id)
            ->orderByDesc('fecha')->first();

        $gpd = null;
        if ($anterior) {
            $diasDiff = Carbon::parse($anterior->fecha)->diffInDays(Carbon::parse($request->fecha));
            if ($diasDiff > 0) {
                $gpd = round(($request->peso_promedio_g - $anterior->peso_promedio_g) / $diasDiff, 3);
            }
        } else {
            // GPD desde la siembra
            if ($diasCultivo > 0) {
                $gpd = round(($request->peso_promedio_g - $siembra->peso_promedio_inicial_g) / $diasCultivo, 3);
            }
        }

        // Cantidad estimada: usar la del campo si la ingresan, si no usar la actual de siembra
        $cantidadEst  = $request->cantidad_estimada ?? $siembra->cantidad_actual ?? $siembra->cantidad_alevinos;
        $biomasaEst   = round($cantidadEst * $request->peso_promedio_g / 1000, 3);

        DB::table('piscicola_muestreos')->insert([
            'siembra_id'          => $request->siembra_id,
            'estanque_id'         => $siembra->estanque_id,
            'usuario_id'          => $uid,
            'fecha'               => $request->fecha,
            'semana_cultivo'      => $semanaCultivo,
            'peces_muestreados'   => $request->peces_muestreados,
            'peso_promedio_g'     => $request->peso_promedio_g,
            'cantidad_estimada'   => $cantidadEst,
            'biomasa_estimada_kg' => $biomasaEst,
            'ganancia_diaria_g'   => $gpd,
            'observaciones'       => $request->observaciones ?: null,
            'creado_en'           => now()->toDateTimeString(),
        ]);

        // Actualizar siembra con datos actuales
        DB::table('piscicola_siembras')->where('id', $request->siembra_id)->update([
            'peso_promedio_actual_g' => $request->peso_promedio_g,
            'cantidad_actual'        => $cantidadEst,
            'biomasa_actual_kg'      => $biomasaEst,
            'actualizado_en'         => now()->toDateTimeString(),
        ]);

        $msg = 'Muestreo registrado: ' . $request->peso_promedio_g . 'g promedio · Biomasa estimada: ' . $biomasaEst . ' kg';
        if ($gpd !== null) {
            $msg .= ' · GPD: ' . $gpd . ' g/dia';
        }

        return redirect()->route('piscicola.muestreo')
            ->with('msg', $msg)->with('msgType', 'success');
    }

    // ─────────────────────────────────────────────────────────────────
    // CALIDAD DEL AGUA
    // ─────────────────────────────────────────────────────────────────

    public function calidadAgua()
    {
        $uid = session('usuario_id');

        $estanques = DB::table('piscicola_estanques')
            ->where('usuario_id', $uid)
            ->where('estado', '!=', 'cosechado')
            ->orderBy('nombre')->get();

        $registros = DB::table('piscicola_calidad_agua as c')
            ->join('piscicola_estanques as e', 'e.id', '=', 'c.estanque_id')
            ->where('c.usuario_id', $uid)
            ->select('c.*', 'e.nombre as nombre_estanque', 'e.especie_cultivada')
            ->orderByDesc('c.fecha')->orderByDesc('c.creado_en')
            ->limit(50)->get();

        // Último registro por estanque para el resumen
        $ultimosPorEstanque = [];
        foreach ($estanques as $est) {
            $ultimosPorEstanque[$est->id] = DB::table('piscicola_calidad_agua')
                ->where('estanque_id', $est->id)
                ->orderByDesc('fecha')->orderByDesc('creado_en')->first();
        }

        $alertasActivas = $registros->where('alerta', 1)->take(5);

        $historial       = $registros;
        $ultimosRegistros = $ultimosPorEstanque; // alias usado en la vista

        return view('pages.piscicola.calidad_agua', compact(
            'estanques', 'registros', 'historial', 'ultimosPorEstanque',
            'ultimosRegistros', 'alertasActivas'
        ));
    }

    public function storeCalidadAgua(Request $request)
    {
        $request->validate([
            'estanque_id' => 'required|integer',
            'fecha'       => 'required|date',
        ]);

        $uid      = session('usuario_id');
        $estanque = DB::table('piscicola_estanques')
            ->where('id', $request->estanque_id)->where('usuario_id', $uid)->first();
        if (!$estanque) abort(404);

        // Detectar alertas automáticamente
        $alerta  = 0;
        $oxigeno = $request->oxigeno_mgl;
        $ph      = $request->ph;
        $temp    = $request->temperatura_c;

        if ($oxigeno && $oxigeno < 3.0)  $alerta = 1;
        if ($ph && ($ph < 6.0 || $ph > 9.5)) $alerta = 1;
        if ($request->amonio_mgl && $request->amonio_mgl > 0.02) $alerta = 1;
        if ($request->nitrito_mgl && $request->nitrito_mgl > 0.1) $alerta = 1;

        DB::table('piscicola_calidad_agua')->insert([
            'estanque_id'      => $request->estanque_id,
            'usuario_id'       => $uid,
            'fecha'            => $request->fecha,
            'hora'             => $request->hora ?: null,
            'oxigeno_mgl'      => $oxigeno ?: null,
            'ph'               => $ph ?: null,
            'temperatura_c'    => $temp ?: null,
            'amonio_mgl'       => $request->amonio_mgl ?: null,
            'nitrito_mgl'      => $request->nitrito_mgl ?: null,
            'transparencia_cm' => $request->transparencia_cm ?: null,
            'alerta'           => $alerta,
            'observaciones'    => $request->observaciones ?: null,
            'creado_en'        => now()->toDateTimeString(),
        ]);

        // Generar tarea si hay alerta
        if ($alerta) {
            DB::table('tareas')->insert([
                'usuario_id' => $uid,
                'titulo'     => 'Alerta calidad agua: ' . $estanque->nombre,
                'tipo'       => 'salud',
                'fecha'      => $request->fecha,
                'prioridad'  => 'alta',
                'completada' => 0,
                'notas'      => 'Revisar parametros: oxigeno=' . $oxigeno . ' pH=' . $ph,
                'creado_en'  => now()->toDateTimeString(),
            ]);
        }

        $msg = 'Calidad de agua registrada.' . ($alerta ? ' ALERTA: parametros fuera de rango.' : '');
        return redirect()->route('piscicola.calidad_agua')
            ->with('msg', $msg)->with('msgType', $alerta ? 'warning' : 'success');
    }

    // ─────────────────────────────────────────────────────────────────
    // COSECHA — Cierre del ciclo
    // ─────────────────────────────────────────────────────────────────

    public function cosecha()
    {
        $uid = session('usuario_id');

        $siembrasActivas = DB::table('piscicola_siembras as s')
            ->join('piscicola_estanques as e', 'e.id', '=', 's.estanque_id')
            ->where('s.usuario_id', $uid)->where('s.activo', 1)
            ->select('s.*', 'e.nombre as nombre_estanque', 'e.area_m2', 'e.especie_cultivada')
            ->orderBy('e.nombre')->get()
            ->map(function ($s) {
                $s->dias_cultivo = Carbon::parse($s->fecha_siembra)->diffInDays(now());
                $s->mortalidad_total = DB::table('piscicola_mortalidad')
                    ->where('siembra_id', $s->id)->sum('cantidad');
                $s->especie = $s->especie_cultivada ?? $s->especie ?? '—';
                $s->sobrevivencia_pct = $s->cantidad_alevinos > 0
                    ? round((($s->cantidad_alevinos - $s->mortalidad_total) / $s->cantidad_alevinos) * 100, 1)
                    : 100;
                return $s;
            });

        $cosechas = DB::table('piscicola_cosechas as c')
            ->join('piscicola_estanques as e', 'e.id', '=', 'c.estanque_id')
            ->join('piscicola_siembras as s', 's.id', '=', 'c.siembra_id')
            ->where('c.usuario_id', $uid)
            ->select('c.*', 'e.nombre as nombre_estanque', 's.especie', 's.fecha_siembra')
            ->orderByDesc('c.fecha_cosecha')->get();

        $totalCosechado = round($cosechas->sum('biomasa_cosechada_kg'), 2);
        $caPromedio     = round($cosechas->whereNotNull('conversion_alimenticia')
                            ->avg('conversion_alimenticia') ?? 0, 3);
        $sobrevPromedio = round($cosechas->whereNotNull('sobrevivencia_pct')
                            ->avg('sobrevivencia_pct') ?? 0, 1);

        return view('pages.piscicola.cosecha', compact(
            'siembrasActivas', 'cosechas',
            'totalCosechado', 'caPromedio', 'sobrevPromedio'
        ));
    }

    public function storeCosecha(Request $request)
    {
        $request->validate([
            'siembra_id'             => 'required|integer',
            'fecha_cosecha'          => 'required|date',
            'cantidad_cosechada'     => 'required|integer|min:1',
            'peso_promedio_final_g'  => 'required|numeric|min:1',
        ]);

        $uid     = session('usuario_id');
        $siembra = DB::table('piscicola_siembras as s')
            ->join('piscicola_estanques as e', 'e.id', '=', 's.estanque_id')
            ->where('s.id', $request->siembra_id)->where('s.usuario_id', $uid)
            ->select('s.*', 'e.area_m2', 'e.nombre as nombre_estanque')
            ->first();
        if (!$siembra) abort(404);

        $diasCultivo    = Carbon::parse($siembra->fecha_siembra)->diffInDays(Carbon::parse($request->fecha_cosecha));
        $biomasaKg      = round($request->cantidad_cosechada * $request->peso_promedio_final_g / 1000, 3);
        $sobrevivencia  = $siembra->cantidad_alevinos > 0
            ? round(($request->cantidad_cosechada / $siembra->cantidad_alevinos) * 100, 1)
            : null;
        $alimentoTotal  = $siembra->alimento_acumulado_kg ?? 0;
        $ca             = ($alimentoTotal > 0 && $biomasaKg > 0)
            ? round($alimentoTotal / $biomasaKg, 3)
            : null;
        $rendim         = ($siembra->area_m2 > 0)
            ? round($biomasaKg / $siembra->area_m2, 3)
            : null;
        $valorTotal     = ($request->precio_kg_cop && $biomasaKg)
            ? round($request->precio_kg_cop * $biomasaKg, 2)
            : null;

        DB::table('piscicola_cosechas')->insert([
            'siembra_id'             => $request->siembra_id,
            'estanque_id'            => $siembra->estanque_id,
            'usuario_id'             => $uid,
            'fecha_cosecha'          => $request->fecha_cosecha,
            'dias_cultivo'           => $diasCultivo,
            'cantidad_cosechada'     => $request->cantidad_cosechada,
            'peso_promedio_final_g'  => $request->peso_promedio_final_g,
            'biomasa_cosechada_kg'   => $biomasaKg,
            'cantidad_sembrada'      => $siembra->cantidad_alevinos,
            'sobrevivencia_pct'      => $sobrevivencia,
            'alimento_total_kg'      => $alimentoTotal ?: null,
            'conversion_alimenticia' => $ca,
            'rendimiento_kg_m2'      => $rendim,
            'precio_kg_cop'          => $request->precio_kg_cop ?: null,
            'valor_total_cop'        => $valorTotal,
            'comprador'              => $request->comprador ?: null,
            'destino'                => $request->destino ?? 'venta_directa',
            'observaciones'          => $request->observaciones ?: null,
            'creado_en'              => now()->toDateTimeString(),
        ]);

        // Marcar siembra como inactiva y estanque como cosechado
        DB::table('piscicola_siembras')->where('id', $request->siembra_id)
            ->update(['activo' => 0, 'actualizado_en' => now()->toDateTimeString()]);
        DB::table('piscicola_estanques')->where('id', $siembra->estanque_id)
            ->update(['estado' => 'cosechado', 'actualizado_en' => now()->toDateTimeString()]);

        // Crear ingreso si hay valor de venta
        if ($valorTotal && $valorTotal > 0) {
            DB::table('ingresos')->insert([
                'usuario_id'  => $uid,
                'descripcion' => 'Cosecha piscicola: ' . $siembra->especie . ' — ' . $siembra->nombre_estanque,
                'valor_total' => $valorTotal,
                'fecha'       => $request->fecha_cosecha,
                'tipo'        => 'animal',
                'comprador'   => $request->comprador,
                'notas'       => 'CA: ' . $ca . ' · Sobrevivencia: ' . $sobrevivencia . '% · ' . $diasCultivo . ' dias de cultivo',
                'creado_en'   => now()->toDateTimeString(),
            ]);
        }

        $msg = 'Cosecha registrada: ' . $biomasaKg . ' kg';
        if ($ca) $msg .= ' · CA: ' . $ca;
        if ($sobrevivencia) $msg .= ' · Sobrevivencia: ' . $sobrevivencia . '%';
        if ($valorTotal) $msg .= ' · Ingreso creado: $' . number_format($valorTotal, 0, ',', '.');

        return redirect()->route('piscicola.cosecha')
            ->with('msg', $msg)->with('msgType', 'success');
    }

    // ─────────────────────────────────────────────────────────────────
    // MORTALIDAD — Registro
    // ─────────────────────────────────────────────────────────────────

    public function storeMortalidad(Request $request)
    {
        $request->validate([
            'siembra_id' => 'required|integer',
            'fecha'      => 'required|date',
            'cantidad'   => 'required|integer|min:1',
            'causa'      => 'required',
        ]);

        $uid     = session('usuario_id');
        $siembra = DB::table('piscicola_siembras')
            ->where('id', $request->siembra_id)->where('usuario_id', $uid)->first();
        if (!$siembra) abort(404);

        DB::table('piscicola_mortalidad')->insert([
            'siembra_id'  => $request->siembra_id,
            'estanque_id' => $siembra->estanque_id,
            'usuario_id'  => $uid,
            'fecha'       => $request->fecha,
            'cantidad'    => $request->cantidad,
            'causa'       => $request->causa,
            'descripcion' => $request->descripcion ?: null,
            'creado_en'   => now()->toDateTimeString(),
        ]);

        // Actualizar cantidad_actual en la siembra
        $cantNueva = max(0, ($siembra->cantidad_actual ?? $siembra->cantidad_alevinos) - $request->cantidad);
        $biomasaNueva = $siembra->peso_promedio_actual_g
            ? round($cantNueva * $siembra->peso_promedio_actual_g / 1000, 3)
            : null;
        DB::table('piscicola_siembras')->where('id', $request->siembra_id)->update([
            'cantidad_actual'   => $cantNueva,
            'biomasa_actual_kg' => $biomasaNueva ?? $siembra->biomasa_actual_kg,
            'actualizado_en'    => now()->toDateTimeString(),
        ]);

        $mortalidadTotal = DB::table('piscicola_mortalidad')
            ->where('siembra_id', $request->siembra_id)->sum('cantidad');
        $sobrev = $siembra->cantidad_alevinos > 0
            ? round((($siembra->cantidad_alevinos - $mortalidadTotal) / $siembra->cantidad_alevinos) * 100, 1)
            : 100;

        return back()
            ->with('msg', $request->cantidad . ' peces registrados como mortalidad. Sobrevivencia: ' . $sobrev . '%')
            ->with('msgType', 'warning');
    }

    // ─────────────────────────────────────────────────────────────────
    // REPORTES
    // ─────────────────────────────────────────────────────────────────

    public function reportes()
    {
        $uid = session('usuario_id');

        $estanques = DB::table('piscicola_estanques')
            ->where('usuario_id', $uid)->orderBy('nombre')->get();

        // Ciclos completados (cosechas)
        $cosechas = DB::table('piscicola_cosechas as c')
            ->join('piscicola_estanques as e', 'e.id', '=', 'c.estanque_id')
            ->join('piscicola_siembras as s', 's.id', '=', 'c.siembra_id')
            ->where('c.usuario_id', $uid)
            ->select('c.*', 'e.nombre as nombre_estanque', 's.especie', 'e.area_m2')
            ->orderByDesc('c.fecha_cosecha')->get();

        // KPIs generales
        $totalBiomasaCosechada = $cosechas->sum('biomasa_cosechada_kg');
        $promedioCA            = $cosechas->whereNotNull('conversion_alimenticia')->avg('conversion_alimenticia');
        $promSobrevivencia     = $cosechas->whereNotNull('sobrevivencia_pct')->avg('sobrevivencia_pct');
        $totalIngresos         = $cosechas->sum('valor_total_cop');

        // Alertas de agua activas
        $alertasAgua = DB::table('piscicola_calidad_agua as c')
            ->join('piscicola_estanques as e', 'e.id', '=', 'c.estanque_id')
            ->where('c.usuario_id', $uid)->where('c.alerta', 1)
            ->where('c.fecha', '>=', now()->subDays(7)->toDateString())
            ->select('c.*', 'e.nombre as nombre_estanque')
            ->orderByDesc('c.fecha')->get();

        // Siembras activas con estado
        $siembrasActivas = DB::table('piscicola_siembras as s')
            ->join('piscicola_estanques as e', 'e.id', '=', 's.estanque_id')
            ->where('s.usuario_id', $uid)->where('s.activo', 1)
            ->select('s.*', 'e.nombre as nombre_estanque', 'e.area_m2', 'e.especie_cultivada')
            ->orderBy('s.fecha_siembra')->get()
            ->map(function ($s) {
                $s->dias_cultivo     = Carbon::parse($s->fecha_siembra)->diffInDays(now());
                $s->mortalidad_total = DB::table('piscicola_mortalidad')
                    ->where('siembra_id', $s->id)->sum('cantidad');
                $s->sobrevivencia    = $s->cantidad_alevinos > 0
                    ? round((($s->cantidad_alevinos - $s->mortalidad_total) / $s->cantidad_alevinos) * 100, 1)
                    : 100;
                $s->sobrevivencia_pct = $s->sobrevivencia;
                $s->ultimo_muestreo   = DB::table('piscicola_muestreos')
                    ->where('siembra_id', $s->id)->orderByDesc('fecha')->first();
                $s->especie = $s->especie_cultivada ?? $s->especie ?? '—';
                return $s;
            });

        $totales = (object)[
            'cosechas'       => $cosechas->count(),
            'kg_total'       => $totalBiomasaCosechada,
            'ca_promedio'    => round($promedioCA ?? 0, 3),
            'sobrev_promedio'=> round($promSobrevivencia ?? 0, 1),
            'ingresos_total' => $totalIngresos,
        ];

        // Mejores cosechas por CA
        $mejoresCosechas = $cosechas
            ->whereNotNull('conversion_alimenticia')
            ->sortBy('conversion_alimenticia')
            ->values()
            ->take(5);

        // alertasCalidad = alias de alertasAgua (nombre usado en la vista)
        $alertasCalidad = $alertasAgua;

        // Datos para la gráfica de producción histórica por mes
        $chartMeses = $cosechas->groupBy(fn($co) => \Carbon\Carbon::parse($co->fecha_cosecha)->format('m/Y'))
            ->map(fn($g) => round($g->sum('biomasa_cosechada_kg'), 1))
            ->toArray();
        $chartLabelsRep = array_keys($chartMeses);
        $chartKg        = array_values($chartMeses);

        return view('pages.piscicola.reportes', compact(
            'estanques', 'cosechas', 'siembrasActivas',
            'totalBiomasaCosechada', 'promedioCA', 'promSobrevivencia',
            'totalIngresos', 'alertasAgua', 'alertasCalidad',
            'totales', 'mejoresCosechas',
            'chartKg', 'chartMeses', 'chartLabelsRep'
        ));
    }
}