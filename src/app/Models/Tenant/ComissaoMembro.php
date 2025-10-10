<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class ComissaoMembro extends Model
{
    use HasFactory, LogsActivity, UsesTenantConnection;

    protected $table = 'comissao_membros';

    protected $fillable = [
        'comissao_id',
        'politico_id',
        'cargo',
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
     * Get the commission that the member belongs to.
     */
    public function comissao(): BelongsTo
    {
        return $this->belongsTo(Comissao::class);
    }

    /**
     * Get the politician that is a member.
     */
    public function politico(): BelongsTo
    {
        return $this->belongsTo(Politico::class);
    }
}
