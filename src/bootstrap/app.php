<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
        $host = request()->getHost();
        if (! in_array($host, config('multitenancy.central_domains', []))) {
            Log::info("[DEBUG] Carregando rotas de tenant para host '{$host}'.");
            Route::middleware('tenant')
                ->group(base_path('routes/tenant.php'));
        } else {
            Log::info("[DEBUG] Host '{$host}' é central; rotas de tenant NÃO carregadas.");
        }
    }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->group('tenant', [
            'web',
            \Spatie\Multitenancy\Http\Middleware\NeedsTenant::class,
            \Spatie\Multitenancy\Http\Middleware\EnsureValidTenantSession::class,
        ]);
        $middleware->alias([
            'needs_tenant' => \Spatie\Multitenancy\Http\Middleware\NeedsTenant::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (\Spatie\Multitenancy\Exceptions\NoCurrentTenantException $e) {
        return redirect('/');  // Redireciona para central ou erro 404
    });
})
    ->create();
