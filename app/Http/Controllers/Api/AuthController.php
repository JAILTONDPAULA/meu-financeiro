<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Throwable;

class AuthController extends Controller
{
    /**
     * Autentica o usuário e grava o ID dele na sessão.
     * A mesma sessão é enxergada pelas rotas web.
     */
    public function login(Request $request): Response
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ], [
                'email.required' => 'Informe o e-mail.',
                'email.email' => 'Informe um e-mail válido.',
                'password.required' => 'Informe a senha.',
                'password.string' => 'Senha inválida.',
            ]);

            if ($validator->fails()) {
                return response($validator->errors()->first(), 422);
            }

            if (! Auth::guard('web')->attempt($validator->validated(), $request->boolean('remember'))) {
                return response('Credenciais inválidas.', 401);
            }

            // Novo ID de sessão mantendo os dados: evita session fixation.
            $request->session()->regenerate();

            return response('Autenticado com sucesso.', 200);
        } catch (Throwable $e) {
            report($e);

            return response('Não foi possível autenticar. Tente novamente.', 500);
        }
    }

    /**
     * Encerra a sessão do usuário.
     */
    public function logout(Request $request): Response
    {
        try {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response('Sessão encerrada.', 200);
        } catch (Throwable $e) {
            report($e);

            return response('Não foi possível encerrar a sessão.', 500);
        }
    }

    /**
     * Devolve o usuário da sessão atual. Serve para testar a autenticação.
     */
    public function me(Request $request): Response|JsonResponse
    {
        try {
            return response()->json($request->user());
        } catch (Throwable $e) {
            report($e);

            return response('Não foi possível carregar o usuário.', 500);
        }
    }
}
