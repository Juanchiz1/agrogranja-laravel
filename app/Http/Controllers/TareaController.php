<?php

namespace App\Http\Controllers;

use App\Http\Requests\TareaRequest;
use App\Models\Tarea;
use App\Models\Cultivo;
use App\Models\Animal;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TareaController extends Controller
{
    private function tipos(): array
    {
        return [
            '🌱 Cultivos' => ['riego'=>'💧 Riego','fertilizacion'=>'🌿 Fertilización','fumigacion'=>'🧴 Fumigación','poda'=>'✂️ Poda','siembra'=>'🌱 Siembra','cosecha'=>'🌾 Cosecha'],
            '🐄 Animales' => ['vacunacion'=>'💉 Vacunación','medicamento'=>'💊 Medicamento','traslado_animal'=>'🏃 Traslado animal','parto'=>'🐣 Parto / Nacimiento','destete'=>'🍼 Destete','visita_veterinario'=>'🩺 Visita veterinario'],
            '💰 Finanzas' => ['pago_proveedor'=>'🏪 Pago a proveedor','cobro_venta'=>'💰 Cobro de venta','revision_gastos'=>'📊 Revisión de gastos'],
            '🔧 Finca'    => ['mantenimiento'=>'🔧 Mantenimiento','compra_insumo'=>'📦 Compra de insumos','revision_inventario'=>'📋 Revisión inventario','limpieza'=>'🧹 Limpieza'],
            '📋 General'  => ['reunion'=>'🤝 Reunión','capacitacion'=>'📚 Capacitación','otro'=>'📝 Otro'],
        ];
    }

    private function tiposPlanos(): array
    {
        $r = [];
        foreach ($this->tipos() as $items) {
            foreach ($items as $k => $v) $r[$k] = $v;
        }
        return $r;
    }

    /**
     * Muestra el calendario de tareas con tabs por estado y mini-calendario.
     */
    public function index(Request $request)
    {
        $uid = session('usuario_id');
        $tab = $request->tab ?? 'hoy';
        $mes = $request->mes ?? now()->format('Y-m');
        $hoy = now()->toDateString();

        // Mantiene JOINs en DB::table para traer cultivo_nombre y animal_nombre
        // junto a cada tarea en una sola colección
        $base = DB::table('tareas as t')
            ->leftJoin('cultivos as c', 'c.id', '=', 't.cultivo_id')
            ->leftJoin('animales as a', 'a.id', '=', 't.animal_id')
            ->where('t.usuario_id', $uid)
            ->select('t.*', 'c.nombre as cultivo_nombre', 'a.nombre_lote as animal_nombre', 'a.especie as animal_especie');

        if ($request->tipo)                    $base->where('t.tipo', $request->tipo);
        if ($request->prioridad)               $base->where('t.prioridad', $request->prioridad);
        if ($request->asociado === 'cultivo')  $base->whereNotNull('t.cultivo_id');
        if ($request->asociado === 'animal')   $base->whereNotNull('t.animal_id');

        $tareas = (clone $base)
            ->when($tab === 'hoy',        fn($q) => $q->where('t.completada', 0)->whereDate('t.fecha', $hoy)->orderByRaw("FIELD(t.prioridad,'alta','media','baja')"))
            ->when($tab === 'proximas',   fn($q) => $q->where('t.completada', 0)->where('t.fecha', '>', $hoy)->orderBy('t.fecha')->orderByRaw("FIELD(t.prioridad,'alta','media','baja')"))
            ->when($tab === 'vencidas',   fn($q) => $q->where('t.completada', 0)->where('t.fecha', '<', $hoy)->orderBy('t.fecha'))
            ->when($tab === 'completadas',fn($q) => $q->where('t.completada', 1)->orderBy('t.fecha_completada', 'desc'))
            ->when($tab === 'todas',      fn($q) => $q->orderBy('t.fecha')->orderByRaw("FIELD(t.prioridad,'alta','media','baja')"))
            ->get();

        // Contadores usando el modelo — más legible que 4 DB::table seguidos
        $statsHoy         = Tarea::delUsuario($uid)->pendientes()->deHoy()->count();
        $statsVencidas    = Tarea::delUsuario($uid)->pendientes()->where('fecha', '<', $hoy)->count();
        $statsProximas    = Tarea::delUsuario($uid)->pendientes()->whereBetween('fecha', [$hoy, now()->addDays(7)->toDateString()])->count();
        $statsCompletadas = Tarea::delUsuario($uid)->where('completada', 1)->whereMonth('fecha_completada', now()->month)->count();

        // Alerta de vencidas (sidebar) — mantiene JOIN para los nombres
        $vencidas = DB::table('tareas as t')
            ->leftJoin('cultivos as c', 'c.id', '=', 't.cultivo_id')
            ->leftJoin('animales as a', 'a.id', '=', 't.animal_id')
            ->where('t.usuario_id', $uid)->where('t.completada', 0)->where('t.fecha', '<', $hoy)
            ->select('t.*', 'c.nombre as cultivo_nombre', 'a.nombre_lote as animal_nombre')
            ->orderBy('t.fecha')->limit(5)->get();

        // Días con tareas pendientes para pintar el mini-calendario
        $diasConTareas = Tarea::delUsuario($uid)->pendientes()
            ->whereRaw("DATE_FORMAT(fecha,'%Y-%m') = ?", [$mes])
            ->pluck('fecha')
            ->map(fn($f) => substr($f, 0, 10))
            ->toArray();

        $cultivos    = Cultivo::delUsuario($uid)->activos()->orderBy('nombre')->get();
        $animales    = Animal::delUsuario($uid)->activos()->orderBy('nombre_lote')->get();
        $personas    = Persona::delUsuario($uid)->activos()->trabajadores()->orderBy('nombre')->get();
        $tipos       = $this->tipos();
        $tiposPlanos = $this->tiposPlanos();

        return view('pages.calendario', compact(
            'tareas', 'tab', 'mes', 'diasConTareas', 'cultivos', 'animales', 'personas',
            'tipos', 'tiposPlanos', 'statsHoy', 'statsVencidas', 'statsProximas', 'statsCompletadas', 'vencidas'
        ));
    }

    /**
     * Registra una nueva tarea en el calendario.
     */
    public function store(TareaRequest $request)
    {
        Tarea::create([
            'usuario_id' => session('usuario_id'),
            'titulo'     => $request->titulo,
            'tipo'       => $request->tipo ?? 'otro',
            'fecha'      => $request->fecha ?? now()->toDateString(),
            'hora'       => $request->hora ?: null,
            'prioridad'  => $request->prioridad ?? 'media',
            'notas'      => $request->notas,
            'cultivo_id' => $request->cultivo_id ?: null,
            'animal_id'  => $request->animal_id ?: null,
            'responsable'=> $request->responsable,
        ]);

        return redirect()->route('calendario.index')
            ->with('msg', 'Tarea registrada.')->with('msgType', 'success');
    }

    /**
     * Actualiza los datos de una tarea existente.
     */
    public function update(TareaRequest $request, $id)
    {
        $uid   = session('usuario_id');
        $tarea = Tarea::where('id', $id)->where('usuario_id', $uid)->firstOrFail();

        $tarea->update([
            'titulo'     => $request->titulo,
            'tipo'       => $request->tipo,
            'fecha'      => $request->fecha,
            'hora'       => $request->hora ?: null,
            'prioridad'  => $request->prioridad,
            'notas'      => $request->notas,
            'cultivo_id' => $request->cultivo_id ?: null,
            'animal_id'  => $request->animal_id ?: null,
            'responsable'=> $request->responsable,
        ]);

        return redirect()->route('calendario.index')
            ->with('msg', 'Tarea actualizada.')->with('msgType', 'success');
    }

    /**
     * Marca una tarea como completada con fecha y responsable opcionales.
     */
    public function completar(Request $request, $id)
    {
        $uid   = session('usuario_id');
        $tarea = Tarea::where('id', $id)->where('usuario_id', $uid)->firstOrFail();

        $tarea->update([
            'completada'            => 1,
            'fecha_completada'      => now(),
            'notas_completada'      => $request->notas_completada,
            'persona_completada_id' => $request->persona_completada_id ?: null,
        ]);

        return back()->with('msg', '¡Tarea completada! ✓')->with('msgType', 'success');
    }

    /**
     * Elimina una tarea del calendario.
     */
    public function destroy($id)
    {
        $uid   = session('usuario_id');
        $tarea = Tarea::where('id', $id)->where('usuario_id', $uid)->firstOrFail();
        $tarea->delete();

        return redirect()->route('calendario.index')
            ->with('msg', 'Tarea eliminada.')->with('msgType', 'warning');
    }
}