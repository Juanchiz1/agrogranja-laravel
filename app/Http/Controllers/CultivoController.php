<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CultivoController extends Controller
{
    public function index(Request $request)
    {
        $uid = session('usuario_id');
        $query = DB::table('cultivos')->where('usuario_id', $uid);

        if ($request->q) {
            $q = $request->q;
            $query->where(fn($w) => $w->where('nombre','like',"%$q%")->orWhere('tipo','like',"%$q%"));
        }
        if ($request->estado) $query->where('estado', $request->estado);

        $cultivos = $query->orderBy('id','desc')->get();
        $stats = DB::table('cultivos')->where('usuario_id', $uid)
            ->selectRaw('estado, count(*) as c')->groupBy('estado')->pluck('c','estado');

        $tiposCultivo = ['Maíz','Yuca','Plátano','Arroz','Frijol','Tomate','Cebolla','Ají','Papa','Aguacate','Limón','Naranja','Mango','Caña de azúcar','Café','Cacao','Ganado bovino','Cerdos','Gallinas','Peces','Caballos','Cabras','Otro'];

        return view('pages.cultivos', compact('cultivos','stats','tiposCultivo'));
    }

    public function store(Request $request)
    {
        $request->validate(['tipo'=>'required','nombre'=>'required']);
        DB::table('cultivos')->insert([
            'usuario_id'    => session('usuario_id'),
            'tipo'          => $request->tipo,
            'nombre'        => $request->nombre,
            'fecha_siembra' => $request->fecha_siembra ?? now()->toDateString(),
            'area'          => $request->area ?: null,
            'unidad'        => $request->unidad ?? 'hectareas',
            'estado'        => $request->estado ?? 'activo',
            'notas'         => $request->notas,
            'creado_en'    => now()->toDateTimeString(), 'actualizado_en' => now()->toDateTimeString(),
        ]);
        return redirect()->route('cultivos.index')->with('msg','Cultivo registrado correctamente.')->with('msgType','success');
    }

    public function update(Request $request, $id)
    {
        $request->validate(['tipo'=>'required','nombre'=>'required']);
        DB::table('cultivos')->where('id',$id)->where('usuario_id',session('usuario_id'))->update([
            'tipo'=>$request->tipo,'nombre'=>$request->nombre,
            'fecha_siembra'=>$request->fecha_siembra,'area'=>$request->area ?: null,
            'unidad'=>$request->unidad,'estado'=>$request->estado,'notas'=>$request->notas,
            'actualizado_en'=>now()->toDateTimeString(),
        ]);
        return redirect()->route('cultivos.index')->with('msg','Cultivo actualizado.')->with('msgType','success');
    }

    public function destroy($id)
    {
        DB::table('cultivos')->where('id',$id)->where('usuario_id',session('usuario_id'))->delete();
        return redirect()->route('cultivos.index')->with('msg','Cultivo eliminado.')->with('msgType','warning');
    }
}
