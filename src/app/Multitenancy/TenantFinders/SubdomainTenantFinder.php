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
        // 1. Obtém o Host da requisição, priorizando o cabeçalho X-Forwarded-Host para ambientes de proxy.
        // Se o TrustProxies já corrigiu, $request->getHost() deve estar correto, mas este fallback 
        // é mantido como segurança máxima para consistência.
        $host = $request->header('X-Forwarded-Host') ?? $request->getHost();
        
        Log::info("[DEBUG-TENANT] SubdomainTenantFinder: Host avaliado '{$host}'.");

        // Obtém a lista de domínios centrais do arquivo de configuração (config/multitenancy.php)
        $centralDomains = config('multitenancy.central_domains', []);
        
        // 2. Verifica se o Host é um domínio central exato
        if (in_array($host, $centralDomains)) {
            Log::info("[DEBUG-TENANT] SubdomainTenantFinder: Host '{$host}' é um domínio central. Nenhum tenant será usado.");
            return null;
        }

        $subdomain = null;

        // 3. Itera sobre os domínios centrais para extrair o subdomínio
        foreach ($centralDomains as $centralDomain) {
            // Cria o sufixo que esperamos encontrar (ex: ".consultafacilweb.online")
            $suffix = '.' . $centralDomain;

            if (Str::endsWith($host, $suffix)) {
                // Remove o sufixo para isolar o subdomínio (ex: 'cmsm.consultafacilweb.online' -> 'cmsm')
                $subdomain = Str::beforeLast($host, $suffix);
                break; // Subdomínio encontrado, interrompe o loop
            }
        }
        
        // 4. Checa se um subdomínio foi extraído e se ele não está vazio
        if (empty($subdomain) || $subdomain === $host) {
            Log::info("[DEBUG-TENANT] SubdomainTenantFinder: Não foi possível extrair um subdomínio válido do host '{$host}'.");
            return null;
        }
        
        Log::info("[DEBUG-TENANT] SubdomainTenantFinder: Subdomínio extraído '{$subdomain}'.");

        // 5. Busca no Banco de Dados Central
        $tenant = TenantModel::where('subdomain', $subdomain)->first();

        // 6. Log do resultado
        if ($tenant) {
            Log::critical("[DEBUG-TENANT] Tenant ID '{$tenant->id}' ENCONTRADO com SUCESSO para o subdomínio '{$subdomain}'.");
        } else {
            Log::error("[DEBUG-TENANT] FALHA: Nenhum tenant encontrado para o subdomínio '{$subdomain}'. Verifique a tabela de tenants.");
        }

        return $tenant;
    }
}
