<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * Este é um modelo virtual usado apenas para o mapeamento da Policy de Relatórios.
 * Não precisa de uma tabela correspondente no banco de dados.
 */
class Relatorio extends Model
{
    // Adiciona os traits para conexão do tenant e para o log de atividades.
    use LogsActivity, UsesTenantConnection;

    /**
     * Configura como as atividades deste modelo devem ser logadas.
     * Como este é um modelo virtual, os logs geralmente serão acionados manualmente.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            // Descreve o que foi logado
            ->setDescriptionForEvent(fn (string $eventName) => "Um relatório foi {$eventName}")
            // Evita logs vazios
            ->dontSubmitEmptyLogs();
    }
}
