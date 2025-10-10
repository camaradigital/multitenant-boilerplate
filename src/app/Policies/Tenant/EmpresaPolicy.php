<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\Empresa;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class EmpresaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('empresas.visualizar_todos');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Empresa $empresa): bool
    {
        return $user->can('empresas.visualizar');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('empresas.criar');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Empresa $empresa): bool
    {
        return $user->can('empresas.atualizar');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Empresa $empresa): Response
    {
        if (! $user->can('empresas.excluir')) {
            return Response::deny('Você não tem permissão para excluir empresas.');
        }

        // Regra de negócio: Não permitir exclusão se a empresa tiver vagas associadas.
        if ($empresa->vagas()->exists()) {
            return Response::deny('Não é possível excluir uma empresa que possui vagas cadastradas.');
        }

        return Response::allow();
    }
}
