<?php

namespace App\Providers;

use App\Models\LineaProductiva;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    /**
     * Bootstrapping de la app.
     *
     * Aquí registramos un view composer GLOBAL que comparte con todas
     * las vistas el array `$lineasActivas` (códigos de las líneas
     * productivas del usuario logueado). Así, en cualquier .blade.php
     * podemos hacer:
     *
     *   @if(in_array('bovino', $lineasActivas)) ... @endif
     *
     * sin tocar ningún controlador. Si no hay sesión, $lineasActivas
     * llega como array vacío.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $lineasActivas = [];
            try {
                if (session('usuario_id')) {
                    $lineasActivas = LineaProductiva::activasDelUsuario(session('usuario_id'));
                }
            } catch (\Throwable $e) {
                $lineasActivas = [];
            }

            $view->with('lineasActivas', $lineasActivas);
        });
    }
}