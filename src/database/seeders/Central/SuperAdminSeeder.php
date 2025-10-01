<?php

namespace Database\Seeders\Central;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    /**
     * Executa os seeders do banco de dados.
     */
    public function run(): void
    {
        // Limpa o cache de permissões para garantir que as novas sejam registradas
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Cria todas as permissões que já definimos para os tenants,
        // mas no contexto do guard 'web' (central)
        $permissions = [
            'gerenciar tenants',
            'gerenciar permissoes centrais',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // Cria o papel 'Super Admin' e dá a ele todas as permissões
        $superAdminRole = Role::findOrCreate('Super Admin', 'web');
        $superAdminRole->givePermissionTo(Permission::all());

        // Cria o usuário Super Admin se ele não existir
        $superAdminUser = User::firstOrCreate(
            ['email' => 'admin@cacsystem.com.br'],
            [
                'name' => 'Super Administrador',
                'password' => Hash::make('password'), // Altere em produção!
            ]
        );

        // Atribui o papel de Super Admin ao usuário
        $superAdminUser->assignRole($superAdminRole);
    }
}
