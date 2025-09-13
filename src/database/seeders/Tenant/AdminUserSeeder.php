<?php

namespace Database\Seeders\Tenant;

use App\Models\Central\Tenant;
use App\Models\Tenant\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @param \App\Models\Central\Tenant $tenant
     * @return void
     */
    public function run(Tenant $tenant): void
    {
        try {
            // Limpa o cache de permissões para garantir dados atualizados
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            // Cria o usuário administrador no banco de dados do tenant
            $user = User::firstOrCreate(
                ['email' => $tenant->admin_email],
                [
                    'name'     => 'Administrador',
                    'password' => Hash::make(Str::random(16)),
                ]
            );

            // Pega o nome da conexão do tenant a partir da configuração.
            $tenantConnection = config('multitenancy.tenant_database_connection_name');

            // Busca o papel 'Admin Tenant' explicitamente NA CONEXÃO CORRETA.
            $adminRole = Role::on($tenantConnection)
                ->where('name', 'Admin Tenant')
                ->first();

            // Atribui o papel ao usuário usando o objeto do modelo, não a string.
            if ($adminRole) {
                $user->assignRole($adminRole);
                Log::info('[AdminUserSeeder] Usuário e papel criados e atribuídos com sucesso.');
            } else {
                Log::error('[AdminUserSeeder] O papel "Admin Tenant" não foi encontrado no banco de dados do tenant.');
            }

        } catch (\Exception $e) {
            Log::error('[AdminUserSeeder] Ocorreu uma exceção durante a criação do usuário ou atribuição de papel.', [
                'error' => $e->getMessage(),
            ]);
        }

        // A LÓGICA DE ENVIO DE NOTIFICAÇÃO FOI REMOVIDA DESTE ARQUIVO
        // para centralizar o envio no TenantManagerService e evitar duplicidade.
    }
}
