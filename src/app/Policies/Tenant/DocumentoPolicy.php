<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\Documento;
use App\Models\Tenant\SolicitacaoServico;
use App\Models\Tenant\User;

class DocumentoPolicy
{
    /**
     * Determine whether the user can view (download) the document.
     * O usuário pode baixar se for o dono da solicitação OU um funcionário/admin.
     */
    public function view(User $user, Documento $documento): bool
    {
        return $user->id === $documento->solicitacao->user_id || $user->hasRole(['Admin Tenant', 'Funcionario']);
    }

    /**
     * Determine whether the user can create documents for a given solicitation.
     * O usuário pode criar (fazer upload) se for o dono da solicitação OU um funcionário/admin.
     */
    public function create(User $user, SolicitacaoServico $solicitacao): bool
    {
        return $user->id === $solicitacao->user_id || $user->hasRole(['Admin Tenant', 'Funcionario']);
    }

    /**
     * Determine whether the user can delete the document.
     * A regra principal: o usuário pode deletar SOMENTE se ele mesmo fez o upload do arquivo, exceto Admin
     */
    public function delete(User $user, Documento $documento): bool
    {
        return $documento->user_id === $user->id || $user->hasRole('Admin Tenant');
    }
}
