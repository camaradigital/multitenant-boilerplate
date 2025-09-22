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
        // Permite o acesso à página de listagem para todos os perfis administrativos.
        // A filtragem do que cada um vê é feita no Controller.
        return $user->hasAnyRole(['Admin Tenant', 'Funcionario', 'Advogado Coordenador']);
    }

    /**
     * Determina se o usuário pode visualizar os detalhes de UMA solicitação específica.
     */
    public function view(User $user, SolicitacaoServico $solicitacaoServico): bool
    {
        // Um usuário pode ver uma solicitação se:
        // 1. Ele for o "dono" (o cidadão que solicitou).
        if ($user->id === $solicitacaoServico->user_id) {
            return true;
        }

        // 2. Ele tiver a permissão geral para gerenciar TODAS as solicitações (como um Admin).
        if ($user->can('gerenciar solicitacoes')) {
            return true;
        }

        // 3. (LÓGICA DO ADVOGADO COORDENADOR) Ele puder supervisionar solicitações jurídicas
        //    E a solicitação em questão for de um serviço jurídico.
        if ($user->can('supervisionar solicitacoes juridicas') && $solicitacaoServico->servico->is_juridico) {
            return true;
        }

        return false;
    }

    /**
     * Determina se o usuário pode criar novas solicitações.
     */
    public function create(User $user): bool
    {
        // Qualquer usuário autenticado pode tentar criar uma solicitação.
        // As regras de negócio (limites, etc.) são validadas no Service/Controller.
        return true;
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
     * Determina se o usuário pode atualizar a solicitação (mudar status, atendente, etc.).
     */
    public function update(User $user, SolicitacaoServico $solicitacaoServico): bool
    {
        // Um usuário pode atualizar se tiver permissão geral OU
        // se tiver permissão de supervisão E a solicitação for jurídica.
        return $user->can('gerenciar solicitacoes') || ($user->can('supervisionar solicitacoes juridicas') && $solicitacaoServico->servico->is_juridico);
    }

    /**
     * Determina se o usuário pode deletar la solicitação.
     */
    public function delete(User $user, SolicitacaoServico $solicitacaoServico): bool
    {
        // --- ALTERAÇÃO SUGERIDA ---
        // A regra original era muito restritiva. A nova regra permite a exclusão se:

        // 1. O usuário tiver a permissão geral 'gerenciar solicitacoes' (ideal para o 'Admin Tenant').
        if ($user->can('gerenciar solicitacoes')) {
            return true;
        }

        // 2. O usuário for um 'Advogado Coordenador' E a solicitação for da área jurídica.
        //    Isso dá mais poder ao coordenador, mas de forma controlada.
        if ($user->can('supervisionar solicitacoes juridicas') && $solicitacaoServico->servico->is_juridico) {
            return true;
        }

        // Nega a exclusão para todos os outros casos.
        return false;
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
