<?php

namespace App\Multitenancy\TenantFinders;

use App\Models\Central\Tenant as TenantModel; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str; 
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

class SubdomainTenantFinder extends TenantFinder
{
    public function findForRequest(Request $request): ?IsTenant
    {
        // 1. Obter host da requisição e normalizar
        $hostRaw = $request->header('X-Forwarded-Host') ?? $request->getHost();
        $host = strtolower(explode(':', $hostRaw)[0]); // minusculas + remove porta
        $ip = $request->ip();

        Log::info("[DEBUG-TENANT] Host bruto: '{$hostRaw}', normalizado: '{$host}', IP: {$ip}");

        // 2. Lista de domínios centrais (landlord)
        $centralDomains = config('multitenancy.central_domains', []);

        // 3. Verifica se é domínio central exato
        if (in_array($host, $centralDomains)) {
            Log::info("[DEBUG-TENANT] Host '{$host}' é um domínio central. Nenhum tenant será usado.");
            return null;
        }

        // 4. Extrair subdomínio
        $subdomain = null;
        foreach ($centralDomains as $centralDomain) {
            $suffix = '.' . $centralDomain;
            if (Str::endsWith($host, $suffix)) {
                $subdomain = Str::beforeLast($host, $suffix);
                break; 
            }
        }

        if (empty($subdomain) || $subdomain === $host) {
            Log::warning("[DEBUG-TENANT] Não foi possível extrair um subdomínio válido do host '{$host}'.");
            abort(404, "Tenant não encontrado para '{$host}'.");
        }

        Log::info("[DEBUG-TENANT] Subdomínio extraído '{$subdomain}'.");

        // 5. Buscar tenant no banco
        $tenant = TenantModel::where('subdomain', $subdomain)->first();

        if (!$tenant) {
            Log::error("[DEBUG-TENANT] FALHA: Subdomínio '{$subdomain}' extraído, mas nenhum tenant encontrado no banco. Abortando.");
            abort(404, "Tenant para o subdomínio '{$subdomain}' não encontrado.");
        }

        // 6. Log do resultado final
        Log::info("[DEBUG-TENANT] Tenant ID '{$tenant->id}' ENCONTRADO com sucesso para subdomínio '{$subdomain}', IP: {$ip}.");

        return $tenant;
    }
}
