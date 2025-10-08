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
     *
     * @return bool
     */
    public function viewAnyAdmin(User $user)
    {
        // Apenas usuários com a permissão específica podem ver a lista administrativa.
        return $user->can('gerenciar gabinete virtual');
    }

    /**
     * Determina se o usuário pode visualizar a lista de suas próprias mensagens (cidadão).
     *
     * @return bool
     */
    public function viewAny(User $user)
    {
        // Assumindo que o cidadão tem a role 'Cidadao'.
        // Qualquer usuário autenticado pode ver sua própria caixa de entrada.
        return $user->hasRole('Cidadao');
    }

    /**
     * Determina se o usuário pode visualizar uma mensagem específica.
     *
     * @return bool
     */
    public function view(User $user, GabineteVirtualMensagem $mensagem)
    {
        // Permite se o usuário for o dono da mensagem OU tiver a permissão de admin.
        return $user->id === $mensagem->user_id || $user->can('gerenciar gabinete virtual');
    }

    /**
     * Determina se o usuário pode criar uma nova mensagem (cidadão).
     *
     * @return bool
     */
    public function create(User $user)
    {
        // Apenas cidadãos podem criar novas mensagens.
        return $user->hasRole('Cidadao');
    }

    /**
     * Determina se o usuário pode atualizar uma mensagem (admin).
     * Neste caso, usado para atualizar o status.
     *
     * @return bool
     */
    public function update(User $user, GabineteVirtualMensagem $mensagem)
    {
        // Apenas administradores podem atualizar o status da mensagem.
        return $user->can('gerenciar gabinete virtual');
    }

    /**
     * Determina se o usuário pode criar uma resposta para uma mensagem (admin).
     *
     * @return bool
     */
    public function reply(User $user, GabineteVirtualMensagem $mensagem)
    {
        // Apenas administradores podem responder.
        return $user->can('gerenciar gabinete virtual');
    }
}
