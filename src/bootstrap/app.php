<?php

use App\Console\Commands\VerificarRenovacaoMesaCommand;
use App\Console\Commands\VerificarSolicitacoesParadas;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request; // Importante
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // Deixamos 'web' nulo para controlar o carregamento manualmente.
        web: null,
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // --- ETAPA DE DEBUG ---
            $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? null;
            $centralDomains = config('multitenancy.central_domains', []);
            $centralDomainsString = implode(', ', $centralDomains);

            Log::info("[DEBUG] --- Nova Requisição ---");
            Log::info("[DEBUG] Host detectado: '{$host}'");
            Log::info("[DEBUG] Domínios Centrais: [{$centralDomainsString}]");

            // LÓGICA CORRIGIDA:
            // Um host é um tenant SE ele existir (não for null) E NÃO estiver
            // na lista de domínios centrais.
            if ($host && !in_array($host, $centralDomains)) {
                // É um TENANT
                Log::info("[DEBUG] DECISÃO: Carregando rotas de TENANT (tenant.php).");
                Route::middleware('tenant')
                    ->group(base_path('routes/tenant.php'));
            } else {
                // É CENTRAL (ou null)
                Log::info("[DEBUG] DECISÃO: Carregando rotas CENTRAIS (web.php).");
                Route::middleware('web')
                    ->group(base_path('routes/web.php'));
            }
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Confia no proxy reverso (NGINX, etc.)
        $middleware->trustProxies(
            '*',
            Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO
        );

        // Grupo 'web' (para domínios centrais)
        $middleware->appendToGroup('web', [
            'auth.session',
        ]);

        // Grupo 'tenant' (para domínios de tenant) com ORDEM CORRIGIDA
        $middleware->group('tenant', [
            // 1. Middlewares básicos (não dependem de sessão/DB)
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            
            // 2. ETAPA DE TENANT (ANTES DA SESSÃO)
            \Spatie\Multitenancy\Http\Middleware\NeedsTenant::class,
            \Spatie\Multitenancy\Http\Middleware\EnsureValidTenantSession::class,

            // 3. ETAPA DE SESSÃO E AUTH (DEPOIS DO TENANT)
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            'auth.session', 

            // 4. ETAPA DA APLICAÇÃO
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'needs_tenant' => \Spatie\Multitenancy\Http\Middleware\NeedsTenant::class,
        ]);
    })
    // --- BLOCO DE AGENDAMENTO ---
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command(VerificarSolicitacoesParadas::class)->dailyAt('09:00');
        $schedule->command(VerificarRenovacaoMesaCommand::class)->monthlyOn(1, '09:00');
    })
    // --- BLOCO DE EXCEÇÕES ---
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Spatie\Multitenancy\Exceptions\NoCurrentTenantException $e) {
            // Loga o erro antes de redirecionar para sabermos que aconteceu
            Log::warning("[WARN] NoCurrentTenantException capturada. Redirecionando para /");
            return redirect('/');
        });
    })
    ->create();
