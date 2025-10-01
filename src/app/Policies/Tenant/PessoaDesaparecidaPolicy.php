<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\PessoaDesaparecida;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PessoaDesaparecidaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        // Qualquer usuário autenticado pode visualizar a lista de pessoas desaparecidas.
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('gerenciar achados e perdidos');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, PessoaDesaparecida $pessoaDesaparecida)
    {
        // Apenas usuários com perfil de 'admin' podem atualizar o status.
        // Adapte esta lógica conforme a sua implementação de roles/permissões.
        // Exemplo: return $user->hasRole('admin');
        return $user->can('gerenciar achados e perdidos');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, PessoaDesaparecida $pessoaDesaparecida)
    {
        // Apenas usuários com perfil de 'admin' podem excluir o registro.
        // Adapte esta lógica conforme a sua implementação de roles/permissões.
        return $user->can('gerenciar achados e perdidos');
    }

    /**
     * Determine whether the user can view the police report.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewBoletim(User $user, PessoaDesaparecida $pessoaDesaparecida)
    {
        // Apenas admins podem visualizar/baixar o boletim de ocorrência.
        // Adapte conforme sua necessidade.
        return $user->can('gerenciar achados e perdidos');
    }
}
