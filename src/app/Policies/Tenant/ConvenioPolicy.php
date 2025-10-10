<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\Convenio;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConvenioPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('convenios.visualizar_todos');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Convenio $convenio): bool
    {
        return $user->can('convenios.visualizar');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('convenios.criar');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Convenio $convenio): bool
    {
        return $user->can('convenios.atualizar');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Convenio $convenio): bool
    {
        return $user->can('convenios.excluir');
    }
}
