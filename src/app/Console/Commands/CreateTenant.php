<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Central\TenantManagerService;
use Illuminate\Support\Facades\Artisan;
// use Spatie\Multitenancy\Commands\Concerns\TenantAware; // Uncomment if needed, but not typical for tenant creation command

class CreateTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:tenant {name} {cnpj} {subdomain} {admin_email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria um novo tenant, seu banco de dados e o administrador inicial.';

    /**
     * The TenantManagerService instance.
     *
     * @var \App\Services\Central\TenantManagerService
     */
    protected $tenantManager;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(TenantManagerService $tenantManager)
    {
        parent::__construct();
        $this->tenantManager = $tenantManager;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');
        $cnpj = $this->argument('cnpj');
        $subdomain = $this->argument('subdomain');
        $adminEmail = $this->argument('admin_email');

        $this->info("Iniciando a criação do tenant: {$name} ({$subdomain})...");

        try {
            // !! IMPORTANT: Call the 'create' method with the correct parameter order !!
            $tenant = $this->tenantManager->create(
                $name,
                $subdomain,
                $adminEmail,
                $cnpj
            );

            $this->info("Tenant '{$name}' com subdomínio '{$subdomain}' criado com sucesso!");
            $this->info("Banco de dados '{$tenant->database_name}' criado e migrado."); // Use database_name as per your Tenant model
            // The TenantManagerService you provided doesn't directly create the admin user or send email.
            // It calls 'tenants:artisan' which might handle seeding.
            // If admin creation/email sending is part of the seed, it will happen.
            // Otherwise, you might need to add that logic to your TenantManagerService::create method.
            $this->info("Verifique os logs para detalhes sobre a criação do administrador e envio de e-mail.");


        } catch (\Exception $e) {
            $this->error("Erro ao criar o tenant: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
