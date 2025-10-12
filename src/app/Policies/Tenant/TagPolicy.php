<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\Tag;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\Response;

class TagPolicy
{
    /**
     * Determine whether the user can view any models (Index).
     */
    public function viewAny(User $user): Response
    {
        // Verifica a permissão de visualização da listagem
        return $user->can('tags.visualizar_todos')
            ? Response::allow()
            : Response::deny('Você não tem permissão para visualizar tags.');
    }

    /**
     * Determine whether the user can view the model (Show).
     */
    public function view(User $user, Tag $tag): Response
    {
        // Reutiliza a permissão de visualização da listagem
        return $user->can('tags.visualizar_todos')
            ? Response::allow()
            : Response::deny('Você não tem permissão para visualizar esta tag.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        // Verifica a permissão de criação
        return $user->can('tags.criar')
            ? Response::allow()
            : Response::deny('Você não tem permissão para cadastrar tags.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Tag $tag): Response
    {
        // Verifica a permissão de atualização
        return $user->can('tags.atualizar')
            ? Response::allow()
            : Response::deny('Você não tem permissão para atualizar esta tag.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Tag $tag): Response
    {
        // Verifica a permissão de exclusão
        return $user->can('tags.excluir')
            ? Response::allow()
            : Response::deny('Você não tem permissão para excluir esta tag.');
    }
}
