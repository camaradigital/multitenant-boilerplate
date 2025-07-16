<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'name',
        'cnpj',
        'subdomain',
        'database_name',
        'admin_email',
        'endereco_cep',
        'endereco_logradouro',
        'endereco_numero',
        'endereco_complemento',
        'endereco_bairro',
        'endereco_cidade',
        'endereco_estado',
        'logotipo_url',
        'site_url',
        'cor_primaria',
        'cor_secundaria',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Este método é obrigatório para que o pacote saiba qual
     * banco de dados usar para este tenant.
     */
    public function getDatabaseName(): string
    {
        return $this->database_name;
    }

    public function getDomainUrl(): string
{
    // Monta a URL completa: http://<subdomínio>.<domínio_central>
    return 'http://' . $this->subdomain . '.' . config('app.central_domain');
}
}
