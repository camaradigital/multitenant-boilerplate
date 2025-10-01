<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * Representa a participação de um político em uma legislatura específica, com um cargo definido.
 * Este modelo atua como a tabela "pivô" entre Legislatura e Politico.
 */
class Mandato extends Model
{
    use HasFactory, LogsActivity, UsesTenantConnection;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['legislatura_id', 'politico_id', 'cargo'];

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
     * Define o relacionamento com o político.
     * Um mandato pertence a um único político.
     */
    public function politico()
    {
        return $this->belongsTo(Politico::class);
    }

    /**
     * Define o relacionamento com a legislatura.
     * Um mandato pertence a uma única legislatura.
     */
    public function legislatura()
    {
        return $this->belongsTo(Legislatura::class);
    }
}
