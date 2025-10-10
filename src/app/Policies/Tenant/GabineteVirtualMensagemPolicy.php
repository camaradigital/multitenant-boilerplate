<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\GabineteVirtualMensagem;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GabineteVirtualMensagemPolicy
{
    use HandlesAuthorization;

    /**
     * Determina se um usuário administrador pode visualizar a lista de todas as mensagens.
     */
    public function viewAnyAdmin(User $user): bool
    {
        // Alinhado com a nova permissão granular para a visão administrativa.
        return $user->can('gabinete_virtual.visualizar_mensagens');
    }

    /**
     * Determina se o usuário pode visualizar a lista de suas próprias mensagens (cidadão).
     */
    public function viewAny(User $user): bool
    {
        // A regra de negócio aqui é que o usuário é um Cidadão.
        // Manter a verificação de Role é o mais direto para este caso específico.
        return $user->hasRole('Cidadao');
    }

    /**
     * Determina se o usuário pode visualizar uma mensagem específica.
     */
    public function view(User $user, GabineteVirtualMensagem $mensagem): bool
    {
        // Permite se o usuário for o dono da mensagem OU tiver a permissão de admin.
        return $user->id === $mensagem->user_id || $user->can('gabinete_virtual.visualizar_mensagens');
    }

    /**
     * Determina se o usuário pode criar uma nova mensagem (cidadão).
     */
    public function create(User $user): bool
    {
        // Apenas cidadãos podem criar novas mensagens. Manter a verificação por Role é o correto.
        return $user->hasRole('Cidadao');
    }

    /**
     * Determina se o usuário pode atualizar uma mensagem (admin).
     * Neste caso, usado para atualizar o status.
     */
    public function update(User $user, GabineteVirtualMensagem $mensagem): bool
    {
        // A permissão para responder implica a capacidade de gerenciar o estado da mensagem (ex: marcar como lida).
        return $user->can('gabinete_virtual.responder_mensagens');
    }

    /**
     * Determina se o usuário pode criar uma resposta para uma mensagem (admin).
     */
    public function reply(User $user, GabineteVirtualMensagem $mensagem): bool
    {
        // Alinhado com a nova permissão granular para responder.
        return $user->can('gabinete_virtual.responder_mensagens');
    }
}
