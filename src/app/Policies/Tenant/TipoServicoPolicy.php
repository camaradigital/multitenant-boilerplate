<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\TipoServico;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class TipoServicoPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('tipos_servico.visualizar_todos');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('tipos_servico.criar');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TipoServico $tipoServico): bool
    {
        return $user->can('tipos_servico.atualizar');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TipoServico $tipoServico): Response
    {
        if (! $user->can('tipos_servico.excluir')) {
            return Response::deny('Você não tem permissão para excluir tipos de serviço.');
        }

        // Regra de negócio: Não permitir exclusão se o tipo de serviço estiver em uso.
        if ($tipoServico->servicos()->exists()) {
            return Response::deny('Não é possível excluir. Existem serviços associados a este tipo.');
        }

        return Response::allow();
    }
}
