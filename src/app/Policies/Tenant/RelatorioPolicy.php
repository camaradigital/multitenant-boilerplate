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

    /**
     * (NOVO) Determina se o usuário pode visualizar o mapeamento de demandas por bairro.
     *
     * @param  \App\Models\Tenant\User  $user
     * @return bool
     */
    public function viewDemandasPorBairro(User $user): bool
    {
        return $user->hasAnyRole(['Admin Tenant']);
    }

    /**
     * (NOVO) Determina se o usuário pode visualizar a análise de tendências.
     *
     * @param  \App\Models\Tenant\User  $user
     * @return bool
     */
    public function viewAnaliseDeTendencias(User $user): bool
    {
        return $user->hasAnyRole(['Admin Tenant']);
    }
}
