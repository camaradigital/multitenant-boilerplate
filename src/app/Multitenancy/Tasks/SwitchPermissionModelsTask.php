<?php

namespace App\Multitenancy\Tasks;

use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

class SwitchPermissionModelsTask implements SwitchTenantTask
{
    /**
     * @var array Armazena a configuração original dos modelos de permissão.
     */
    protected array $originalModelsConfig;

    /**
     * Guarda a configuração original do landlord no momento da construção.
     */
    public function __construct()
    {
        $this->originalModelsConfig = config('permission.models');
    }

    /**
     * Executado quando um tenant se torna o atual.
     * Altera a configuração para usar os modelos de Role e Permission do tenant.
     */
    public function makeCurrent(IsTenant $tenant): void
    {
        config()->set('permission.models.role', \App\Models\Tenant\Role::class);
        config()->set('permission.models.permission', \App\Models\Tenant\Permission::class);
    }

    /**
     * Executado quando o contexto do tenant é finalizado.
     * Restaura a configuração original dos modelos do landlord.
     */
    public function forgetCurrent(): void
    {
        config()->set('permission.models', $this->originalModelsConfig);
    }
}
