<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\SolicitacaoServico;
use App\Models\Tenant\User; // Corretamente apontando para seu modelo de usuário.
use Illuminate\Auth\Access\HandlesAuthorization;

class SolicitacaoServicoPolicy
{
    use HandlesAuthorization;

    /**
     * Determina se o usuário pode visualizar a página com a listagem de SUAS solicitações.
     */
    public function viewAny(User $user): bool
    {
        // Apenas usuários administrativos podem ver a lista completa.
        return $user->hasRole(['Admin Tenant', 'Funcionario']);
    }

    /**
     * Determina se o usuário pode visualizar os detalhes de UMA solicitação específica.
     */
    public function view(User $user, SolicitacaoServico $solicitacaoServico): bool
    {
        // CORREÇÃO: O usuário só pode ver a solicitação se for o dono dela.
        // Podemos adicionar uma verificação de permissão para administradores, se necessário.
        return $user->id === $solicitacaoServico->user_id || $user->can('gerenciar solicitacoes');
    }

    /**
     * Determina se o usuário pode acessar a página para criar novas solicitações.
     */
    public function create(User $user): bool
    {
        // CORREÇÃO: Qualquer usuário autenticado deve poder tentar criar uma solicitação.
        return true;
    }

    /**
     * ADICIONADO: Determina se o usuário pode avaliar a solicitação.
     * Este método será usado no método `avaliar()` do seu controller.
     */
    public function avaliar(User $user, SolicitacaoServico $solicitacaoServico): bool
    {
        // A regra é clara: apenas o dono da solicitação pode avaliá-la.
        return $user->id === $solicitacaoServico->user_id;
    }

    /**
     * Determina se o usuário pode atualizar a solicitação.
     */
    public function update(User $user, SolicitacaoServico $solicitacaoServico): bool
    {
        // Apenas quem pode gerenciar solicitações pode usar este método de update.
        return $user->can('gerenciar solicitacoes');
    }

    /**
     * Determina se o usuário pode deletar a solicitação.
     * Retornar false é uma opção segura se essa funcionalidade não existe.
     */
    public function delete(User $user, SolicitacaoServico $solicitacaoServico): bool
    {
        // Lógica de exemplo: permitir apenas para administradores.
        return $user->can('gerenciar solicitacoes');
    }

    // Os métodos restore e forceDelete podem permanecer como false se não forem utilizados.
    public function restore(User $user, SolicitacaoServico $solicitacaoServico): bool
    {
        return false;
    }

    public function forceDelete(User $user, SolicitacaoServico $solicitacaoServico): bool
    {
        return false;
    }
}
