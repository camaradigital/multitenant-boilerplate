<?php

use App\Console\Commands\VerificarRenovacaoMesaCommand;
use App\Console\Commands\VerificarSolicitacoesParadas;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
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
            $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? null;
            $centralDomains = config('multitenancy.central_domains', []);

            if ($host && !in_array($host, $centralDomains)) {
                Log::debug("[Routing] Host '{$host}' é tenant. Carregando routes/tenant.php.");
                Route::middleware('tenant')
                    ->group(base_path('routes/tenant.php'));
            } else {
                Log::debug("[Routing] Host '{$host}' é central. Carregando routes/web.php.");
                Route::middleware('web')
                    ->group(base_path('routes/web.php'));
            }
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(
            '*',
            Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO
        );

        $middleware->appendToGroup('web', [
            'auth.session',
        ]);

        $middleware->group('tenant', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            
            \Spatie\Multitenancy\Http\Middleware\NeedsTenant::class,
            \Spatie\Multitenancy\Http\Middleware\EnsureValidTenantSession::class,

            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            'auth.session', 

            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'needs_tenant' => \Spatie\Multitenancy\Http\Middleware\NeedsTenant::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command(VerificarSolicitacoesParadas::class)->dailyAt('09:00');
        $schedule->command(VerificarRenovacaoMesaCommand::class)->monthlyOn(1, '09:00');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Spatie\Multitenancy\Exceptions\NoCurrentTenantException $e, Request $request) {
            Log::warning("[Tenant] NoCurrentTenantException capturada. Redirecionando para /.", [
                'host' => $request->getHost(),
                'url' => $request->fullUrl()
            ]);
            return redirect('/');
        });
    })
    ->create();
