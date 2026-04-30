<?php

namespace App\Http\Controllers;

use App\Models\LineaProductiva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function welcome()
    {
        if (session('usuario_id')) return redirect()->route('dashboard');
        return view('auth.welcome');
    }

    public function showLogin()
    {
        if (session('usuario_id')) return redirect()->route('dashboard');
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'El correo es obligatorio.',
            'email.email'       => 'Ingresa un correo válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $user = DB::table('usuarios')
            ->where('email', strtolower(trim($request->email)))
            ->where('activo', 1)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['email' => 'Correo o contraseña incorrectos.'])->withInput();
        }

        session(['usuario_id' => $user->id, 'usuario_nombre' => $user->nombre]);

        if (!$user->onboarding_completado) {
            return redirect()->route('onboarding');
        }

        return redirect()->route('dashboard');
    }

    public function showRegister()
    {
        if (session('usuario_id')) return redirect()->route('dashboard');
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'nombre'   => 'required|min:2',
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ], [
            'nombre.required'   => 'El nombre es obligatorio.',
            'nombre.min'        => 'El nombre debe tener al menos 2 caracteres.',
            'email.required'    => 'El correo es obligatorio.',
            'email.email'       => 'Ingresa un correo válido.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min'      => 'La contraseña debe tener al menos 6 caracteres.',
        ]);

        $exists = DB::table('usuarios')->where('email', strtolower(trim($request->email)))->exists();
        if ($exists) {
            return back()->withErrors(['email' => 'Este correo ya está registrado.'])->withInput();
        }

        $id = DB::table('usuarios')->insertGetId([
            'nombre'         => $request->nombre,
            'email'          => strtolower(trim($request->email)),
            'password'       => Hash::make($request->password),
            'nombre_finca'   => $request->finca,
            'departamento'   => $request->departamento,
            'municipio'      => $request->municipio,
            'telefono'       => $request->telefono,
            'creado_en'      => now()->toDateTimeString(),
            'actualizado_en' => now()->toDateTimeString(),
        ]);

        session(['usuario_id' => $id, 'usuario_nombre' => $request->nombre]);
        return redirect()->route('onboarding');
    }

    public function logout()
    {
        session()->flush();
        return redirect()->route('login');
    }

    /**
     * Muestra el wizard de onboarding (5 pasos).
     * Pasa el catálogo de líneas productivas y los datos actuales del
     * usuario (por si vuelve a entrar al wizard sin completar).
     */
    public function onboarding()
    {
        $uid  = session('usuario_id');
        $user = DB::table('usuarios')->find($uid);

        $lineas = LineaProductiva::activas()->get();

        // Líneas que ya tenga marcadas (por si reabre el wizard).
        $lineasUsuario = DB::table('usuario_lineas')
            ->where('usuario_id', $uid)
            ->where('activa', 1)
            ->get()
            ->keyBy('linea_codigo');

        return view('auth.onboarding', compact('user', 'lineas', 'lineasUsuario'));
    }

    /**
     * Recibe los datos del wizard completo:
     *   - Datos de la finca (paso 2)
     *   - Líneas productivas seleccionadas (paso 3)
     *   - Configuración de cada línea: cantidad, escala, metadata (paso 4)
     *
     * Guarda todo y marca onboarding_completado = 1.
     */
    public function onboardingComplete(Request $request)
    {
        $uid = session('usuario_id');
        if (!$uid) return redirect()->route('login');

        $request->validate([
            'lineas' => 'required|array|min:1',
        ], [
            'lineas.required' => 'Debes seleccionar al menos una línea productiva.',
            'lineas.min'      => 'Debes seleccionar al menos una línea productiva.',
        ]);

        DB::transaction(function () use ($request, $uid) {

            // 1. Actualizar datos de la finca (paso 2 del wizard).
            $datosFinca = [
                'nombre_finca'      => $request->nombre_finca ?: null,
                'hectareas_total'   => $request->hectareas_total ?: null,
                'departamento'      => $request->departamento ?: null,
                'municipio'         => $request->municipio ?: null,
                'descripcion_finca' => $request->descripcion_finca ?: null,
                'actualizado_en'    => now()->toDateTimeString(),
            ];
            // Algunos campos pueden no existir en BDs antiguas; ignorarlas si fallan.
            try {
                DB::table('usuarios')->where('id', $uid)->update($datosFinca);
            } catch (\Exception $e) {
                DB::table('usuarios')->where('id', $uid)->update([
                    'nombre_finca'   => $request->nombre_finca ?: null,
                    'departamento'   => $request->departamento ?: null,
                    'municipio'      => $request->municipio ?: null,
                    'actualizado_en' => now()->toDateTimeString(),
                ]);
            }

            // 2. Líneas productivas: borrar y reinsertar para mantenerlo simple.
            DB::table('usuario_lineas')->where('usuario_id', $uid)->delete();

            $codigosValidos = LineaProductiva::activas()->pluck('codigo')->toArray();
            $configs        = $request->input('config', []); // arr asociativo por código

            foreach ((array) $request->lineas as $codigo) {
                if (!in_array($codigo, $codigosValidos, true)) continue;

                $cfg = $configs[$codigo] ?? [];

                // Sacar cantidad/escala fijas; el resto va a metadata como JSON.
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

            // 3. Recalcular `tipo_produccion` (campo legado) para coherencia visual.
            try {
                $resumen = LineaProductiva::resumenTexto($uid);
                if ($resumen !== '') {
                    DB::table('usuarios')->where('id', $uid)
                        ->update(['tipo_produccion' => $resumen]);
                }
            } catch (\Exception $e) { /* campo opcional */ }

            // 4. Marcar onboarding completado.
            DB::table('usuarios')->where('id', $uid)
                ->update(['onboarding_completado' => 1]);
        });

        LineaProductiva::limpiarCache($uid);

        return redirect()->route('dashboard')
            ->with('msg', '¡Bienvenido! Tu finca está lista.')
            ->with('msgType', 'success');
    }
}