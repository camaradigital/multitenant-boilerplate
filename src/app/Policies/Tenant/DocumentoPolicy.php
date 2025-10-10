<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\Documento;
use App\Models\Tenant\SolicitacaoServico;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DocumentoPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view (download) the document.
     * O usuário pode baixar se for o dono da solicitação OU se tiver a permissão 'documentos.visualizar'.
     */
    public function view(User $user, Documento $documento): bool
    {
        // Permite se o usuário for o dono da solicitação à qual o documento pertence.
        if ($user->id === $documento->solicitacao->user_id) {
            return true;
        }

        // Permite se o usuário tiver a permissão específica para visualizar documentos.
        return $user->can('documentos.visualizar');
    }

    /**
     * Determine whether the user can create documents for a given solicitation.
     * O usuário pode criar (fazer upload) se for o dono da solicitação OU se tiver a permissão 'documentos.criar'.
     */
    public function create(User $user, SolicitacaoServico $solicitacao): bool
    {
        // Permite se o usuário for o dono da solicitação.
        if ($user->id === $solicitacao->user_id) {
            return true;
        }

        // Permite se o usuário tiver a permissão específica para criar documentos.
        return $user->can('documentos.criar');
    }

    /**
     * Determine whether the user can delete the document.
     * O usuário pode deletar se ele mesmo fez o upload do arquivo OU se tiver a permissão 'documentos.excluir'.
     */
    public function delete(User $user, Documento $documento): bool
    {
        // Permite se o usuário for quem fez o upload do documento.
        if ($documento->user_id === $user->id) {
            return true;
        }

        // Permite se o usuário tiver a permissão específica para excluir documentos.
        return $user->can('documentos.excluir');
    }
}
