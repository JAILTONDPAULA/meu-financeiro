<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EnsureUserIsAuthenticated
{
    /**
     * Garante que a requisição pertence a uma sessão autenticada.
     *
     * API (/api/*) -> status HTTP + mensagem crua no corpo.
     * Web          -> redireciona para /login.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $autenticado = Auth::guard('web')->check();
        } catch (Throwable $e) {
            report($e);

            return $request->is('api/*')
                ? response('Não foi possível validar a sessão.', 500)
                : redirect()->guest(route('login'));
        }

        if ($autenticado) {
            return $next($request);
        }

        if ($request->is('api/*')) {
            return response('Não autenticado.', 401);
        }

        return redirect()->guest(route('login'));
    }
}
