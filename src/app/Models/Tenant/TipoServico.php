<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class TipoServico extends Model
{
    use HasFactory, UsesTenantConnection;

    protected $table = 'tipos_servico';
    protected $fillable = ['nome', 'descricao', 'is_active'];
}
