<?php

namespace App\Multitenancy\Tasks;

use App\Models\Central\Tenant;
use Illuminate\Support\Facades\Cache;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

class CacheTenantSettingsTask implements SwitchTenantTask
{
    /**
     * The cache key for tenant settings.
     * By defining it as a constant, we avoid "magic strings".
     */
    protected const CACHE_KEY = 'settings';

    /**
     * Executado quando um tenant é identificado.
     * Carrega as configurações de personalização do tenant e as guarda em cache.
     * A PrefixCacheTask irá isolar este cache automaticamente para cada tenant.
     */
    public function makeCurrent(IsTenant $tenant): void
    {
        // Usa a coluna 'data' (JSON) da tabela tenants
        $settings = $tenant->data ?? [];

        // A chave agora é simples. A `PrefixCacheTask` adicionará o prefixo `tenant_{id}`.
        // A chave final em Redis será, por exemplo: "tenant_1_settings"
        Cache::store('redis')->rememberForever(self::CACHE_KEY, fn () => $settings);
    }

    /**
     * Executado quando saímos do contexto de um tenant.
     * Limpa o cache específico daquele tenant.
     */
    public function forgetCurrent(): void
    {
        // Não é necessário limpar o cache aqui.
        // Quando o próximo tenant se tornar o atual, a `PrefixCacheTask`
        // mudará o prefixo, e o cache do tenant anterior ficará inacessível,
        // que tem o mesmo efeito de "limpar" o cache para a requisição atual.
        // O cache antigo permanecerá em Redis até expirar ou ser sobrescrito,
        // o que é o comportamento esperado.
        //
        // Se você realmente precisar remover a chave do Redis no momento da troca,
        // o código abaixo funcionaria, mas geralmente é desnecessário.
        /*
        if (Tenant::current()) {
             Cache::store('redis')->forget(self::CACHE_KEY);
        }
        */
    }
}
