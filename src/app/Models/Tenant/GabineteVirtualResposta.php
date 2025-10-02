<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class GabineteVirtualResposta extends Model
{
    use HasFactory, LogsActivity, UsesTenantConnection;

    protected $table = 'gabinete_virtual_respostas';

    protected $fillable = [
        'mensagem_id',
        'user_id',
        'resposta',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mensagem()
    {
        return $this->belongsTo(GabineteVirtualMensagem::class, 'mensagem_id');
    }
}
