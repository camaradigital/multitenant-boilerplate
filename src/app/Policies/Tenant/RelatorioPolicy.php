<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User;

class RelatorioPolicy
{
    /**
     * Determina se o usuário pode visualizar os relatórios de atendimento.
     *
     * @param  \App\Models\Tenant\User  $user
     * @return bool
     */
    public function viewAtendimentos(User $user): bool
    {
        return $user->hasAnyRole(['Admin Tenant', 'Funcionario']);
    }

    /**
     * Determina se o usuário pode visualizar o relatório de satisfação.
     *
     * @param  \App\Models\Tenant\User  $user
     * @return bool
     */
    public function viewSatisfacao(User $user): bool
    {
        return $user->hasAnyRole(['Admin Tenant', 'Funcionario']);
    }

    /**
     * Determina se o usuário pode visualizar o relatório de cidadãos.
     *
     * @param  \App\Models\Tenant\User  $user
     * @return bool
     */
    public function viewCidadaos(User $user): bool
    {
        return $user->hasAnyRole(['Admin Tenant', 'Funcionario']);
    }
}
