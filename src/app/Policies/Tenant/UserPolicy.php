<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    // --- Habilidades para o Próprio Perfil (Profile) ---

    /**
     * Determina se o usuário pode visualizar seu próprio perfil.
     */
    public function viewProfile(User $user, User $profileOwner): bool
    {
        // Um usuário só pode ver seu próprio perfil.
        return $user->id === $profileOwner->id;
    }

    /**
     * Determina se o usuário pode exportar seus próprios dados.
     */
    public function exportProfileData(User $user, User $profileOwner): bool
    {
        // Um usuário só pode exportar seus próprios dados.
        return $user->id === $profileOwner->id;
    }

    /**
     * Determina se o usuário pode anonimizar sua própria conta.
     */
    public function anonymizeProfile(User $user, User $profileOwner): bool
    {
        // Apenas o próprio usuário e que tenha o papel 'Cidadao' pode anonimizar a conta.
        return ($user->id === $profileOwner->id) && $user->hasRole('Cidadao');
    }

    /**
     * Determina se o usuário pode deletar sua própria conta.
     */
    public function deleteProfile(User $user, User $profileOwner): bool
    {
        // Um usuário só pode tentar deletar sua própria conta.
        return $user->id === $profileOwner->id;
    }

    // --- Habilidades para Cidadãos ---

    public function viewAnyCidadao(User $user): bool
    {
        return $user->can('gerenciar cidadaos');
    }

    public function viewCidadao(User $user, User $cidadao): bool
    {
        return $user->can('gerenciar cidadaos');
    }

    public function createCidadao(User $user): bool
    {
        return $user->can('gerenciar cidadaos');
    }

    public function updateCidadao(User $user, User $cidadao): bool
    {
        return $user->can('gerenciar cidadaos');
    }

    public function deleteCidadao(User $user, User $cidadao): bool
    {
        return $user->can('gerenciar cidadaos');
    }

    public function anonymizeCidadao(User $user, User $cidadao): bool
    {
        return $user->can('gerenciar cidadaos');
    }

    public function exportDataCidadao(User $user, User $cidadao): bool
    {
        return $user->can('gerenciar cidadaos');
    }


    // --- Habilidades para Funcionários ---

    public function viewAnyFuncionario(User $user): bool
    {
        return $user->can('gerenciar funcionarios');
    }

    public function createFuncionario(User $user): bool
    {
        return $user->can('gerenciar funcionarios');
    }

    public function updateFuncionario(User $user, User $funcionario): bool
    {
        return $user->can('gerenciar funcionarios');
    }

    public function deleteFuncionario(User $user, User $funcionario): bool
    {
        // Impede que o usuário exclua a si mesmo E verifica a permissão
        if ($user->id === $funcionario->id) {
            return false;
        }

        return $user->can('gerenciar funcionarios');
    }
}
