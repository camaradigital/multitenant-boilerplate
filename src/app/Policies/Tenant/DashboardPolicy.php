<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DashboardPolicy
{
    use HandlesAuthorization;

    /**
     * Determina se o usuário pode visualizar o dashboard do cidadão (Portal Pessoal).
     * A regra de negócio aqui é que o usuário precisa ser um Cidadão.
     * Manter a verificação de Role é o mais direto e correto para este caso.
     */
    public function viewCidadaoDashboard(User $user): bool
    {
        return $user->hasRole('Cidadao');
    }

    /**
     * Determina se o usuário pode visualizar o dashboard administrativo.
     * A verificação foi atualizada para usar a permissão granular 'dashboard.visualizar'.
     */
    public function viewAdminDashboard(User $user): bool
    {
        return $user->can('dashboard.visualizar');
    }
}
