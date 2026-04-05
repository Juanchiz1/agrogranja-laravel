<?php
// ============================================================
// GastoController
// ============================================================
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GastoController extends Controller
{
    public function index(Request $request)
    {
        $uid = session('usuario_id');
        $query = DB::table('gastos as g')
            ->leftJoin('cultivos as c','c.id','=','g.cultivo_id')
            ->where('g.usuario_id',$uid)
            ->select('g.*','c.nombre as cultivo_nombre');

        if ($request->q) { $q=$request->q; $query->where(fn($w)=>$w->where('g.descripcion','like',"%$q%")->orWhere('g.categoria','like',"%$q%")); }
        if ($request->mes) $query->whereRaw("DATE_FORMAT(g.fecha,'%Y-%m') = ?",[$request->mes]);
        if ($request->cat) $query->where('g.categoria',$request->cat);

        $gastos    = $query->orderBy('g.fecha','desc')->get();
        $totalMes  = DB::table('gastos')->where('usuario_id',$uid)->whereMonth('fecha',now()->month)->whereYear('fecha',now()->year)->sum('valor');
        $totalAnio = DB::table('gastos')->where('usuario_id',$uid)->whereYear('fecha',now()->year)->sum('valor');
        $statsCat  = DB::table('gastos')->where('usuario_id',$uid)->whereMonth('fecha',now()->month)->whereYear('fecha',now()->year)->selectRaw('categoria, SUM(valor) as total')->groupBy('categoria')->orderByDesc('total')->limit(5)->get();
        $cultivos  = DB::table('cultivos')->where('usuario_id',$uid)->where('estado','activo')->orderBy('nombre')->get();
        $categorias = ['Semillas','Fertilizantes','Plaguicidas','Herramientas','Combustible','Mano de obra','Transporte','Alimento animal','Veterinario','Mantenimiento','Otros'];

        return view('pages.gastos', compact('gastos','totalMes','totalAnio','statsCat','cultivos','categorias'));
    }

    public function store(Request $request)
    {
        $request->validate(['categoria'=>'required','descripcion'=>'required','valor'=>'required|numeric']);
        DB::table('gastos')->insert([
            'usuario_id'=>session('usuario_id'),'categoria'=>$request->categoria,
            'descripcion'=>$request->descripcion,'cantidad'=>$request->cantidad ?: null,
            'unidad_cantidad'=>$request->unidad_cantidad,'valor'=>$request->valor,
            'fecha'=>$request->fecha ?? now()->toDateString(),'proveedor'=>$request->proveedor,
            'cultivo_id'=>$request->cultivo_id ?: null,'creado_en'=>now()->toDateTimeString(),
        ]);
        return redirect()->route('gastos.index')->with('msg','Gasto registrado.')->with('msgType','success');
    }

    public function update(Request $request, $id)
    {
        $request->validate(['categoria'=>'required','descripcion'=>'required','valor'=>'required|numeric']);
        DB::table('gastos')->where('id',$id)->where('usuario_id',session('usuario_id'))->update([
            'categoria'=>$request->categoria,'descripcion'=>$request->descripcion,
            'cantidad'=>$request->cantidad ?: null,'unidad_cantidad'=>$request->unidad_cantidad,
            'valor'=>$request->valor,'fecha'=>$request->fecha,'proveedor'=>$request->proveedor,
            'cultivo_id'=>$request->cultivo_id ?: null,
        ]);
        return redirect()->route('gastos.index')->with('msg','Gasto actualizado.')->with('msgType','success');
    }

    public function destroy($id)
    {
        DB::table('gastos')->where('id',$id)->where('usuario_id',session('usuario_id'))->delete();
        return redirect()->route('gastos.index')->with('msg','Gasto eliminado.')->with('msgType','warning');
    }
}
