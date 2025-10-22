<?php

use App\Console\Commands\VerificarRenovacaoMesaCommand;
use App\Console\Commands\VerificarSolicitacoesParadas;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\TrustProxies; // <-- Importante para configuração de proxy
use Illuminate\Http\Request; // <-- Importante para constantes de header
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
            // === DEBUG ADICIONAL v2 ===
            // Use $_SERVER diretamente para capturar os headers crus o mais cedo possível
            $serverHttpHost = $_SERVER['HTTP_HOST'] ?? 'N/A';
            $serverForwardedHost = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? 'N/A';
            // Para comparação, pegue também os valores que o objeto Request do Laravel interpreta (após TrustProxies ter sido configurado)
            $laravelForwardedHost = request()->header('X-Forwarded-Host');
            $laravelHostHeader = request()->header('Host'); // Header Host original recebido
            $laravelGetHost = request()->getHost(); // O que o Laravel pensa que é o host após processamento
            $ip = $_SERVER['REMOTE_ADDR'] ?? request()->ip(); // IP do cliente (pode ser o IP do proxy se TrustProxies não está configurado)

            Log::info("--- [DEBUG-ROUTING START v2] ---");
            Log::info("[DEBUG-ROUTING] Request IP (REMOTE_ADDR): {$ip}");
            Log::info("[DEBUG-ROUTING] \$_SERVER['HTTP_HOST']: {$serverHttpHost}"); // Geralmente o domínio base atrás de proxy
            Log::info("[DEBUG-ROUTING] \$_SERVER['HTTP_X_FORWARDED_HOST']: {$serverForwardedHost}"); // O domínio que o usuário digitou
            Log::info("[DEBUG-ROUTING] request()->header('X-Forwarded-Host'): " . ($laravelForwardedHost ?? 'N/A')); // O que o Laravel lê do header
            Log::info("[DEBUG-ROUTING] request()->header('Host'): " . ($laravelHostHeader ?? 'N/A')); // Header Host original
            Log::info("[DEBUG-ROUTING] request()->getHost(): {$laravelGetHost}"); // O que o Laravel usa por padrão se não confia no proxy
            // === FIM DEBUG ADICIONAL v2 ===

            // Lógica principal de decisão:
            // 1. Prioriza o header X-Forwarded-Host lido diretamente do $_SERVER, pois é o mais confiável atrás de um proxy configurado corretamente.
            $hostParaVerificacao = $serverForwardedHost !== 'N/A' ? $serverForwardedHost : $serverHttpHost;

            // Log da decisão final
            Log::info("[DEBUG-ROUTING] Host Usado para Verificação: {$hostParaVerificacao}");

            $centralDomains = config('multitenancy.central_domains', []);
            Log::info("[DEBUG-ROUTING] Domínios Centrais Configurados: " . implode(', ', $centralDomains));

            // Comparação
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
        // Configura globalmente para confiar em headers X-Forwarded-* de qualquer proxy.
        // Essencial para obter o Host e IP corretos atrás do Nginx do Cleavr.
        $middleware->trustProxies(
            proxies: '*', // Confia em qualquer proxy (adequado para DO + Cleavr)
            headers: Request::HEADER_X_FORWARDED_FOR |
                     Request::HEADER_X_FORWARDED_HOST |
                     Request::HEADER_X_FORWARDED_PORT |
                     Request::HEADER_X_FORWARDED_PROTO
                     // Request::HEADER_X_FORWARDED_AWS_ELB // Remova se não estiver usando AWS ELB
        );
        // --- Fim da Configuração do TrustProxies ---


        // Define os middlewares para o grupo 'web' (Landlord/Central)
        $middleware->appendToGroup('web', [
             \Illuminate\Cookie\Middleware\EncryptCookies::class,
             \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
             \Illuminate\Session\Middleware\StartSession::class,
             'auth.session', // Alias do Fortify/Jetstream para autenticação de sessão web
             \Illuminate\View\Middleware\ShareErrorsFromSession::class,
             \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
             \Illuminate\Routing\Middleware\SubstituteBindings::class,
             \App\Http\Middleware\HandleInertiaRequests::class, // Necessário se o landlord usa Inertia.js
        ]);

        // Define o grupo de middleware 'tenant' com a ordem explícita e correta.
        $middleware->group('tenant', [
            // Middlewares essenciais para funcionamento web (sessão, cookies, CSRF, bindings)
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            'auth.session', // Alias do Fortify/Jetstream para autenticação de sessão tenant
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,

            // Middlewares específicos para a lógica multi-tenant (após os de sessão/auth)
            // NeedsTenant tentará encontrar o tenant baseado no SubdomainTenantFinder
            \Spatie\Multitenancy\Http\Middleware\NeedsTenant::class,
            // EnsureValidTenantSession garante que a sessão pertence ao tenant atual
            \Spatie\Multitenancy\Http\Middleware\EnsureValidTenantSession::class,
            // HandleInertiaRequests configura o Inertia.js para o tenant
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        // Mantém os aliases necessários.
        $middleware->alias([
            'needs_tenant' => \Spatie\Multitenancy\Http\Middleware\NeedsTenant::class,
            // Adicione outros aliases personalizados se você os utiliza nas rotas
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
        // Handler para a exceção específica da Spatie quando nenhum tenant é encontrado
        $exceptions->render(function (\Spatie\Multitenancy\Exceptions\NoCurrentTenantException $e) {
             // Em caso de erro ao encontrar tenant (ex: subdomínio inválido após a verificação inicial),
             // redireciona para a home central para evitar erros genéricos.
            Log::warning('[DEBUG-EXCEPTION] NoCurrentTenantException capturada. Redirecionando para /');
            return redirect('/');
        });
        // Você pode adicionar outros handlers de exceção aqui, se necessário
        // Exemplo:
        // $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
        //     if ($request->is('api/*')) {
        //         return response()->json(['message' => 'Not Found.'], 404);
        //     }
        // });
    })
    ->create();
