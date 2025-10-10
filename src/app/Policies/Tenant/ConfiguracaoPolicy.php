<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConfiguracaoPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the configuration.
     */
    public function view(User $user): bool
    {
        return $user->can('configuracoes.visualizar');
    }

    /**
     * Determine whether the user can update the configuration.
     */
    public function update(User $user): bool
    {
        return $user->can('configuracoes.atualizar');
    }
}
