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
        'telefone_contato', // Novo campo
        'whatsapp',         // Novo campo
        'email_contato',    // Novo campo
        'instagram',        // Novo campo
        'youtube',          // Novo campo
        'cor_primaria',
        'cor_secundaria',
        'permite_cadastro_cidade_externa',
        'limite_renda_juridico',
        'exigir_renda_juridico',
        'publicar_achados_e_perdidos',
        'publicar_pessoas_desaparecidas',
        'publicar_memoria_legislativa',
        'publicar_vagas_emprego',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
        'permite_cadastro_cidade_externa' => 'boolean',
        'exigir_renda_juridico' => 'boolean',
        'limite_renda_juridico' => 'decimal:2',
        'publicar_achados_e_perdidos' => 'boolean',
        'publicar_pessoas_desaparecidas' => 'boolean',
        'publicar_memoria_legislativa' => 'boolean',
        'publicar_vagas_emprego' => 'boolean',
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
        return 'http://'.$this->subdomain.'.'.config('app.central_domain');
    }
}
