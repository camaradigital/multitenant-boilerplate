<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User;

class RelatorioPolicy
{
    /**
     * Determina se o usuário pode visualizar os relatórios de atendimento.
     */
    public function viewAtendimentos(User $user): bool
    {
        return $user->hasAnyRole(['Admin Tenant', 'Funcionario']);
    }

    /**
     * Determina se o usuário pode visualizar o relatório de satisfação.
     */
    public function viewSatisfacao(User $user): bool
    {
        return $user->hasAnyRole(['Admin Tenant', 'Funcionario']);
    }

    /**
     * Determina se o usuário pode visualizar o relatório de cidadãos.
     */
    public function viewCidadaos(User $user): bool
    {
        return $user->hasAnyRole(['Admin Tenant', 'Funcionario']);
    }

    /**
     * (NOVO) Determina se o usuário pode visualizar o mapeamento de demandas por bairro.
     */
    public function viewDemandasPorBairro(User $user): bool
    {
        return $user->hasAnyRole(['Admin Tenant']);
    }

    /**
     * (NOVO) Determina se o usuário pode visualizar a análise de tendências.
     */
    public function viewAnaliseDeTendencias(User $user): bool
    {
        return $user->hasAnyRole(['Admin Tenant']);
    }
}
