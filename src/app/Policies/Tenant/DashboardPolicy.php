<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DashboardPolicy
{
    use HandlesAuthorization;

    /**
     * Determina se o usuário pode visualizar o dashboard do cidadão (Portal Pessoal).
     *
     * @param  \App\Models\Tenant\User  $user
     * @return bool
     */
    public function viewCidadaoDashboard(User $user): bool
    {
        return $user->hasRole('Cidadao');
    }

    /**
     * Determina se o usuário pode visualizar o dashboard administrativo.
     *
     * A lógica aqui é permitir o acesso a qualquer usuário que NÃO seja um 'Cidadao',
     * o que implicitamente cobre os papéis administrativos.
     *
     * @param  \App\Models\Tenant\User  $user
     * @return bool
     */
    public function viewAdminDashboard(User $user): bool
    {
        return !$user->hasRole('Cidadao');
    }
}
