<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TareaController extends Controller
{
    private function tipos(): array {
        return [
            '🌱 Cultivos'   => ['riego'=>'💧 Riego','fertilizacion'=>'🌿 Fertilización','fumigacion'=>'🧴 Fumigación','poda'=>'✂️ Poda','siembra'=>'🌱 Siembra','cosecha'=>'🌾 Cosecha'],
            '🐄 Animales'   => ['vacunacion'=>'💉 Vacunación','medicamento'=>'💊 Medicamento','traslado_animal'=>'🏃 Traslado animal','parto'=>'🐣 Parto / Nacimiento','destete'=>'🍼 Destete','visita_veterinario'=>'🩺 Visita veterinario'],
            '💰 Finanzas'   => ['pago_proveedor'=>'🏪 Pago a proveedor','cobro_venta'=>'💰 Cobro de venta','revision_gastos'=>'📊 Revisión de gastos'],
            '🔧 Finca'      => ['mantenimiento'=>'🔧 Mantenimiento','compra_insumo'=>'📦 Compra de insumos','revision_inventario'=>'📋 Revisión inventario','limpieza'=>'🧹 Limpieza'],
            '📋 General'    => ['reunion'=>'🤝 Reunión','capacitacion'=>'📚 Capacitación','otro'=>'📝 Otro'],
        ];
    }

    private function tiposPlanos(): array {
        $r = [];
        foreach ($this->tipos() as $items) foreach ($items as $k => $v) $r[$k] = $v;
        return $r;
    }

    public function index(Request $request)
    {
        $uid = session('usuario_id');
        $tab = $request->tab ?? 'hoy';
        $mes = $request->mes ?? now()->format('Y-m');
        $hoy = now()->toDateString();

        $base = DB::table('tareas as t')
            ->leftJoin('cultivos as c', 'c.id', '=', 't.cultivo_id')
            ->leftJoin('animales as a', 'a.id', '=', 't.animal_id')
            ->where('t.usuario_id', $uid)
            ->select('t.*', 'c.nombre as cultivo_nombre', 'a.nombre_lote as animal_nombre', 'a.especie as animal_especie');

        // Filtros opcionales
        if ($request->tipo)     $base->where('t.tipo', $request->tipo);
        if ($request->prioridad) $base->where('t.prioridad', $request->prioridad);
        if ($request->asociado === 'cultivo') $base->whereNotNull('t.cultivo_id');
        if ($request->asociado === 'animal')  $base->whereNotNull('t.animal_id');

        $tareas = (clone $base)->when($tab === 'hoy',         fn($q) => $q->where('t.completada',0)->whereDate('t.fecha',$hoy)->orderByRaw("FIELD(t.prioridad,'alta','media','baja')")  )
                               ->when($tab === 'proximas',    fn($q) => $q->where('t.completada',0)->where('t.fecha','>',$hoy)->orderBy('t.fecha')->orderByRaw("FIELD(t.prioridad,'alta','media','baja')")  )
                               ->when($tab === 'vencidas',    fn($q) => $q->where('t.completada',0)->where('t.fecha','<',$hoy)->orderBy('t.fecha')  )
                               ->when($tab === 'completadas', fn($q) => $q->where('t.completada',1)->orderBy('t.fecha_completada','desc')  )
                               ->when($tab === 'todas',       fn($q) => $q->orderBy('t.fecha')->orderByRaw("FIELD(t.prioridad,'alta','media','baja')")  )
                               ->get();

        // Stats resumen
        $statsHoy      = DB::table('tareas')->where('usuario_id',$uid)->where('completada',0)->whereDate('fecha',$hoy)->count();
        $statsVencidas = DB::table('tareas')->where('usuario_id',$uid)->where('completada',0)->where('fecha','<',$hoy)->count();
        $statsProximas = DB::table('tareas')->where('usuario_id',$uid)->where('completada',0)->whereBetween('fecha',[$hoy, now()->addDays(7)->toDateString()])->count();
        $statsCompletadas = DB::table('tareas')->where('usuario_id',$uid)->where('completada',1)->whereMonth('fecha_completada',now()->month)->count();

        // Tareas vencidas (siempre mostrar alerta si hay)
        $vencidas = DB::table('tareas as t')
            ->leftJoin('cultivos as c','c.id','=','t.cultivo_id')
            ->leftJoin('animales as a','a.id','=','t.animal_id')
            ->where('t.usuario_id',$uid)->where('t.completada',0)->where('t.fecha','<',$hoy)
            ->select('t.*','c.nombre as cultivo_nombre','a.nombre_lote as animal_nombre')
            ->orderBy('t.fecha')->limit(5)->get();

        $diasConTareas = DB::table('tareas')->where('usuario_id',$uid)->where('completada',0)
            ->whereRaw("DATE_FORMAT(fecha,'%Y-%m') = ?",[$mes])->pluck('fecha')
            ->map(fn($f)=>substr($f,0,10))->toArray();

        $cultivos    = DB::table('cultivos')->where('usuario_id',$uid)->where('estado','activo')->orderBy('nombre')->get();
        $animales    = DB::table('animales')->where('usuario_id',$uid)->where('estado','activo')->orderBy('nombre_lote')->get();
        $tipos       = $this->tipos();
        $tiposPlanos = $this->tiposPlanos();

        return view('pages.calendario', compact(
            'tareas','tab','mes','diasConTareas','cultivos','animales',
            'tipos','tiposPlanos','statsHoy','statsVencidas','statsProximas','statsCompletadas','vencidas'
        ));
    }

    public function store(Request $request)
    {
        $request->validate(['titulo'=>'required']);
        DB::table('tareas')->insert([
            'usuario_id'  => session('usuario_id'),
            'titulo'      => $request->titulo,
            'tipo'        => $request->tipo ?? 'otro',
            'fecha'       => $request->fecha ?? now()->toDateString(),
            'hora'        => $request->hora ?: null,
            'prioridad'   => $request->prioridad ?? 'media',
            'notas'       => $request->notas,
            'cultivo_id'  => $request->cultivo_id ?: null,
            'animal_id'   => $request->animal_id ?: null,
            'responsable' => $request->responsable,
            'creado_en'   => now()->toDateTimeString(),
        ]);
        return redirect()->route('calendario.index')->with('msg','Tarea registrada.')->with('msgType','success');
    }

    public function update(Request $request, $id)
    {
        $request->validate(['titulo'=>'required']);
        DB::table('tareas')->where('id',$id)->where('usuario_id',session('usuario_id'))->update([
            'titulo'      => $request->titulo,
            'tipo'        => $request->tipo,
            'fecha'       => $request->fecha,
            'hora'        => $request->hora ?: null,
            'prioridad'   => $request->prioridad,
            'notas'       => $request->notas,
            'cultivo_id'  => $request->cultivo_id ?: null,
            'animal_id'   => $request->animal_id ?: null,
            'responsable' => $request->responsable,
        ]);
        return redirect()->route('calendario.index')->with('msg','Tarea actualizada.')->with('msgType','success');
    }

    public function completar(Request $request, $id)
    {
        DB::table('tareas')->where('id',$id)->where('usuario_id',session('usuario_id'))->update([
            'completada'       => 1,
            'fecha_completada' => now(),
            'notas_completada' => $request->notas_completada,
        ]);
        return redirect()->route('calendario.index')->with('msg','¡Tarea completada! ✓')->with('msgType','success');
    }

    public function destroy($id)
    {
        DB::table('tareas')->where('id',$id)->where('usuario_id',session('usuario_id'))->delete();
        return redirect()->route('calendario.index')->with('msg','Tarea eliminada.')->with('msgType','warning');
    }
}