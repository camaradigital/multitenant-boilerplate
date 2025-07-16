<?php

namespace App\Services\Central;

use App\Models\Central\Tenant;
use App\Models\Tenant\User;
use App\Mail\TenantWelcomeMail;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Auth\Passwords\DatabaseTokenRepository;
use Illuminate\Support\Facades\Config;

class TenantManagerService
{
    private ?Tenant $tenant = null;

    public function create(string $nome, string $subdomain, string $adminEmail, string $cnpj): Tenant
    {
        $dbName = 'cac_' . $subdomain;

        try {
            $this->tenant = Tenant::create([
                'name' => $nome, 'subdomain' => $subdomain, 'cnpj' => $cnpj,
                'database_name' => $dbName, 'admin_email' => $adminEmail,
            ]);

            DB::connection('central')->statement("CREATE DATABASE `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            Log::info("Banco de dados {$dbName} criado com sucesso.");

            $this->tenant->makeCurrent();

            Artisan::call('tenants:artisan', [
                'artisanCommand' => 'migrate --path=database/migrations/tenant --seed --database=tenant',
                '--tenant' => $this->tenant->id,
            ]);
            Log::info("Migrations e Seeders para o tenant {$this->tenant->id} executadas com sucesso.");

            $user = User::create([
                'name' => 'Administrador', 'email' => $adminEmail,
                'password' => Hash::make(Str::random(16)),
            ]);
            Log::info("Usuário admin {$adminEmail} criado no banco do tenant.");

            $adminRole = Role::on('tenant')->where('name', 'Admin Tenant')->first();
            if ($adminRole) {
                $user->syncRoles($adminRole);
            }

            // ---- CORREÇÃO FINAL DA CRIAÇÃO DO TOKEN ----

            // 1. Pega a conexão do banco de dados do TENANT.
            $connection = DB::connection('tenant');

            // 2. Pega a instância do serviço de HASH.
            $hasher = app('hash');

            // 3. Pega as outras configurações.
            $table = 'password_reset_tokens';
            $hashKey = Config::get('app.key');
            $expires = Config::get('auth.passwords.users.expire', 60);

            // 4. Cria o repositório de tokens com os ARGUMENTOS NA ORDEM CORRETA.
            $tokenRepository = new DatabaseTokenRepository($connection, $hasher, $table, $hashKey, $expires);

            // 5. Cria o token usando a instância correta.
            $token = $tokenRepository->create($user);
            Log::info("Token criado explicitamente na conexão do tenant.");

            // 6. Envia o e-mail.
            Mail::to($user->email)->send(new TenantWelcomeMail($this->tenant, $user, $token));
            Log::info("E-mail de boas-vindas enviado para {$adminEmail}.");

            return $this->tenant;

        } catch (Exception $e) {
            Log::error("Falha ao criar o tenant: " . $e->getMessage(), ['exception' => $e]);
            if ($this->tenant) {
                DB::connection('central')->statement("DROP DATABASE IF EXISTS `{$dbName}`");
                $this->tenant->delete();
            }
            throw $e;
        } finally {
            Tenant::forgetCurrent();
        }
    }
}
