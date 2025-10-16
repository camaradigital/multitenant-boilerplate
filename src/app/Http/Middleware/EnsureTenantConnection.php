<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Multitenancy\Facades\Tenancy;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EnsureTenantConnection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // O middleware 'NeedsTenant' já deve ter sido executado.
        // Verificamos se o tenant atual foi definido com sucesso.
        if (! Tenancy::current()) {
            // Este cenário não deveria acontecer se o middleware estiver na ordem correta,
            // mas é uma proteção extra. Indica um problema de configuração.
            abort(500, 'O contexto do tenant é necessário, mas não foi encontrado.');
        }

        try {
            // O pacote da Spatie troca a conexão PADRÃO dinamicamente.
            // Então, testamos a conexão padrão, que neste ponto DEVE ser a do tenant.
            // O 'getPdo()' força uma conexão real.
            DB::connection('tenant')->getPdo();

        } catch (Throwable $e) {
            // Se a conexão falhar (timeout, credenciais erradas, etc.),
            // logamos o erro e interrompemos a requisição com um erro 503.
            // ISSO É O QUE IMPEDE O FALLBACK PARA A CONEXÃO LANDLORD! 🛡️
            report($e);
            abort(503, 'Serviço temporariamente indisponível devido a um problema na conexão com o banco de dados.');
        }

        return $next($request);
    }
}
