<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\Servico;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServicoPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('servicos.visualizar_todos');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Servico $servico): bool
    {
        return $user->can('servicos.visualizar');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('servicos.criar');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Servico $servico): bool
    {
        return $user->can('servicos.atualizar');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Servico $servico): bool
    {
        return $user->can('servicos.excluir');
    }
}
