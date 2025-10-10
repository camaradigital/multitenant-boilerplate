<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\SolicitacaoServico;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SolicitacaoServicoPolicy
{
    use HandlesAuthorization;

    /**
     * Determina se o usuário pode visualizar a listagem de solicitações.
     */
    public function viewAny(User $user): bool
    {
        // Agora verifica a permissão granular para ver a lista de solicitações.
        return $user->can('solicitacoes.visualizar_todos');
    }

    /**
     * Determina se o usuário pode visualizar os detalhes de UMA solicitação específica.
     */
    public function view(User $user, SolicitacaoServico $solicitacaoServico): bool
    {
        // 1. O "dono" (cidadão) sempre pode ver sua própria solicitação.
        if ($user->id === $solicitacaoServico->user_id) {
            return true;
        }

        // 2. (LÓGICA DO ADVOGADO COORDENADOR) Permite se tiver permissão de supervisão e a solicitação for jurídica.
        if ($user->can('solicitacoes.supervisionar_juridico') && $solicitacaoServico->servico?->is_juridico) {
            return true;
        }

        // 3. Permite se o usuário tiver a permissão para visualizar qualquer solicitação.
        return $user->can('solicitacoes.visualizar');
    }

    /**
     * Determina se o usuário pode criar novas solicitações.
     */
    public function create(User $user): bool
    {
        // Controlado pela permissão 'criar'. Por padrão, cidadãos devem ter essa permissão.
        return $user->can('solicitacoes.criar');
    }

    /**
     * Determina se o usuário pode atualizar a solicitação (mudar status, atendente, etc.).
     */
    public function update(User $user, SolicitacaoServico $solicitacaoServico): bool
    {
        // Permite se tiver permissão de supervisão para um caso jurídico.
        if ($user->can('solicitacoes.supervisionar_juridico') && $solicitacaoServico->servico?->is_juridico) {
            return true;
        }

        // Permite se tiver a permissão geral de atualização.
        return $user->can('solicitacoes.atualizar');
    }

    /**
     * Determina se o usuário pode deletar a solicitação.
     */
    public function delete(User $user, SolicitacaoServico $solicitacaoServico): bool
    {
        // Permite se tiver permissão de supervisão para um caso jurídico.
        if ($user->can('solicitacoes.supervisionar_juridico') && $solicitacaoServico->servico?->is_juridico) {
            return true;
        }

        // Permite se tiver a permissão geral para excluir.
        return $user->can('solicitacoes.excluir');
    }

    /**
     * Determina se o usuário pode avaliar a solicitação.
     */
    public function avaliar(User $user, SolicitacaoServico $solicitacaoServico): bool
    {
        // Apenas o dono da solicitação (cidadão) pode avaliá-la.
        return $user->id === $solicitacaoServico->user_id;
    }

    /**
     * Determina se o usuário pode atribuir um responsável à solicitação.
     */
    public function assign(User $user, SolicitacaoServico $solicitacaoServico): bool
    {
        return $user->can('solicitacoes.atribuir');
    }

    /**
     * Determina se o usuário pode gerenciar o status da solicitação.
     */
    public function manageStatus(User $user, SolicitacaoServico $solicitacaoServico): bool
    {
        return $user->can('solicitacoes.gerenciar_status');
    }

    /**
     * Determina se o usuário pode restaurar a solicitação.
     */
    public function restore(User $user, SolicitacaoServico $solicitacaoServico): bool
    {
        return false;
    }

    /**
     * Determina se o usuário pode apagar permanentemente a solicitação.
     */
    public function forceDelete(User $user, SolicitacaoServico $solicitacaoServico): bool
    {
        return false;
    }
}
