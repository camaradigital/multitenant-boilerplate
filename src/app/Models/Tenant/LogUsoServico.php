<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection; // Importação do Trait
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class LogUsoServico extends Model
{
    // CORREÇÃO: Adicionado o trait que instrui o modelo a usar a conexão 'tenant'
    use HasFactory, LogsActivity, UsesTenantConnection;

    /**
     * O nome da tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'log_uso_servico';

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'servico_id',
        'solicitacao_servico_id'
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
     * Relacionamento com o usuário que utilizou o serviço.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com o serviço que foi utilizado.
     */
    public function servico()
    {
        return $this->belongsTo(Servico::class);
    }

    /**
     * Relacionamento com a solicitação que gerou este log.
     */
    public function solicitacaoServico()
    {
        return $this->belongsTo(SolicitacaoServico::class);
    }
}
