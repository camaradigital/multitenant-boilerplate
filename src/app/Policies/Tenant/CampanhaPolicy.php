<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\CampanhaComunicacao;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CampanhaPolicy
{
    use HandlesAuthorization;

    /**
     * Determina se o usuário pode visualizar qualquer modelo.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        // Apenas usuários com a permissão 'gerenciar campanhas' podem listar as campanhas.
        return $user->can('gerenciar campanhas');
    }

    /**
     * Determina se o usuário pode visualizar o modelo.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, CampanhaComunicacao $campanhaComunicacao)
    {
        // A mesma permissão para visualizar a lista serve para ver um item específico.
        return $user->can('gerenciar campanhas');
    }

    /**
     * Determina se o usuário pode criar modelos.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        // Apenas usuários com a permissão 'gerenciar campanhas' podem criar.
        return $user->can('gerenciar campanhas');
    }

    /**
     * Determina se o usuário pode atualizar o modelo.
     * (Método não utilizado no controller atual, mas é uma boa prática tê-lo)
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, CampanhaComunicacao $campanhaComunicacao)
    {
        // Por padrão, desabilitado. Poderia ser, por exemplo:
        // return $user->can('gerenciar campanhas');
        return false;
    }

    /**
     * Determina se o usuário pode deletar o modelo.
     * (Método não utilizado no controller atual, mas é uma boa prática tê-lo)
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, CampanhaComunicacao $campanhaComunicacao)
    {
        // Por padrão, desabilitado.
        return false;
    }

    /**
     * Determina se o usuário pode restaurar o modelo.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, CampanhaComunicacao $campanhaComunicacao)
    {
        //
        return false;
    }

    /**
     * Determina se o usuário pode deletar permanentemente o modelo.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, CampanhaComunicacao $campanhaComunicacao)
    {
        //
        return false;
    }
}
