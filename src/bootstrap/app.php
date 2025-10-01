<?php

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
            $host = request()->getHost();

            if (in_array($host, config('multitenancy.central_domains', []))) {
                // Domínio CENTRAL: Carrega APENAS as rotas de 'web.php'
                // com o grupo de middleware 'web' padrão.
                Log::info("[DEBUG] Host '{$host}' é central. Carregando rotas de web.php.");
                Route::middleware('web')
                    ->group(base_path('routes/web.php'));
            } else {
                // Domínio de TENANT: Carrega APENAS as rotas de 'tenant.php'
                // com o grupo de middleware 'tenant' customizado.
                Log::info("[DEBUG] Carregando rotas de tenant para host '{$host}'.");
                Route::middleware('tenant')
                    ->group(base_path('routes/tenant.php'));
            }
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
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Spatie\Multitenancy\Exceptions\NoCurrentTenantException $e) {
            return redirect('/');
        });
    })
    ->create();
