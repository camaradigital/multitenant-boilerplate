<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\Legislatura;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LegislaturaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('memoria_legislativa.visualizar_todos');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Legislatura $legislatura): bool
    {
        return $user->can('memoria_legislativa.visualizar');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('memoria_legislativa.criar');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Legislatura $legislatura): bool
    {
        return $user->can('memoria_legislativa.atualizar');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Legislatura $legislatura): bool
    {
        return $user->can('memoria_legislativa.excluir');
    }
}
