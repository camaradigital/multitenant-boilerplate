<?php

namespace App\Models\Central;

use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    // Força este modelo a usar a conexão do landlord (central)
    use UsesLandlordConnection;

    /**
     * O nome da conexão do banco de dados para o modelo.
     *
     * @var string
     */
    protected $connection = 'central';
}
