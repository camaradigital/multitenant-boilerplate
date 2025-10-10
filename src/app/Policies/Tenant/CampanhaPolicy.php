<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\CampanhaComunicacao;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CampanhaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->can('campanhas.visualizar_todos');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return bool
     */
    public function view(User $user, CampanhaComunicacao $campanhaComunicacao)
    {
        return $user->can('campanhas.visualizar');
    }

    /**
     * Determine whether the user can create models.
     *
     * @return bool
     */
    public function create(User $user)
    {
        return $user->can('campanhas.criar');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return bool
     */
    public function update(User $user, CampanhaComunicacao $campanhaComunicacao)
    {
        return $user->can('campanhas.atualizar');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return bool
     */
    public function delete(User $user, CampanhaComunicacao $campanhaComunicacao)
    {
        return $user->can('campanhas.excluir');
    }

    /**
     * Determine whether the user can send the campaign.
     *
     * @return bool
     */
    public function send(User $user, CampanhaComunicacao $campanhaComunicacao)
    {
        return $user->can('campanhas.enviar');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return bool
     */
    public function restore(User $user, CampanhaComunicacao $campanhaComunicacao)
    {
        // Esta ação não foi definida no seeder, mantida como false.
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return bool
     */
    public function forceDelete(User $user, CampanhaComunicacao $campanhaComunicacao)
    {
        // Esta ação não foi definida no seeder, mantida como false.
        return false;
    }
}
