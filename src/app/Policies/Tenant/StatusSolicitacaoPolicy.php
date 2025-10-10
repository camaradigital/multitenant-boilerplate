<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\StatusSolicitacao;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class StatusSolicitacaoPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('status_solicitacao.visualizar_todos');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('status_solicitacao.criar');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, StatusSolicitacao $statusSolicitacao): bool
    {
        return $user->can('status_solicitacao.atualizar');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, StatusSolicitacao $statusSolicitacao): Response
    {
        // Primeiro, verifica a permissão de exclusão.
        if (! $user->can('status_solicitacao.excluir')) {
            return Response::deny('Você não tem permissão para excluir status.');
        }

        // Regra de segurança: Não permitir exclusão do status padrão de abertura.
        if ($statusSolicitacao->is_default_abertura) {
            return Response::deny('Não é possível excluir o status padrão de abertura.');
        }

        // Regra de segurança: Não permitir exclusão se o status estiver em uso.
        if ($statusSolicitacao->solicitacoesServico()->exists()) {
            return Response::deny('Não é possível excluir o status, pois ele já está sendo utilizado em solicitações.');
        }

        return Response::allow();
    }
}
