<?php

namespace App\Http\Requests\Central;

use App\Models\Central\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreTenantRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição.
     * A autorização é baseada na policy 'create' para o modelo Tenant.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Gate::allows('create', Tenant::class);
    }

    protected function prepareForValidation(): void
    {
        // Converte o subdomínio para minúsculas antes da validação.
        if ($this->has('subdomain')) {
            $this->merge([
                'subdomain' => strtolower($this->subdomain),
            ]);
        }
    }

    /**
     * Obtém as regras de validação que se aplicam à requisição.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'subdomain' => 'required|string|lowercase|alpha_dash|max:50|unique:tenants,subdomain',
            'cnpj' => 'required|string|size:18|unique:tenants,cnpj',
            'admin_email' => 'required|email|max:255',
            'endereco_cep' => 'nullable|string|max:9',
            'endereco_logradouro' => 'nullable|string|max:255',
            'endereco_numero' => 'nullable|string|max:255',
            'endereco_complemento' => 'nullable|string|max:255',
            'endereco_bairro' => 'nullable|string|max:255',
            'endereco_cidade' => 'nullable|string|max:255',
            'endereco_estado' => 'nullable|string|max:2',
            'logotipo_url' => 'nullable|url|max:255',
            'site_url' => 'nullable|url|max:255',
            'cor_primaria' => 'nullable|string|size:7',
            'cor_secundaria' => 'nullable|string|size:7',
        ];
    }
}
