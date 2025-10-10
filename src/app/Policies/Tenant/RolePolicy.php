<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\Role;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('perfis.visualizar_todos');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('perfis.criar');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Role $role): bool
    {
        return $user->can('perfis.atualizar');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Role $role): bool
    {
        // Regra de negócio: não permite excluir papéis essenciais do sistema.
        if (in_array($role->name, ['Admin Tenant', 'Funcionario', 'Cidadao', 'Advogado Coordenador'])) {
            return false;
        }

        return $user->can('perfis.excluir');
    }
}
