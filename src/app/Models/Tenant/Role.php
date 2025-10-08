<?php

namespace App\Models\Tenant;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    // Adiciona os traits para conexão do tenant e para o log de atividades.
    use LogsActivity, UsesTenantConnection;

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'guard_name',
    ];

    /**
     * Configura como as atividades deste modelo devem ser logadas.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            // Loga todas as alterações nos campos do modelo
            ->logAll()
            // Registra apenas os campos que realmente mudaram
            ->logOnlyDirty()
            // Evita a criação de logs de atividade vazios se nada for alterado
            ->dontSubmitEmptyLogs();
    }
}
