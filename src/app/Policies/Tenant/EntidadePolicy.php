<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\Entidade;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class EntidadePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('entidades.visualizar_todos');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Entidade $entidade): bool
    {
        return $user->can('entidades.visualizar');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('entidades.criar');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Entidade $entidade): bool
    {
        return $user->can('entidades.atualizar');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Entidade $entidade): Response
    {
        if (! $user->can('entidades.excluir')) {
            return Response::deny('Você não tem permissão para excluir entidades.');
        }

        // Regra de negócio: Não permitir exclusão se a entidade tiver convênios associados.
        if ($entidade->convenios()->exists()) {
            return Response::deny('Não é possível excluir esta entidade, pois ela possui convênios associados.');
        }

        return Response::allow();
    }
}
