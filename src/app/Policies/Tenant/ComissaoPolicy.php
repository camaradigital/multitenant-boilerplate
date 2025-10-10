<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\Comissao;
use App\Models\Tenant\User;

class ComissaoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('comissoes.visualizar_todos');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Comissao $comissao): bool
    {
        return $user->can('comissoes.visualizar');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('comissoes.criar');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Comissao $comissao): bool
    {
        return $user->can('comissoes.atualizar');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Comissao $comissao): bool
    {
        return $user->can('comissoes.excluir');
    }
}
