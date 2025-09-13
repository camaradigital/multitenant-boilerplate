<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Multitenancy\Models\Tenant;
use Symfony\Component\HttpFoundation\Response;

class SetTenantAuthGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Se a requisição for para um tenant, define o guarda de autenticação
        // padrão da aplicação como 'tenant'. Isso garante que o middleware 'auth'
        // verifique a sessão do guarda correto.
        if (Tenant::current()) {
            config(['auth.defaults.guard' => 'tenant']);
        }

        return $next($request);
    }
}
