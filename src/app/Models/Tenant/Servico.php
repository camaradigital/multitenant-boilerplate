<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Servico extends Model
{
    use HasFactory, UsesTenantConnection;

    protected $table = 'servicos';

    /**
     * Os atributos que podem ser atribuídos em massa.
     */
    protected $fillable = [
        'nome',
        'descricao',
        'is_active',
        'is_juridico',
        'regras_limite',
        'tipo_servico_id',
        'permite_solicitacao_online',
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_juridico' => 'boolean',
        'regras_limite' => 'array',
        'permite_solicitacao_online' => 'boolean',
    ];

    /**
     * Um serviço pertence a um Tipo de Serviço.
     */
    public function tipoServico(): BelongsTo
    {
        return $this->belongsTo(TipoServico::class, 'tipo_servico_id');
    }

    /**
     * Um serviço pode ter muitas Solicitações de Serviço.
     * Este relacionamento é crucial para a lógica de exclusão.
     */
    public function solicitacoes(): HasMany
    {
        return $this->hasMany(SolicitacaoServico::class, 'servico_id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(TipoServico::class, 'tipo_servico_id');
    }
}

