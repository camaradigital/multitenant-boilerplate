<?php

use App\Multitenancy\Tasks\CacheTenantSettingsTask;
use App\Multitenancy\TenantFinders\SubdomainTenantFinder; // Ajustado para o finder personalizado
use Spatie\Multitenancy\Actions\ForgetCurrentTenantAction;
use Spatie\Multitenancy\Actions\MakeTenantCurrentAction;

return [
    /*
     * Localiza o tenant com base no subdomínio.
     * Exemplo: camara-sp.cacsystem.com.br
     */
    'tenant_finder' => \App\Multitenancy\TenantFinders\SubdomainTenantFinder::class,

    'tenant_finder_config' => [
        'domain_column' => 'subdomain',
    ],
    /*
     * Lista de domínios que pertencem ao contexto central (sem tenant).
     * Adicione também seu domínio de produção, se aplicável.
     */
    'central_domains' => [
        'localhost',
        '127.0.0.1',
        'cacsystem.test', // exemplo local
        'cacsystem.com.br', // produção
    ],

    /*
     * Tasks executadas quando o contexto de tenant muda.
     */
    'switch_tenant_tasks' => [
        \Spatie\Multitenancy\Tasks\SwitchTenantDatabaseTask::class,
        \Spatie\Multitenancy\Tasks\PrefixCacheTask::class,
        \App\Multitenancy\Tasks\CacheTenantSettingsTask::class,
        \App\Multitenancy\Tasks\SwitchAuthConfigTask::class,  // Adicione a \
    ],

    'tenant_providers' => [
        \App\Providers\FortifyServiceProvider::class,
        \App\Providers\JetstreamServiceProvider::class,
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
     * Pode deixar null se a conexão padrão já for a central.
     */
    'landlord_database_connection_name' => 'central', // Ajustado para explicitar a conexão

    /*
     * Nome da conexão usada para os tenants.
     * Certifique-se de que "tenant" esteja definida em config/database.php
     */
    'tenant_database_connection_name' => 'tenant',

    /*
     * Jobs são executados por padrão com contexto do tenant.
     */
    'queues_are_tenant_aware_by_default' => true,

    /*
     * Se quiser mapear tipos de eventos para jobs específicos, defina aqui.
     */
    'queueable_to_job_map' => [],

    /*
     * Campos que podem ser usados para encontrar tenants via Artisan
     */
    'tenant_artisan_search_fields' => ['id', 'subdomain'], // Ajustado para corresponder ao campo do modelo

    /*
     * Comandos Artisan que devem rodar para cada tenant
     */
    'commands' => [],
];
