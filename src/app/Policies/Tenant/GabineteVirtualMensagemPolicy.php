<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\GabineteVirtualMensagem;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GabineteVirtualMensagemPolicy
{
    use HandlesAuthorization;

    /**
     * Determina se o usuário pode visualizar a lista de suas próprias mensagens (cidadão).
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Cidadao');
    }

    /**
     * Determina se um usuário administrador pode visualizar a lista de todas as mensagens.
     */
    public function viewAnyAdmin(User $user): bool
    {
        return $user->can('gabinete_virtual.visualizar_mensagens');
    }

    /**
     * Determina se o usuário pode visualizar uma mensagem específica (usado no portal do cidadão).
     */
    public function view(User $user, GabineteVirtualMensagem $mensagem): bool
    {
        // Permite se o usuário for o dono da mensagem OU tiver a permissão de admin.
        return $user->id === $mensagem->user_id || $user->can('gabinete_virtual.visualizar_mensagens');
    }

    /**
     * Determina se APENAS o admin pode ver a mensagem na área administrativa.
     */
    public function viewAdmin(User $user, GabineteVirtualMensagem $mensagem): bool
    {
        // Permite apenas se o usuário tiver a permissão de admin, ignorando quem é o dono.
        return $user->can('gabinete_virtual.visualizar_mensagens');
    }

    /**
     * Determina se o usuário pode criar uma nova mensagem (cidadão).
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Cidadao');
    }

    /**
     * Determina se o usuário pode atualizar o status de uma mensagem (admin).
     */
    public function update(User $user, GabineteVirtualMensagem $mensagem): bool
    {
        return $user->can('gabinete_virtual.responder_mensagens');
    }

    /**
     * Determina se o usuário pode criar uma resposta para uma mensagem (admin).
     */
    public function reply(User $user, GabineteVirtualMensagem $mensagem): bool
    {
        return $user->can('gabinete_virtual.responder_mensagens');
    }
}
