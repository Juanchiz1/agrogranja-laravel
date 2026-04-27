<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EncuestaController extends Controller
{
    private function supabaseUrl()
    {
        return env('SUPABASE_URL') . '/rest/v1/encuesta_respuestas';
    }

    private function headers()
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
        // Si ya respondió en los últimos 90 días, redirige
        if ($this->yaRespondioReciente()) {
            return redirect('/dashboard')
                ->with('info', 'Ya respondiste la encuesta recientemente. ¡Gracias!');
        }

        return view('pages.encuesta');
    }

    public function store(Request $request)
    {
        $datos = [
            'p1'         => $request->p1,
            'p2'         => $request->p2,
            'p3'         => $request->p3,
            'p4'         => $request->p4,
            'p5'         => $request->p5,
            'p6'         => is_array($request->p6)
                                ? implode(', ', $request->p6)
                                : $request->p6,
            'p7'         => $request->p7,
            'p8'         => $request->p8,
            'p9'         => $request->p9,
            'p10'        => $request->p10,
            'p11'        => $request->p11,
            'p12'        => $request->p12,
            'p13'        => $request->p13,
            'p14'        => $request->p14,
            'p15'        => $request->p15,
            'p16'        => $request->p16,
            'p17'        => $request->p17,
            'p18'        => $request->p18,
            'p19'        => $request->p19,
            'p20'        => $request->p20,
            'usuario_id' => session('usuario_id'),
            'instancia'  => config('app.name'),
        ];

        $response = Http::withHeaders($this->headers())
            ->post($this->supabaseUrl(), $datos);

        if ($response->successful()) {
            // Guarda en sesión la fecha de respuesta para no volver a preguntar pronto
            session(['encuesta_respondida_en' => now()->toDateTimeString()]);

            return redirect('/dashboard')
                ->with('success', '¡Gracias por tu respuesta! Nos ayuda mucho a mejorar.');
        }

        return redirect()->back()
            ->with('error', 'Hubo un problema al enviar tu respuesta. Intenta de nuevo.');
    }

    private function yaRespondioReciente(): bool
    {
        $fecha = session('encuesta_respondida_en');

        if (!$fecha) return false;

        $dias = now()->diffInDays(\Carbon\Carbon::parse($fecha));

        return $dias < 0;
    }

    public function ignorar()
{
    // Pospone 30 días sin que haya respondido
    session(['encuesta_respondida_en' => now()->subDays(0)->toDateTimeString()]);
    return redirect()->back();
}
}