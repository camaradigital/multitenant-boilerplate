<?php

use App\Console\Commands\VerificarRenovacaoMesaCommand;
use App\Console\Commands\VerificarSolicitacoesParadas;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request; // <-- ADICIONADO (do seu segundo arquivo)
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
            // MÉTODO SEGURO (do seu primeiro arquivo):
            // Usa o helper 'request()' que respeita os 'trustProxies'.
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
        // ADICIONADO (do seu segundo arquivo):
        // Confia no proxy reverso (NGINX, etc.) para obter o host correto.
        // Isso é essencial para 'request()->getHost()' funcionar.
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
            // Identifica o tenant e troca a conexão do DB
            \Spatie\Multitenancy\Http\Middleware\NeedsTenant::class,
            // Garante que a sessão (se houver) pertence a este tenant
            \Spatie\Multitenancy\Http\Middleware\EnsureValidTenantSession::class,

            // 3. ETAPA DE SESSÃO E AUTH (DEPOIS DO TENANT)
            // Inicia a sessão (agora usando a conexão DB do tenant)
            \Illuminate\Session\Middleware\StartSession::class,
            // Compartilha erros da sessão
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            // Verifica o token CSRF (depende da sessão)
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            // Carrega a sessão de autenticação
            'auth.session', 

            // 4. ETAPA DA APLICAÇÃO
            // Middleware do Inertia (depende da sessão/auth)
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
        // Verifica a renovação da Mesa Diretora no primeiro dia de cada mês, às 9h da manhã.
        $schedule->command(VerificarRenovacaoMesaCommand::class)->monthlyOn(1, '09:00');
    })
    // --- BLOCO DE EXCEÇÕES ---
    ->withExceptions(function (Exceptions $exceptions) {
        // Isso captura 'NoCurrentTenantException' e previne o loop de redirect
        $exceptions->render(function (\Spatie\Multitenancy\Exceptions\NoCurrentTenantException $e) {
            return redirect('/');
        });
    })
    ->create();
