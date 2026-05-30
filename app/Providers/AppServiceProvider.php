<?php

namespace App\Providers;

use App\Models\LineaProductiva;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Forzar HTTPS en producción (Railway termina SSL en el proxy)
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

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