<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class DiagnosticoController extends Controller
{
    private function supabaseUrl(): string
    {
        return env('SUPABASE_URL') . '/rest/v1/diagnostico_inicial';
    }

    private function headers(): array
    {
        return [
            'apikey'        => env('SUPABASE_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_KEY'),
            'Content-Type'  => 'application/json',
            'Prefer'        => 'return=minimal',
        ];
    }

    public function show()
    {
        $uid     = session('usuario_id');
        $usuario = DB::table('usuarios')->where('id', $uid)->first();

        if ($usuario && $usuario->diagnostico_completado) {
            return redirect()->route('dashboard');
        }

        return view('pages.diagnostico');
    }

    public function store(Request $request)
    {
        $uid = session('usuario_id');

        $datos = [
            'usuario_id' => (string) $uid,
            'instancia'  => config('app.name'),
            'd1'         => $request->d1,
            'd2'         => $request->d2,
            'd3'         => $request->d3,
            'd4'         => $request->d4,
            'd5'         => $request->d5,
            'd6'         => $request->d6,
        ];

        Http::withOptions(['verify' => false])
            ->withHeaders($this->headers())
            ->post($this->supabaseUrl(), $datos);

        DB::table('usuarios')
            ->where('id', $uid)
            ->update(['diagnostico_completado' => 1]);

        return redirect()->route('dashboard')
            ->with('msg', '¡Gracias! Tus datos de línea base quedaron registrados.')
            ->with('msgType', 'success');
    }

    public function omitir()
    {
        DB::table('usuarios')
            ->where('id', session('usuario_id'))
            ->update(['diagnostico_completado' => 1]);

        return redirect()->route('dashboard');
    }

    /**
     * Permite al usuario responder el diagnóstico de nuevo desde Perfil.
     */
    public function reset()
    {
        DB::table('usuarios')
            ->where('id', session('usuario_id'))
            ->update(['diagnostico_completado' => 0]);

        return redirect()->route('diagnostico.show');
    }
}