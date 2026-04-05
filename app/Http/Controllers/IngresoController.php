<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IngresoController extends Controller
{
    public function index()
    {
        $uid = session('usuario_id');
        $ingresos  = DB::table('ingresos as i')->leftJoin('cultivos as c','c.id','=','i.cultivo_id')->where('i.usuario_id',$uid)->select('i.*','c.nombre as cultivo_nombre')->orderBy('i.fecha','desc')->get();
        $totalMes  = DB::table('ingresos')->where('usuario_id',$uid)->whereMonth('fecha',now()->month)->whereYear('fecha',now()->year)->sum('valor_total');
        $totalAnio = DB::table('ingresos')->where('usuario_id',$uid)->whereYear('fecha',now()->year)->sum('valor_total');
        $cultivos  = DB::table('cultivos')->where('usuario_id',$uid)->orderBy('nombre')->get();
        return view('pages.ingresos', compact('ingresos','totalMes','totalAnio','cultivos'));
    }
    public function store(Request $request)
    {
        $request->validate(['descripcion'=>'required','valor_total'=>'required|numeric']);
        $cant=$request->cantidad; $punit=$request->precio_unitario;
        $total = ($cant && $punit) ? $cant*$punit : $request->valor_total;
        DB::table('ingresos')->insert(['usuario_id'=>session('usuario_id'),'descripcion'=>$request->descripcion,'cantidad'=>$cant ?: null,'unidad'=>$request->unidad,'precio_unitario'=>$punit ?: null,'valor_total'=>$total,'fecha'=>$request->fecha ?? now()->toDateString(),'comprador'=>$request->comprador,'notas'=>$request->notas,'cultivo_id'=>$request->cultivo_id ?: null,'creado_en'=>now()->toDateTimeString()]);
        return redirect()->route('ingresos.index')->with('msg','Ingreso registrado.')->with('msgType','success');
    }
    public function update(Request $request, $id)
    {
        $request->validate(['descripcion'=>'required','valor_total'=>'required|numeric']);
        DB::table('ingresos')->where('id',$id)->where('usuario_id',session('usuario_id'))->update(['descripcion'=>$request->descripcion,'cantidad'=>$request->cantidad ?: null,'unidad'=>$request->unidad,'precio_unitario'=>$request->precio_unitario ?: null,'valor_total'=>$request->valor_total,'fecha'=>$request->fecha,'comprador'=>$request->comprador,'notas'=>$request->notas,'cultivo_id'=>$request->cultivo_id ?: null]);
        return redirect()->route('ingresos.index')->with('msg','Ingreso actualizado.')->with('msgType','success');
    }
    public function destroy($id)
    {
        DB::table('ingresos')->where('id',$id)->where('usuario_id',session('usuario_id'))->delete();
        return redirect()->route('ingresos.index')->with('msg','Ingreso eliminado.')->with('msgType','warning');
    }
}
