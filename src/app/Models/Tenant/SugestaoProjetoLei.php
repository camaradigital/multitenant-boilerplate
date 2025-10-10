<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class SugestaoProjetoLei extends Model
{
    use HasFactory, LogsActivity, UsesTenantConnection;

    protected $table = 'sugestoes_projetos_lei';

    protected $fillable = [
        'protocolo',
        'cidadao_nome',
        'cidadao_email',
        'cidadao_telefone',
        'titulo',
        'descricao',
        'area_tematica_id',
        'anexo_path',
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

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($sugestao) {
            $sugestao->protocolo = date('YmdHis').substr(str_shuffle('0123456789'), 0, 4);
        });
    }

    /**
     * Get the thematic area (commission) that the suggestion belongs to.
     */
    public function areaTematica(): BelongsTo
    {
        return $this->belongsTo(Comissao::class, 'area_tematica_id');
    }
}
