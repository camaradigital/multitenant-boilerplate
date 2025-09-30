<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\Candidatura;
use App\Models\Tenant\User;
use App\Models\Tenant\Vaga;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class CandidaturaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     * Apenas administradores podem ver a lista de todas as candidaturas.
     *
     * @param  \App\Models\Tenant\User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('gerenciar vagas de emprego');
    }

    /**
     * Determine whether the user can view the model.
     * O administrador pode ver qualquer uma, ou o utilizador pode ver a sua própria.
     *
     * @param  \App\Models\Tenant\User  $user
     * @param  \App\Models\Tenant\Candidatura  $candidatura
     * @return bool
     */
    public function view(User $user, Candidatura $candidatura): bool
    {
        if ($user->can('gerenciar vagas de emprego')) {
            return true;
        }

        return $user->id === $candidatura->user_id;
    }

    /**
     * Determine whether the user can create models.
     * Qualquer utilizador autenticado pode candidatar-se, mas apenas uma vez por vaga.
     *
     * @param  \App\Models\Tenant\User  $user
     * @param  \App\Models\Tenant\Vaga  $vaga
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user, Vaga $vaga)
    {
        // Verifica se o utilizador já se candidatou a esta vaga específica
        $hasApplied = Candidatura::where('user_id', $user->id)
                                 ->where('vaga_id', $vaga->id)
                                 ->exists();

        return !$hasApplied
            ? Response::allow()
            : Response::deny('Você já se candidatou para esta vaga.');
    }

    /**
     * Determine whether the user can update the model.
     * Apenas administradores podem atualizar o estado de uma candidatura.
     *
     * @param  \App\Models\Tenant\User  $user
     * @param  \App\Models\Tenant\Candidatura  $candidatura
     * @return bool
     */
    public function update(User $user, Candidatura $candidatura): bool
    {
        return $user->can('gerenciar vagas de emprego');
    }

    /**
     * Determine whether the user can delete the model.
     * Apenas administradores podem apagar uma candidatura.
     *
     * @param  \App\Models\Tenant\User  $user
     * @param  \App\Models\Tenant\Candidatura  $candidatura
     * @return bool
     */
    public function delete(User $user, Candidatura $candidatura): bool
    {
        return $user->can('gerenciar vagas de emprego');
    }
}
