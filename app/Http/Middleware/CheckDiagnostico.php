<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckDiagnostico
{
    /**
     * Redirige al diagnóstico inicial si el usuario aún no lo ha completado.
     * Solo aplica al dashboard para no interrumpir otras rutas.
     */
    public function handle(Request $request, Closure $next)
    {
        $uid = session('usuario_id');

        if (!$uid) {
            return $next($request);
        }

        $usuario = DB::table('usuarios')->where('id', $uid)->first();

        if ($usuario && !$usuario->diagnostico_completado) {
            return redirect()->route('diagnostico.show');
        }

        return $next($request);
    }
}