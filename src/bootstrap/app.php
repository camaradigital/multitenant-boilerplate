<?php

use App\Console\Commands\VerificarRenovacaoMesaCommand;
use App\Console\Commands\VerificarSolicitacoesParadas;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

// Função auxiliar para obter o Host real (ajuda a resolver a intermitência na App Platform)
if (!function_exists('get_real_host')) {
    function get_real_host(): string
    {
        $request = request();
        // 1. Prioriza o cabeçalho X-Forwarded-Host (o host real enviado pelo Cloudflare/DigitalOcean)
        $forwardedHost = $request->header('X-Forwarded-Host');

        if ($forwardedHost) {
            return $forwardedHost;
        }

        // 2. Fallback para o host padrão
        return $request->getHost();
    }
}


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // Deixamos 'web' nulo para controlar o carregamento manualmente.
        // Isto é crucial para separar os contextos central e de tenant.
        web: null,
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Usa a função auxiliar para garantir que o host correto seja lido
            $host = get_real_host();

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
        // O TrustProxies ainda é importante, mas o Host já foi resolvido acima para o roteamento.
        $middleware->trustProxies(at: '*');
        
        // Adiciona o middleware do Fortify ao grupo 'web' usando seu alias.
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
            \App\Http\Middleware\EnsureTenantConnection::class,
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
