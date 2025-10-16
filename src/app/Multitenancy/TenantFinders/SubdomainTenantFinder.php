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
        // MANTEMOS esta linha para máxima resiliência contra a intermitência anterior.
        $host = $request->header('X-Forwarded-Host') ?? $request->getHost();
        
        Log::info("[DEBUG-TENANT] SubdomainTenantFinder: Host avaliado '{$host}'.");

        // Obtém a lista de domínios centrais do arquivo de configuração
        $centralDomains = config('multitenancy.central_domains', []);
        
        // 2. Verifica se o Host é um domínio central exato (Landlord)
        if (in_array($host, $centralDomains)) {
            Log::info("[DEBUG-TENANT] SubdomainTenantFinder: Host '{$host}' é um domínio central. Nenhum tenant será usado.");
            return null; // É Landlord
        }

        $subdomain = null;

        // 3. Itera sobre os domínios centrais para extrair o subdomínio
        foreach ($centralDomains as $centralDomain) {
            $suffix = '.' . $centralDomain;

            if (Str::endsWith($host, $suffix)) {
                $subdomain = Str::beforeLast($host, $suffix);
                break; 
            }
        }
        
        // 4. Checa se um subdomínio foi extraído e se ele não está vazio
        if (empty($subdomain) || $subdomain === $host) {
            Log::info("[DEBUG-TENANT] SubdomainTenantFinder: Não foi possível extrair um subdomínio válido do host '{$host}'.");
            return null; // Fallback para Landlord se não houver subdomínio válido
        }
        
        Log::info("[DEBUG-TENANT] SubdomainTenantFinder: Subdomínio extraído '{$subdomain}'.");

        // 5. Busca no Banco de Dados Central
        $tenant = TenantModel::where('subdomain', $subdomain)->first();

        // =========================================================================
        // === CORREÇÃO CRÍTICA PARA ERRO DE FALLBACK EM PRODUÇÃO ====================
        // =========================================================================
        if (!$tenant) {
            Log::error("[DEBUG-TENANT] FALHA: Subdomínio '{$subdomain}' extraído, mas NENHUM tenant encontrado no banco de dados. ABORTANDO.");
            // Força a aplicação a parar (abortar) com um erro 404,
            // impedindo que caia no domínio Landlord.
            abort(404, "Tenant para o subdomínio '{$subdomain}' não encontrado.");
        }
        // =========================================================================
        
        // 6. Log do resultado
        Log::critical("[DEBUG-TENANT] Tenant ID '{$tenant->id}' ENCONTRADO com SUCESSO para o subdomínio '{$subdomain}'.");

        return $tenant;
    }
}
