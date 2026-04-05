<?php

namespace App\Http\Controllers;

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

        $valid = false;
        if ($user) {
            $valid = Hash::check($request->password, $user->password);
            // Bypass demo
            if (!$valid && $user->email === 'demo@demo.com' && $request->password === 'demo123') {
                $valid = true;
                DB::table('usuarios')->where('id', $user->id)
                    ->update(['password' => Hash::make('demo123')]);
                $user = DB::table('usuarios')->where('id', $user->id)->first();
            }
        }

        if (!$valid) {
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
            'nombre'       => $request->nombre,
            'email'        => strtolower(trim($request->email)),
            'password'     => Hash::make($request->password),
            'nombre_finca' => $request->finca,
            'departamento' => $request->departamento,
            'municipio'    => $request->municipio,
            'telefono'     => $request->telefono,
            'creado_en'   => now()->toDateTimeString(),
            'actualizado_en'   => now()->toDateTimeString(),
        ]);

        session(['usuario_id' => $id, 'usuario_nombre' => $request->nombre]);
        return redirect()->route('onboarding');
    }

    public function logout()
    {
        session()->flush();
        return redirect()->route('login');
    }

    public function onboarding()
    {
        return view('auth.onboarding');
    }

    public function onboardingComplete()
    {
        DB::table('usuarios')->where('id', session('usuario_id'))
            ->update(['onboarding_completado' => 1]);
        return redirect()->route('dashboard');
    }
}
