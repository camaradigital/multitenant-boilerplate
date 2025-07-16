<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection; // Importação do Trait

class SolicitacaoServico extends Model
{
    // CORREÇÃO: Adicionado o trait que instrui o modelo a usar a conexão 'tenant'
    use HasFactory, UsesTenantConnection;

    /**
     * O nome da tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'solicitacoes_servico';

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'servico_id',
        'status',
        'observacoes_atendente',
        'observacoes_cidadao',
    ];

    /**
     * Relacionamento com o usuário (cidadão).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com o serviço solicitado.
     */
    public function servico()
    {
        return $this->belongsTo(Servico::class);
    }
}
