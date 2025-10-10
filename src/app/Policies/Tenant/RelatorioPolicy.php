<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RelatorioPolicy
{
    use HandlesAuthorization;

    /**
     * Determina se o usuário pode visualizar os relatórios de atendimento.
     * (Usa a permissão geral de relatórios)
     */
    public function viewAtendimentos(User $user): bool
    {
        return $user->can('relatorios.visualizar_atendimentos');
    }

    /**
     * Determina se o usuário pode visualizar o relatório de satisfação.
     * (Usa a permissão geral de relatórios)
     */
    public function viewSatisfacao(User $user): bool
    {
        return $user->can('relatorios.visualizar_satisfacao');
    }

    /**
     * Determina se o usuário pode visualizar o relatório de cidadãos.
     * (Usa a permissão geral de relatórios)
     */
    public function viewCidadaos(User $user): bool
    {
        return $user->can('relatorios.visualizar_cidadaos');
    }

    /**
     * Determina se o usuário pode visualizar o mapeamento de demandas por bairro.
     * (Usa a permissão específica deste relatório)
     */
    public function viewDemandasPorBairro(User $user): bool
    {
        return $user->can('relatorios.visualizar_demandas_por_bairro');
    }

    /**
     * Determina se o usuário pode visualizar la análise de tendências.
     * (Usa a permissão específica deste relatório)
     */
    public function viewAnaliseDeTendencias(User $user): bool
    {
        return $user->can('relatorios.visualizar_analise_de_tendencias');
    }

    /**
     * Determina se o usuário pode visualizar o Painel de Mapeamento Político.
     * (Usa a permissão geral de relatórios)
     */
    public function viewMapeamentoPolitico(User $user): bool
    {
        return $user->can('relatorios.visualizar_mapeamento_politico');
    }
}
