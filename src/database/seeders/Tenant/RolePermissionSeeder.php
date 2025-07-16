<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reseta o cache de papéis e permissões
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // --- CRIAÇÃO DE PERMISSÕES ---
        // Gestão de Serviços
        Permission::create(['name' => 'gerenciar servicos']);
        Permission::create(['name' => 'solicitar servicos']);

        // Gestão de Usuários
        Permission::create(['name' => 'gerenciar funcionarios']);
        Permission::create(['name' => 'ver cidadaos']);

        // Gestão do Sistema (NOVA PERMISSÃO)
        Permission::create(['name' => 'gerenciar parametros']);
        Permission::create(['name' => 'ver relatorios']);


        // --- CRIAÇÃO DE PAPÉIS E ATRIBUIÇÃO DE PERMISSÕES ---

        // Papel do Cidadão (Munícipe)
        $roleCidadao = Role::create(['name' => 'Cidadao']);
        $roleCidadao->givePermissionTo('solicitar servicos');

        // Papel do Funcionário (Atendente)
        $roleFuncionario = Role::create(['name' => 'Funcionario']);
        $roleFuncionario->givePermissionTo(['solicitar servicos', 'ver cidadaos']);

        // Papel do Administrador do Tenant
        $roleAdmin = Role::create(['name' => 'Admin Tenant']);
        // O Admin pode tudo
        $roleAdmin->givePermissionTo(Permission::all());
    }
}
