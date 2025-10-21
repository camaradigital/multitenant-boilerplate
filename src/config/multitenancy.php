<?php

// Caminho: config/multitenancy.php

// use App\Multitenancy\Tasks\CacheTenantSettingsTask;
use Spatie\Multitenancy\Actions\ForgetCurrentTenantAction;
use Spatie\Multitenancy\Actions\MakeTenantCurrentAction;

return [
    /*
     * Localiza o tenant com base no subdomínio.
     */
    'tenant_finder' => \App\Multitenancy\TenantFinders\SubdomainTenantFinder::class,

    /*
     * Lista de domínios que pertencem ao contexto central (sem tenant).
     */
    'central_domains' => [
        'cacsystem.test', // exemplo local
        'camaradigital.app', // produção
        'www.camaradigital.app', // produção
        'consultafacilweb.online',
        'qvbfit7dkyi3ggzw14597.cleavr.one',
    ],

    /*
     * Tasks executadas quando o contexto de tenant muda.
     */
    'switch_tenant_tasks' => [
        \Spatie\Multitenancy\Tasks\SwitchTenantDatabaseTask::class,
        \Spatie\Multitenancy\Tasks\PrefixCacheTask::class,

        // ATIVE ESTA TAREFA PARA QUE A AUTENTICAÇÃO FUNCIONE NOS TENANTS
        \App\Multitenancy\Tasks\SwitchAuthConfigTask::class,
        \App\Multitenancy\Tasks\SwitchPermissionModelsTask::class,
    ],

    /*
     * Modelo Tenant que implementa Spatie\Multitenancy\Contracts\Tenant
     */
    'tenant_model' => \App\Models\Central\Tenant::class,

    'actions' => [
        'make_tenant_current_action' => MakeTenantCurrentAction::class,
        'forget_current_tenant_action' => ForgetCurrentTenantAction::class,
    ],

    /*
     * Nome da conexão de banco de dados do landlord (central).
     */
    'landlord_database_connection_name' => 'central',

    /*
     * Nome da conexão usada para os tenants.
     */
    'tenant_database_connection_name' => 'tenant',

    /*
     * Jobs são executados por padrão com contexto do tenant.
     */
    'queues_are_tenant_aware_by_default' => true,

    /*
     * Campos que podem ser usados para encontrar tenants via Artisan
     */
    'tenant_artisan_search_fields' => ['id', 'subdomain'],
];
