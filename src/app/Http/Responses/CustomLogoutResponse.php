<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Spatie\Multitenancy\Models\Tenant;
use Symfony\Component\HttpFoundation\Response;

class CustomLogoutResponse implements LogoutResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toResponse($request): Response
    {
        // Log inicial para rastrear o início do logout
        Log::debug('Iniciando logout response');

        // Força o guard baseado em tenant atual
        $guard = Tenant::current() ? 'tenant' : 'web';

        // Log do guard detectado
        Log::debug('Guard detectado para logout: '.$guard);

        // Executa o logout no guard correto
        Auth::guard($guard)->logout();

        // Log após logout
        Log::debug('Logout executado no guard: '.$guard);

        // Remove tenant_id da sessão (se tenant)
        if (Tenant::current()) {
            $request->session()->forget('tenant_id');
            Log::debug('tenant_id removido da sessão');
        }

        // Invalida a sessão e regenera o token para segurança
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Log após invalidação
        Log::debug('Sessão invalidada e token regenerado');

        if ($request->wantsJson()) {
            Log::debug('Resposta JSON retornada para logout');

            return new JsonResponse('', 204);
        }

        // Log antes do redirect
        Log::debug('Redirecionando para raiz após logout');

        // Redireciona para a raiz do domínio atual (que será a página de login do tenant)
        return redirect('/');
    }
}
