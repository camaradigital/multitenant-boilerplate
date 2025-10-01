<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions; // Importar a classe Attribute
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Legislatura extends Model
{
    use HasFactory, LogsActivity, UsesTenantConnection;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'titulo',
        'data_inicio',
        'data_fim',
        'foto_principal_path',
        'texto_destaque',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['foto_principal_url'];

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
     * Define o acessor para a URL da foto principal da legislatura.
     */
    protected function fotoPrincipalUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->foto_principal_path && Storage::disk('public')->exists($this->foto_principal_path)) {
                    return Storage::disk('public')->url($this->foto_principal_path);
                }

                return null;
            }
        );
    }

    /**
     * Define o relacionamento com os mandatos.
     * Uma legislatura é composta por vários mandatos.
     */
    public function mandatos()
    {
        return $this->hasMany(Mandato::class);
    }
}
