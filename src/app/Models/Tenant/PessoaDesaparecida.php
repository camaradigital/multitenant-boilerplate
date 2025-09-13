<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PessoaDesaparecida extends Model
{
    use HasFactory, LogsActivity, UsesTenantConnection;

    protected $table = 'pessoas_desaparecidas';

    protected $fillable = [
        'nome_completo',
        'idade',
        'data_desaparecimento',
        'local_desaparecimento',
        'detalhes',
        'foto_path',
        'boletim_ocorrencia_path',
        'status',
        'registrado_por_user_id',
        'moderado_por_user_id',
    ];

    protected $casts = [
        'data_desaparecimento' => 'date',
    ];

    // Adiciona o novo atributo 'foto_url' aos dados do modelo
    protected $appends = ['foto_url'];

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
     * Retorna a URL completa e pública para a foto usando o disco 'public'.
     */
    public function getFotoUrlAttribute()
    {
        if ($this->foto_path && Storage::disk('public')->exists($this->foto_path)) {
            return Storage::disk('public')->url($this->foto_path);
        }
        // Retorna uma imagem padrão se a foto não for encontrada
        return 'https://via.placeholder.com/400x400.png?text=Foto+Indisponivel';
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por_user_id');
    }

    public function moderadoPor()
    {
        return $this->belongsTo(User::class, 'moderado_por_user_id');
    }
}
