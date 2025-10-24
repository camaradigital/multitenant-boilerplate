<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Bairro extends Model
{
    use HasFactory, LogsActivity, UsesTenantConnection;

    protected $fillable = ['nome', 'tipo_logradouro', 'aprovado']; // <--- 'aprovado' foi adicionado

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
     * Um bairro pode ter muitos usuários.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
