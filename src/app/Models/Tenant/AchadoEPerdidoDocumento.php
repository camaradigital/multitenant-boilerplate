<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AchadoEPerdidoDocumento extends Model
{
    use HasFactory, LogsActivity, UsesTenantConnection;

    protected $table = 'achados_e_perdidos_documentos';

    protected $fillable = [
        'tipo_documento',
        'nome_completo',
        'numero_documento',
        'data_encontrado',
        'local_encontrado',
        'entregue_por',
        'status',
        'observacoes',
        'data_entrega',
        'retirado_por_nome',
        'retirado_por_cpf',
        'registrado_por_user_id',
        'entregue_por_user_id',
    ];

    protected $casts = [
        'data_encontrado' => 'date',
        'data_entrega' => 'date',
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
     * Obtém o funcionário que registrou o documento.
     */
    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por_user_id');
    }

    /**
     * Obtém o funcionário que realizou a entrega do documento.
     */
    public function entreguePor()
    {
        return $this->belongsTo(User::class, 'entregue_por_user_id');
    }
}
