<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PesquisaSatisfacao extends Model
{
    // Usamos os traits HasFactory e o importantíssimo UsesTenantConnection
    use HasFactory, LogsActivity, UsesTenantConnection;

    /**
     * O nome da tabela associada ao modelo.
     * Isso corrige o problema do nome pluralizado incorretamente.
     *
     * @var string
     */
    protected $table = 'pesquisas_satisfacao';

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'solicitacao_servico_id',
        'user_id',
        'nota',
        'comentario',
    ];

    /**
     * Configura como as atividades deste modelo devem ser logadas.
     */
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
     * Define o relacionamento: uma pesquisa pertence a uma solicitação de serviço.
     */
    public function solicitacaoServico(): BelongsTo
    {
        return $this->belongsTo(SolicitacaoServico::class, 'solicitacao_servico_id', 'id');
    }

    /**
     * Define o relacionamento: uma pesquisa pertence a um cidadão (usuário).
     */
    public function cidadao(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
