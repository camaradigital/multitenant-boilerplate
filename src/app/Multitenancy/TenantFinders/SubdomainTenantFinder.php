<?php

namespace App\Multitenancy\TenantFinders;

// Importamos a classe TenantModel para uso direto, assumindo que ela está em App\Models\Central\Tenant
use App\Models\Central\Tenant as TenantModel; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str; // Necessário para a extração robusta do subdomínio
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

class SubdomainTenantFinder extends TenantFinder
{
    public function findForRequest(Request $request): ?IsTenant
    {
        // SOLUÇÃO CRÍTICA PARA NUVEM (DigitalOcean/Cloudflare):
        // Prioriza o cabeçalho X-Forwarded-Host, que contém o nome de domínio real
        // digitado pelo usuário, resolvendo a intermitência de host de proxy.
        $host = $request->header('X-Forwarded-Host') ?? $request->getHost();
        
        Log::info("[DEBUG-TENANT] SubdomainTenantFinder: Host REAL verificado '{$host}'.");

        // 1. Verificar se é um domínio central (Landlord)
        if (in_array($host, config('multitenancy.central_domains', []))) {
            Log::info("[DEBUG-TENANT] SubdomainTenantFinder: Host '{$host}' é um domínio central. Nenhum tenant será usado.");
            return null;
        }

        // 2. Extração robusta do subdomínio
        $subdomain = $host;
        $found = false;

        // Itera sobre todos os domínios centrais configurados
        $centralDomains = config('multitenancy.central_domains', []);
        
        foreach ($centralDomains as $centralDomain) {
            // Verifica se o host termina com um domínio central, incluindo o ponto
            // Ex: 'cmsm.consultafacilweb.online' termina com '.consultafacilweb.online'
            if (Str::endsWith($host, '.' . $centralDomain)) {
                // Remove o domínio central (e o ponto que o precede), deixando apenas o subdomínio
                $subdomain = Str::beforeLast($host, '.' . $centralDomain);
                $found = true;
                break; // Encontramos, paramos a iteração
            }
        }
        
        // Se a busca falhou ou resultou em uma string vazia (ex: se o host fosse o próprio central domain)
        if (!$found || $subdomain === '' || $subdomain === $host) {
            Log::info("[DEBUG-TENANT] SubdomainTenantFinder: Não foi possível extrair um subdomínio válido do host '{$host}'.");
            return null;
        }
        
        Log::info("[DEBUG-TENANT] SubdomainTenantFinder: Subdomínio extraído '{$subdomain}'.");

        // 3. Busca no Banco de Dados Central
        $tenant = TenantModel::where('subdomain', $subdomain)->first();

        // 4. Log do resultado da busca
        if ($tenant) {
            // Usamos Log::critical/Log::error para garantir que o log apareça mesmo em níveis de log restritos
            Log::critical("[DEBUG-TENANT] Tenant ID '{$tenant->id}' ENCONTRADO com SUCESSO para o subdomínio '{$subdomain}'.");
        } else {
            Log::error("[DEBUG-TENANT] FALHA: Nenhum tenant encontrado para o subdomínio '{$subdomain}'. Verifique a tabela de tenants.");
        }

        return $tenant;
    }
}
