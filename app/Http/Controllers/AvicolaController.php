<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AvicolaController extends Controller
{
    // Especies avícolas manejadas por este módulo
    private const ESPECIES_AVICOLAS = ['Gallinas', 'Patos', 'Pavos', 'Codornices', 'Aves de corral'];

    // Protocolos de vacunación estándar avícola colombiano
    private const PROTOCOLOS_VACUNAS = [
        ['protocolo' => 'marek',        'nombre' => 'Marek',               'dia_vida' => 1,   'via' => 'inyectable',  'dosis' => '0.2 mL/ave'],
        ['protocolo' => 'gumboro_1',    'nombre' => 'Gumboro (1ra dosis)', 'dia_vida' => 14,  'via' => 'agua',        'dosis' => 'Según fabricante'],
        ['protocolo' => 'newcastle_1',  'nombre' => 'Newcastle (1ra)',     'dia_vida' => 7,   'via' => 'ocular',      'dosis' => '1 gota/ave'],
        ['protocolo' => 'bronquitis_1', 'nombre' => 'Bronquitis (1ra)',    'dia_vida' => 7,   'via' => 'nasal',       'dosis' => '1 gota/ave'],
        ['protocolo' => 'gumboro_2',    'nombre' => 'Gumboro (2da dosis)', 'dia_vida' => 28,  'via' => 'agua',        'dosis' => 'Según fabricante'],
        ['protocolo' => 'newcastle_2',  'nombre' => 'Newcastle (2da)',     'dia_vida' => 28,  'via' => 'agua',        'dosis' => 'Según fabricante'],
        ['protocolo' => 'newcastle_3',  'nombre' => 'Newcastle (3ra)',     'dia_vida' => 60,  'via' => 'agua',        'dosis' => 'Según fabricante'],
        ['protocolo' => 'coriza',       'nombre' => 'Coriza infecciosa',   'dia_vida' => 56,  'via' => 'inyectable',  'dosis' => '0.5 mL/ave'],
    ];

    // ─────────────────────────────────────────────────────────────────
    // GALPÓN — Dashboard principal avícola
    // ─────────────────────────────────────────────────────────────────

    public function galpon()
    {
        $uid = session('usuario_id');
        $hoy = now()->toDateString();

        // Lotes activos avícolas
        $lotes = DB::table('animales')
            ->where('usuario_id', $uid)
            ->where('estado', 'activo')
            ->whereIn('especie', self::ESPECIES_AVICOLAS)
            ->orderBy('nombre_lote')
            ->get();

        // Total de aves activas
        $totalAves   = $lotes->sum('cantidad');
        $totalLotes  = $lotes->count();

        // Producción de hoy (todas las posturas del día)
        $produccionHoy = DB::table('avicola_postura')
            ->where('usuario_id', $uid)
            ->where('fecha', $hoy)
            ->sum('huevos_total');

        // Mortalidad de la semana
        $mortSemana = DB::table('avicola_mortalidad')
            ->where('usuario_id', $uid)
            ->where('fecha', '>=', now()->subDays(7)->toDateString())
            ->sum('cantidad');

        // % postura promedio hoy (de lotes ponedores con registro)
        $posturaHoy = DB::table('avicola_postura')
            ->where('usuario_id', $uid)
            ->where('fecha', $hoy)
            ->whereNotNull('porcentaje_postura')
            ->avg('porcentaje_postura');

       // Vacunas vencidas o próximas (15 días)
$alertasVacunas = DB::table('avicola_vacunacion as v')
    ->leftJoin('animales as a', 'a.id', '=', 'v.animal_id')
    ->where('v.usuario_id', $uid)
    ->whereNull('v.fecha_aplicada')
    ->where('v.activo', 1)
    ->where('v.fecha_programada', '<=', now()->addDays(15)->toDateString())
    ->select(
        'v.*',
        'a.nombre_lote as nombre_lote'
    )
    ->orderBy('v.fecha_programada')
    ->get();

        // Producción últimos 30 días para la curva
        $curva30 = DB::table('avicola_postura')
            ->where('usuario_id', $uid)
            ->where('fecha', '>=', now()->subDays(30)->toDateString())
            ->selectRaw('fecha, SUM(huevos_total) as total, AVG(porcentaje_postura) as pct_postura')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $chartLabels = $curva30->pluck('fecha')
            ->map(fn($f) => Carbon::parse($f)->format('d/m'))->toArray();
        $chartHuevos = $curva30->pluck('total')->toArray();
        $chartPct    = $curva30->map(fn($r) => round($r->pct_postura ?? 0, 1))->toArray();

        // Estadísticas por lote con etapa calculada
        $lotesConEtapa = $lotes->map(function ($l) {
            $l->etapa      = $this->calcularEtapa($l);
            $l->semanas    = $this->calcularSemanas($l);
            return $l;
        });

        return view('pages.avicola.galpon', compact(
            'lotes', 'lotesConEtapa', 'totalAves', 'totalLotes',
            'produccionHoy', 'mortSemana', 'posturaHoy',
            'alertasVacunas', 'chartLabels', 'chartHuevos', 'chartPct'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    // POSTURA — Registro diario de huevos
    // ─────────────────────────────────────────────────────────────────

    public function postura(Request $request)
    {
        $uid  = session('usuario_id');
        $fecha = $request->fecha ?? now()->toDateString();

        // Lotes ponedores activos
        $lotesPonedores = DB::table('animales')
            ->where('usuario_id', $uid)
            ->where('estado', 'activo')
            ->whereIn('especie', self::ESPECIES_AVICOLAS)
            ->whereIn('tipo_ave', ['ponedora', 'doble_proposito', null])
            ->orderBy('nombre_lote')
            ->get();

        // Registros del día seleccionado
        $posturaDelDia = DB::table('avicola_postura')
            ->where('usuario_id', $uid)
            ->where('fecha', $fecha)
            ->get()
            ->keyBy('animal_id');

        // Total del día
        $totalDia = $posturaDelDia->sum('huevos_total');
        $pctDia   = $posturaDelDia->avg('porcentaje_postura');

        // Historial de los últimos 30 días para gráfica
        $historial = DB::table('avicola_postura')
            ->where('usuario_id', $uid)
            ->where('fecha', '>=', now()->subDays(30)->toDateString())
            ->selectRaw('fecha, SUM(huevos_total) as total, AVG(porcentaje_postura) as pct')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $chartLabels = $historial->pluck('fecha')
            ->map(fn($f) => Carbon::parse($f)->format('d/m'))->toArray();
        $chartHuevos = $historial->pluck('total')->toArray();
        $chartPct    = $historial->map(fn($r) => round($r->pct ?? 0, 1))->toArray();

        // Clasificación acumulada del mes
        $mesActual = now()->format('Y-m');
        $clasifMes = DB::table('avicola_postura')
            ->where('usuario_id', $uid)
            ->whereRaw("DATE_FORMAT(fecha,'%Y-%m') = ?", [$mesActual])
            ->selectRaw('SUM(huevos_aa) as aa, SUM(huevos_a) as a_cls,
                         SUM(huevos_b) as b_cls, SUM(huevos_sucios) as sucios,
                         SUM(huevos_rotos) as rotos, SUM(huevos_total) as total')
            ->first();

        return view('pages.avicola.postura', compact(
            'lotesPonedores', 'posturaDelDia', 'fecha',
            'totalDia', 'pctDia', 'historial',
            'chartLabels', 'chartHuevos', 'chartPct', 'clasifMes'
        ));
    }

    public function storePostura(Request $request)
    {
        $request->validate([
            'animal_id'    => 'required|integer',
            'fecha'        => 'required|date',
            'huevos_total' => 'required|integer|min:0',
        ]);

        $uid      = session('usuario_id');
        $animal   = DB::table('animales')
            ->where('id', $request->animal_id)->where('usuario_id', $uid)->first();
        if (!$animal) abort(404);

        $aves       = $request->aves_presentes ?? $animal->cantidad;
        $pctPostura = $aves > 0 ? round(($request->huevos_total / $aves) * 100, 2) : null;

        // UPSERT — si ya existe el registro del día, lo actualiza
        $existente = DB::table('avicola_postura')
            ->where('animal_id', $request->animal_id)
            ->where('fecha', $request->fecha)
            ->first();

        $datos = [
            'animal_id'          => $request->animal_id,
            'usuario_id'         => $uid,
            'fecha'              => $request->fecha,
            'huevos_total'       => $request->huevos_total,
            'huevos_aa'          => $request->huevos_aa ?? 0,
            'huevos_a'           => $request->huevos_a ?? 0,
            'huevos_b'           => $request->huevos_b ?? 0,
            'huevos_sucios'      => $request->huevos_sucios ?? 0,
            'huevos_rotos'       => $request->huevos_rotos ?? 0,
            'aves_presentes'     => $aves,
            'porcentaje_postura' => $pctPostura,
            'alimento_kg'        => $request->alimento_kg ?: null,
            'agua_litros'        => $request->agua_litros ?: null,
            'observaciones'      => $request->observaciones ?: null,
        ];

        if ($existente) {
            DB::table('avicola_postura')->where('id', $existente->id)->update($datos);
            $msg = 'Postura actualizada para ' . $animal->nombre_lote;
        } else {
            DB::table('avicola_postura')->insert(array_merge($datos, ['creado_en' => now()->toDateTimeString()]));
            $msg = 'Postura registrada: ' . $request->huevos_total . ' huevos (' . round($pctPostura ?? 0, 1) . '%)';
        }

        return redirect()->route('avicola.postura', ['fecha' => $request->fecha])
            ->with('msg', $msg)->with('msgType', 'success');
    }

    public function destroyPostura($id)
    {
        $uid = session('usuario_id');
        DB::table('avicola_postura')
            ->where('id', $id)->where('usuario_id', $uid)->delete();
        return back()->with('msg', 'Registro eliminado.')->with('msgType', 'warning');
    }

    // ─────────────────────────────────────────────────────────────────
    // ENGORDE — Control semanal de peso (pollos de engorde)
    // ─────────────────────────────────────────────────────────────────

    public function engorde()
    {
        $uid = session('usuario_id');

        // Lotes de engorde activos
        $lotesEngorde = DB::table('animales')
            ->where('usuario_id', $uid)
            ->where('estado', 'activo')
            ->whereIn('especie', self::ESPECIES_AVICOLAS)
            ->where('tipo_ave', 'engorde')
            ->orderBy('nombre_lote')
            ->get()
            ->map(function ($l) {
                $l->semanas = $this->calcularSemanas($l);
                $l->etapa   = $this->calcularEtapa($l);
                return $l;
            });

        // Pesos por lote con proyección
        $pesosConProyeccion = [];
        foreach ($lotesEngorde as $lote) {
            $pesos = DB::table('avicola_pesos_engorde')
                ->where('animal_id', $lote->id)
                ->orderBy('semana')
                ->get();

            // Tabla meta (Ross 308 por defecto)
            $linea = $lote->linea_ave ?? 'Ross 308';
            $tablaStd = DB::table('avicola_tabla_peso_std')
                ->where('linea_ave', $linea)
                ->orderBy('semana')
                ->get()->keyBy('semana');

            // Proyección de fecha de sacrificio
            $ultimoPeso    = $pesos->last();
            $fechaSacrificio = null;
            if ($ultimoPeso && $ultimoPeso->gpd_g > 0) {
                $pesoMeta   = 2400; // 2.4 kg estándar sacrificio
                $diasRest   = max(0, round(($pesoMeta - $ultimoPeso->peso_promedio_g) / $ultimoPeso->gpd_g));
                $fechaSacrificio = Carbon::parse($ultimoPeso->fecha)->addDays($diasRest)->toDateString();
            }

            $pesosConProyeccion[$lote->id] = [
                'lote'           => $lote,
                'pesos'          => $pesos,
                'tablaStd'       => $tablaStd,
                'fechaSacrificio'=> $fechaSacrificio,
                'ultimoPeso'     => $ultimoPeso,
            ];
        }

        // Todos los lotes (para el modal de agregar peso a cualquiera)
        $todosLotes = DB::table('animales')
            ->where('usuario_id', $uid)->where('estado', 'activo')
            ->whereIn('especie', self::ESPECIES_AVICOLAS)
            ->orderBy('nombre_lote')->get();

        // Líneas disponibles en tabla_peso_std
        $lineasDisponibles = DB::table('avicola_tabla_peso_std')
            ->distinct()->pluck('linea_ave');

        return view('pages.avicola.engorde', compact(
            'lotesEngorde', 'pesosConProyeccion', 'todosLotes', 'lineasDisponibles'
        ));
    }

    public function storePesoEngorde(Request $request)
    {
        $request->validate([
            'animal_id'      => 'required|integer',
            'semana'         => 'required|integer|min:1|max:20',
            'peso_promedio_g'=> 'required|numeric|min:0',
            'fecha'          => 'required|date',
        ]);

        $uid = session('usuario_id');

        // Calcular GPD vs semana anterior
        $anterior = DB::table('avicola_pesos_engorde')
            ->where('animal_id', $request->animal_id)
            ->where('semana', $request->semana - 1)
            ->first();

        $gpd = null;
        if ($anterior) {
            $dias = Carbon::parse($anterior->fecha)->diffInDays(Carbon::parse($request->fecha));
            if ($dias > 0) {
                $gpd = round(($request->peso_promedio_g - $anterior->peso_promedio_g) / $dias, 2);
            }
        }

        // Peso meta de la tabla estándar
        $animal  = DB::table('animales')->where('id', $request->animal_id)->first();
        $linea   = $animal->linea_ave ?? 'Ross 308';
        $tablaStd = DB::table('avicola_tabla_peso_std')
            ->where('linea_ave', $linea)->where('semana', $request->semana)->first();

        DB::table('avicola_pesos_engorde')->updateOrInsert(
            ['animal_id' => $request->animal_id, 'semana' => $request->semana],
            [
                'usuario_id'      => $uid,
                'fecha'           => $request->fecha,
                'peso_promedio_g' => $request->peso_promedio_g,
                'aves_pesadas'    => $request->aves_pesadas ?: null,
                'gpd_g'           => $gpd,
                'peso_meta_g'     => $tablaStd->peso_meta_g ?? null,
                'uniformidad_pct' => $request->uniformidad_pct ?: null,
                'observaciones'   => $request->observaciones ?: null,
                'creado_en'       => now()->toDateTimeString(),
            ]
        );

        $dif = '';
        if ($tablaStd) {
            $delta = $request->peso_promedio_g - $tablaStd->peso_meta_g;
            $dif   = $delta >= 0
                ? ' (' . round($delta) . 'g sobre la meta)'
                : ' (' . round(abs($delta)) . 'g bajo la meta)';
        }

        return redirect()->route('avicola.engorde')
            ->with('msg', 'Peso semana ' . $request->semana . ' registrado: ' . $request->peso_promedio_g . 'g' . $dif)
            ->with('msgType', 'success');
    }

    // ─────────────────────────────────────────────────────────────────
    // MORTALIDAD — Registro y análisis
    // ─────────────────────────────────────────────────────────────────

    public function mortalidad()
    {
        $uid = session('usuario_id');

        $lotes = DB::table('animales')
            ->where('usuario_id', $uid)->where('estado', 'activo')
            ->whereIn('especie', self::ESPECIES_AVICOLAS)
            ->orderBy('nombre_lote')->get();

        // Mortalidad de los últimos 30 días
        $registros = DB::table('avicola_mortalidad as m')
            ->join('animales as a', 'a.id', '=', 'm.animal_id')
            ->where('m.usuario_id', $uid)
            ->where('m.fecha', '>=', now()->subDays(30)->toDateString())
            ->select('m.*', 'a.nombre_lote', 'a.cantidad as total_aves')
            ->orderByDesc('m.fecha')
            ->get();

        // Acumulado por causa (último mes)
        $porCausa = $registros->groupBy('causa')
            ->map(fn($g) => $g->sum('cantidad'))
            ->sortDesc();

        // % mortalidad acumulada por lote
        $mortPorLote = [];
        foreach ($lotes as $l) {
            $muertas = DB::table('avicola_mortalidad')
                ->where('animal_id', $l->id)->sum('cantidad');
            $mortPorLote[$l->id] = $l->cantidad > 0
                ? round(($muertas / $l->cantidad) * 100, 2) : 0;
        }

        // Causas disponibles para el select
        $causas = [
            'enfermedad_respiratoria' => 'Enfermedad respiratoria',
            'enfermedad_digestiva'    => 'Enfermedad digestiva',
            'marek'                   => 'Marek',
            'newcastle'               => 'Newcastle',
            'gumboro'                 => 'Gumboro',
            'estres_calor'            => 'Estrés por calor',
            'trauma'                  => 'Trauma / aplastamiento',
            'predador'                => 'Predador',
            'causa_desconocida'       => 'Causa desconocida',
            'otro'                    => 'Otro',
        ];

        return view('pages.avicola.mortalidad', compact(
            'lotes', 'registros', 'porCausa', 'mortPorLote', 'causas'
        ));
    }

    public function storeMortalidad(Request $request)
    {
        $request->validate([
            'animal_id' => 'required|integer',
            'fecha'     => 'required|date',
            'cantidad'  => 'required|integer|min:1',
            'causa'     => 'required',
        ]);

        $uid    = session('usuario_id');
        $animal = DB::table('animales')
            ->where('id', $request->animal_id)->where('usuario_id', $uid)->first();
        if (!$animal) abort(404);

        DB::table('avicola_mortalidad')->insert([
            'animal_id'   => $request->animal_id,
            'usuario_id'  => $uid,
            'fecha'       => $request->fecha,
            'cantidad'    => $request->cantidad,
            'causa'       => $request->causa,
            'descripcion' => $request->descripcion ?: null,
            'descartadas' => $request->descartadas ?? 0,
            'creado_en'   => now()->toDateTimeString(),
        ]);

        // Reducir cantidad del lote
        $nuevaCantidad = max(0, $animal->cantidad - $request->cantidad - ($request->descartadas ?? 0));
        DB::table('animales')->where('id', $request->animal_id)
            ->update(['cantidad' => $nuevaCantidad, 'actualizado_en' => now()->toDateTimeString()]);

        // Evento en timeline del animal
        DB::table('animal_eventos')->insert([
            'animal_id'   => $request->animal_id,
            'usuario_id'  => $uid,
            'tipo'        => 'nota',
            'titulo'      => '💀 Mortalidad: ' . $request->cantidad . ' ave(s) — ' . $request->causa,
            'descripcion' => $request->descripcion,
            'fecha'       => $request->fecha,
            'creado_en'   => now()->toDateTimeString(),
        ]);

        return redirect()->route('avicola.mortalidad')
            ->with('msg', 'Mortalidad registrada. Lote actualizado: ' . $nuevaCantidad . ' aves.')
            ->with('msgType', 'warning');
    }

    // ─────────────────────────────────────────────────────────────────
    // VACUNACIÓN — Calendario y aplicaciones
    // ─────────────────────────────────────────────────────────────────

    public function vacunacion()
    {
        $uid = session('usuario_id');

        $lotes = DB::table('animales')
            ->where('usuario_id', $uid)->where('estado', 'activo')
            ->whereIn('especie', self::ESPECIES_AVICOLAS)
            ->orderBy('nombre_lote')->get();

        // Auto-inicializar protocolos si no existen para lotes nuevos
        foreach ($lotes as $lote) {
            if (!$lote->fecha_nacimiento_lote) continue;
            $yaExiste = DB::table('avicola_vacunacion')
                ->where('animal_id', $lote->id)->exists();
            if (!$yaExiste) {
                $this->inicializarVacunas($uid, $lote);
            }
        }

        // Traer todas las vacunas agrupadas por lote
        $vacunas = DB::table('avicola_vacunacion as v')
            ->leftJoin('animales as a', 'a.id', '=', 'v.animal_id')
            ->where('v.usuario_id', $uid)
            ->where('v.activo', 1)
            ->select('v.*', 'a.nombre_lote')
            ->orderBy('v.fecha_programada')
            ->get();

        $hoy              = now()->toDateString();
        $vacunasVencidas  = $vacunas->filter(fn($v) => $v->fecha_programada < $hoy && !$v->fecha_aplicada);
        $vacunasProximas  = $vacunas->filter(fn($v) => $v->fecha_programada >= $hoy
                                && $v->fecha_programada <= now()->addDays(15)->toDateString()
                                && !$v->fecha_aplicada);
        $vacunasAplicadas = $vacunas->filter(fn($v) => $v->fecha_aplicada !== null);
        $vacunasPendientes= $vacunas->filter(fn($v) => !$v->fecha_aplicada
                                && ($v->fecha_programada > now()->addDays(15)->toDateString()));

        return view('pages.avicola.vacunacion', compact(
            'lotes', 'vacunas', 'vacunasVencidas', 'vacunasProximas',
            'vacunasAplicadas', 'vacunasPendientes', 'hoy'
        ));
    }

    public function aplicarVacuna(Request $request, $id)
    {
        $request->validate(['fecha_aplicada' => 'required|date']);
        $uid = session('usuario_id');

        $vacuna = DB::table('avicola_vacunacion')
            ->where('id', $id)->where('usuario_id', $uid)->first();
        if (!$vacuna) abort(404);

        DB::table('avicola_vacunacion')->where('id', $id)->update([
            'fecha_aplicada'   => $request->fecha_aplicada,
            'producto_comercial'=> $request->producto_comercial ?: $vacuna->producto_comercial,
            'dosis'            => $request->dosis ?: $vacuna->dosis,
            'observaciones'    => $request->observaciones ?: $vacuna->observaciones,
            'actualizado_en'   => now()->toDateTimeString(),
        ]);

        // Tarea en Agenda completada / registrar en timeline
        DB::table('tareas')
            ->where('usuario_id', $uid)
            ->where('animal_id', $vacuna->animal_id)
            ->where('titulo', 'like', '%' . $vacuna->nombre_vacuna . '%')
            ->where('completada', 0)
            ->update(['completada' => 1, 'fecha_completada' => $request->fecha_aplicada]);

        return redirect()->route('avicola.vacunacion')
            ->with('msg', 'Vacuna "' . $vacuna->nombre_vacuna . '" marcada como aplicada.')
            ->with('msgType', 'success');
    }

    public function storeVacunaPersonalizada(Request $request)
    {
        $request->validate([
            'nombre_vacuna'    => 'required|string|max:120',
            'fecha_programada' => 'required|date',
            'via_administracion' => 'required',
        ]);

        $uid = session('usuario_id');

        DB::table('avicola_vacunacion')->insert([
            'animal_id'          => $request->animal_id ?: null,
            'usuario_id'         => $uid,
            'protocolo'          => 'personalizado_' . time(),
            'nombre_vacuna'      => $request->nombre_vacuna,
            'via_administracion' => $request->via_administracion,
            'fecha_programada'   => $request->fecha_programada,
            'dosis'              => $request->dosis ?: null,
            'producto_comercial' => $request->producto_comercial ?: null,
            'observaciones'      => $request->observaciones ?: null,
            'activo'             => 1,
            'creado_en'          => now()->toDateTimeString(),
            'actualizado_en'     => now()->toDateTimeString(),
        ]);

        // Generar tarea en Agenda
        DB::table('tareas')->insert([
            'usuario_id' => $uid,
            'animal_id'  => $request->animal_id ?: null,
            'titulo'     => 'Vacuna: ' . $request->nombre_vacuna,
            'tipo'       => 'salud',
            'fecha'      => $request->fecha_programada,
            'prioridad'  => 'alta',
            'completada' => 0,
            'creado_en'  => now()->toDateTimeString(),
        ]);

        return redirect()->route('avicola.vacunacion')
            ->with('msg', 'Vacuna programada y tarea generada en Agenda.')
            ->with('msgType', 'success');
    }

    // ─────────────────────────────────────────────────────────────────
    // CONVERSIÓN ALIMENTICIA — Registro semanal
    // ─────────────────────────────────────────────────────────────────

    public function conversion()
    {
        $uid = session('usuario_id');

        $lotes = DB::table('animales')
            ->where('usuario_id', $uid)->where('estado', 'activo')
            ->whereIn('especie', self::ESPECIES_AVICOLAS)
            ->orderBy('nombre_lote')->get();

        $conversiones = DB::table('avicola_conversion as c')
            ->join('animales as a', 'a.id', '=', 'c.animal_id')
            ->where('c.usuario_id', $uid)
            ->select('c.*', 'a.nombre_lote')
            ->orderByDesc('c.fecha_inicio')
            ->get();

        // Promedio CA por lote (últimas 4 semanas)
        $caPromLote = [];
        foreach ($lotes as $l) {
            $ca = DB::table('avicola_conversion')
                ->where('animal_id', $l->id)
                ->orderByDesc('semana')->limit(4)->avg('conversion_alimenticia');
            $caPromLote[$l->id] = round($ca ?? 0, 3);
        }

        return view('pages.avicola.conversion', compact(
            'lotes', 'conversiones', 'caPromLote'
        ));
    }

    public function storeConversion(Request $request)
    {
        $request->validate([
            'animal_id'             => 'required|integer',
            'semana'                => 'required|integer|min:1',
            'fecha_inicio'          => 'required|date',
            'fecha_fin'             => 'required|date|after_or_equal:fecha_inicio',
            'alimento_consumido_kg' => 'required|numeric|min:0',
            'produccion_kg'         => 'required|numeric|min:0.001',
            'tipo'                  => 'required|in:postura,engorde',
        ]);

        $uid = session('usuario_id');
        $ca  = round($request->alimento_consumido_kg / $request->produccion_kg, 3);

        DB::table('avicola_conversion')->updateOrInsert(
            ['animal_id' => $request->animal_id, 'semana' => $request->semana, 'tipo' => $request->tipo],
            [
                'usuario_id'            => $uid,
                'fecha_inicio'          => $request->fecha_inicio,
                'fecha_fin'             => $request->fecha_fin,
                'alimento_consumido_kg' => $request->alimento_consumido_kg,
                'produccion_kg'         => $request->produccion_kg,
                'conversion_alimenticia'=> $ca,
                'observaciones'         => $request->observaciones ?: null,
                'creado_en'             => now()->toDateTimeString(),
            ]
        );

        $cal = $request->tipo === 'postura' ? 'CA postura' : 'CA engorde';
        return redirect()->route('avicola.conversion')
            ->with('msg', "$cal semana {$request->semana}: $ca (menor es mejor)")
            ->with('msgType', $ca <= 2.0 ? 'success' : 'warning');
    }

    // ─────────────────────────────────────────────────────────────────
    // REPORTES AVÍCOLAS
    // ─────────────────────────────────────────────────────────────────

    public function reportes()
    {
        $uid = session('usuario_id');

        $lotes = DB::table('animales')
            ->where('usuario_id', $uid)->where('estado', 'activo')
            ->whereIn('especie', self::ESPECIES_AVICOLAS)
            ->get()->map(function ($l) {
                $l->etapa   = $this->calcularEtapa($l);
                $l->semanas = $this->calcularSemanas($l);
                return $l;
            });

        // Producción total del mes
        $mesActual = now()->format('Y-m');
        $prodMes = DB::table('avicola_postura')
            ->where('usuario_id', $uid)
            ->whereRaw("DATE_FORMAT(fecha,'%Y-%m') = ?", [$mesActual])
            ->selectRaw('SUM(huevos_total) as total, AVG(porcentaje_postura) as pct_prom,
                         SUM(huevos_aa) as aa, SUM(huevos_a) as a_cls,
                         SUM(huevos_b) as b_cls, SUM(huevos_rotos+huevos_sucios) as bajas')
            ->first();

        // Mortalidad del mes
        $mortMes = DB::table('avicola_mortalidad')
            ->where('usuario_id', $uid)
            ->whereRaw("DATE_FORMAT(fecha,'%Y-%m') = ?", [$mesActual])
            ->selectRaw('SUM(cantidad) as total, causa, COUNT(*) as eventos')
            ->groupBy('causa')->orderByDesc('total')->get();

        // Mejor CA del mes
        $mejorCA = DB::table('avicola_conversion as c')
            ->join('animales as a', 'a.id', '=', 'c.animal_id')
            ->where('c.usuario_id', $uid)
            ->whereRaw("DATE_FORMAT(c.fecha_inicio,'%Y-%m') = ?", [$mesActual])
            ->select('a.nombre_lote', 'c.conversion_alimenticia', 'c.tipo')
            ->orderBy('c.conversion_alimenticia')
            ->first();

        // Producción por lote del mes
        $prodPorLote = DB::table('avicola_postura as p')
            ->join('animales as a', 'a.id', '=', 'p.animal_id')
            ->where('p.usuario_id', $uid)
            ->whereRaw("DATE_FORMAT(p.fecha,'%Y-%m') = ?", [$mesActual])
            ->select('a.nombre_lote', 'a.cantidad',
                     DB::raw('SUM(p.huevos_total) as total'),
                     DB::raw('AVG(p.porcentaje_postura) as pct_prom'))
            ->groupBy('a.id', 'a.nombre_lote', 'a.cantidad')
            ->orderByDesc('total')
            ->get();

        return view('pages.avicola.reportes', compact(
            'lotes', 'prodMes', 'mortMes', 'mejorCA', 'prodPorLote'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    // PRIVADOS — Helpers
    // ─────────────────────────────────────────────────────────────────

    private function calcularEtapa($lote): string
    {
        $semanas = $this->calcularSemanas($lote);
        if ($semanas === null) return 'desconocida';
        if ($semanas <= 6)  return 'cria';
        if ($semanas <= 18) return 'levante';
        return 'postura_produccion';
    }

    private function calcularSemanas($lote): ?int
    {
        $fn = $lote->fecha_nacimiento_lote ?? $lote->fecha_nacimiento ?? $lote->fecha_ingreso;
        if (!$fn) return null;
        return (int) Carbon::parse($fn)->diffInWeeks(now());
    }

    private function inicializarVacunas(int $uid, $lote): void
    {
        $fechaNac = Carbon::parse($lote->fecha_nacimiento_lote);

        foreach (self::PROTOCOLOS_VACUNAS as $p) {
            $fechaProg = $fechaNac->copy()->addDays($p['dia_vida'])->toDateString();
            DB::table('avicola_vacunacion')->insert([
                'animal_id'          => $lote->id,
                'usuario_id'         => $uid,
                'protocolo'          => $p['protocolo'],
                'nombre_vacuna'      => $p['nombre'],
                'via_administracion' => $p['via'],
                'dia_vida'           => $p['dia_vida'],
                'fecha_programada'   => $fechaProg,
                'dosis'              => $p['dosis'],
                'activo'             => 1,
                'creado_en'          => now()->toDateTimeString(),
                'actualizado_en'     => now()->toDateTimeString(),
            ]);

            // Generar tarea automática en Agenda
            DB::table('tareas')->insert([
                'usuario_id' => $uid,
                'animal_id'  => $lote->id,
                'titulo'     => 'Vacuna: ' . $p['nombre'] . ' — ' . $lote->nombre_lote,
                'tipo'       => 'salud',
                'fecha'      => $fechaProg,
                'prioridad'  => 'alta',
                'completada' => 0,
                'creado_en'  => now()->toDateTimeString(),
            ]);
        }
    }
}