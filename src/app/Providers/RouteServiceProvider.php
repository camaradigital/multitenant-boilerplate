<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define a sua configuração de rotas.
     */
    public function boot(): void
    {
        $this->routes(function (Request $request) {
            // A lógica agora é executada aqui, de forma segura.
            $host = $request->getHost();

            if (in_array($host, config('multitenancy.central_domains', []))) {
                // Se o host pertence aos domínios centrais, carrega as rotas do landlord.
                Log::info("[RouteServiceProvider] Host '{$host}' é central. Carregando 'routes/web.php'.");
                Route::middleware('web')
                    ->group(base_path('routes/web.php'));
            } else {
                // Caso contrário, carrega as rotas do tenant.
                Log::info("[RouteServiceProvider] Host '{$host}' é de tenant. Carregando 'routes/tenant.php'.");
                Route::middleware('tenant')
                    ->group(base_path('routes/tenant.php'));
            }
        });
    }
}
