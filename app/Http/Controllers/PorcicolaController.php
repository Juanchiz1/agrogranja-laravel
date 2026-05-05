<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PorcicolaController extends Controller
{
    // Especies porcinas manejadas
    private const ESPECIES_PORCINAS = ['Cerdos', 'Cerdas de cría', 'Porcinos'];

    // Gestación porcina estándar
    private const DIAS_GESTACION = 114;

    // Protocolos sanitarios estándar para Colombia
    private const PROTOCOLOS = [
        ['protocolo' => 'ppc',            'nombre' => 'PPC (Cólera Porcino Clásico)',
         'tipo' => 'vacuna', 'frecuencia_dias' => 180, 'via' => 'intramuscular',
         'dosis' => '2 mL/animal'],
        ['protocolo' => 'parvovirus',     'nombre' => 'Parvovirus porcino',
         'tipo' => 'vacuna', 'frecuencia_dias' => 365, 'via' => 'intramuscular',
         'dosis' => '2 mL/animal'],
        ['protocolo' => 'leptospirosis',  'nombre' => 'Leptospirosis',
         'tipo' => 'vacuna', 'frecuencia_dias' => 180, 'via' => 'intramuscular',
         'dosis' => '2 mL/animal'],
        ['protocolo' => 'desparasitacion','nombre' => 'Desparasitación interna/externa',
         'tipo' => 'desparasitante', 'frecuencia_dias' => 90, 'via' => 'oral',
         'dosis' => 'Según peso vivo'],
        ['protocolo' => 'hierro_lechones','nombre' => 'Hierro dextrano (lechones)',
         'tipo' => 'vitamina', 'frecuencia_dias' => null, 'via' => 'intramuscular',
         'dosis' => '1 mL (200 mg Fe)'],
    ];

    // ─────────────────────────────────────────────────────────────────
    // PIARA — Dashboard principal porcícola
    // ─────────────────────────────────────────────────────────────────

    public function piara()
    {
        $uid = session('usuario_id');
        $hoy = now()->toDateString();

        // Inventario de la piara por categoría
        $inventario = DB::table('animales')
            ->where('usuario_id', $uid)->where('estado', 'activo')
            ->whereIn('especie', self::ESPECIES_PORCINAS)
            ->selectRaw('categoria_porcina, SUM(cantidad) as total, COUNT(*) as lotes')
            ->groupBy('categoria_porcina')
            ->orderByRaw("FIELD(categoria_porcina,'lechon','levante','ceba','hembra_cria','verraco','otro')")
            ->get();

        $totalCerdos = $inventario->sum('total');

        // Hembras preñadas (camada con servicio positivo, sin parto real)
        $hembrasPreniadas = DB::table('porcicola_camadas')
            ->where('usuario_id', $uid)
            ->where('resultado_diagnostico', 'positivo')
            ->whereNull('fecha_parto_real')
            ->count();

        // Partos próximos (15 días)
        $partosProximos = DB::table('porcicola_camadas as c')
            ->join('animales as a', 'a.id', '=', 'c.cerda_id')
            ->where('c.usuario_id', $uid)
            ->whereNull('c.fecha_parto_real')
            ->where('c.resultado_diagnostico', 'positivo')
            ->whereBetween('c.fecha_probable_parto', [$hoy, now()->addDays(15)->toDateString()])
            ->select('c.*', 'a.nombre_lote')
            ->orderBy('c.fecha_probable_parto')
            ->get();

        // Camadas en lactancia (paridas sin destetar)
        $enLactancia = DB::table('porcicola_camadas')
            ->where('usuario_id', $uid)
            ->whereNotNull('fecha_parto_real')
            ->whereNull('fecha_destete')
            ->count();

        // Destetes próximos (camadas paridas hace 21-28 días sin destetar)
        $desteteProximos = DB::table('porcicola_camadas as c')
            ->join('animales as a', 'a.id', '=', 'c.cerda_id')
            ->where('c.usuario_id', $uid)
            ->whereNotNull('c.fecha_parto_real')
            ->whereNull('c.fecha_destete')
            ->where('c.fecha_parto_real', '<=', now()->subDays(21)->toDateString())
            ->select('c.*', 'a.nombre_lote')
            ->orderBy('c.fecha_parto_real')
            ->get();

        // Sanidad vencida o próxima
        $alertasSanidad = DB::table('porcicola_sanidad')
            ->where('usuario_id', $uid)
            ->whereNull('fecha_aplicada')
            ->where('activo', 1)
            ->where('fecha_programada', '<=', now()->addDays(15)->toDateString())
            ->orderBy('fecha_programada')
            ->get();

        // Todos los lotes porcinos para el dashboard
        $lotes = DB::table('animales')
            ->where('usuario_id', $uid)->where('estado', 'activo')
            ->whereIn('especie', self::ESPECIES_PORCINAS)
            ->orderByRaw("FIELD(categoria_porcina,'hembra_cria','verraco','ceba','levante','lechon','otro')")
            ->get()->map(fn($l) => $this->enriquecerLote($l, $uid));

        return view('pages.porcicola.piara', compact(
            'inventario', 'totalCerdos', 'hembrasPreniadas',
            'partosProximos', 'enLactancia', 'desteteProximos',
            'alertasSanidad', 'lotes'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    // REPRODUCTIVO — Ciclo cerda + camadas
    // ─────────────────────────────────────────────────────────────────

    public function reproductivo()
    {
        $uid = session('usuario_id');

        // Hembras de cría activas
        $hembras = DB::table('animales')
            ->where('usuario_id', $uid)->where('estado', 'activo')
            ->whereIn('especie', self::ESPECIES_PORCINAS)
            ->whereIn('categoria_porcina', ['hembra_cria'])
            ->orderBy('nombre_lote')->get();

        // Verracos disponibles
        $verracos = DB::table('animales')
            ->where('usuario_id', $uid)->where('estado', 'activo')
            ->whereIn('especie', self::ESPECIES_PORCINAS)
            ->where('categoria_porcina', 'verraco')
            ->orderBy('nombre_lote')->get();

        // Historial de camadas con estado actual
        $camadas = DB::table('porcicola_camadas as c')
            ->join('animales as a', 'a.id', '=', 'c.cerda_id')
            ->where('c.usuario_id', $uid)
            ->select('c.*', 'a.nombre_lote', 'a.num_partos', 'a.raza_porcina')
            ->orderByDesc('c.fecha_servicio')
            ->get()
            ->map(fn($c) => $this->enriquecerCamada($c));

        // Métricas reproductivas
        $camadasConParto = DB::table('porcicola_camadas')
            ->where('usuario_id', $uid)
            ->whereNotNull('fecha_parto_real')->get();

        $promedioNacidos  = round($camadasConParto->avg('lechones_nacidos_vivos') ?? 0, 1);
        $promedioDestete  = round($camadasConParto->whereNotNull('fecha_destete')
                               ->avg('lechones_destetados') ?? 0, 1);
        $mortalidadPreD   = $camadasConParto->sum('muertes_pre_destete');
        $totalNacidos     = $camadasConParto->sum('lechones_nacidos_vivos');
        $pctMortPreD      = $totalNacidos > 0 ? round(($mortalidadPreD / $totalNacidos) * 100, 1) : 0;

        return view('pages.porcicola.reproductivo', compact(
            'hembras', 'verracos', 'camadas',
            'promedioNacidos', 'promedioDestete', 'pctMortPreD'
        ));
    }

    public function storeServicio(Request $request)
    {
        $request->validate([
            'cerda_id'      => 'required|integer',
            'fecha_servicio'=> 'required|date',
            'tipo_servicio' => 'required',
        ]);

        $uid     = session('usuario_id');
        $cerda   = DB::table('animales')
            ->where('id', $request->cerda_id)->where('usuario_id', $uid)->first();
        if (!$cerda) abort(404);

        // Número de camada = partos actuales + 1
        $numCamada = ($cerda->num_partos ?? 0) + 1
                   + DB::table('porcicola_camadas')
                       ->where('cerda_id', $cerda->id)
                       ->whereNull('fecha_parto_real')->count();

        $fechaProbable = Carbon::parse($request->fecha_servicio)
            ->addDays(self::DIAS_GESTACION)->toDateString();

        $camadaId = DB::table('porcicola_camadas')->insertGetId([
            'cerda_id'            => $request->cerda_id,
            'usuario_id'          => $uid,
            'numero_camada'       => $numCamada,
            'fecha_servicio'      => $request->fecha_servicio,
            'tipo_servicio'       => $request->tipo_servicio,
            'verraco_descripcion' => $request->verraco_descripcion ?: null,
            'fecha_probable_parto'=> $fechaProbable,
            'resultado_diagnostico' => 'pendiente',
            'observaciones'       => $request->observaciones ?: null,
            'creado_en'           => now()->toDateTimeString(),
            'actualizado_en'      => now()->toDateTimeString(),
        ]);

        // Registrar fase en ciclo reproductivo
        DB::table('porcicola_celo_servicio')->insert([
            'cerda_id'   => $request->cerda_id,
            'usuario_id' => $uid,
            'camada_id'  => $camadaId,
            'fase'       => 'servicio_realizado',
            'fecha'      => $request->fecha_servicio,
            'notas'      => $request->verraco_descripcion,
            'creado_en'  => now()->toDateTimeString(),
        ]);

        // Tarea diagnóstico de preñez (día 25-30 post-servicio)
        DB::table('tareas')->insert([
            'usuario_id' => $uid,
            'animal_id'  => $request->cerda_id,
            'titulo'     => 'Diagnóstico de preñez — ' . $cerda->nombre_lote,
            'tipo'       => 'salud',
            'fecha'      => Carbon::parse($request->fecha_servicio)->addDays(28)->toDateString(),
            'prioridad'  => 'alta',
            'completada' => 0,
            'notas'      => 'Camada #' . $numCamada . '. Parto probable: ' . $fechaProbable,
            'creado_en'  => now()->toDateTimeString(),
        ]);

        // Tarea recordatorio de parto (10 días antes)
        DB::table('tareas')->insert([
            'usuario_id' => $uid,
            'animal_id'  => $request->cerda_id,
            'titulo'     => 'Parto esperado — ' . $cerda->nombre_lote . ' (preparar parideras)',
            'tipo'       => 'salud',
            'fecha'      => Carbon::parse($fechaProbable)->subDays(10)->toDateString(),
            'prioridad'  => 'alta',
            'completada' => 0,
            'notas'      => 'Camada #' . $numCamada . '. Verificar temperatura, cama, neonatos.',
            'creado_en'  => now()->toDateTimeString(),
        ]);

        return redirect()->route('porcicola.reproductivo')
            ->with('msg', 'Servicio registrado. Parto probable: ' .
                Carbon::parse($fechaProbable)->format('d/m/Y') .
                '. Tareas de diagnóstico y preparación generadas en Agenda.')
            ->with('msgType', 'success');
    }

    public function confirmarPrenez(Request $request, $id)
    {
        $request->validate([
            'resultado_diagnostico' => 'required|in:positivo,negativo',
            'fecha_diagnostico'     => 'required|date',
        ]);

        $uid    = session('usuario_id');
        $camada = DB::table('porcicola_camadas')
            ->where('id', $id)->where('usuario_id', $uid)->first();
        if (!$camada) abort(404);

        DB::table('porcicola_camadas')->where('id', $id)->update([
            'resultado_diagnostico' => $request->resultado_diagnostico,
            'fecha_diagnostico'     => $request->fecha_diagnostico,
            'actualizado_en'        => now()->toDateTimeString(),
        ]);

        $cerda = DB::table('animales')->where('id', $camada->cerda_id)->first();

        if ($request->resultado_diagnostico === 'positivo') {
            DB::table('porcicola_celo_servicio')->insert([
                'cerda_id'   => $camada->cerda_id,
                'usuario_id' => $uid,
                'camada_id'  => $id,
                'fase'       => 'preñez_confirmada',
                'fecha'      => $request->fecha_diagnostico,
                'creado_en'  => now()->toDateTimeString(),
            ]);
            $msg = 'Preñez confirmada. Parto probable: ' .
                Carbon::parse($camada->fecha_probable_parto)->format('d/m/Y');
        } else {
            $msg = 'Diagnóstico negativo registrado. ' . ($cerda->nombre_lote ?? '') .
                   ' regresará a celo en aprox. 5-7 días.';
        }

        return redirect()->route('porcicola.reproductivo')
            ->with('msg', $msg)->with('msgType', 'success');
    }

    public function storeParto(Request $request)
    {
        $request->validate([
            'camada_id'              => 'required|integer',
            'fecha_parto_real'       => 'required|date',
            'lechones_nacidos_vivos' => 'required|integer|min:0',
        ]);

        $uid    = session('usuario_id');
        $camada = DB::table('porcicola_camadas')
            ->where('id', $request->camada_id)->where('usuario_id', $uid)->first();
        if (!$camada) abort(404);

        $pesoPromNacer = null;
        if ($request->peso_camada_nacer_kg && $request->lechones_nacidos_vivos > 0) {
            $pesoPromNacer = round($request->peso_camada_nacer_kg / $request->lechones_nacidos_vivos, 2);
        }

        DB::table('porcicola_camadas')->where('id', $camada->id)->update([
            'fecha_parto_real'          => $request->fecha_parto_real,
            'lechones_nacidos_vivos'    => $request->lechones_nacidos_vivos,
            'lechones_nacidos_muertos'  => $request->lechones_nacidos_muertos ?? 0,
            'lechones_momificados'      => $request->lechones_momificados ?? 0,
            'peso_camada_nacer_kg'      => $request->peso_camada_nacer_kg ?: null,
            'peso_promedio_nacer_kg'    => $pesoPromNacer,
            'observaciones'             => $request->observaciones ?: $camada->observaciones,
            'actualizado_en'            => now()->toDateTimeString(),
        ]);

        // Actualizar num_partos y fecha_ultimo_parto de la cerda
        $cerda = DB::table('animales')->where('id', $camada->cerda_id)->first();
        DB::table('animales')->where('id', $camada->cerda_id)->update([
            'num_partos'        => ($cerda->num_partos ?? 0) + 1,
            'fecha_ultimo_parto'=> $request->fecha_parto_real,
            'actualizado_en'    => now()->toDateTimeString(),
        ]);

        // Registrar fases
        DB::table('porcicola_celo_servicio')->insert([
            'cerda_id'   => $camada->cerda_id,
            'usuario_id' => $uid,
            'camada_id'  => $camada->id,
            'fase'       => 'parto',
            'fecha'      => $request->fecha_parto_real,
            'notas'      => $request->lechones_nacidos_vivos . ' vivos / ' . ($request->lechones_nacidos_muertos ?? 0) . ' muertos',
            'creado_en'  => now()->toDateTimeString(),
        ]);
        DB::table('porcicola_celo_servicio')->insert([
            'cerda_id'   => $camada->cerda_id,
            'usuario_id' => $uid,
            'camada_id'  => $camada->id,
            'fase'       => 'lactancia',
            'fecha'      => $request->fecha_parto_real,
            'creado_en'  => now()->toDateTimeString(),
        ]);

        // Tarea de destete (día 21-28 post-parto)
        DB::table('tareas')->insert([
            'usuario_id' => $uid,
            'animal_id'  => $camada->cerda_id,
            'titulo'     => 'Destete — ' . ($cerda->nombre_lote ?? 'cerda') .
                            ' · Camada #' . $camada->numero_camada,
            'tipo'       => 'salud',
            'fecha'      => Carbon::parse($request->fecha_parto_real)->addDays(24)->toDateString(),
            'prioridad'  => 'alta',
            'completada' => 0,
            'notas'      => $request->lechones_nacidos_vivos . ' lechones. ' .
                            'Aplicar hierro dextrano al día 3.',
            'creado_en'  => now()->toDateTimeString(),
        ]);

        // Tarea hierro dextrano para lechones (día 3)
        DB::table('tareas')->insert([
            'usuario_id' => $uid,
            'animal_id'  => $camada->cerda_id,
            'titulo'     => 'Hierro dextrano lechones — ' . ($cerda->nombre_lote ?? ''),
            'tipo'       => 'salud',
            'fecha'      => Carbon::parse($request->fecha_parto_real)->addDays(3)->toDateString(),
            'prioridad'  => 'alta',
            'completada' => 0,
            'notas'      => '1 mL IM por lechón. Castrar machos día 7. Descolmillar al nacer.',
            'creado_en'  => now()->toDateTimeString(),
        ]);

        $msg = 'Parto registrado: ' . $request->lechones_nacidos_vivos . ' lechones vivos. ' .
               'Tareas de hierro dextrano y destete generadas en Agenda.';

        return redirect()->route('porcicola.reproductivo')
            ->with('msg', $msg)->with('msgType', 'success');
    }

    public function storeDestete(Request $request)
    {
        $request->validate([
            'camada_id'           => 'required|integer',
            'fecha_destete'       => 'required|date',
            'lechones_destetados' => 'required|integer|min:0',
        ]);

        $uid    = session('usuario_id');
        $camada = DB::table('porcicola_camadas')
            ->where('id', $request->camada_id)->where('usuario_id', $uid)->first();
        if (!$camada) abort(404);

        $pesoPromDestete = null;
        if ($request->peso_camada_destete_kg && $request->lechones_destetados > 0) {
            $pesoPromDestete = round($request->peso_camada_destete_kg / $request->lechones_destetados, 2);
        }

        $muertesPre = ($camada->lechones_nacidos_vivos ?? 0) - $request->lechones_destetados;

        DB::table('porcicola_camadas')->where('id', $camada->id)->update([
            'fecha_destete'             => $request->fecha_destete,
            'lechones_destetados'       => $request->lechones_destetados,
            'peso_camada_destete_kg'    => $request->peso_camada_destete_kg ?: null,
            'peso_promedio_destete_kg'  => $pesoPromDestete,
            'muertes_pre_destete'       => max(0, $muertesPre),
            'causa_mortalidad'          => $request->causa_mortalidad ?: null,
            'actualizado_en'            => now()->toDateTimeString(),
        ]);

        // Registrar fase destete
        DB::table('porcicola_celo_servicio')->insert([
            'cerda_id'   => $camada->cerda_id,
            'usuario_id' => $uid,
            'camada_id'  => $camada->id,
            'fase'       => 'destete',
            'fecha'      => $request->fecha_destete,
            'notas'      => $request->lechones_destetados . ' lechones destetados',
            'creado_en'  => now()->toDateTimeString(),
        ]);

        // Tarea retorno a servicio (5-7 días post-destete)
        $cerda = DB::table('animales')->where('id', $camada->cerda_id)->first();
        DB::table('tareas')->insert([
            'usuario_id' => $uid,
            'animal_id'  => $camada->cerda_id,
            'titulo'     => 'Retorno a celo — ' . ($cerda->nombre_lote ?? '') .
                            ' (post-destete)',
            'tipo'       => 'salud',
            'fecha'      => Carbon::parse($request->fecha_destete)->addDays(6)->toDateString(),
            'prioridad'  => 'media',
            'completada' => 0,
            'notas'      => 'Detectar celo 5-7 días post-destete para próximo servicio.',
            'creado_en'  => now()->toDateTimeString(),
        ]);

        $msg = 'Destete registrado: ' . $request->lechones_destetados . ' lechones' .
               ($pesoPromDestete ? ' · Peso promedio: ' . $pesoPromDestete . ' kg' : '') .
               '. Tarea de retorno a servicio generada.';

        return redirect()->route('porcicola.reproductivo')
            ->with('msg', $msg)->with('msgType', 'success');
    }

    // ─────────────────────────────────────────────────────────────────
    // CEBA — Control de peso y conversión
    // ─────────────────────────────────────────────────────────────────

    public function ceba()
    {
        $uid = session('usuario_id');

        $lotesCeba = DB::table('animales')
            ->where('usuario_id', $uid)->where('estado', 'activo')
            ->whereIn('especie', self::ESPECIES_PORCINAS)
            ->whereIn('categoria_porcina', ['ceba', 'levante'])
            ->orderBy('nombre_lote')->get();

        $datosLotes = [];
        $tablaStd   = DB::table('porcicola_tabla_peso_std')
            ->where('categoria', 'ceba_comercial')
            ->orderBy('semana_ceba')->get()->keyBy('semana_ceba');

        foreach ($lotesCeba as $lote) {
            $pesos = DB::table('porcicola_pesos_ceba')
                ->where('animal_id', $lote->id)
                ->orderBy('semana')->get();

            $conversiones = DB::table('porcicola_conversion')
                ->where('animal_id', $lote->id)
                ->orderByDesc('semana')->limit(4)->get();

            $ultimoPeso = $pesos->last();
            $fechaSacrificio = null;
            $diasRestantes   = null;

            if ($ultimoPeso && $ultimoPeso->gpd_kg > 0) {
                $metaKg  = $lote->peso_meta_sacrificio_kg ?? 100;
                $diasRest = max(0, round(($metaKg - $ultimoPeso->peso_promedio_kg) / $ultimoPeso->gpd_kg));
                $fechaSacrificio = Carbon::parse($ultimoPeso->fecha)->addDays($diasRest)->toDateString();
                $diasRestantes   = $diasRest;
            }

            // Avance % hacia la meta
            $metaKg  = $lote->peso_meta_sacrificio_kg ?? 100;
            $entradaKg = $lote->peso_entrada_kg ?? 20;
            $pesoActual = $ultimoPeso->peso_promedio_kg ?? $lote->peso_promedio ?? $entradaKg;
            $pctAvance  = $metaKg > $entradaKg
                ? min(100, round((($pesoActual - $entradaKg) / ($metaKg - $entradaKg)) * 100))
                : 0;

            $datosLotes[$lote->id] = compact(
                'lote', 'pesos', 'conversiones', 'tablaStd',
                'ultimoPeso', 'fechaSacrificio', 'diasRestantes',
                'pctAvance', 'metaKg', 'pesoActual'
            );
        }

        $todosPorcinos = DB::table('animales')
            ->where('usuario_id', $uid)->where('estado', 'activo')
            ->whereIn('especie', self::ESPECIES_PORCINAS)
            ->orderBy('nombre_lote')->get();

        return view('pages.porcicola.ceba', compact(
            'lotesCeba', 'datosLotes', 'todosPorcinos', 'tablaStd'
        ));
    }

    public function storePesoCeba(Request $request)
    {
        $request->validate([
            'animal_id'       => 'required|integer',
            'semana'          => 'required|integer|min:1',
            'peso_promedio_kg'=> 'required|numeric|min:0',
            'fecha'           => 'required|date',
        ]);

        $uid     = session('usuario_id');
        $anterior = DB::table('porcicola_pesos_ceba')
            ->where('animal_id', $request->animal_id)
            ->where('semana', $request->semana - 1)->first();

        $gpd = null;
        if ($anterior) {
            $dias = max(1, Carbon::parse($anterior->fecha)->diffInDays(Carbon::parse($request->fecha)));
            $gpd  = round(($request->peso_promedio_kg - $anterior->peso_promedio_kg) / $dias, 3);
        }

        $std = DB::table('porcicola_tabla_peso_std')
            ->where('categoria', 'ceba_comercial')
            ->where('semana_ceba', $request->semana)->first();

        DB::table('porcicola_pesos_ceba')->updateOrInsert(
            ['animal_id' => $request->animal_id, 'semana' => $request->semana],
            [
                'usuario_id'      => $uid,
                'fecha'           => $request->fecha,
                'peso_promedio_kg'=> $request->peso_promedio_kg,
                'animales_pesados'=> $request->animales_pesados ?: null,
                'gpd_kg'          => $gpd,
                'peso_meta_kg'    => $std->peso_meta_kg ?? null,
                'uniformidad_pct' => $request->uniformidad_pct ?: null,
                'observaciones'   => $request->observaciones ?: null,
                'creado_en'       => now()->toDateTimeString(),
            ]
        );

        $dif = '';
        if ($std) {
            $delta = $request->peso_promedio_kg - $std->peso_meta_kg;
            $dif   = $delta >= 0
                ? ' (+' . round($delta, 1) . 'kg sobre meta)'
                : ' (' . round(abs($delta), 1) . 'kg bajo meta)';
        }

        return redirect()->route('porcicola.ceba')
            ->with('msg', 'Peso semana ' . $request->semana . ': ' . $request->peso_promedio_kg . 'kg' . $dif)
            ->with('msgType', 'success');
    }

    public function storeConversion(Request $request)
    {
        $request->validate([
            'animal_id'            => 'required|integer',
            'semana'               => 'required|integer|min:1',
            'fecha_inicio'         => 'required|date',
            'fecha_fin'            => 'required|date|after_or_equal:fecha_inicio',
            'alimento_consumido_kg'=> 'required|numeric|min:0',
            'ganancia_peso_kg'     => 'required|numeric|min:0.001',
        ]);

        $uid = session('usuario_id');
        $ca  = round($request->alimento_consumido_kg / $request->ganancia_peso_kg, 3);

        DB::table('porcicola_conversion')->updateOrInsert(
            ['animal_id' => $request->animal_id, 'semana' => $request->semana],
            [
                'usuario_id'            => $uid,
                'fecha_inicio'          => $request->fecha_inicio,
                'fecha_fin'             => $request->fecha_fin,
                'alimento_consumido_kg' => $request->alimento_consumido_kg,
                'ganancia_peso_kg'      => $request->ganancia_peso_kg,
                'conversion_alimenticia'=> $ca,
                'tipo_alimento'         => $request->tipo_alimento ?: null,
                'observaciones'         => $request->observaciones ?: null,
                'creado_en'             => now()->toDateTimeString(),
            ]
        );

        $nivel = $ca <= 2.8 ? 'success' : ($ca <= 3.2 ? 'warning' : 'error');
        return redirect()->route('porcicola.ceba')
            ->with('msg', 'CA semana ' . $request->semana . ': ' . $ca .
                   ($ca <= 2.8 ? ' ✅ Eficiente' : ($ca <= 3.2 ? ' ⚠️ Normal' : ' ❌ Alta')))
            ->with('msgType', $nivel);
    }

    // ─────────────────────────────────────────────────────────────────
    // SANIDAD — Vacunación y tratamientos
    // ─────────────────────────────────────────────────────────────────

    public function sanidad()
    {
        $uid = session('usuario_id');

        $porcinos = DB::table('animales')
            ->where('usuario_id', $uid)->where('estado', 'activo')
            ->whereIn('especie', self::ESPECIES_PORCINAS)
            ->orderBy('nombre_lote')->get();

        // Auto-inicializar protocolos si la piara existe y no tiene sanidad
        $tienePiara  = $porcinos->isNotEmpty();
        $tieneSanidad = DB::table('porcicola_sanidad')
            ->where('usuario_id', $uid)->exists();

        if ($tienePiara && !$tieneSanidad) {
            $this->inicializarProtocolos($uid);
        }

        $sanidad = DB::table('porcicola_sanidad as s')
            ->leftJoin('animales as a', 'a.id', '=', 's.animal_id')
            ->where('s.usuario_id', $uid)->where('s.activo', 1)
            ->select('s.*', 'a.nombre_lote')
            ->orderBy('s.fecha_programada')
            ->get();

        $hoy            = now()->toDateString();
        $vencidas       = $sanidad->filter(fn($s) => $s->fecha_programada < $hoy && !$s->fecha_aplicada);
        $proximas       = $sanidad->filter(fn($s) => !$s->fecha_aplicada
                                && $s->fecha_programada >= $hoy
                                && $s->fecha_programada <= now()->addDays(15)->toDateString());
        $aplicadas      = $sanidad->filter(fn($s) => $s->fecha_aplicada !== null);
        $pendientesFut  = $sanidad->filter(fn($s) => !$s->fecha_aplicada
                                && ($s->fecha_programada ?? '9999') > now()->addDays(15)->toDateString());

        return view('pages.porcicola.sanidad', compact(
            'porcinos', 'sanidad', 'vencidas', 'proximas', 'aplicadas', 'pendientesFut', 'hoy'
        ));
    }

    public function aplicarSanidad(Request $request, $id)
    {
        $request->validate(['fecha_aplicada' => 'required|date']);
        $uid = session('usuario_id');

        $prot = DB::table('porcicola_sanidad')
            ->where('id', $id)->where('usuario_id', $uid)->first();
        if (!$prot) abort(404);

        $proxima = null;
        if ($prot->frecuencia_dias) {
            $proxima = Carbon::parse($request->fecha_aplicada)
                ->addDays($prot->frecuencia_dias)->toDateString();
        }

        DB::table('porcicola_sanidad')->where('id', $id)->update([
            'fecha_aplicada'  => $request->fecha_aplicada,
            'producto_usado'  => $request->producto_usado ?: $prot->producto_usado,
            'dosis'           => $request->dosis ?: $prot->dosis,
            'proxima_aplicacion' => $proxima,
            'observaciones'   => $request->observaciones ?: $prot->observaciones,
            'actualizado_en'  => now()->toDateTimeString(),
        ]);

        // Crear tarea para la próxima aplicación
        if ($proxima) {
            DB::table('tareas')->insert([
                'usuario_id' => $uid,
                'animal_id'  => $prot->animal_id,
                'titulo'     => 'Sanidad porcina: ' . $prot->nombre_protocolo,
                'tipo'       => 'salud',
                'fecha'      => $proxima,
                'prioridad'  => 'alta',
                'completada' => 0,
                'creado_en'  => now()->toDateTimeString(),
            ]);

            // Nuevo registro del protocolo para la próxima fecha
            DB::table('porcicola_sanidad')->insert([
                'animal_id'        => $prot->animal_id,
                'usuario_id'       => $uid,
                'protocolo'        => $prot->protocolo,
                'nombre_protocolo' => $prot->nombre_protocolo,
                'tipo'             => $prot->tipo,
                'via_administracion'=> $prot->via_administracion,
                'frecuencia_dias'  => $prot->frecuencia_dias,
                'fecha_programada' => $proxima,
                'dosis'            => $prot->dosis,
                'activo'           => 1,
                'creado_en'        => now()->toDateTimeString(),
                'actualizado_en'   => now()->toDateTimeString(),
            ]);
        }

        return redirect()->route('porcicola.sanidad')
            ->with('msg', '"' . $prot->nombre_protocolo . '" aplicado.' .
                   ($proxima ? ' Próxima: ' . Carbon::parse($proxima)->format('d/m/Y') : ''))
            ->with('msgType', 'success');
    }

    public function storeSanidadPersonalizada(Request $request)
    {
        $request->validate([
            'nombre_protocolo' => 'required|string|max:120',
            'fecha_programada' => 'required|date',
            'tipo'             => 'required',
            'via_administracion'=> 'required',
        ]);

        $uid = session('usuario_id');

        $id = DB::table('porcicola_sanidad')->insertGetId([
            'animal_id'        => $request->animal_id ?: null,
            'usuario_id'       => $uid,
            'protocolo'        => 'personalizado_' . time(),
            'nombre_protocolo' => $request->nombre_protocolo,
            'tipo'             => $request->tipo,
            'via_administracion'=> $request->via_administracion,
            'frecuencia_dias'  => $request->frecuencia_dias ?: null,
            'fecha_programada' => $request->fecha_programada,
            'dosis'            => $request->dosis ?: null,
            'producto_usado'   => $request->producto_usado ?: null,
            'observaciones'    => $request->observaciones ?: null,
            'activo'           => 1,
            'creado_en'        => now()->toDateTimeString(),
            'actualizado_en'   => now()->toDateTimeString(),
        ]);

        DB::table('tareas')->insert([
            'usuario_id' => $uid,
            'animal_id'  => $request->animal_id ?: null,
            'titulo'     => 'Sanidad porcina: ' . $request->nombre_protocolo,
            'tipo'       => 'salud',
            'fecha'      => $request->fecha_programada,
            'prioridad'  => 'alta',
            'completada' => 0,
            'creado_en'  => now()->toDateTimeString(),
        ]);

        return redirect()->route('porcicola.sanidad')
            ->with('msg', 'Protocolo programado y tarea generada en Agenda.')
            ->with('msgType', 'success');
    }

    // ─────────────────────────────────────────────────────────────────
    // REPORTES
    // ─────────────────────────────────────────────────────────────────

    public function reportes()
    {
        $uid = session('usuario_id');

        $lotes = DB::table('animales')
            ->where('usuario_id', $uid)->where('estado', 'activo')
            ->whereIn('especie', self::ESPECIES_PORCINAS)
            ->get()->map(fn($l) => $this->enriquecerLote($l, $uid));

        // Métricas reproductivas
        $camadasConParto = DB::table('porcicola_camadas')
            ->where('usuario_id', $uid)->whereNotNull('fecha_parto_real')->get();

        $totalCamadas    = $camadasConParto->count();
        $totalNacidos    = $camadasConParto->sum('lechones_nacidos_vivos');
        $promedioNacidos = $totalCamadas > 0 ? round($totalNacidos / $totalCamadas, 1) : 0;

        $camadasDestete  = $camadasConParto->whereNotNull('fecha_destete');
        $totalDestetados = $camadasDestete->sum('lechones_destetados');
        $promDestetados  = $camadasDestete->count() > 0
            ? round($totalDestetados / $camadasDestete->count(), 1) : 0;

        $mortPreDestete  = $camadasConParto->sum('muertes_pre_destete');
        $pctMortPreD     = $totalNacidos > 0 ? round(($mortPreDestete / $totalNacidos) * 100, 1) : 0;

        // Mejor CA del mes
        $mesActual = now()->format('Y-m');
        $mejorCA   = DB::table('porcicola_conversion as c')
            ->join('animales as a', 'a.id', '=', 'c.animal_id')
            ->where('c.usuario_id', $uid)
            ->whereRaw("DATE_FORMAT(c.fecha_inicio,'%Y-%m') = ?", [$mesActual])
            ->select('a.nombre_lote', 'c.conversion_alimenticia', 'c.semana')
            ->orderBy('c.conversion_alimenticia')->first();

        // Hembras por número de parto
        $hembrasParidad = DB::table('animales')
            ->where('usuario_id', $uid)->where('estado', 'activo')
            ->where('categoria_porcina', 'hembra_cria')
            ->selectRaw('num_partos, COUNT(*) as cantidad')
            ->groupBy('num_partos')->orderBy('num_partos')->get();

        return view('pages.porcicola.reportes', compact(
            'lotes', 'totalCamadas', 'promedioNacidos',
            'promDestetados', 'pctMortPreD', 'mejorCA', 'hembrasParidad'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    // PRIVADOS — Helpers
    // ─────────────────────────────────────────────────────────────────

    private function enriquecerLote($lote, int $uid): object
    {
        // Última camada activa de este animal (si es hembra)
        if ($lote->categoria_porcina === 'hembra_cria') {
            $lote->camada_activa = DB::table('porcicola_camadas')
                ->where('cerda_id', $lote->id)
                ->whereNull('fecha_parto_real')
                ->where('resultado_diagnostico', 'positivo')
                ->orderByDesc('fecha_servicio')->first();

            $lote->en_lactancia = DB::table('porcicola_camadas')
                ->where('cerda_id', $lote->id)
                ->whereNotNull('fecha_parto_real')
                ->whereNull('fecha_destete')->first();
        } else {
            $lote->camada_activa = null;
            $lote->en_lactancia  = null;
        }

        return $lote;
    }

    private function enriquecerCamada($camada): object
    {
        // Estado legible de la camada
        if ($camada->fecha_destete) {
            $camada->estado_legible = 'destetada';
            $camada->estado_color   = '#15803d';
        } elseif ($camada->fecha_parto_real) {
            $diasLact = now()->diffInDays(Carbon::parse($camada->fecha_parto_real));
            $camada->estado_legible = 'Lactancia día ' . $diasLact;
            $camada->estado_color   = '#2563eb';
            $camada->dias_lactancia = $diasLact;
        } elseif ($camada->resultado_diagnostico === 'positivo') {
            $diasGest = now()->diffInDays(Carbon::parse($camada->fecha_servicio));
            $diasRest = self::DIAS_GESTACION - $diasGest;
            $camada->estado_legible = 'Preñada · día ' . $diasGest . ' · faltan ' . max(0,$diasRest) . 'd';
            $camada->estado_color   = '#ea580c';
        } elseif ($camada->resultado_diagnostico === 'negativo') {
            $camada->estado_legible = 'Diagnóstico negativo';
            $camada->estado_color   = '#dc2626';
        } else {
            $camada->estado_legible = 'Diagnóstico pendiente';
            $camada->estado_color   = '#94a3b8';
        }

        return $camada;
    }

    private function inicializarProtocolos(int $uid): void
    {
        $hoy = now()->toDateString();
        foreach (self::PROTOCOLOS as $p) {
            // Solo crear si no existe ya este protocolo para este usuario
            $existe = DB::table('porcicola_sanidad')
                ->where('usuario_id', $uid)->where('protocolo', $p['protocolo'])->exists();
            if ($existe) continue;

            DB::table('porcicola_sanidad')->insert([
                'animal_id'        => null,
                'usuario_id'       => $uid,
                'protocolo'        => $p['protocolo'],
                'nombre_protocolo' => $p['nombre'],
                'tipo'             => $p['tipo'],
                'via_administracion'=> $p['via'],
                'frecuencia_dias'  => $p['frecuencia_dias'] ?? null,
                'fecha_programada' => $hoy,
                'dosis'            => $p['dosis'],
                'activo'           => 1,
                'creado_en'        => now()->toDateTimeString(),
                'actualizado_en'   => now()->toDateTimeString(),
            ]);

            // Tarea automática
            DB::table('tareas')->insert([
                'usuario_id' => $uid,
                'animal_id'  => null,
                'titulo'     => 'Sanidad porcina: ' . $p['nombre'] . ' (1ra aplicación)',
                'tipo'       => 'salud',
                'fecha'      => $hoy,
                'prioridad'  => 'alta',
                'completada' => 0,
                'creado_en'  => now()->toDateTimeString(),
            ]);
        }
    }
}