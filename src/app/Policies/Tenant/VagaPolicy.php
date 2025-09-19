<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User;
use App\Models\Tenant\Vaga;
use Illuminate\Auth\Access\HandlesAuthorization;

class VagaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\Tenant\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->can('gerenciar vagas de emprego');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\Tenant\User  $user
     * @param  \App\Models\Tenant\Vaga  $vaga
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Vaga $vaga)
    {
        return $user->can('gerenciar vagas de emprego');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\Tenant\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('gerenciar vagas de emprego');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\Tenant\User  $user
     * @param  \App\Models\Tenant\Vaga  $vaga
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Vaga $vaga)
    {
        return $user->can('gerenciar vagas de emprego');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\Tenant\User  $user
     * @param  \App\Models\Tenant\Vaga  $vaga
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Vaga $vaga)
    {
        return $user->can('gerenciar vagas de emprego');
    }
}
