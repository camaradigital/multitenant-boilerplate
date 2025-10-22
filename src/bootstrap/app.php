<?php

use App\Console\Commands\VerificarRenovacaoMesaCommand; // <-- ADICIONADO
use App\Console\Commands\VerificarSolicitacoesParadas; // <-- ADICIONADO
use Illuminate\Console\Scheduling\Schedule; // <-- ADICIONADO
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // Deixamos 'web' nulo para controlar o carregamento manualmente.
        // Isto é crucial para separar os contextos central e de tenant.
        web: null,
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
    // === DEBUG ADICIONAL ===
    $forwardedHost = request()->header('X-Forwarded-Host');
    $hostHeader = request()->header('Host'); // Pega o header Host original
    $getHost = request()->getHost(); // O que o Laravel pensa que é o host
    $ip = request()->ip(); // IP que o Laravel vê

    Log::info("--- [DEBUG-ROUTING START] ---");
    Log::info("[DEBUG-ROUTING] Request IP: {$ip}");
    Log::info("[DEBUG-ROUTING] X-Forwarded-Host Header: " . ($forwardedHost ?? 'N/A'));
    Log::info("[DEBUG-ROUTING] Host Header: " . ($hostHeader ?? 'N/A'));
    Log::info("[DEBUG-ROUTING] request()->getHost(): {$getHost}");
    // === FIM DEBUG ADICIONAL ===

    // Lógica principal (mantém a correção anterior)
    $hostParaVerificacao = $forwardedHost ?? $getHost;
    Log::info("[DEBUG-ROUTING] Host Usado para Verificação: {$hostParaVerificacao}");

    $centralDomains = config('multitenancy.central_domains', []);
    Log::info("[DEBUG-ROUTING] Domínios Centrais Configurados: " . implode(', ', $centralDomains));

    if (in_array($hostParaVerificacao, $centralDomains)) {
        Log::info("[DEBUG-ROUTING] DECISÃO: Host '{$hostParaVerificacao}' É central. Carregando web.php.");
        Route::middleware('web')
            ->group(base_path('routes/web.php'));
    } else {
        Log::info("[DEBUG-ROUTING] DECISÃO: Host '{$hostParaVerificacao}' NÃO é central. Carregando tenant.php.");
        Route::middleware('tenant')
            ->group(base_path('routes/tenant.php'));
    }
    Log::info("--- [DEBUG-ROUTING END] ---");
}
    )
    ->withMiddleware(function (Middleware $middleware) {
        // CORREÇÃO 1: Adiciona o middleware do Fortify ao grupo 'web' usando seu alias.
        $middleware->appendToGroup('web', [
            'auth.session',
        ]);

        // Define o grupo de middleware 'tenant' com a ordem explícita e correta.
        $middleware->group('tenant', [
            // Middlewares padrão do grupo 'web' para sessão, cookies, etc.
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,

            // CORREÇÃO 2: Adiciona o middleware de autenticação de sessão usando seu alias.
            'auth.session',

            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,

            // Middlewares específicos para a lógica multi-tenant
            \Spatie\Multitenancy\Http\Middleware\NeedsTenant::class,
            \Spatie\Multitenancy\Http\Middleware\EnsureValidTenantSession::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        // Mantém os aliases necessários.
        $middleware->alias([
            'needs_tenant' => \Spatie\Multitenancy\Http\Middleware\NeedsTenant::class,
        ]);
    })
    // --- BLOCO DE AGENDAMENTO ---
    ->withSchedule(function (Schedule $schedule) {
        // Roda o comando `app:verificar-solicitacoes-paradas` todos os dias às 09:00.
        $schedule->command(VerificarSolicitacoesParadas::class)->dailyAt('09:00');
        // [NOVO] Verifica a renovação da Mesa Diretora no primeiro dia de cada mês, às 9h da manhã.
        $schedule->command(VerificarRenovacaoMesaCommand::class)->monthlyOn(1, '09:00');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Spatie\Multitenancy\Exceptions\NoCurrentTenantException $e) {
            return redirect('/');
        });
    })
    ->create();
