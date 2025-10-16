<?php

namespace App\Multitenancy\Tasks;

use Illuminate\Support\Facades\DB;
use Spatie\Multitenancy\Tasks\SwitchTenantDatabaseTask as BaseSwitchTenantDatabaseTask;

class SwitchTenantDatabaseTask extends BaseSwitchTenantDatabaseTask
{
    protected function setTenantConnectionDatabaseName(?string $databaseName)
    {
        parent::setTenantConnectionDatabaseName($databaseName);

        $tenantConnectionName = is_null($databaseName)
            ? $this->landlordDatabaseConnectionName()
            : $this->tenantDatabaseConnectionName();

        DB::setDefaultConnection($tenantConnectionName);
    }
}
