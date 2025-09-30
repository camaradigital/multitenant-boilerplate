<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Candidatura extends Model
{
    use HasFactory, LogsActivity, UsesTenantConnection;

    protected $fillable = [
        'vaga_id',
        'user_id',
        'curriculo_path',
        'mensagem_apresentacao',
        'status',
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

    public function vaga()
    {
        return $this->belongsTo(Vaga::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
