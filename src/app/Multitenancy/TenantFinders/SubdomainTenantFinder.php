<?php

// ARQUIVO 1: app/Multitenancy/TenantFinders/SubdomainTenantFinder.php
// Objetivo: Verificar se o tenant está sendo identificado corretamente.

namespace App\Multitenancy\TenantFinders;

use App\Models\Central\Tenant as TenantModel;
use Illuminate\Http\Request; // Adicionado
use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

class SubdomainTenantFinder extends TenantFinder
{
    public function findForRequest(Request $request): ?IsTenant
    {
        $host = $request->getHost();
        Log::info("[DEBUG] SubdomainTenantFinder: Verificando host '{$host}'.");

        if (in_array($host, config('multitenancy.central_domains', []))) {
            Log::info("[DEBUG] SubdomainTenantFinder: Host '{$host}' é um domínio central. Nenhum tenant será usado.");

            return null;
        }

        $subdomain = explode('.', $host)[0];
        Log::info("[DEBUG] SubdomainTenantFinder: Subdomínio extraído '{$subdomain}'.");

        $tenant = TenantModel::where('subdomain', $subdomain)->first();

        if ($tenant) {
            Log::info("[DEBUG] SubdomainTenantFinder: Tenant ID '{$tenant->id}' encontrado para o subdomínio '{$subdomain}'.");
        } else {
            Log::info("[DEBUG] SubdomainTenantFinder: Nenhum tenant encontrado para o subdomínio '{$subdomain}'.");
        }

        return $tenant;
    }
}
