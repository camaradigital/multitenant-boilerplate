<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Comissao extends Model
{
    use HasFactory, LogsActivity, UsesTenantConnection;

    protected $table = 'comissoes';

    protected $fillable = [
        'legislatura_id',
        'nome',
        'descricao',
        'tipo',
        'email_contato',
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
     * Get the legislature that the commission belongs to.
     */
    public function legislatura(): BelongsTo
    {
        return $this->belongsTo(Legislatura::class);
    }

    /**
     * Get the members of the commission.
     */
    public function membros(): HasMany
    {
        return $this->hasMany(ComissaoMembro::class);
    }
}
