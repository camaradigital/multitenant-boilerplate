<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TrustProxies extends Middleware
{
    /**
     * Os proxies confiáveis para esta aplicação.
     * '*' significa confiar em todos os proxies, essencial em ambientes de nuvem (DigitalOcean, Cloudflare).
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = '*';

    /**
     * Os headers que devem ser usados para detectar proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;

    /**
     * Este método foi customizado para **forçar a leitura do Host Real** usando cabeçalhos de proxy.
     * Ele é executado antes do Multitenancy e resolve inconsistências onde o $request->getHost()
     * retorna intermitentemente IPs internos ou domínios de proxy.
     */
    public function handle($request, \Closure $next)
    {
        // 1. Tenta obter o Host real do cabeçalho de proxy (Cloudflare/DigitalOcean)
        $forwardedHost = $request->header('X-Forwarded-Host');
        
        // 2. Se o cabeçalho existe, e não é um IP local de fallback, sobrescrevemos o Host.
        if ($forwardedHost && !Str::contains($forwardedHost, ['127.0.0.1', '10.'])) {
            
            // Sobrescreve as variáveis de servidor (SERVER) que o Laravel usa para getHost()
            $request->headers->set('host', $forwardedHost);
            $request->server->set('HTTP_HOST', $forwardedHost);
            $request->server->set('SERVER_NAME', $forwardedHost);

            Log::debug("[TRUST-PROXY] Host forçado via X-Forwarded-Host: {$forwardedHost}");

        } else {
             // 3. Fallback: Se o cabeçalho não veio, tenta usar o Host configurado no APP_URL
            $appUrlHost = parse_url(config('app.url'), PHP_URL_HOST);
            
            // Apenas força se o Host atual for diferente do Host configurado (para evitar loops ou erros)
            if ($appUrlHost && $request->getHost() !== $appUrlHost) {
                 $request->headers->set('host', $appUrlHost);
                 $request->server->set('HTTP_HOST', $appUrlHost);
                 $request->server->set('SERVER_NAME', $appUrlHost);
                 
                 Log::debug("[TRUST-PROXY] Host forçado via APP_URL fallback: {$appUrlHost}");
            }
        }

        // Continua com a execução padrão do Middleware (incluindo o TrustProxies herdado)
        return parent::handle($request, $next);
    }
}
