<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Limpa o cache de permissões
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Pega o nome da conexão do tenant
        $tenantConnection = config('multitenancy.tenant_database_connection_name');

        // Lista de permissões sincronizada com o menu de navegação e outras permissões do sistema
        $permissions = [
            // Portal (Cidadão)
            'ver portal',

            // Gerenciamento
            'ver solicitacoes',
            'criar solicitacoes',
            'gerenciar solicitacoes',
            'gerenciar funcionarios',
            'gerenciar cidadaos',
            'gerenciar servicos',
            'gerenciar entidades',
            // Memória Legislativa
            'gerenciar memoria',
            // Achados e Perdidos
            'gerenciar achados e perdidos',
            // Relatórios
            'visualizar relatorios',
            // Parâmetros do Sistema
            'gerenciar parametros',
        ];

        foreach ($permissions as $permission) {
            // Usa o método on() para garantir que a permissão seja criada na conexão do tenant
            Permission::on($tenantConnection)->firstOrCreate([
                'name' => $permission,
                'guard_name' => 'tenant' // Especifica o guard 'tenant'
            ]);
        }
    }
}
