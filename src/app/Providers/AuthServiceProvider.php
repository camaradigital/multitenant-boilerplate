<?php

namespace App\Providers;

use App\Models\Tenant\Documento; // 1. Importe o seu modelo
use App\Policies\Tenant\DocumentoPolicy; // 2. Importe a sua policy
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        Documento::class => DocumentoPolicy::class, // 3. Adicione esta linha
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}
