<?php

namespace App\Policies;

use App\Models\Tenant\SolicitacaoServico;
use App\Models\Tenant\User; // Certifique-se de que este é o seu modelo de usuário do tenant
use Illuminate\Auth\Access\HandlesAuthorization;

class SolicitacaoServicoPolicy
{
    use HandlesAuthorization;

    /**
     * Determina se o usuário pode visualizar qualquer modelo.
     */
    public function viewAny(User $user): bool
    {
        // Verifica se o usuário tem a permissão para ver as solicitações
        return $user->can('ver solicitacoes');
    }

    /**
     * Determina se o usuário pode visualizar o modelo.
     */
    public function view(User $user, SolicitacaoServico $solicitacaoServico): bool
    {
        // Exemplo: permitir se o usuário pode gerenciar ou se ele criou a solicitação
        return $user->can('gerenciar solicitacoes'); // || $user->id === $solicitacaoServico->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SolicitacaoServico $solicitacaoServico): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SolicitacaoServico $solicitacaoServico): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SolicitacaoServico $solicitacaoServico): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SolicitacaoServico $solicitacaoServico): bool
    {
        return false;
    }
}
