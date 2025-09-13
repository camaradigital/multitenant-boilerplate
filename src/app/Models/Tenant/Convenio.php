<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Convenio extends Model
{
    use HasFactory, LogsActivity, UsesTenantConnection;

    protected $fillable = [
        'entidade_id',
        'objeto',
        'descricao',
        'data_inicio',
        'data_fim',
        'status',
        'registrado_por_user_id',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            // Loga todas as alterações nos campos que estão no array $fillable.
            ->logFillable()
            // Registra apenas os campos que realmente mudaram.
            ->logOnlyDirty()
            // Evita a criação de logs de atividade vazios se nada for alterado.
            ->dontSubmitEmptyLogs();
    }

    /**
     * Obtém a entidade com a qual o convênio foi firmado.
     */
    public function entidade()
    {
        return $this->belongsTo(Entidade::class);
    }

    /**
     * Obtém o funcionário que registrou o convênio.
     */
    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por_user_id');
    }
}
