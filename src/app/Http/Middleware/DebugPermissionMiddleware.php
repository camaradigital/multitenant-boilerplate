<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DebugPermissionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Verifica se há um usuário autenticado antes de tentar acessar suas propriedades
        if (Auth::check()) {
            $user = Auth::user();
            $guard = Auth::getDefaultDriver();

            Log::info('====================== INÍCIO DEBUG PERMISSÃO ======================');
            Log::info("Middleware 'DebugPermissionMiddleware' ATIVADO.");
            Log::info("Usuário Autenticado: " . $user->email . " (ID: " . $user->id . ")");
            Log::info("Guarda de Autenticação Padrão: " . $guard);

            // Verifica as roles do usuário no guard padrão
            Log::info("Papéis (Roles) no guard '{$guard}': " . json_encode($user->getRoleNames()));

            // Força a verificação da permissão usando o guard 'tenant' e loga o resultado
            $canView = $user->hasPermissionTo('ver solicitacoes', 'tenant');
            Log::info("Checando permissão 'ver solicitacoes' no guard 'tenant': " . ($canView ? 'SIM, POSSUI PERMISSÃO' : 'NÃO, PERMISSÃO NEGADA'));
            Log::info('======================= FIM DEBUG PERMISSÃO =======================');
        } else {
            Log::warning('====================== ALERTA DEBUG PERMISSÃO ======================');
            Log::warning("Middleware 'DebugPermissionMiddleware' ATIVADO, mas NENHUM usuário está autenticado.");
            Log::warning('===================================================================');
        }

        return $next($request);
    }
}
