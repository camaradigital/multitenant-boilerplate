<?php

namespace App\Multitenancy\Tasks;

use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

class SwitchAuthConfigTask implements SwitchTenantTask
{
    public function makeCurrent(IsTenant $tenant): void
    {
        // Switch to the tenant's configuration
        config([
            'auth.defaults.guard' => 'tenant',
            'auth.defaults.passwords' => 'tenant_users',
            'fortify.guard' => 'tenant',
            'fortify.passwords' => 'tenant_users',
        ]);
    }

    public function forgetCurrent(): void // <- This method was renamed
    {
        // Revert to the central/landlord configuration
        config([
            'auth.defaults.guard' => 'web',
            'auth.defaults.passwords' => 'users',
            'fortify.guard' => 'web',
            'fortify.passwords' => 'users',
        ]);
    }
}
