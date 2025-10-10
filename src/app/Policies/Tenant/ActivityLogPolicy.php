<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Activitylog\Models\Activity;

class ActivityLogPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('logs_de_atividade.visualizar_todos');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Activity $activity): bool
    {
        // O causer pode ser nulo (ações do sistema), então verificamos.
        // Permite ver o detalhe se for o causador do log ou se tiver permissão geral.
        if ($activity->causer && $user->id === $activity->causer->id) {
            return true;
        }

        return $user->can('logs_de_atividade.visualizar');
    }
}
