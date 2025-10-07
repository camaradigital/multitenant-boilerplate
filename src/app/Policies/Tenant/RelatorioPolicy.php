<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User;
use Illuminate\Auth\Access\HandlesAuthorization; // Importe este Trait

class RelatorioPolicy
{
    use HandlesAuthorization; // Use o Trait aqui

    /**
     * Determina se o usuário pode visualizar os relatórios de atendimento.
     */
    public function viewAtendimentos(User $user): bool
    {
        return $user->can('visualizar relatorios');
    }

    /**
     * Determina se o usuário pode visualizar o relatório de satisfação.
     */
    public function viewSatisfacao(User $user): bool
    {
        return $user->can('visualizar relatorios');
    }

    /**
     * Determina se o usuário pode visualizar o relatório de cidadãos.
     */
    public function viewCidadaos(User $user): bool
    {
        return $user->can('visualizar relatorios');
    }

    /**
     * Determina se o usuário pode visualizar o mapeamento de demandas por bairro.
     */
    public function viewDemandasPorBairro(User $user): bool
    {
        return $user->can('visualizar relatorios');
    }

    /**
     * Determina se o usuário pode visualizar a análise de tendências.
     */
    public function viewAnaliseDeTendencias(User $user): bool
    {
        return $user->can('visualizar relatorios');
    }

    /**
     * [ADICIONE ESTE MÉTODO]
     * Determina se o usuário pode visualizar o Painel de Mapeamento Político.
     */
    public function viewMapeamentoPolitico(User $user): bool
    {
        // Reutiliza a mesma permissão dos outros relatórios
        return $user->can('visualizar relatorios');
    }
}
