<?php

namespace App\Http\Controllers;

use App\Models\Cultivo;
use App\Models\CultivoFase;
use App\Models\CultivoEventoAvanzado;
use App\Models\PlanManejoCultivo;
use App\Models\Cosecha;
use App\Traits\ManejadorImagenes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CultivoFaseController extends Controller
{
    use ManejadorImagenes;

    // ─────────────────────────────────────────────────────────
    // CAMBIO DE FASE
    // ─────────────────────────────────────────────────────────

    /**
     * POST /cultivos/{id}/fase
     * Cambia la fase actual del cultivo y genera actividades sugeridas en tareas.
     */
    public function cambiarFase(Request $request, int $id)
    {
        $request->validate([
            'fase_id'       => 'required|exists:cultivo_fases,id',
            'fecha_cambio'  => 'required|date',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $uid     = session('usuario_id');
        $cultivo = Cultivo::where('id', $id)->where('usuario_id', $uid)->firstOrFail();
        $fase    = CultivoFase::findOrFail($request->fase_id);

        // Cerrar historial anterior
        DB::table('cultivo_historial_fases')
            ->where('cultivo_id', $id)
            ->whereNull('fecha_fin')
            ->update(['fecha_fin' => $request->fecha_cambio]);

        // Nuevo registro en historial
        DB::table('cultivo_historial_fases')->insert([
            'cultivo_id'    => $id,
            'usuario_id'    => $uid,
            'fase_id'       => $fase->id,
            'fecha_inicio'  => $request->fecha_cambio,
            'fecha_fin'     => null,
            'observaciones' => $request->observaciones,
            'creado_en'     => now()->toDateTimeString(),
        ]);

        // Actualizar cultivo
        $cultivo->update([
            'fase_actual_id'   => $fase->id,
            'fecha_cambio_fase'=> $request->fecha_cambio,
        ]);

        // Registrar en timeline clásico (cultivo_eventos)
        DB::table('cultivo_eventos')->insert([
            'cultivo_id'  => $id,
            'usuario_id'  => $uid,
            'tipo'        => 'cambio_estado',
            'titulo'      => $fase->icono . ' Fase: ' . $fase->nombre,
            'descripcion' => $request->observaciones ?? ('Cultivo avanzó a fase ' . $fase->nombre),
            'fecha'       => $request->fecha_cambio,
            'creado_en'   => now()->toDateTimeString(),
        ]);

        // Generar tareas sugeridas del plan de manejo
        $actividades = PlanManejoCultivo::where('cultivo_fase_id', $fase->id)->get();
        foreach ($actividades as $act) {
            DB::table('tareas')->insert([
                'usuario_id'   => $uid,
                'cultivo_id'   => $id,
                'titulo'       => '[Sugerido] ' . $act->actividad,
                'tipo'         => $this->mapearTipoTarea($act->tipo_actividad),
                'fecha'        => Carbon::parse($request->fecha_cambio)->addDays(2)->toDateString(),
                'completada'   => false,
                'prioridad'    => $act->obligatoria ? 'alta' : 'media',
                'notas'        => $act->descripcion . ($act->producto_sugerido ? "\nProducto sugerido: {$act->producto_sugerido} — Dosis: {$act->dosis_sugerida}" : ''),
                'creado_en'    => now()->toDateTimeString(),
            ]);
        }

        $msg = "Fase cambiada a «{$fase->nombre}»";
        if ($actividades->count()) {
            $msg .= " · {$actividades->count()} actividades sugeridas generadas en Agenda.";
        }

        return redirect()->route('cultivos.show', $id)
            ->with('msg', $msg)
            ->with('msgType', 'success');
    }

    // ─────────────────────────────────────────────────────────
    // EVENTOS AVANZADOS
    // ─────────────────────────────────────────────────────────

    /**
     * POST /cultivos/{id}/eventos-avanzados
     * Registra un evento avanzado (agroquímico, riego, fertilización, etc.)
     */
    public function storeEventoAvanzado(Request $request, int $id)
    {
        $request->validate([
            'tipo'   => 'required|in:aplicacion_agroquimico,riego,fertilizacion,control_fitosanitario,deshierbe,otro',
            'titulo' => 'required|string|max:200',
            'fecha'  => 'required|date',
        ]);

        $uid     = session('usuario_id');
        $cultivo = Cultivo::where('id', $id)->where('usuario_id', $uid)->firstOrFail();

        $fotoRuta = null;
        if ($request->hasFile('foto')) {
            $fotoRuta = $this->guardarImagen($request->file('foto'), 'cultivos/eventos');
        }

        $data = [
            'cultivo_id'  => $id,
            'usuario_id'  => $uid,
            'fase_id'     => $request->fase_id ?: $cultivo->fase_actual_id,
            'tipo'        => $request->tipo,
            'titulo'      => $request->titulo,
            'descripcion' => $request->descripcion,
            'fecha'       => $request->fecha,
            'foto_ruta'   => $fotoRuta,
            'persona_id'  => $request->persona_id ?: null,
        ];

        // Campos específicos por tipo
        switch ($request->tipo) {
            case 'aplicacion_agroquimico':
                $request->validate([
                    'producto_nombre'       => 'required|string|max:150',
                    'dosis'                 => 'nullable|numeric|min:0',
                    'periodo_carencia_dias' => 'nullable|integer|min:0',
                ]);
                $fechaMinCosecha = null;
                $alertaActiva    = false;
                if ($request->periodo_carencia_dias) {
                    $fechaMinCosecha = CultivoEventoAvanzado::calcularFechaMinimaCosecha(
                        $request->fecha, (int)$request->periodo_carencia_dias
                    );
                    // Verificar si hay cosecha programada antes
                    $cosechaConflicto = Cosecha::where('cultivo_id', $id)
                        ->where('fecha_cosecha', '>=', $request->fecha)
                        ->where('fecha_cosecha', '<', $fechaMinCosecha)
                        ->first();
                    $alertaActiva = (bool) $cosechaConflicto;
                }
                $data += [
                    'producto_nombre'          => $request->producto_nombre,
                    'producto_registro_ica'    => $request->producto_registro_ica,
                    'dosis'                    => $request->dosis,
                    'dosis_unidad'             => $request->dosis_unidad,
                    'periodo_carencia_dias'    => $request->periodo_carencia_dias,
                    'fecha_minima_cosecha'     => $fechaMinCosecha,
                    'alerta_cosecha_activa'    => $alertaActiva,
                ];
                break;

            case 'riego':
                $data += [
                    'volumen_agua_litros' => $request->volumen_agua_litros,
                    'metodo_riego'        => $request->metodo_riego,
                    'duracion_minutos'    => $request->duracion_minutos,
                ];
                break;

            case 'fertilizacion':
                $data += [
                    'nitrogeno_n'                    => $request->nitrogeno_n,
                    'fosforo_p'                      => $request->fosforo_p,
                    'potasio_k'                      => $request->potasio_k,
                    'fuente_fertilizante'            => $request->fuente_fertilizante,
                    'metodo_aplicacion_fertilizante' => $request->metodo_aplicacion_fertilizante,
                    'producto_nombre'                => $request->producto_nombre,
                    'dosis'                          => $request->dosis,
                    'dosis_unidad'                   => $request->dosis_unidad,
                ];
                break;

            case 'control_fitosanitario':
                $data += [
                    'plaga_enfermedad' => $request->plaga_enfermedad,
                    'tipo_control'     => $request->tipo_control,
                    'nivel_severidad'  => $request->nivel_severidad,
                    'producto_nombre'  => $request->producto_nombre,
                    'dosis'            => $request->dosis,
                    'dosis_unidad'     => $request->dosis_unidad,
                ];
                break;

            case 'deshierbe':
                $data += [
                    'metodo_deshierbe'     => $request->metodo_deshierbe,
                    'area_deshierbada_ha'  => $request->area_deshierbada_ha,
                ];
                break;
        }

        $evento = CultivoEventoAvanzado::create($data);

        // Espejo en timeline clásico (cultivo_eventos) para que aparezca en detalle
        $iconos = [
            'aplicacion_agroquimico' => '🧪',
            'riego'                  => '💧',
            'fertilizacion'          => '🌿',
            'control_fitosanitario'  => '🐛',
            'deshierbe'              => '🪚',
            'otro'                   => '📝',
        ];
        DB::table('cultivo_eventos')->insert([
            'cultivo_id'    => $id,
            'usuario_id'    => $uid,
            'tipo'          => 'aplicacion',
            'titulo'        => ($iconos[$request->tipo] ?? '📝') . ' ' . $request->titulo,
            'descripcion'   => $request->descripcion,
            'foto_ruta'     => $fotoRuta,
            'fecha'         => $request->fecha,
            'referencia_id' => $evento->id,
            'creado_en'     => now()->toDateTimeString(),
        ]);

        $msg = 'Evento registrado.';
        if (isset($data['alerta_cosecha_activa']) && $data['alerta_cosecha_activa']) {
            $msg .= ' ⚠️ Hay una cosecha programada antes del período de carencia ('
                   . Carbon::parse($data['fecha_minima_cosecha'])->format('d/m/Y') . ').';
        }

        return redirect()->route('cultivos.show', $id)
            ->with('msg', $msg)
            ->with('msgType', isset($data['alerta_cosecha_activa']) && $data['alerta_cosecha_activa'] ? 'warning' : 'success');
    }

    /**
     * POST /cultivos/{cultivoId}/eventos-avanzados/{eventoId}/delete
     */
    public function destroyEventoAvanzado(int $cultivoId, int $eventoId)
    {
        $uid    = session('usuario_id');
        $evento = CultivoEventoAvanzado::where('id', $eventoId)
            ->where('cultivo_id', $cultivoId)
            ->where('usuario_id', $uid)
            ->firstOrFail();

        if ($evento->foto_ruta) {
            $this->eliminarImagen($evento->foto_ruta);
        }

        // Borrar espejo en timeline clásico
        DB::table('cultivo_eventos')
            ->where('referencia_id', $eventoId)
            ->where('tipo', 'aplicacion')
            ->where('cultivo_id', $cultivoId)
            ->delete();

        $evento->delete();

        return redirect()->route('cultivos.show', $cultivoId)
            ->with('msg', 'Evento eliminado.')->with('msgType', 'warning');
    }

    // ─────────────────────────────────────────────────────────
    // CALENDARIO FENOLÓGICO (vista dedicada)
    // ─────────────────────────────────────────────────────────

    /**
     * GET /cultivos/{id}/fenologia
     */
    public function fenologia(int $id)
    {
        $uid     = session('usuario_id');
        $cultivo = Cultivo::where('id', $id)->where('usuario_id', $uid)->firstOrFail();

        $fases           = $cultivo->fasesConProgreso();
        $faseActual      = $cultivo->faseActual;
        $eventosAvanzados= CultivoEventoAvanzado::where('cultivo_id', $id)
                            ->where('usuario_id', $uid)
                            ->with('fase')
                            ->orderBy('fecha', 'desc')
                            ->get();

        // Historial de fases con JOIN a nombre
        $historialFases = DB::table('cultivo_historial_fases as h')
            ->join('cultivo_fases as f', 'f.id', '=', 'h.fase_id')
            ->where('h.cultivo_id', $id)
            ->select('h.*', 'f.nombre as fase_nombre', 'f.color_hex', 'f.icono', 'f.orden')
            ->orderBy('h.fecha_inicio', 'asc')
            ->get();

        // Alertas de carencia activas
        $alertasCarencia = CultivoEventoAvanzado::where('cultivo_id', $id)
            ->where('alerta_cosecha_activa', true)
            ->where('fecha_minima_cosecha', '>=', now()->toDateString())
            ->get();

        // Plan de manejo de la fase actual
        $planFaseActual = $faseActual
            ? PlanManejoCultivo::where('cultivo_fase_id', $faseActual->id)->get()
            : collect();

        // Rendimiento
        $userRow = DB::table('usuarios')->find($uid);
        $deptoUsuario    = $userRow->departamento ?? 'Córdoba';
        $comparativa     = $cultivo->comparativaRendimiento($deptoUsuario);
        $rendRegional    = DB::table('rendimiento_regional')
            ->where('tipo_cultivo', $cultivo->tipo)
            ->orderBy('anio', 'desc')
            ->get()
            ->groupBy('departamento');

        // Personas para asignar eventos
        $personas = \App\Models\Persona::delUsuario($uid)->activos()->orderBy('nombre')->get();

        $porcentaje = $cultivo->porcentajeFenologico();

        // Resumen de eventos por tipo para el gráfico
        $resumenEventos = $eventosAvanzados->groupBy('tipo')
            ->map(fn($g) => $g->count());

        return view('pages.cultivo-fenologia', compact(
            'cultivo', 'fases', 'faseActual', 'eventosAvanzados',
            'historialFases', 'alertasCarencia', 'planFaseActual',
            'comparativa', 'rendRegional', 'personas', 'porcentaje',
            'resumenEventos'
        ));
    }

    // ─────────────────────────────────────────────────────────
    // RENDIMIENTO ESPERADO
    // ─────────────────────────────────────────────────────────

    /**
     * POST /cultivos/{id}/rendimiento
     */
    public function actualizarRendimiento(Request $request, int $id)
    {
        $request->validate([
            'rendimiento_esperado_ha' => 'nullable|numeric|min:0',
        ]);

        $uid     = session('usuario_id');
        $cultivo = Cultivo::where('id', $id)->where('usuario_id', $uid)->firstOrFail();

        $cultivo->update(['rendimiento_esperado_ha' => $request->rendimiento_esperado_ha]);
        $cultivo->recalcularRendimientoReal();

        return redirect()->route('cultivos.fenologia', $id)
            ->with('msg', 'Rendimiento actualizado.')->with('msgType', 'success');
    }

    // ─────────────────────────────────────────────────────────
    // API JSON — para el timeline visual en frontend
    // ─────────────────────────────────────────────────────────

    /**
     * GET /cultivos/{id}/fenologia/data  (retorna JSON)
     */
    public function fenologiaData(int $id)
    {
        $uid     = session('usuario_id');
        $cultivo = Cultivo::where('id', $id)->where('usuario_id', $uid)->firstOrFail();

        $fases = $cultivo->fasesConProgreso();

        $eventos = CultivoEventoAvanzado::where('cultivo_id', $id)
            ->where('usuario_id', $uid)
            ->orderBy('fecha', 'asc')
            ->get()
            ->map(fn($e) => [
                'id'        => $e->id,
                'tipo'      => $e->tipo,
                'titulo'    => $e->titulo,
                'fecha'     => $e->fecha->format('Y-m-d'),
                'fase_id'   => $e->fase_id,
                'alerta'    => $e->alerta_cosecha_activa,
                'fecha_carencia' => $e->fecha_minima_cosecha?->format('Y-m-d'),
            ]);

        return response()->json([
            'fases'   => $fases,
            'eventos' => $eventos,
            'porcentaje' => $cultivo->porcentajeFenologico(),
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // PRIVADOS
    // ─────────────────────────────────────────────────────────

    private function mapearTipoTarea(string $tipoActividad): string
    {
        return match($tipoActividad) {
            'riego'                 => 'riego',
            'fertilizacion'        => 'fertilizacion',
            'aplicacion_agroquimico','control_fitosanitario' => 'fumigacion',
            'poda'                  => 'poda',
            default                 => 'otro',
        };
    }
}