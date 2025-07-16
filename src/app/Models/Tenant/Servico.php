<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection; // Importação do Trait

class Servico extends Model
{
    // CORREÇÃO: Adicionado o trait que instrui o modelo a usar a conexão 'tenant'
    use HasFactory, UsesTenantConnection;

    protected $fillable = [
        'nome',
        'tipo_servico_id',
        'descricao',
        'regras_limite',
        'is_active',
    ];

    protected $casts = [
        'regras_limite' => 'array',
        'is_active' => 'boolean',
    ];

    public function tipoServico()
    {
        return $this->belongsTo(TipoServico::class);
    }
}
