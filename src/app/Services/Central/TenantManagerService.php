<?php

namespace App\Services\Central;

use App\Mail\TenantWelcomeMail;
use App\Models\Central\Tenant;
use Database\Seeders\Tenant\TenantDatabaseSeeder;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class TenantManagerService
{
    private ?Tenant $tenant = null;

    public function create(array $data): Tenant
    {
        // Define o nome do banco de dados a partir do subdomínio
        $dbName = 'cac_' . $data['subdomain'];
        $data['database_name'] = $dbName;

        try {
            // Cria o tenant no banco de dados central usando o array de dados validado.
            // Isso funciona porque todos os campos (incluindo endereço, urls, etc.)
            // já estão no array $data graças à validação no controller.
            $this->tenant = Tenant::create($data);

            // Cria o banco de dados específico para o novo tenant.
            DB::connection('central')->statement("CREATE DATABASE `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            Log::info("Banco de dados {$dbName} criado com sucesso.");

            // Executa as operações no contexto do banco de dados do novo tenant.
            $this->tenant->execute(function ($tenant) use ($data) {

                // Executa as migrações do tenant.
                Artisan::call('migrate', [
                    '--database' => 'tenant',
                    '--path'     => 'database/migrations/tenant',
                    '--force'    => true,
                ]);
                Log::info("Migrations para o tenant {$tenant->id} executadas com sucesso.");

                // Executa os seeders do tenant.
                (new TenantDatabaseSeeder())->run($tenant);
                Log::info("Seeders para o tenant {$tenant->id} executados com sucesso.");

                // Encontra o usuário administrador recém-criado para enviar o e-mail de boas-vindas.
                $user = \App\Models\Tenant\User::where('email', $data['admin_email'])->first();

                if ($user) {
                    $token = Password::broker('tenant_users')->createToken($user);
                    Mail::to($user->email)->send(new TenantWelcomeMail($tenant, $user, $token));
                    Log::info("E-mail de boas-vindas enviado para {$data['admin_email']}.");
                } else {
                    Log::error("Não foi possível encontrar o usuário admin '{$data['admin_email']}' após o seeding para enviar o e-mail.");
                }
            });

            return $this->tenant;

        } catch (Exception $e) {
            Log::error("Falha ao criar o tenant: " . $e->getMessage(), ['exception' => $e]);
            // Se algo der errado, desfaz as operações (rollback).
            if ($this->tenant) {
                DB::connection('central')->statement("DROP DATABASE IF EXISTS `{$dbName}`");
                $this->tenant->delete();
            }
            throw $e; // Relança a exceção para que o controller possa tratá-la.
        }
    }
}
