<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class StatusSolicitacao extends Model
{
    use HasFactory, LogsActivity, UsesTenantConnection;

    /**
     * O nome da tabela associada com o model.
     *
     * @var string
     */
    protected $table = 'status_solicitacao';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nome',
        'cor',
        'is_default_abertura',
        'is_final',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_default_abertura' => 'boolean',
        'is_final' => 'boolean',
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
     * Obtém as solicitações de serviço que possuem este status.
     */
    public function solicitacoesServico()
    {
        return $this->hasMany(SolicitacaoServico::class, 'status_id');
    }
}
