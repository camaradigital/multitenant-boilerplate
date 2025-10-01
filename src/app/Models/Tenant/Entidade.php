<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Entidade extends Model
{
    use HasFactory, LogsActivity, UsesTenantConnection;

    protected $fillable = [
        'nome',
        'categoria',
        'cnpj',
        'telefone',
        'email',
        'endereco',
        'descricao',
        'is_active',
        'registrado_por_user_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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
     * Obtém o funcionário que registrou a entidade.
     */
    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por_user_id');
    }
}
