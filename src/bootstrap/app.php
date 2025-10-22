<?php

use App\Console\Commands\VerificarRenovacaoMesaCommand;
use App\Console\Commands\VerificarSolicitacoesParadas;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\TrustProxies;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: null,
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // === DEBUG ADICIONAL v2 ===
            $serverHttpHost = $_SERVER['HTTP_HOST'] ?? 'N/A';
            $serverForwardedHost = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? 'N/A';
            $laravelForwardedHost = request()->header('X-Forwarded-Host');
            $laravelHostHeader = request()->header('Host');
            $laravelGetHost = request()->getHost();
            $ip = $_SERVER['REMOTE_ADDR'] ?? request()->ip();

            Log::info("--- [DEBUG-ROUTING START v2] ---");
            Log::info("[DEBUG-ROUTING] Request IP (REMOTE_ADDR): {$ip}");
            Log::info("[DEBUG-ROUTING] \$_SERVER['HTTP_HOST']: {$serverHttpHost}");
            Log::info("[DEBUG-ROUTING] \$_SERVER['HTTP_X_FORWARDED_HOST']: {$serverForwardedHost}");
            Log::info("[DEBUG-ROUTING] request()->header('X-Forwarded-Host'): " . ($laravelForwardedHost ?? 'N/A'));
            Log::info("[DEBUG-ROUTING] request()->header('Host'): " . ($laravelHostHeader ?? 'N/A'));
            Log::info("[DEBUG-ROUTING] request()->getHost(): {$laravelGetHost}"); 
            // === FIM DEBUG ADICIONAL v2 ===

            $hostParaVerificacao = $serverForwardedHost !== 'N/A' ? $serverForwardedHost : $serverHttpHost;

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
            Log::info("--- [DEBUG-ROUTING END v2] ---");
        }
    )
    ->withMiddleware(function (Middleware $middleware) {

        // --- Configuração do TrustProxies ---
        $middleware->trustProxies(
            '*', 
            Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO
        );


        // Define os middlewares para o grupo 'web' (Landlord/Central)
        $middleware->appendToGroup('web', [
             \Illuminate\Cookie\Middleware\EncryptCookies::class,
             \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
             \Illuminate\Session\Middleware\StartSession::class,
             'auth.session',
             \Illuminate\View\Middleware\ShareErrorsFromSession::class,
             \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
             \Illuminate\Routing\Middleware\SubstituteBindings::class,
             \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        $middleware->group('tenant', [
            // Middlewares essenciais para funcionamento web (sessão, cookies, CSRF, bindings)
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            'auth.session',
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,

            \Spatie\Multitenancy\Http\Middleware\NeedsTenant::class,
            \Spatie\Multitenancy\Http\Middleware\EnsureValidTenantSession::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'needs_tenant' => \Spatie\Multitenancy\Http\Middleware\NeedsTenant::class,
        ]);
    })
    // --- BLOCO DE AGENDAMENTO ---
    ->withSchedule(function (Schedule $schedule) {
        // Roda o comando `app:verificar-solicitacoes-paradas` todos os dias às 09:00.
        $schedule->command(VerificarSolicitacoesParadas::class)->dailyAt('09:00');
        // Verifica a renovação da Mesa Diretora no primeiro dia de cada mês, às 9h da manhã.
        $schedule->command(VerificarRenovacaoMesaCommand::class)->monthlyOn(1, '09:00');
    })
    // --- BLOCO DE EXCEÇÕES ---
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Spatie\Multitenancy\Exceptions\NoCurrentTenantException $e) {
            Log::warning('[DEBUG-EXCEPTION] NoCurrentTenantException capturada. Redirecionando para /');
            return redirect('/');
        });
    })
    ->create();
