<?php

namespace App\Providers;

use App\Services\Auth\TenantAwarePasswordBrokerManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // <-- 1. ADICIONE ESTA LINHA (IMPORT)

class AppServiceProvider extends ServiceProvider
{
    /**
     * Registra qualquer serviço da aplicação.
     */
    public function register(): void
    {
        // Esta parte do seu código está correta e foi mantida.
        $this->app->singleton('auth.password', function ($app) {
            return new TenantAwarePasswordBrokerManager($app);
        });
    }

    /**
     * Inicializa qualquer serviço da aplicação.
     */
    public function boot(): void
    {
        // Carrega as migrações do landlord (banco central).
        $this->loadMigrationsFrom(database_path('migrations/landlord'));

        // <-- 2. ADICIONE ESTE BLOCO DE CÓDIGO
        // Força o Laravel a usar 'https' para todas as URLs geradas
        // quando a aplicação estiver em ambiente de produção.
        // Isso resolve o erro de "Mixed Content".
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
