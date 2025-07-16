<?php
namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        Permission::create(['name' => 'gerenciar servicos']);
        Permission::create(['name' => 'solicitar servicos']);
        Permission::create(['name' => 'gerenciar funcionarios']);
        Permission::create(['name' => 'ver cidadaos']);
        Permission::create(['name' => 'gerenciar parametros']);
        Permission::create(['name' => 'ver relatorios']);

        // Create Roles and assign permissions
        $roleCidadao = Role::create(['name' => 'Cidadao']);
        $roleCidadao->givePermissionTo('solicitar servicos');

        $roleFuncionario = Role::create(['name' => 'Funcionario']);
        $roleFuncionario->givePermissionTo(['solicitar servicos', 'ver cidadaos']);

        $roleAdmin = Role::create(['name' => 'Admin Tenant']);
        $roleAdmin->givePermissionTo(Permission::all());
    }
}
