<?php

// docker-compose run --rm app php artisan tenants:seed --class=Database\\Seeders\\Tenant\\RolePermissionSeeder --tenantID=21

namespace App\Console\Commands;

use App\Models\Central\Tenant; // Certifique-se de que este é o caminho para seu modelo Tenant
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;

class SeedTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:seed
                            {--class= : A classe do seeder a ser executada (obrigatório)}
                            {--force : Forçar a execução em ambiente de produção}
                            {--tenantID=* : O(s) ID(s) do(s) tenant(s) a receber o seed. Se não for informado, executa para todos.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executa um seeder para um ou mais tenants específicos.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantIds = $this->option('tenantID');
        $seederClass = $this->option('class');

        if (empty($seederClass)) {
            $this->error('A classe do seeder é obrigatória. Use a opção --class.');

            return Command::FAILURE;
        }

        $tenants = Tenant::query()
            ->when($tenantIds, fn ($query) => $query->whereIn('id', $tenantIds))
            ->get();

        if ($tenants->isEmpty()) {
            $this->warn('Nenhum tenant encontrado para os IDs fornecidos.');

            return Command::SUCCESS;
        }

        $this->info("Executando seeder '{$seederClass}' para ".$tenants->count().' tenant(s).');

        $tenants->each(function (Tenant $tenant) use ($seederClass) {
            $this->line('');
            $this->info('========================================');
            $this->info("Tenant: {$tenant->name} (ID: {$tenant->id})");
            $this->info("Database: {$tenant->database_name}");
            $this->info('========================================');

            try {
                // A função execute() muda o contexto para o banco de dados do tenant.
                $tenant->execute(function () use ($seederClass) {
                    $this->call('db:seed', [
                        '--class' => $seederClass,
                        '--force' => $this->option('force'),
                        // CORREÇÃO: Especifica que o comando db:seed deve usar
                        // a conexão 'tenant', que agora aponta para o BD correto.
                        '--database' => 'tenant',
                    ]);
                });
            } catch (QueryException $e) {
                $this->error("Erro ao executar seeder no tenant {$tenant->id}: ".$e->getMessage());
            }
        });

        $this->line('');
        $this->info('Seeding concluído para os tenants especificados.');

        return Command::SUCCESS;
    }
}
