<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\Tenant\User;

class Documento extends Model
{
    use HasFactory, LogsActivity, UsesTenantConnection;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'solicitacao_servico_id',
        'user_id',
        'nome_original',
        'path',
        'mime_type',
        'tamanho',
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
     * Obtém a solicitação de serviço à qual este documento pertence.
     */
    public function solicitacao()
    {
        return $this->belongsTo(SolicitacaoServico::class, 'solicitacao_servico_id');
    }

    /**
     * Obtém o usuário que fez o upload do documento.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
