<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// Importa a nossa nova classe personalizada
use App\Services\Auth\TenantAwarePasswordBrokerManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Registra qualquer serviço da aplicação.
     *
     * O método `register` é usado para vincular coisas ao contêiner de serviço.
     * Ele é executado antes do método `boot`.
     */
    public function register(): void
    {
        // **ESTA É A PARTE CRÍTICA E DEFINITIVA**
        // Estamos substituindo o gerenciador de senhas padrão do Laravel ('auth.password')
        // pela nossa versão ciente do tenant. A partir de agora, sempre que o Laravel
        // precisar de um serviço de senha, ele usará a nossa classe customizada.
        // Isso resolve o problema na raiz, pois não depende da ordem dos middlewares.
        $this->app->singleton('auth.password', function ($app) {
            return new TenantAwarePasswordBrokerManager($app);
        });
    }

    /**
     * Inicializa qualquer serviço da aplicação.
     *
     * O método `boot` é executado depois que todos os outros provedores de serviço
     * foram registrados.
     */
    public function boot(): void
    {
        // Carrega as migrações do landlord (banco central).
        // Esta linha está correta e foi mantida do seu código original.
        $this->loadMigrationsFrom(database_path('migrations/landlord'));
    }
}
