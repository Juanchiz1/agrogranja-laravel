<?php

namespace App\Http\Controllers;

use App\Models\AnimalLactancia;
use App\Models\AnimalOrdeno;
use App\Models\AnimalReproduccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BovinoController extends Controller
{
    // Especies bovinas manejadas
    private const ESPECIES_BOVINAS = ['Ganado bovino', 'Terneros'];

    // Protocolos sanitarios estándar del hato bovino colombiano
    private const PROTOCOLOS = [
        ['protocolo'=>'aftosa',          'nombre'=>'Vacunación Aftosa',               'frecuencia_dias'=>180, 'via'=>'subcutanea'],
        ['protocolo'=>'brucelosis',      'nombre'=>'Vacunación Brucelosis (hembras)', 'frecuencia_dias'=>365, 'via'=>'subcutanea'],
        ['protocolo'=>'desparasitacion', 'nombre'=>'Desparasitación interna/externa', 'frecuencia_dias'=>90,  'via'=>'oral'],
        ['protocolo'=>'vitaminas',       'nombre'=>'Vitaminas AD3E + Selenio',        'frecuencia_dias'=>90,  'via'=>'intramuscular'],
        ['protocolo'=>'carbunco',        'nombre'=>'Vacunación Carbunco',             'frecuencia_dias'=>365, 'via'=>'subcutanea'],
    ];

    // ─────────────────────────────────────────────────────────────────
    // HATO — Dashboard principal del módulo bovino
    // ─────────────────────────────────────────────────────────────────

    public function hato()
    {
        $uid = session('usuario_id');
        $hoy = now()->toDateString();

        // ── Conteos del hato ──────────────────────────────────────────
        $totalBovinos = DB::table('animales')
            ->where('usuario_id', $uid)->where('estado', 'activo')
            ->whereIn('especie', self::ESPECIES_BOVINAS)->sum('cantidad');

        // Vacas en producción (con lactancia activa)
        $vacasProduccion = DB::table('animales as a')
            ->join('animal_lactancia as l', 'l.animal_id', '=', 'a.id')
            ->where('a.usuario_id', $uid)->where('a.estado', 'activo')
            ->whereNull('l.fecha_secado')->count('a.id');

        // Vacas secas (sin lactancia activa)
        $vacasSecas = DB::table('animales as a')
            ->where('a.usuario_id', $uid)->where('a.estado', 'activo')
            ->whereIn('a.especie', self::ESPECIES_BOVINAS)
            ->whereNotExists(function ($q) {
                $q->from('animal_lactancia as l')
                  ->whereRaw('l.animal_id = a.id')
                  ->whereNull('l.fecha_secado');
            })->count();

        // En gestación (preñadas, sin parto registrado)
        $enGestacion = DB::table('animal_reproduccion')
            ->where('usuario_id', $uid)
            ->where('resultado_diagnostico', 'positivo')
            ->whereNull('fecha_parto_real')->count();

        // ── Producción del día ────────────────────────────────────────
        $litrosHoy = DB::table('animal_ordenos')
            ->where('usuario_id', $uid)->where('fecha', $hoy)->sum('litros');

        $litrosAyer = DB::table('animal_ordenos')
            ->where('usuario_id', $uid)
            ->where('fecha', now()->subDay()->toDateString())->sum('litros');

        // Promedio últimos 7 días
        // La subquery agrupa litros por fecha; el AVG se calcula sobre ella
        // sin filtros adicionales en el query externo (evita error 'unknown column').
        $fecha7d    = now()->subDays(7)->toDateString();
        $promedio7d = DB::selectOne(
            'SELECT AVG(litros_dia) as prom
               FROM (SELECT fecha, SUM(litros) as litros_dia
                       FROM animal_ordenos
                      WHERE usuario_id = ? AND fecha >= ?
                      GROUP BY fecha) sub',
            [$uid, $fecha7d]
        )->prom ?? 0;

        // ── Alertas ───────────────────────────────────────────────────
        // Partos próximos (15 días)
        $partosProximos = DB::table('animal_reproduccion as r')
            ->join('animales as a', 'a.id', '=', 'r.animal_id')
            ->where('r.usuario_id', $uid)
            ->where('r.resultado_diagnostico', 'positivo')
            ->whereNull('r.fecha_parto_real')
            ->where('r.fecha_probable_parto', '<=', now()->addDays(15)->toDateString())
            ->where('r.fecha_probable_parto', '>=', $hoy)
            ->select('a.nombre_lote', 'a.id as animal_id', 'r.fecha_probable_parto')
            ->orderBy('r.fecha_probable_parto')->get();

        // Sanidad próxima o vencida (7 días)
        $alertasSanidad = DB::table('animal_sanidad_programada')
            ->where('usuario_id', $uid)->where('activo', 1)
            ->where('proxima_aplicacion', '<=', now()->addDays(7)->toDateString())
            ->orderBy('proxima_aplicacion')->get();

        // Vacas con días abiertos > 90 días (sin servicio después del parto)
        $diasAbiertosAltos = DB::table('animal_reproduccion as r')
            ->join('animales as a', 'a.id', '=', 'r.animal_id')
            ->where('r.usuario_id', $uid)
            ->whereNotNull('r.fecha_parto_real')
            ->where('r.fecha_parto_real', '<=', now()->subDays(90)->toDateString())
            ->whereNotExists(function ($q) {
                $q->from('animal_reproduccion as r2')
                  ->whereRaw('r2.animal_id = r.animal_id')
                  ->where('r2.fecha_servicio', '>', DB::raw('r.fecha_parto_real'));
            })
            ->select('a.nombre_lote', 'a.id as animal_id', 'r.fecha_parto_real')
            ->limit(5)->get();

        // ── Producción últimos 14 días (para mini-gráfica) ────────────
        $historico14d = DB::table('animal_ordenos')
            ->where('usuario_id', $uid)
            ->where('fecha', '>=', now()->subDays(13)->toDateString())
            ->selectRaw('fecha, SUM(litros) as total')
            ->groupBy('fecha')->orderBy('fecha')->get()
            ->pluck('total', 'fecha');

        // Completar días sin datos con 0
        $labels = []; $valores = [];
        for ($i = 13; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $labels[]  = Carbon::parse($d)->format('d/m');
            $valores[] = (float)($historico14d[$d] ?? 0);
        }

        return view('pages.bovino.hato', compact(
            'totalBovinos', 'vacasProduccion', 'vacasSecas', 'enGestacion',
            'litrosHoy', 'litrosAyer', 'promedio7d',
            'partosProximos', 'alertasSanidad', 'diasAbiertosAltos',
            'labels', 'valores'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    // ORDEÑOS — Registro AM/PM y curva de producción
    // ─────────────────────────────────────────────────────────────────

    public function ordenos(Request $request)
    {
        $uid   = session('usuario_id');
        $fecha = $request->fecha ?? now()->toDateString();

        // Vacas con lactancia activa (son las que se ordeñan)
        $vacasProductoras = DB::table('animales as a')
            ->join('animal_lactancia as l', 'l.animal_id', '=', 'a.id')
            ->where('a.usuario_id', $uid)
            ->where('a.estado', 'activo')
            ->whereIn('a.especie', self::ESPECIES_BOVINAS)
            ->whereNull('l.fecha_secado')
            ->select('a.id', 'a.nombre_lote', 'a.raza', 'l.id as lactancia_id',
                     'l.fecha_inicio', 'l.numero_lactancia')
            ->orderBy('a.nombre_lote')->get();

        // Ordeños registrados para la fecha seleccionada
        $ordenosDelDia = DB::table('animal_ordenos')
            ->where('usuario_id', $uid)
            ->where('fecha', $fecha)
            ->get()->groupBy('animal_id');

        // Total del día
        $totalDia = DB::table('animal_ordenos')
            ->where('usuario_id', $uid)->where('fecha', $fecha)->sum('litros');

        // Historial 30 días para el chart
        $historico = DB::table('animal_ordenos')
            ->where('usuario_id', $uid)
            ->where('fecha', '>=', now()->subDays(29)->toDateString())
            ->selectRaw('fecha, SUM(litros) as total')
            ->groupBy('fecha')->orderBy('fecha')->get();

        $chartLabels = []; $chartData = [];
        $mapa = $historico->pluck('total','fecha');
        for ($i = 29; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $chartLabels[] = Carbon::parse($d)->format('d/m');
            $chartData[]   = (float)($mapa[$d] ?? 0);
        }

        // Todas las vacas bovinas (incluso sin lactancia activa) para filtro
        $todasBovinas = DB::table('animales')
            ->where('usuario_id', $uid)->where('estado','activo')
            ->whereIn('especie', self::ESPECIES_BOVINAS)
            ->orderBy('nombre_lote')->get();

        // Compradores para el modal de venta de leche
        $personas = DB::table('personas')
            ->where('usuario_id', $uid)->where('activo', 1)
            ->whereIn('tipo', ['cliente','comprador','otro'])
            ->orderBy('nombre')->get();

        return view('pages.bovino.ordenos', compact(
            'vacasProductoras', 'ordenosDelDia', 'totalDia', 'fecha',
            'chartLabels', 'chartData', 'todasBovinas', 'personas'
        ));
    }

    public function storeOrdeno(Request $request)
    {
        $request->validate([
            'animal_id'   => 'required|integer',
            'fecha'       => 'required|date',
            'sesion'      => 'required|in:am,pm,unica',
            'litros'      => 'required|numeric|min:0',
        ]);

        $uid = session('usuario_id');

        // Verificar que el animal pertenece al usuario
        $animal = DB::table('animales')
            ->where('id', $request->animal_id)
            ->where('usuario_id', $uid)->first();
        if (!$animal) abort(403);

        // Lactancia activa
        $lactancia = DB::table('animal_lactancia')
            ->where('animal_id', $request->animal_id)
            ->whereNull('fecha_secado')
            ->orderBy('id','desc')->first();

        // Upsert: si ya existe el registro de esa sesión, actualizar
        $existe = DB::table('animal_ordenos')
            ->where('animal_id', $request->animal_id)
            ->where('fecha', $request->fecha)
            ->where('sesion', $request->sesion)->first();

        if ($existe) {
            DB::table('animal_ordenos')->where('id', $existe->id)->update([
                'litros'            => $request->litros,
                'temperatura_leche' => $request->temperatura_leche ?: null,
                'observaciones'     => $request->observaciones ?: null,
            ]);
        } else {
            DB::table('animal_ordenos')->insert([
                'animal_id'         => $request->animal_id,
                'usuario_id'        => $uid,
                'lactancia_id'      => $lactancia->id ?? null,
                'fecha'             => $request->fecha,
                'sesion'            => $request->sesion,
                'litros'            => $request->litros,
                'temperatura_leche' => $request->temperatura_leche ?: null,
                'observaciones'     => $request->observaciones ?: null,
                'creado_en'         => now()->toDateTimeString(),
            ]);
        }

        // Recalcular acumulado de la lactancia
        if ($lactancia) {
            $total = DB::table('animal_ordenos')
                ->where('lactancia_id', $lactancia->id)->sum('litros');
            DB::table('animal_lactancia')->where('id', $lactancia->id)
                ->update(['produccion_acumulada_litros' => $total]);
        }

        return redirect()->route('bovino.ordenos', ['fecha' => $request->fecha])
            ->with('msg', 'Ordeño registrado correctamente.')->with('msgType','success');
    }

    public function destroyOrdeno(int $id)
    {
        $uid    = session('usuario_id');
        $ordeno = DB::table('animal_ordenos')->where('id',$id)->where('usuario_id',$uid)->first();
        if (!$ordeno) abort(404);

        DB::table('animal_ordenos')->where('id',$id)->delete();

        // Recalcular acumulado
        if ($ordeno->lactancia_id) {
            $total = DB::table('animal_ordenos')
                ->where('lactancia_id', $ordeno->lactancia_id)->sum('litros');
            DB::table('animal_lactancia')->where('id', $ordeno->lactancia_id)
                ->update(['produccion_acumulada_litros' => $total]);
        }

        return redirect()->route('bovino.ordenos', ['fecha' => $ordeno->fecha])
            ->with('msg','Ordeño eliminado.')->with('msgType','warning');
    }

    // ─────────────────────────────────────────────────────────────────
    // REPRODUCCIÓN
    // ─────────────────────────────────────────────────────────────────

    public function reproduccion()
    {
        $uid = session('usuario_id');

        // Hembras bovinas activas con su estado reproductivo actual
        $hembras = DB::table('animales as a')
            ->where('a.usuario_id', $uid)
            ->where('a.estado', 'activo')
            ->whereIn('a.especie', self::ESPECIES_BOVINAS)
            // Último registro de reproducción (puede ser null)
            ->leftJoinSub(
                DB::table('animal_reproduccion as r')
                  ->select('animal_id', DB::raw('MAX(id) as ultimo_id'))
                  ->where('usuario_id', $uid)
                  ->groupBy('animal_id'),
                'ur', 'ur.animal_id', '=', 'a.id'
            )
            ->leftJoin('animal_reproduccion as repr','repr.id','=','ur.ultimo_id')
            // Lactancia activa
            ->leftJoinSub(
                DB::table('animal_lactancia')
                  ->selectRaw('animal_id, 1 as en_produccion')
                  ->where('usuario_id', $uid)
                  ->whereNull('fecha_secado'),
                'lact', 'lact.animal_id', '=', 'a.id'
            )
            ->select(
                'a.id','a.nombre_lote','a.raza','a.fecha_nacimiento',
                'a.categoria_bovina', 'a.etapa_vida',
                'repr.id as repr_id','repr.tipo_servicio','repr.fecha_servicio',
                'repr.resultado_diagnostico','repr.fecha_probable_parto',
                'repr.fecha_parto_real',
                DB::raw('COALESCE(lact.en_produccion, 0) as en_produccion')
            )
            ->orderBy('a.nombre_lote')->get();

        // Toros registrados (categoría toro); si no hay ninguno, muestra
        // todos los bovinos activos como fallback
        $toros = DB::table('animales')
            ->where('usuario_id', $uid)->where('estado','activo')
            ->whereIn('especie', self::ESPECIES_BOVINAS)
            ->where('categoria_bovina', 'toro')
            ->select('id','nombre_lote','raza')
            ->orderBy('nombre_lote')->get();

        // Fallback: si no hay toros registrados, mostrar todos los bovinos
        if ($toros->isEmpty()) {
            $toros = DB::table('animales')
                ->where('usuario_id', $uid)->where('estado','activo')
                ->whereIn('especie', self::ESPECIES_BOVINAS)
                ->select('id','nombre_lote','raza')
                ->orderBy('nombre_lote')->get();
        }

        // Partos de los últimos 90 días
        $partosRecientes = DB::table('animal_reproduccion as r')
            ->join('animales as a','a.id','=','r.animal_id')
            ->where('r.usuario_id', $uid)
            ->whereNotNull('r.fecha_parto_real')
            ->where('r.fecha_parto_real','>=', now()->subDays(90)->toDateString())
            ->select('r.*','a.nombre_lote as vaca')
            ->orderBy('r.fecha_parto_real','desc')->limit(10)->get();

        return view('pages.bovino.reproduccion', compact('hembras','toros','partosRecientes'));
    }

    public function storeServicio(Request $request)
    {
        $request->validate([
            'animal_id'          => 'required|integer',
            'tipo_servicio'      => 'required|in:monta_natural,inseminacion_artificial,monta_controlada',
            'fecha_servicio'     => 'required|date',
            'macho_descripcion'  => 'nullable|string|max:150',
        ]);

        $uid = session('usuario_id');
        $fechaProbable = AnimalReproduccion::calcularFechaParto($request->fecha_servicio);

        DB::table('animal_reproduccion')->insert([
            'animal_id'           => $request->animal_id,
            'usuario_id'          => $uid,
            'tipo_servicio'       => $request->tipo_servicio,
            'fecha_servicio'      => $request->fecha_servicio,
            'macho_descripcion'   => $request->macho_descripcion,
            'resultado_diagnostico' => 'pendiente',
            'fecha_probable_parto'  => $fechaProbable,
            'observaciones'       => $request->observaciones,
            'creado_en'           => now()->toDateTimeString(),
            'actualizado_en'      => now()->toDateTimeString(),
        ]);

        // Tarea de diagnóstico de preñez (35 días después del servicio)
        DB::table('tareas')->insert([
            'usuario_id' => $uid,
            'animal_id'  => $request->animal_id,
            'titulo'     => '[Bovino] Diagnóstico de preñez',
            'tipo'       => 'salud',
            'fecha'      => Carbon::parse($request->fecha_servicio)->addDays(35)->toDateString(),
            'prioridad'  => 'alta',
            'notas'      => 'Palpar o ecografiar para confirmar preñez. Servicio: '.
                            $request->fecha_servicio.' · '.$request->tipo_servicio,
            'creado_en'  => now()->toDateTimeString(),
        ]);

        return redirect()->route('bovino.reproduccion')
            ->with('msg','Servicio registrado. Tarea de diagnóstico generada en Agenda.')
            ->with('msgType','success');
    }

    public function confirmarPrenez(Request $request, int $reprId)
    {
        $request->validate([
            'resultado_diagnostico'    => 'required|in:positivo,negativo',
            'fecha_diagnostico_prenez' => 'required|date',
        ]);

        $uid  = session('usuario_id');
        $repr = DB::table('animal_reproduccion')
            ->where('id',$reprId)->where('usuario_id',$uid)->first();
        if (!$repr) abort(404);

        DB::table('animal_reproduccion')->where('id',$reprId)->update([
            'resultado_diagnostico'    => $request->resultado_diagnostico,
            'fecha_diagnostico_prenez' => $request->fecha_diagnostico_prenez,
            'actualizado_en'           => now()->toDateTimeString(),
        ]);

        if ($request->resultado_diagnostico === 'positivo') {
            // Tarea de parto esperado
            DB::table('tareas')->insert([
                'usuario_id' => $uid,
                'animal_id'  => $repr->animal_id,
                'titulo'     => '[Bovino] Parto esperado — revisión',
                'tipo'       => 'salud',
                'fecha'      => Carbon::parse($repr->fecha_probable_parto)->subDays(10)->toDateString(),
                'prioridad'  => 'alta',
                'notas'      => 'Parto probable: '.Carbon::parse($repr->fecha_probable_parto)->format('d/m/Y').
                                '. Preparar área de parición.',
                'creado_en'  => now()->toDateTimeString(),
            ]);
        }

        $msg = $request->resultado_diagnostico === 'positivo'
            ? '¡Preñez confirmada! Tarea de parto generada en Agenda.'
            : 'Resultado negativo registrado.';

        return redirect()->route('bovino.reproduccion')
            ->with('msg',$msg)->with('msgType','success');
    }

    public function storeParto(Request $request)
    {
        $request->validate([
            'repr_id'           => 'required|integer',
            'fecha_parto_real'  => 'required|date',
            'num_crias_nacidas' => 'required|integer|min:0',
            'num_crias_vivas'   => 'required|integer|min:0',
        ]);

        $uid  = session('usuario_id');
        $repr = DB::table('animal_reproduccion')
            ->where('id', $request->repr_id)->where('usuario_id', $uid)->first();
        if (!$repr) abort(404);

        // Datos de la madre para genealogía
        $madre = DB::table('animales')->where('id', $repr->animal_id)->first();

        // ── Actualizar registro de reproducción ──────────────────────
        DB::table('animal_reproduccion')->where('id', $request->repr_id)->update([
            'fecha_parto_real'  => $request->fecha_parto_real,
            'num_crias_nacidas' => $request->num_crias_nacidas,
            'num_crias_vivas'   => $request->num_crias_vivas,
            'sexo_cria'         => $request->sexo_cria ?: null,
            'peso_cria_kg'      => $request->peso_cria_kg ?: null,
            'observaciones'     => $request->observaciones ?: $repr->observaciones,
            'actualizado_en'    => now()->toDateTimeString(),
        ]);

        // ── Cerrar lactancia anterior y abrir nueva ──────────────────
        DB::table('animal_lactancia')
            ->where('animal_id', $repr->animal_id)->whereNull('fecha_secado')
            ->update(['fecha_secado' => $request->fecha_parto_real,
                      'actualizado_en' => now()->toDateTimeString()]);

        $numLact = DB::table('animal_lactancia')
            ->where('animal_id', $repr->animal_id)->count() + 1;

        DB::table('animal_lactancia')->insert([
            'animal_id'        => $repr->animal_id,
            'usuario_id'       => $uid,
            'parto_id'         => $repr->id,
            'numero_lactancia' => $numLact,
            'fecha_inicio'     => $request->fecha_parto_real,
            'produccion_acumulada_litros' => 0,
            'creado_en'        => now()->toDateTimeString(),
            'actualizado_en'   => now()->toDateTimeString(),
        ]);

        // ── REGISTRO DE CRÍAS EN ANIMALES (genealogía) ───────────────
        $criasRegistradas = [];
        $registrarCria    = (bool) $request->registrar_cria;

        if ($registrarCria && $request->num_crias_vivas > 0) {
            $numCrias = (int) $request->num_crias_vivas;

            for ($i = 1; $i <= $numCrias; $i++) {
                // Si es más de una cría, el nombre lleva sufijo
                $nombreBase  = $request->nombre_cria ?: ('Cría de '.$madre->nombre_lote);
                $nombreCria  = $numCrias > 1 ? $nombreBase.' #'.$i : $nombreBase;

                // Categoría según sexo
                $sexo        = $request->sexo_cria ?? null;
                $categoria   = null;
                if ($sexo === 'hembra') $categoria = 'novilla';
                if ($sexo === 'macho')  $categoria = 'ternero';

                $criaId = DB::table('animales')->insertGetId([
                    'usuario_id'        => $uid,
                    'especie'           => $madre->especie ?? 'Ganado bovino',
                    'nombre_lote'       => $nombreCria,
                    'cantidad'          => 1,
                    'fecha_ingreso'     => $request->fecha_parto_real,
                    'fecha_nacimiento'  => $request->fecha_parto_real,
                    'estado'            => 'activo',
                    'peso_promedio'     => $request->peso_cria_kg ?: null,
                    'unidad_peso'       => 'kg',
                    'etapa_vida'        => 'cria',
                    'raza'              => $madre->raza ?? null,
                    'categoria_bovina'  => $categoria,
                    // Genealogía
                    'madre_id'          => $repr->animal_id,
                    'padre_descripcion' => $repr->macho_descripcion ?? null,
                    'notas'             => 'Nacida del parto #'.$repr->id.
                                          ' de '.$madre->nombre_lote.
                                          ($repr->macho_descripcion
                                              ? ' × '.$repr->macho_descripcion : ''),
                    'creado_en'         => now()->toDateTimeString(),
                    'actualizado_en'    => now()->toDateTimeString(),
                ]);

                $criasRegistradas[] = $criaId;
            }
        }

        // ── Evento en la línea de tiempo de la madre ──────────────────
        $descEvento = 'Parto #'.$repr->id.' — '.$request->num_crias_vivas.
                      '/'.$request->num_crias_nacidas.' cría(s) viva(s).';
        if (!empty($criasRegistradas)) {
            $descEvento .= ' '.count($criasRegistradas).' cría(s) registrada(s) en Animales.';
        }

        DB::table('animal_eventos')->insert([
            'animal_id'      => $repr->animal_id,
            'usuario_id'     => $uid,
            'tipo'           => 'nota',
            'titulo'         => '🍼 Parto — '.$request->num_crias_vivas.' cría(s) viva(s)',
            'descripcion'    => $descEvento,
            'fecha'          => $request->fecha_parto_real,
            'creado_en'      => now()->toDateTimeString(),
            'actualizado_en' => now()->toDateTimeString(),
        ]);

        // ── Mensaje de éxito ──────────────────────────────────────────
        $msg = 'Parto registrado. Lactancia #'.$numLact.' iniciada.';
        if (!empty($criasRegistradas)) {
            $msg .= ' '.count($criasRegistradas).' cría(s) agregada(s) al módulo de Animales con genealogía.';
        }

        return redirect()->route('bovino.reproduccion')
            ->with('msg', $msg)->with('msgType','success');
    }

    // ─────────────────────────────────────────────────────────────────
    // SANIDAD — Calendario sanitario programado
    // ─────────────────────────────────────────────────────────────────

    public function sanidad()
    {
        $uid = session('usuario_id');

        // Si no tiene protocolos aún, inicializar los estándar
        $count = DB::table('animal_sanidad_programada')
            ->where('usuario_id', $uid)->count();
        if ($count === 0) {
            $this->inicializarProtocolos($uid);
        }

        $protocolos = DB::table('animal_sanidad_programada')
            ->where('usuario_id', $uid)
            ->orderBy('proxima_aplicacion')
            ->get();

        // Clasificar por urgencia
        $vencidos  = $protocolos->filter(fn($p) => $p->proxima_aplicacion && $p->proxima_aplicacion < now()->toDateString());
        $proximos  = $protocolos->filter(fn($p) => $p->proxima_aplicacion && $p->proxima_aplicacion >= now()->toDateString() && $p->proxima_aplicacion <= now()->addDays(30)->toDateString());
        $futuros   = $protocolos->filter(fn($p) => !$p->proxima_aplicacion || $p->proxima_aplicacion > now()->addDays(30)->toDateString());

        // Historial: últimas 10 aplicaciones (tareas completadas de sanidad bovina)
        $historial = DB::table('tareas')
            ->where('usuario_id', $uid)
            ->where('tipo', 'salud')
            ->where('completada', 1)
            ->where('titulo', 'like', '[Bovino Sanidad]%')
            ->orderBy('fecha_completada','desc')
            ->limit(10)->get();

        return view('pages.bovino.sanidad', compact('protocolos','vencidos','proximos','futuros','historial'));
    }

    public function aplicarSanidad(Request $request, int $id)
    {
        $request->validate([
            'fecha_aplicacion' => 'required|date',
        ]);

        $uid      = session('usuario_id');
        $protocolo = DB::table('animal_sanidad_programada')
            ->where('id',$id)->where('usuario_id',$uid)->first();
        if (!$protocolo) abort(404);

        $proxima = Carbon::parse($request->fecha_aplicacion)
            ->addDays($protocolo->frecuencia_dias)->toDateString();

        DB::table('animal_sanidad_programada')->where('id',$id)->update([
            'ultima_aplicacion'  => $request->fecha_aplicacion,
            'proxima_aplicacion' => $proxima,
            'producto_usado'     => $request->producto_usado ?: $protocolo->producto_usado,
            'dosis'              => $request->dosis ?: $protocolo->dosis,
            'observaciones'      => $request->observaciones ?: $protocolo->observaciones,
            'actualizado_en'     => now()->toDateTimeString(),
        ]);

        // Generar tarea para la próxima aplicación
        DB::table('tareas')->insert([
            'usuario_id' => $uid,
            'titulo'     => '[Bovino Sanidad] '.$protocolo->nombre_protocolo,
            'tipo'       => 'salud',
            'fecha'      => $proxima,
            'prioridad'  => 'alta',
            'notas'      => 'Protocolo bovino. Última aplicación: '.$request->fecha_aplicacion.
                            ($request->producto_usado ? ' · Producto: '.$request->producto_usado : ''),
            'creado_en'  => now()->toDateTimeString(),
        ]);

        return redirect()->route('bovino.sanidad')
            ->with('msg', $protocolo->nombre_protocolo.' registrado. Próxima: '.
                   Carbon::parse($proxima)->format('d/m/Y').'. Tarea generada en Agenda.')
            ->with('msgType','success');
    }

    public function storeSanidadPersonalizado(Request $request)
    {
        $request->validate([
            'nombre_protocolo' => 'required|string|max:120',
            'frecuencia_dias'  => 'required|integer|min:1',
        ]);

        $uid = session('usuario_id');
        DB::table('animal_sanidad_programada')->insert([
            'usuario_id'         => $uid,
            'protocolo'          => 'personalizado_'.time(),
            'nombre_protocolo'   => $request->nombre_protocolo,
            'especie_aplicacion' => 'Ganado bovino',
            'frecuencia_dias'    => $request->frecuencia_dias,
            'via_administracion' => $request->via_administracion ?: 'intramuscular',
            'activo'             => 1,
            'creado_en'          => now()->toDateTimeString(),
            'actualizado_en'     => now()->toDateTimeString(),
        ]);

        return redirect()->route('bovino.sanidad')
            ->with('msg','Protocolo personalizado agregado.')->with('msgType','success');
    }

    // ─────────────────────────────────────────────────────────────────
    // PESAJE — Seguimiento de peso con GPD
    // ─────────────────────────────────────────────────────────────────

    public function pesaje(Request $request)
    {
        $uid = session('usuario_id');

        $bovinos = DB::table('animales as a')
            ->where('a.usuario_id', $uid)
            ->where('a.estado', 'activo')
            ->whereIn('a.especie', self::ESPECIES_BOVINAS)
            ->orderBy('a.nombre_lote')->get();

        // Para cada bovino, calcular GPD con los últimos 2 registros de peso
        $pesajeData = [];
        foreach ($bovinos as $b) {
            $pesos = DB::table('animal_pesos')
                ->where('animal_id', $b->id)
                ->orderBy('fecha','desc')->limit(2)->get();

            $gpd    = null;
            $pesosArr = $pesos->toArray();

            if (count($pesosArr) >= 2) {
                $dias  = Carbon::parse($pesosArr[1]->fecha)->diffInDays($pesosArr[0]->fecha);
                $gpd   = $dias > 0
                    ? round(($pesosArr[0]->peso - $pesosArr[1]->peso) / $dias, 3)
                    : null;
            }

            $pesajeData[$b->id] = [
                'bovino'     => $b,
                'pesos'      => $pesos,
                'peso_actual'=> $pesosArr[0]->peso ?? null,
                'gpd'        => $gpd,
                'meta_kg'    => $b->peso_meta_kg ?? null,
            ];
        }

        return view('pages.bovino.pesaje', compact('bovinos','pesajeData'));
    }

    public function storePeso(Request $request)
    {
        $request->validate([
            'animal_id' => 'required|integer',
            'peso'      => 'required|numeric|min:0',
            'fecha'     => 'required|date',
        ]);

        $uid = session('usuario_id');
        DB::table('animal_pesos')->insert([
            'animal_id'  => $request->animal_id,
            'usuario_id' => $uid,
            'peso'       => $request->peso,
            'unidad'     => 'kg',
            'fecha'      => $request->fecha,
            'notas'      => $request->notas ?: null,
            'creado_en'  => now()->toDateTimeString(),
            'actualizado_en' => now()->toDateTimeString(),
        ]);

        // Actualizar peso_promedio en animales si corresponde
        DB::table('animales')->where('id',$request->animal_id)
            ->update(['peso_promedio' => $request->peso, 'actualizado_en' => now()->toDateTimeString()]);

        // Actualizar meta de peso si se envió
        if ($request->peso_meta_kg) {
            DB::table('animales')->where('id',$request->animal_id)
                ->update(['peso_meta_kg' => $request->peso_meta_kg]);
        }

        return redirect()->route('bovino.pesaje')
            ->with('msg','Peso registrado.')->with('msgType','success');
    }

    // ─────────────────────────────────────────────────────────────────
    // REPORTES
    // ─────────────────────────────────────────────────────────────────

    public function reportes()
    {
        $uid = session('usuario_id');

        // ── 1. Vacas en producción vs secas ──────────────────────────
        $vacasConLactancia = DB::table('animales as a')
            ->join('animal_lactancia as l','l.animal_id','=','a.id')
            ->where('a.usuario_id',$uid)->where('a.estado','activo')
            ->whereIn('a.especie', self::ESPECIES_BOVINAS)
            ->whereNull('l.fecha_secado')
            ->select('a.id','a.nombre_lote','a.raza',
                     'l.fecha_inicio','l.produccion_acumulada_litros','l.numero_lactancia')
            ->orderBy('a.nombre_lote')->get();

        $vacasSecas = DB::table('animales as a')
            ->where('a.usuario_id',$uid)->where('a.estado','activo')
            ->whereIn('a.especie', self::ESPECIES_BOVINAS)
            ->whereNotExists(function($q) {
                $q->from('animal_lactancia as l')
                  ->whereRaw('l.animal_id = a.id')
                  ->whereNull('l.fecha_secado');
            })->select('a.id','a.nombre_lote','a.raza')->orderBy('a.nombre_lote')->get();

        // ── 2. Promedio L/vaca/día (últimos 30 días) ─────────────────
        $promedioLitros = 0;
        $totalVacasDias = 0;
        foreach ($vacasConLactancia as $v) {
            $sum = DB::table('animal_ordenos')
                ->where('animal_id',$v->id)
                ->where('fecha','>=',now()->subDays(30)->toDateString())
                ->sum('litros');
            $dias = DB::table('animal_ordenos')
                ->where('animal_id',$v->id)
                ->where('fecha','>=',now()->subDays(30)->toDateString())
                ->selectRaw('COUNT(DISTINCT fecha) as d')->value('d');
            $promedioLitros += $dias > 0 ? $sum/$dias : 0;
            if ($dias > 0) $totalVacasDias++;
        }
        $promedioHato = $totalVacasDias > 0 ? round($promedioLitros/$totalVacasDias,1) : 0;

        // ── 3. Producción por vaca (últimos 30 días) ─────────────────
        $produccionPorVaca = [];
        foreach ($vacasConLactancia as $v) {
            $litros = DB::table('animal_ordenos')
                ->where('animal_id',$v->id)
                ->where('fecha','>=',now()->subDays(30)->toDateString())
                ->selectRaw('fecha, SUM(litros) as total')
                ->groupBy('fecha')->orderBy('fecha')->get();
            $produccionPorVaca[] = [
                'id'       => $v->id,
                'nombre'   => $v->nombre_lote,
                'raza'     => $v->raza,
                'promedio' => $litros->count() > 0 ? round($litros->avg('total'),1) : 0,
                'total30d' => round($litros->sum('total'),1),
                'dias'     => $litros->count(),
            ];
        }
        usort($produccionPorVaca, fn($a,$b) => $b['promedio'] <=> $a['promedio']);

        // ── 4. Intervalo entre partos y días abiertos ─────────────────
        $intervalosPartos = [];
        $diasAbiertos     = [];
        $bovinos = DB::table('animales')
            ->where('usuario_id',$uid)->where('estado','activo')
            ->whereIn('especie', self::ESPECIES_BOVINAS)->get();

        foreach ($bovinos as $b) {
            $partos = DB::table('animal_reproduccion')
                ->where('animal_id',$b->id)
                ->whereNotNull('fecha_parto_real')
                ->orderBy('fecha_parto_real')->pluck('fecha_parto_real')->toArray();

            // Intervalos entre partos consecutivos
            for ($i = 1; $i < count($partos); $i++) {
                $intervalosPartos[] = Carbon::parse($partos[$i-1])->diffInDays($partos[$i]);
            }

            // Días abiertos: días desde último parto hasta próximo servicio
            if (count($partos) > 0) {
                $ultimoParto = end($partos);
                $proximoServicio = DB::table('animal_reproduccion')
                    ->where('animal_id',$b->id)
                    ->where('fecha_servicio','>',$ultimoParto)
                    ->orderBy('fecha_servicio')->value('fecha_servicio');
                $diasAb = $proximoServicio
                    ? Carbon::parse($ultimoParto)->diffInDays($proximoServicio)
                    : Carbon::parse($ultimoParto)->diffInDays(now());

                $diasAbiertos[] = [
                    'nombre'      => $b->nombre_lote,
                    'ultimo_parto'=> $ultimoParto,
                    'dias'        => $diasAb,
                    'estado'      => $diasAb <= 85 ? 'ok' : ($diasAb <= 120 ? 'alerta' : 'critico'),
                ];
            }
        }

        $ipPromedio = count($intervalosPartos) > 0
            ? round(array_sum($intervalosPartos)/count($intervalosPartos)) : null;
        $daPromedio = count($diasAbiertos) > 0
            ? round(array_sum(array_column($diasAbiertos,'dias'))/count($diasAbiertos)) : null;

        // ── 5. GPD por animal ─────────────────────────────────────────
        $gpdPorAnimal = [];
        foreach ($bovinos as $b) {
            $pesos = DB::table('animal_pesos')
                ->where('animal_id',$b->id)->orderBy('fecha','desc')->limit(5)->get();
            if ($pesos->count() >= 2) {
                $all = $pesos->toArray();
                $gpd = round(($all[0]->peso - $all[count($all)-1]->peso) /
                       max(1, Carbon::parse($all[count($all)-1]->fecha)->diffInDays($all[0]->fecha)), 3);
                $gpdPorAnimal[] = [
                    'nombre'     => $b->nombre_lote,
                    'gpd'        => $gpd,
                    'peso_actual'=> $all[0]->peso,
                    'meta_kg'    => $b->peso_meta_kg,
                    'estado'     => $gpd >= 0.5 ? 'bueno' : ($gpd >= 0.2 ? 'regular' : 'bajo'),
                ];
            }
        }
        usort($gpdPorAnimal, fn($a,$b) => $b['gpd'] <=> $a['gpd']);

        return view('pages.bovino.reportes', compact(
            'vacasConLactancia','vacasSecas',
            'promedioHato','produccionPorVaca',
            'ipPromedio','diasAbiertos','daPromedio',
            'gpdPorAnimal'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    // LACTANCIA — Iniciar y secar
    // ─────────────────────────────────────────────────────────────────

    public function storeLactancia(Request $request)
    {
        $request->validate([
            'animal_id'    => 'required|integer',
            'fecha_inicio' => 'required|date',
        ]);
        $uid = session('usuario_id');

        // Cerrar lactancias anteriores abiertas
        DB::table('animal_lactancia')
            ->where('animal_id',$request->animal_id)->whereNull('fecha_secado')
            ->update(['fecha_secado'=>$request->fecha_inicio,'actualizado_en'=>now()->toDateTimeString()]);

        $num = DB::table('animal_lactancia')->where('animal_id',$request->animal_id)->count() + 1;

        DB::table('animal_lactancia')->insert([
            'animal_id'       => $request->animal_id,
            'usuario_id'      => $uid,
            'numero_lactancia'=> $num,
            'fecha_inicio'    => $request->fecha_inicio,
            'produccion_acumulada_litros' => 0,
            'observaciones'   => $request->observaciones,
            'creado_en'       => now()->toDateTimeString(),
            'actualizado_en'  => now()->toDateTimeString(),
        ]);

        return redirect()->route('bovino.ordenos')
            ->with('msg','Lactancia #'.$num.' iniciada. La vaca aparece como productora.')
            ->with('msgType','success');
    }

    public function secarVaca(Request $request, int $lactanciaId)
    {
        $request->validate(['fecha_secado'=>'required|date']);
        $uid = session('usuario_id');

        DB::table('animal_lactancia')
            ->where('id',$lactanciaId)->where('usuario_id',$uid)
            ->update(['fecha_secado'=>$request->fecha_secado,'actualizado_en'=>now()->toDateTimeString()]);

        return redirect()->route('bovino.ordenos')
            ->with('msg','Vaca marcada como seca.')->with('msgType','success');
    }

    // ─────────────────────────────────────────────────────────────────
    // VENTA DE PRODUCCIÓN DE LECHE
    // ─────────────────────────────────────────────────────────────────

    public function venderProduccion(Request $request)
    {
        $request->validate([
            'fecha'          => 'required|date',
            'litros'         => 'required|numeric|min:0.01',
            'precio_litro'   => 'required|numeric|min:0',
            'tipo_venta'     => 'required|in:diaria,mensual',
            'comprador'      => 'nullable|string|max:150',
            'persona_id'     => 'nullable|integer',
        ]);

        $uid    = session('usuario_id');
        $total  = round($request->litros * $request->precio_litro);
        $tipo   = $request->tipo_venta === 'mensual' ? 'mensual' : 'diaria';
        $comprador = $request->comprador ?: ($request->persona_id
            ? DB::table('personas')->where('id',$request->persona_id)->value('nombre')
            : 'Venta de leche');

        $desc = "Venta de leche {$tipo} — {$request->litros} L × \${$request->precio_litro}/L"
              . ($comprador ? " — {$comprador}" : '');

        // Crear ingreso
        DB::table('ingresos')->insert([
            'usuario_id'  => $uid,
            'descripcion' => $desc,
            'valor'       => $total,
            'categoria'   => 'leche',
            'fecha'       => $request->fecha,
            'persona_id'  => $request->persona_id ?: null,
            'notas'       => "Litros: {$request->litros} · Precio/L: {$request->precio_litro}",
            'creado_en'   => now()->toDateTimeString(),
            'actualizado_en' => now()->toDateTimeString(),
        ]);

        return redirect()->route('bovino.ordenos', ['fecha' => $request->fecha])
            ->with('msg', "Venta registrada: {$request->litros} L = \$".number_format($total,0,'.',',').". Ingreso creado.")
            ->with('msgType','success');
    }

    // ─────────────────────────────────────────────────────────────────
    // PRIVADOS
    // ─────────────────────────────────────────────────────────────────

    private function inicializarProtocolos(int $uid): void
    {
        foreach (self::PROTOCOLOS as $p) {
            DB::table('animal_sanidad_programada')->insert([
                'usuario_id'         => $uid,
                'protocolo'          => $p['protocolo'],
                'nombre_protocolo'   => $p['nombre'],
                'especie_aplicacion' => 'Ganado bovino',
                'frecuencia_dias'    => $p['frecuencia_dias'],
                'via_administracion' => $p['via'],
                'activo'             => 1,
                'creado_en'          => now()->toDateTimeString(),
                'actualizado_en'     => now()->toDateTimeString(),
            ]);
        }
    }
}