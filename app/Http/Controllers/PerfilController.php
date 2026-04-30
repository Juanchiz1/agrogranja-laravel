<?php
namespace App\Http\Controllers;

use App\Models\LineaProductiva;
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
            'cultivos'       => DB::table('cultivos')->where('usuario_id',$uid)->count(),
            'animales'       => DB::table('animales')->where('usuario_id',$uid)->sum('cantidad'),
            'gastos'         => DB::table('gastos')->where('usuario_id',$uid)->count(),
            'ingresos'       => DB::table('ingresos')->where('usuario_id',$uid)->count(),
            'tareas'         => DB::table('tareas')->where('usuario_id',$uid)->count(),
            'cosechas'       => DB::table('cosechas')->where('usuario_id',$uid)->count(),
            'total_ingresos' => DB::table('ingresos')->where('usuario_id',$uid)->whereYear('fecha',now()->year)->sum('valor_total'),
            'total_gastos'   => DB::table('gastos')->where('usuario_id',$uid)->whereYear('fecha',now()->year)->sum('valor'),
        ];

        // Catálogo de líneas + las que ya tiene activas el usuario (para el tab preferencias).
        $lineas        = LineaProductiva::activas()->get();
        $lineasUsuario = DB::table('usuario_lineas')
            ->where('usuario_id', $uid)
            ->get()
            ->keyBy('linea_codigo');

        return view('pages.perfil', compact('user','tab','stats','lineas','lineasUsuario'));
    }

    public function update(Request $request)
    {
        $request->validate(['nombre'=>'required']);
        $uid  = session('usuario_id');
        $user = DB::table('usuarios')->find($uid);

        $data = [
            'nombre'         => $request->nombre,
            'nombre_finca'   => $request->nombre_finca,
            'departamento'   => $request->departamento,
            'municipio'      => $request->municipio,
            'telefono'       => $request->telefono,
            'actualizado_en' => now()->toDateTimeString(),
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
                'notif_tareas'   => $request->has('notif_tareas') ? 1 : 0,
                'notif_stock'    => $request->has('notif_stock')  ? 1 : 0,
                'moneda'         => $request->moneda ?? 'COP',
                'actualizado_en' => now()->toDateTimeString(),
            ]);
        } catch (\Exception $e) {}
        return redirect()->route('perfil.index',['tab'=>'preferencias'])
            ->with('msg','Preferencias guardadas.')->with('msgType','success');
    }

    /**
     * Actualiza las líneas productivas activas del usuario desde el
     * tab "Preferencias" del perfil. Se hace en una transacción:
     * borra todas las anteriores e inserta las nuevas.
     */
    public function updateLineas(Request $request)
    {
        $uid = session('usuario_id');
        if (!$uid) return redirect()->route('login');

        $request->validate([
            'lineas' => 'required|array|min:1',
        ], [
            'lineas.required' => 'Debes mantener al menos una línea productiva activa.',
            'lineas.min'      => 'Debes mantener al menos una línea productiva activa.',
        ]);

        DB::transaction(function () use ($request, $uid) {

            DB::table('usuario_lineas')->where('usuario_id', $uid)->delete();

            $codigosValidos = LineaProductiva::activas()->pluck('codigo')->toArray();
            $configs        = $request->input('config', []);

            foreach ((array) $request->lineas as $codigo) {
                if (!in_array($codigo, $codigosValidos, true)) continue;

                $cfg = $configs[$codigo] ?? [];

                $cantidad = isset($cfg['cantidad']) && $cfg['cantidad'] !== ''
                    ? (int) $cfg['cantidad'] : null;
                $escala   = in_array($cfg['escala'] ?? '', ['pequena','mediana','grande'], true)
                    ? $cfg['escala'] : 'pequena';

                $meta = $cfg;
                unset($meta['cantidad'], $meta['escala']);

                DB::table('usuario_lineas')->insert([
                    'usuario_id'     => $uid,
                    'linea_codigo'   => $codigo,
                    'cantidad_aprox' => $cantidad,
                    'escala'         => $escala,
                    'metadata'       => !empty($meta) ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
                    'activa'         => 1,
                    'creado_en'      => now()->toDateTimeString(),
                    'actualizado_en' => now()->toDateTimeString(),
                ]);
            }

            // Refresca el campo legado tipo_produccion para coherencia visual.
            try {
                $resumen = LineaProductiva::resumenTexto($uid);
                if ($resumen !== '') {
                    DB::table('usuarios')->where('id', $uid)
                        ->update(['tipo_produccion' => $resumen]);
                }
            } catch (\Exception $e) {}
        });

        LineaProductiva::limpiarCache($uid);

        return redirect()->route('perfil.index',['tab'=>'preferencias'])
            ->with('msg','Líneas productivas actualizadas. La app se adaptará a tus cambios.')
            ->with('msgType','success');
    }
}