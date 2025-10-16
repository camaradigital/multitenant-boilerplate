<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EnsureTenantConnection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Verifica se o tenant foi inicializado pelo pacote de tenancy
        // tenancy()->initialized é o método chave aqui.
        if (!tenancy()->initialized) {
            // Se o tenant NÃO foi inicializado (ex: domínio não encontrado),
            // é crucial parar a requisição aqui. Você pode retornar um 404
            // ou uma view customizada.
            // Isso evita que a aplicação prossiga usando a conexão 'central'.
            throw new TenantCouldNotBeIdentifiedException($request->getHost());
        }

        try {
            // Força uma tentativa de conexão para validar as credenciais e a disponibilidade
            // A consulta 'select 1' é extremamente leve e ideal para um "ping".
            DB::connection('tenant')->reconnect();
            DB::connection('tenant')->getPdo();

            // Se o comando acima não lançar uma exceção, a conexão está OK.
        } catch (Throwable $e) {
            // Se a conexão com o banco do TENANT falhar (timeout, credenciais erradas, etc.)
            // retornamos um erro de serviço indisponível.
            // Isso previne que a aplicação tente usar a conexão 'central' como fallback.
            report($e); // Opcional: logar o erro
            abort(503, 'Serviço temporariamente indisponível. Falha na conexão com o banco de dados do cliente.');
        }

        return $next($request);
    }
}
