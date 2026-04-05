<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    protected $middlewareGroups = [
        'web' => [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    protected $middlewareAliases = [
        'auth'         => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.session' => \App\Http\Middleware\AuthSession::class,
        'can'          => \Illuminate\Auth\Middleware\Authorize::class,
        'guest'        => \Illuminate\Auth\Middleware\RedirectIfUnauthenticated::class,
        'signed'       => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle'     => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified'     => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];
}
