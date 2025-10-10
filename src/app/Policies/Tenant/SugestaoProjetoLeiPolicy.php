<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\SugestaoProjetoLei;
use App\Models\Tenant\User;

class SugestaoProjetoLeiPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('sugestoes_de_lei.visualizar_todos');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SugestaoProjetoLei $sugestaoProjetoLei): bool
    {
        return $user->can('sugestoes_de_lei.visualizar');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // A criação de sugestões é feita pelo cidadão no portal público.
        // O painel administrativo não tem a função de criar sugestões em nome de outros.
        return $user->hasRole('Cidadao');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SugestaoProjetoLei $sugestaoProjetoLei): bool
    {
        return $user->can('sugestoes_de_lei.atualizar');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SugestaoProjetoLei $sugestaoProjetoLei): bool
    {
        return $user->can('sugestoes_de_lei.excluir');
    }

    /**
     * Determine whether the user can manage the status of the model.
     */
    public function manageStatus(User $user, SugestaoProjetoLei $sugestaoProjetoLei): bool
    {
        return $user->can('sugestoes_de_lei.gerenciar_status');
    }
}
