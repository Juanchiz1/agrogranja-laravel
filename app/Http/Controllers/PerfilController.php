<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PerfilController extends Controller
{
    private function guardarImagen($file, string $sub = 'perfil'): string {
        $dir = public_path("img/{$sub}");
        if (!file_exists($dir)) mkdir($dir, 0775, true);
        $nombre = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
        $file->move($dir, $nombre);
        return "img/{$sub}/{$nombre}";
    }

    private function eliminarImagen(?string $ruta): void {
        if ($ruta) { $f = public_path($ruta); if (file_exists($f)) unlink($f); }
    }

    public function index(Request $request)
    {
        $uid  = session('usuario_id');
        $user = DB::table('usuarios')->find($uid);
        $tab  = $request->tab ?? 'datos';

        $stats = [
            'cultivos'  => DB::table('cultivos')->where('usuario_id',$uid)->count(),
            'animales'  => DB::table('animales')->where('usuario_id',$uid)->sum('cantidad'),
            'gastos'    => DB::table('gastos')->where('usuario_id',$uid)->count(),
            'ingresos'  => DB::table('ingresos')->where('usuario_id',$uid)->count(),
            'tareas'    => DB::table('tareas')->where('usuario_id',$uid)->count(),
            'cosechas'  => DB::table('cosechas')->where('usuario_id',$uid)->count(),
            'total_ingresos' => DB::table('ingresos')->where('usuario_id',$uid)->whereYear('fecha',now()->year)->sum('valor_total'),
            'total_gastos'   => DB::table('gastos')->where('usuario_id',$uid)->whereYear('fecha',now()->year)->sum('valor'),
        ];

        return view('pages.perfil', compact('user','tab','stats'));
    }

    public function update(Request $request)
    {
        $request->validate(['nombre'=>'required']);
        $uid  = session('usuario_id');
        $user = DB::table('usuarios')->find($uid);

        $data = [
            'nombre'            => $request->nombre,
            'nombre_finca'      => $request->nombre_finca,
            'departamento'      => $request->departamento,
            'municipio'         => $request->municipio,
            'telefono'          => $request->telefono,
            'actualizado_en'    => now()->toDateTimeString(),
        ];

        // Campos nuevos (try/catch por si no existen aún en la BD)
        try {
            $data = array_merge($data, [
                'hectareas_total'   => $request->hectareas_total ?: null,
                'tipo_produccion'   => $request->tipo_produccion,
                'descripcion_finca' => $request->descripcion_finca,
                'rut'               => $request->rut,
                'entidad_bancaria'  => $request->entidad_bancaria,
                'num_cuenta'        => $request->num_cuenta,
                'tipo_cuenta'       => $request->tipo_cuenta,
                'moneda'            => $request->moneda ?? 'COP',
            ]);
        } catch (\Exception $e) {}

        // Foto de perfil
        if ($request->hasFile('foto_perfil')) {
            $this->eliminarImagen($user->foto_perfil ?? null);
            $data['foto_perfil'] = $this->guardarImagen($request->file('foto_perfil'), 'perfil');
        }

        // Foto de la finca
        if ($request->hasFile('foto_finca')) {
            try {
                $this->eliminarImagen($user->foto_finca ?? null);
                $data['foto_finca'] = $this->guardarImagen($request->file('foto_finca'), 'perfil/fincas');
            } catch (\Exception $e) {}
        }

        DB::table('usuarios')->where('id',$uid)->update($data);
        session(['usuario_nombre' => $request->nombre]);

        return redirect()->route('perfil.index',['tab'=>$request->tab_actual??'datos'])
            ->with('msg','Perfil actualizado correctamente.')->with('msgType','success');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'password_actual'    => 'required',
            'password_nueva'     => 'required|min:6',
            'password_confirmar' => 'required|same:password_nueva',
        ]);

        $user = DB::table('usuarios')->find(session('usuario_id'));
        if (!Hash::check($request->password_actual, $user->password)) {
            return redirect()->route('perfil.index',['tab'=>'seguridad'])
                ->withErrors(['password_actual'=>'Contraseña actual incorrecta.']);
        }

        DB::table('usuarios')->where('id',session('usuario_id'))
            ->update(['password'=>Hash::make($request->password_nueva),'actualizado_en'=>now()->toDateTimeString()]);

        return redirect()->route('perfil.index',['tab'=>'seguridad'])
            ->with('msg','Contraseña cambiada correctamente.')->with('msgType','success');
    }

    public function updateNotificaciones(Request $request)
    {
        try {
            DB::table('usuarios')->where('id',session('usuario_id'))->update([
                'notif_tareas' => $request->has('notif_tareas') ? 1 : 0,
                'notif_stock'  => $request->has('notif_stock')  ? 1 : 0,
                'moneda'       => $request->moneda ?? 'COP',
                'actualizado_en'=> now()->toDateTimeString(),
            ]);
        } catch (\Exception $e) {}
        return redirect()->route('perfil.index',['tab'=>'preferencias'])
            ->with('msg','Preferencias guardadas.')->with('msgType','success');
    }
}