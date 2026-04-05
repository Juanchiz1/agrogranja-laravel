<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TareaController extends Controller
{
    public function index(Request $request)
    {
        $uid = session('usuario_id');
        $tab = $request->tab ?? 'proximas';
        $mes = $request->mes ?? now()->format('Y-m');

        $query = DB::table('tareas as t')->leftJoin('cultivos as c','c.id','=','t.cultivo_id')->where('t.usuario_id',$uid)->select('t.*','c.nombre as cultivo_nombre');
        if ($tab === 'completadas') $query->where('t.completada',1)->orderBy('t.fecha_completada','desc');
        elseif ($tab === 'proximas')  $query->where('t.completada',0)->where('t.fecha','>=',now()->toDateString())->orderBy('t.fecha')->orderByRaw("FIELD(t.prioridad,'alta','media','baja')");
        else $query->orderBy('t.fecha')->orderByRaw("FIELD(t.prioridad,'alta','media','baja')");
        $tareas = $query->get();

        $diasConTareas = DB::table('tareas')->where('usuario_id',$uid)->where('completada',0)->whereRaw("DATE_FORMAT(fecha,'%Y-%m') = ?",[$mes])->pluck('fecha')->map(fn($f)=>substr($f,0,10))->toArray();
        $cultivos = DB::table('cultivos')->where('usuario_id',$uid)->where('estado','activo')->orderBy('nombre')->get();

        return view('pages.calendario', compact('tareas','tab','mes','diasConTareas','cultivos'));
    }
    public function store(Request $request)
    {
        $request->validate(['titulo'=>'required']);
        DB::table('tareas')->insert(['usuario_id'=>session('usuario_id'),'titulo'=>$request->titulo,'tipo'=>$request->tipo ?? 'otro','fecha'=>$request->fecha ?? now()->toDateString(),'hora'=>$request->hora ?: null,'prioridad'=>$request->prioridad ?? 'media','notas'=>$request->notas,'cultivo_id'=>$request->cultivo_id ?: null,'creado_en'=>now()->toDateTimeString()]);
        return redirect()->route('calendario.index')->with('msg','Tarea registrada.')->with('msgType','success');
    }
    public function update(Request $request, $id)
    {
        $request->validate(['titulo'=>'required']);
        DB::table('tareas')->where('id',$id)->where('usuario_id',session('usuario_id'))->update(['titulo'=>$request->titulo,'tipo'=>$request->tipo,'fecha'=>$request->fecha,'hora'=>$request->hora ?: null,'prioridad'=>$request->prioridad,'notas'=>$request->notas,'cultivo_id'=>$request->cultivo_id ?: null]);
        return redirect()->route('calendario.index')->with('msg','Tarea actualizada.')->with('msgType','success');
    }
    public function completar($id)
    {
        DB::table('tareas')->where('id',$id)->where('usuario_id',session('usuario_id'))->update(['completada'=>1,'fecha_completada'=>now()]);
        return redirect()->route('calendario.index')->with('msg','¡Tarea completada! ✓')->with('msgType','success');
    }
    public function destroy($id)
    {
        DB::table('tareas')->where('id',$id)->where('usuario_id',session('usuario_id'))->delete();
        return redirect()->route('calendario.index')->with('msg','Tarea eliminada.')->with('msgType','warning');
    }
}
