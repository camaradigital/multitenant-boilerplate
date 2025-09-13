<?php

namespace App\Models\Tenant;

use Laravel\Jetstream\Membership as JetstreamMembership;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Membership extends JetstreamMembership
{
    use UsesTenantConnection;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;
}
