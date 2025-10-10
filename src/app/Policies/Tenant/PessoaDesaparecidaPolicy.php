<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\PessoaDesaparecida;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PessoaDesaparecidaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('pessoas_desaparecidas.visualizar_todos');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PessoaDesaparecida $pessoaDesaparecida): bool
    {
        return $user->can('pessoas_desaparecidas.visualizar');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('pessoas_desaparecidas.criar');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PessoaDesaparecida $pessoaDesaparecida): bool
    {
        return $user->can('pessoas_desaparecidas.atualizar');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PessoaDesaparecida $pessoaDesaparecida): bool
    {
        return $user->can('pessoas_desaparecidas.excluir');
    }

    /**
     * Determine whether the user can moderate the model (e.g., change status).
     */
    public function moderate(User $user, PessoaDesaparecida $pessoaDesaparecida): bool
    {
        return $user->can('pessoas_desaparecidas.moderar');
    }

    /**
     * Determine whether the user can view the police report.
     */
    public function viewBoletim(User $user, PessoaDesaparecida $pessoaDesaparecida): bool
    {
        // A permissão de 'moderar' é adequada para ações sensíveis como ver o B.O.
        return $user->can('pessoas_desaparecidas.moderar');
    }
}
