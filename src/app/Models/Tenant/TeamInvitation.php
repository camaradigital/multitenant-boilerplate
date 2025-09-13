<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\TeamInvitation as JetstreamTeamInvitation;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection; // <-- 2. IMPORTAR O TRAIT

class TeamInvitation extends JetstreamTeamInvitation
{
    use UsesTenantConnection; // <-- 3. USAR O TRAIT

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'role',
    ];

    /**
     * Get the team that the invitation belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Jetstream::teamModel());
    }
}
