<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $tenantConnection = config('multitenancy.tenant_database_connection_name');

        // 1. Busca todos as permissões da conexão do tenant
        $allPermissions = Permission::on($tenantConnection)->get()->keyBy('name');

        // 2. Cria os papéis (ou os busca se já existirem)
        $roleCidadao = Role::on($tenantConnection)->firstOrCreate(['name' => 'Cidadao', 'guard_name' => 'tenant']);
        $roleFuncionario = Role::on($tenantConnection)->firstOrCreate(['name' => 'Funcionario', 'guard_name' => 'tenant']);
        $roleAdvogado = Role::on($tenantConnection)->firstOrCreate(['name' => 'Advogado Coordenador', 'guard_name' => 'tenant']); // NOVO PAPEL
        $roleAdmin = Role::on($tenantConnection)->firstOrCreate(['name' => 'Admin Tenant', 'guard_name' => 'tenant']);

        // 3. Define e atribui as permissões para cada papel

        // Papel: Cidadao
        // Permite ver o portal de acesso rápido e criar solicitações.
        $this->assignPermissions($roleCidadao, $allPermissions, [
            'ver portal',
            'criar solicitacoes',
        ]);

        // Papel: Funcionario
        // Permissões operacionais do dia a dia, mas sem acesso aos parâmetros do sistema.
        $this->assignPermissions($roleFuncionario, $allPermissions, [
            'ver solicitacoes',
            'criar solicitacoes',
            'gerenciar solicitacoes',
            'gerenciar cidadaos',
            'gerenciar servicos',
            'gerenciar entidades',
            'gerenciar memoria',
            'gerenciar achados e perdidos',
            'visualizar relatorios',
        ]);

        // Papel: Advogado Coordenador (NOVA ATRIBUIÇÃO)
        // Herda as permissões de um funcionário e adiciona a capacidade de supervisionar.
        $this->assignPermissions($roleAdvogado, $allPermissions, [
            'ver solicitacoes',
            'criar solicitacoes',
            'gerenciar solicitacoes',
            'gerenciar cidadaos',
            'gerenciar servicos',
            'gerenciar entidades',
            'visualizar relatorios',
            'supervisionar solicitacoes juridicas', // PERMISSÃO ESPECIAL
        ]);

        // Papel: Admin Tenant
        // Sincroniza TODAS as permissões disponíveis para este tenant.
        $roleAdmin->syncPermissions($allPermissions->values());
    }

    /**
     * Função auxiliar para filtrar e atribuir permissões de forma segura.
     */
    private function assignPermissions(Role $role, Collection $allPermissions, array $permissionNames): void
    {
        $permissionsToAssign = $allPermissions->whereIn('name', $permissionNames)->values();
        $role->syncPermissions($permissionsToAssign);
    }
}
