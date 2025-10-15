<?php

namespace App\Multitenancy\TenantFinders;

use App\Models\Central\Tenant as TenantModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str; // Adicionado para uso do Str::beforeLast
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

class SubdomainTenantFinder extends TenantFinder
{
    public function findForRequest(Request $request): ?IsTenant
    {
        $host = $request->getHost();
        Log::info("[DEBUG-TENANT] SubdomainTenantFinder: Verificando host '{$host}'.");

        // 1. Verificar se é um domínio central
        if (in_array($host, config('multitenancy.central_domains', []))) {
            Log::info("[DEBUG-TENANT] SubdomainTenantFinder: Host '{$host}' é um domínio central. Nenhum tenant será usado.");
            return null;
        }

        // 2. Extração robusta do subdomínio
        $subdomain = $host;
        $found = false;

        $centralDomains = config('multitenancy.central_domains', []);
        
        foreach ($centralDomains as $centralDomain) {
            // Verifica se o host termina com um domínio central, incluindo o ponto
            if (Str::endsWith($host, '.' . $centralDomain)) {
                // Remove o domínio central (e o ponto que o precede)
                $subdomain = Str::beforeLast($host, '.' . $centralDomain);
                $found = true;
                break;
            }
        }
        
        // Se após a iteração o subdomínio ainda for igual ao host,
        // ou a remoção resultou em uma string vazia, não há subdomínio válido.
        if (!$found || $subdomain === '') {
            Log::info("[DEBUG-TENANT] SubdomainTenantFinder: Não foi possível extrair um subdomínio válido do host '{$host}'.");
            return null;
        }
        
        Log::info("[DEBUG-TENANT] SubdomainTenantFinder: Subdomínio extraído '{$subdomain}'.");

        // 3. Busca no Banco de Dados Central
        // Nota: Assumimos que o TenantModel usa a conexão correta (Landlord)
        $tenant = TenantModel::where('subdomain', $subdomain)->first();

        // 4. Log do resultado da busca
        if ($tenant) {
            Log::critical("[DEBUG-TENANT] Tenant ID '{$tenant->id}' ENCONTRADO com SUCESSO para o subdomínio '{$subdomain}'.");
        } else {
            Log::critical("[DEBUG-TENANT] FALHA: Nenhum tenant encontrado para o subdomínio '{$subdomain}'. Verifique a tabela de tenants.");
        }

        return $tenant;
    }
}
