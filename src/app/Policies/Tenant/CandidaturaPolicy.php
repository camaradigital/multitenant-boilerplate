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
     */
    public function viewAny(User $user): bool
    {
        return $user->can('candidaturas.visualizar_todos');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Candidatura $candidatura): bool
    {
        // Permite se o usuário tiver permissão para ver todas OU se for o dono da candidatura.
        return $user->can('candidaturas.visualizar') || $user->id === $candidatura->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Vaga $vaga): Response
    {
        // CORREÇÃO: Apenas usuários com o perfil 'Cidadao' podem se candidatar.
        if (! $user->hasRole('Cidadao')) {
            return Response::deny('Apenas cidadãos podem se candidatar a vagas.');
        }

        // Verifica se o cidadão já se candidatou a esta vaga específica
        $hasApplied = Candidatura::where('user_id', $user->id)
            ->where('vaga_id', $vaga->id)
            ->exists();

        return ! $hasApplied
            ? Response::allow()
            : Response::deny('Você já se candidatou para esta vaga.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Candidatura $candidatura): bool
    {
        return $user->can('candidaturas.atualizar_status');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Candidatura $candidatura): bool
    {
        // A exclusão de candidatura pode ser controlada pela mesma permissão de atualização de status.
        return $user->can('candidaturas.atualizar_status');
    }
}
