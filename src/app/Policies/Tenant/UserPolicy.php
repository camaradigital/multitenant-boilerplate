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
    // 1. O próprio usuário sempre pode
        if ($user->id === $profileOwner->id) {
            return true;
        }
    // 2. OU um usuário com permissão para exportar dados de cidadãos
        return $user->can('cidadaos.exportar_dados'); // <-- Crie e use uma permissão
    }

    /**
     * Determina se o usuário pode anonimizar sua própria conta.
     */
    public function anonymizeProfile(User $user, User $profileOwner): bool
    {
    // 1. O próprio usuário (Cidadão) pode
        if ($user->id === $profileOwner->id && $user->hasRole('Cidadao')) {
            return true;
        }

    // 2. OU um usuário com permissão para anonimizar cidadãos
        return $user->can('cidadaos.anonimizar'); // <-- Crie e use uma permissão
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
        return $user->can('cidadaos.visualizar_todos');
    }

    public function viewCidadao(User $user, User $cidadao): bool
    {
        return $user->can('cidadaos.visualizar');
    }

    public function createCidadao(User $user): bool
    {
        return $user->can('cidadaos.criar');
    }

    public function updateCidadao(User $user, User $cidadao): bool
    {
        return $user->can('cidadaos.atualizar');
    }

    public function deleteCidadao(User $user, User $cidadao): bool
    {
        return $user->can('cidadaos.excluir');
    }

    public function manageTagsCidadao(User $user): bool
    {
        return $user->can('cidadaos.gerenciar_tags');
    }

    public function manageNotesCidadao(User $user): bool
    {
        return $user->can('cidadaos.gerenciar_notas');
    }

    // --- Habilidades para Funcionários ---

    public function viewAnyFuncionario(User $user): bool
    {
        return $user->can('funcionarios.visualizar_todos');
    }

    public function viewFuncionario(User $user): bool
    {
        return $user->can('funcionarios.visualizar');
    }

    public function createFuncionario(User $user): bool
    {
        return $user->can('funcionarios.criar');
    }

    public function updateFuncionario(User $user, User $funcionario): bool
    {
        return $user->can('funcionarios.atualizar');
    }

    public function deleteFuncionario(User $user, User $funcionario): bool
    {
        // Impede que o usuário exclua a si mesmo E verifica a permissão
        if ($user->id === $funcionario->id) {
            return false;
        }

        return $user->can('funcionarios.excluir');
    }

    public function manageRolesFuncionario(User $user): bool
    {
        return $user->can('funcionarios.gerenciar_perfis');
    }
}
