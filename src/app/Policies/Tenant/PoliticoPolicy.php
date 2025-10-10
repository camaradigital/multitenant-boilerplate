<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\Politico;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class PoliticoPolicy
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
    public function view(User $user, Politico $politico): bool
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
    public function update(User $user, Politico $politico): bool
    {
        return $user->can('memoria_legislativa.atualizar');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Politico $politico): Response
    {
        if (! $user->can('memoria_legislativa.excluir')) {
            return Response::deny('Você não tem permissão para excluir políticos.');
        }

        // Regra de negócio: Não permitir exclusão se o político tiver mandatos associados.
        if ($politico->mandatos()->exists()) {
            return Response::deny('Não é possível excluir este político, pois ele possui mandatos associados.');
        }

        return Response::allow();
    }
}
