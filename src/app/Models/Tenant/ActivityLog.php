<?php

namespace App\Models\Tenant;

use Spatie\Activitylog\Models\Activity as SpatieActivity;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class ActivityLog extends SpatieActivity
{
    // Adiciona o trait para garantir que este modelo
    // sempre use a conexão de banco de dados do tenant.
    use UsesTenantConnection;
}
