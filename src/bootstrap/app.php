<?php

use App\Console\Commands\VerificarRenovacaoMesaCommand;
use App\Console\Commands\VerificarSolicitacoesParadas;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // Define 'web' como nulo para que possamos controlar manualmente o carregamento das rotas.
        // Isso é essencial para separar os contextos de landlord (central) e tenant.
        web: null,
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Executa após o registro básico das rotas.
            $host = request()->getHost();

            if (in_array($host, config('multitenancy.central_domains', []))) {
                // Se for um domínio central, carrega apenas as rotas de 'web.php'
                // com o grupo de middleware 'web' padrão.
                Log::info("[Routing] Host '{$host}' é central. Carregando 'routes/web.php'.");
                Route::middleware('web')
                    ->group(base_path('routes/web.php'));
            } else {
                // Se for um domínio de tenant, carrega apenas as rotas de 'tenant.php'
                // com o grupo de middleware 'tenant' customizado.
                Log::info("[Routing] Host '{$host}' é de tenant. Carregando 'routes/tenant.php'.");
                Route::middleware('tenant')
                    ->group(base_path('routes/tenant.php'));
            }
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');

        // Garante que middlewares importantes estejam no grupo 'web'.
        $middleware->appendToGroup('web', [
            'auth.session',
        ]);

        // Define o grupo de middleware 'tenant' com a ordem correta.
        $middleware->group('tenant', [
            // Middlewares essenciais para sessão, cookies, etc.
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            'auth.session',
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,

            // Middlewares do Spatie para a lógica multi-tenant
            \Spatie\Multitenancy\Http\Middleware\NeedsTenant::class,
            \Spatie\Multitenancy\Http\Middleware\EnsureValidTenantSession::class,
            \App\Http\Middleware\HandleInertiaRequests::class, // Se você usa Inertia
        ]);

        // Define aliases de middleware.
        $middleware->alias([
            'needs_tenant' => \Spatie\Multitenancy\Http\Middleware\NeedsTenant::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        // Agenda os comandos da aplicação.
        $schedule->command(VerificarSolicitacoesParadas::class)->dailyAt('09:00');
        $schedule->command(VerificarRenovacaoMesaCommand::class)->monthlyOn(1, '09:00');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Adiciona um handler de exceção para quando um tenant não é encontrado.
        $exceptions->render(function (\Spatie\Multitenancy\Exceptions\NoCurrentTenantException $e) {
            return redirect('/');
        });
    })
    ->create();
