<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnimalController extends Controller
{
    public function index()
    {
        $uid = session('usuario_id');
        $animales = DB::table('animales')->where('usuario_id',$uid)->orderByRaw("FIELD(estado,'activo','vendido','muerte')")->orderBy('id','desc')->get();
        $totalActivos = DB::table('animales')->where('usuario_id',$uid)->where('estado','activo')->sum('cantidad');
        $especies = ['Ganado bovino','Cerdos','Gallinas','Conejos','Cabras','Ovejas','Caballos','Peces','Patos','Cerdas de cría','Terneros','Pavos','Otro'];
        return view('pages.animales', compact('animales','totalActivos','especies'));
    }
    public function store(Request $request)
    {
        $request->validate(['especie'=>'required']);
        DB::table('animales')->insert(['usuario_id'=>session('usuario_id'),'especie'=>$request->especie,'nombre_lote'=>$request->nombre_lote,'cantidad'=>$request->cantidad ?? 1,'fecha_ingreso'=>$request->fecha_ingreso ?: null,'estado'=>$request->estado ?? 'activo','peso_promedio'=>$request->peso_promedio ?: null,'unidad_peso'=>$request->unidad_peso ?? 'kg','notas'=>$request->notas,'creado_en'=>now()->toDateTimeString(),'actualizado_en'=>now()->toDateTimeString()]);
        return redirect()->route('animales.index')->with('msg','Animal registrado.')->with('msgType','success');
    }
    public function update(Request $request, $id)
    {
        $request->validate(['especie'=>'required']);
        DB::table('animales')->where('id',$id)->where('usuario_id',session('usuario_id'))->update(['especie'=>$request->especie,'nombre_lote'=>$request->nombre_lote,'cantidad'=>$request->cantidad ?? 1,'fecha_ingreso'=>$request->fecha_ingreso ?: null,'estado'=>$request->estado,'peso_promedio'=>$request->peso_promedio ?: null,'unidad_peso'=>$request->unidad_peso,'notas'=>$request->notas,'actualizado_en'=>now()->toDateTimeString()]);
        return redirect()->route('animales.index')->with('msg','Animal actualizado.')->with('msgType','success');
    }
    public function destroy($id)
    {
        DB::table('animales')->where('id',$id)->where('usuario_id',session('usuario_id'))->delete();
        return redirect()->route('animales.index')->with('msg','Registro eliminado.')->with('msgType','warning');
    }
}
