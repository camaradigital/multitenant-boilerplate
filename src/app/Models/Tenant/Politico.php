<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Casts\Attribute; // Importar a classe Attribute
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Politico extends Model
{
    use HasFactory, LogsActivity, UsesTenantConnection;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nome_completo',
        'nome_politico',
        'partido',
        'biografia',
        'foto_path',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    // --- CORREÇÃO: Adicionar esta linha ---
    // Mesmo com a sintaxe moderna, $appends é necessário para que o Inertia inclua o atributo.
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
     * Define o acessor para a URL da foto, usando a sintaxe moderna do Laravel.
     * Retorna a URL da foto armazenada ou uma URL do ui-avatars.com como fallback.
     */
    protected function fotoUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Verifica se o caminho da foto existe e se o arquivo está no disco público
                if ($this->foto_path && Storage::disk('public')->exists($this->foto_path)) {
                    return Storage::disk('public')->url($this->foto_path);
                }
                // Se não houver foto, gera uma URL de avatar com as iniciais do nome político
                return 'https://ui-avatars.com/api/?name=' . urlencode($this->nome_politico) . '&color=FFFFFF&background=0284C7&bold=true';
            },
        );
    }

    /**
     * Define o relacionamento com os mandatos.
     * Um político pode ter vários mandatos.
     */
    public function mandatos()
    {
        return $this->hasMany(Mandato::class);
    }
}
