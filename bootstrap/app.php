<?php

use App\Http\Middleware\EnsureUserIsAuthenticated;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\Middleware\StartSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // A API compartilha a MESMA sessão do front-end: quem loga em /api/login
        // fica autenticado nas rotas web, e vice-versa.
        $middleware->api(prepend: [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
        ]);

        $middleware->alias([
            'authenticated' => EnsureUserIsAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
