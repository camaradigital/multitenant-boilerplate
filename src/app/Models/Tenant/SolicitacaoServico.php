<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany; // Adicionado
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class SolicitacaoServico extends Model
{
    use HasFactory, LogsActivity, UsesTenantConnection;

    protected $table = 'solicitacoes_servico';

    /**
     * Os atributos que podem ser atribuídos em massa.
     * Garante que todos os campos do formulário possam ser guardados.
     */
    protected $fillable = [
        'user_id',
        'servico_id',
        'atendente_id',
        'status_id',
        'status', // Mantido por compatibilidade, mas o ideal é usar status_id
        'observacoes',
        'finalizado_em',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'finalizado_em' => 'datetime',
    ];

    /**
     * Configura como as atividades deste modelo devem ser logadas.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "A solicitação foi {$eventName}");
    }

    // --- RELACIONAMENTOS ---

    /**
     * O cidadão que solicitou o serviço.
     */
    public function cidadao(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * O funcionário que atendeu à solicitação.
     */
    public function atendente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atendente_id');
    }

    /**
     * O serviço que foi solicitado.
     */
    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class, 'servico_id');
    }

    /**
     * O status atual da solicitação.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(StatusSolicitacao::class, 'status_id');
    }

    /**
     * Os documentos anexados a esta solicitação.
     */
    public function documentos(): HasMany
    {
        // Assumindo que a chave estrangeira em sua tabela de documentos é 'solicitacao_servico_id'
        return $this->hasMany(Documento::class, 'solicitacao_servico_id');
    }

    /**
     * A pesquisa de satisfação associada a esta solicitação.
     */
    public function pesquisa_satisfacao(): HasOne
    {
        return $this->hasOne(PesquisaSatisfacao::class, 'solicitacao_servico_id', 'id');
    }

    /**
     * Alias para o relacionamento de logs de atividade (histórico).
     * O controller usa 'historicos', enquanto o trait Spatie/Activitylog fornece 'activities'.
     * Esta função cria um alias para compatibilidade e correção do erro.
     */
    public function historicos(): MorphMany
    {
        // O trait LogsActivity fornece o método activities()
        return $this->activities();
    }
}
