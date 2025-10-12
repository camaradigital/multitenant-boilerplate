<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\Bairro;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\Response;

class BairroPolicy
{
    /**
     * Determine whether the user can view any models (Index).
     */
    public function viewAny(User $user): Response
    {
        // Verifica a permissão de visualização da listagem
        return $user->can('bairros.visualizar_todos')
            ? Response::allow()
            : Response::deny('Você não tem permissão para visualizar bairros.');
    }

    /**
     * Determine whether the user can view the model (Show).
     */
    public function view(User $user, Bairro $bairro): Response
    {
        // Reutiliza a permissão de visualização da listagem
        return $user->can('bairros.visualizar_todos')
            ? Response::allow()
            : Response::deny('Você não tem permissão para visualizar este bairro.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        // Verifica a permissão de criação
        return $user->can('bairros.criar')
            ? Response::allow()
            : Response::deny('Você não tem permissão para cadastrar bairros.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Bairro $bairro): Response
    {
        // Verifica a permissão de atualização
        return $user->can('bairros.atualizar')
            ? Response::allow()
            : Response::deny('Você não tem permissão para atualizar este bairro.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Bairro $bairro): Response
    {
        // Verifica a permissão de exclusão
        return $user->can('bairros.excluir')
            ? Response::allow()
            : Response::deny('Você não tem permissão para excluir este bairro.');
    }
}
