<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PerfilController extends Controller
{
    public function index(Request $request)
    {
        $uid  = session('usuario_id');
        $user = DB::table('usuarios')->find($uid);
        // Normalize column names for compatibility with both old and new DB schemas
        if ($user && !isset($user->created_at)) {
            $user->created_at = $user->creado_en ?? null;
            $user->updated_at = $user->actualizado_en ?? null;
        }
        $tab  = $request->tab ?? 'perfil';
        $stats = [
            'cultivos' => DB::table('cultivos')->where('usuario_id',$uid)->count(),
            'gastos'   => DB::table('gastos')->where('usuario_id',$uid)->count(),
            'tareas'   => DB::table('tareas')->where('usuario_id',$uid)->count(),
            'ingresos' => DB::table('ingresos')->where('usuario_id',$uid)->count(),
        ];
        return view('pages.perfil', compact('user','tab','stats'));
    }
    public function update(Request $request)
    {
        $request->validate(['nombre'=>'required']);
        DB::table('usuarios')->where('id',session('usuario_id'))->update(['nombre'=>$request->nombre,'nombre_finca'=>$request->finca,'departamento'=>$request->departamento,'municipio'=>$request->municipio,'telefono'=>$request->telefono,'updated_at'=>now()]);
        session(['usuario_nombre'=>$request->nombre]);
        return redirect()->route('perfil.index')->with('msg','Perfil actualizado.')->with('msgType','success');
    }
    public function changePassword(Request $request)
    {
        $request->validate(['password_actual'=>'required','password_nueva'=>'required|min:6','password_confirmar'=>'required|same:password_nueva']);
        $user = DB::table('usuarios')->find(session('usuario_id'));
        if (!Hash::check($request->password_actual,$user->password)) {
            return redirect()->route('perfil.index',['tab'=>'seguridad'])->withErrors(['password_actual'=>'Contraseña actual incorrecta.']);
        }
        DB::table('usuarios')->where('id',session('usuario_id'))->update(['password'=>Hash::make($request->password_nueva)]);
        return redirect()->route('perfil.index',['tab'=>'seguridad'])->with('msg','Contraseña cambiada.')->with('msgType','success');
    }
}
