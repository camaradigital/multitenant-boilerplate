<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\CustomField;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomFieldPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('campos_personalizados.visualizar_todos');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('campos_personalizados.criar');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CustomField $customField): bool
    {
        return $user->can('campos_personalizados.atualizar');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CustomField $customField): bool
    {
        return $user->can('campos_personalizados.excluir');
    }
}
