<?php

namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateTenantRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição.
     * A autorização é baseada na policy 'update' para o tenant específico.
     */
    public function authorize(): bool
    {
        // Pega o tenant da rota para verificar a permissão
        $tenant = $this->route('tenant');

        return Gate::allows('update', $tenant);
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
        // Pega o ID do tenant da rota para ignorá-lo nas regras 'unique'
        $tenantId = $this->route('tenant')->id;

        return [
            'name' => 'required|string|max:255',
            'cnpj' => [
                'required',
                'string',
                'size:18',
                Rule::unique('tenants', 'cnpj')->ignore($tenantId),
            ],
            'subdomain' => [
                'required',
                'string',
                'lowercase',
                'alpha_dash',
                'max:50',
                Rule::unique('tenants', 'subdomain')->ignore($tenantId),
            ],
            'admin_email' => 'required|email|max:255',
            'endereco_cep' => 'nullable|string|max:9',
            'endereco_logradouro' => 'nullable|string|max:255',
            'endereco_numero' => 'nullable|string|max:255',
            'endereco_complemento' => 'nullable|string|max:255',
            'endereco_bairro' => 'nullable|string|max:255',
            'endereco_cidade' => 'nullable|string|max:255',
            'endereco_estado' => 'nullable|string|max:2',
            'logotipo' => ['nullable', 'image', 'mimes:jpeg,png,svg', 'max:2048'], // 2MB Max
            'site_url' => 'nullable|url|max:255',
            'cor_primaria' => 'nullable|string|size:7',
            'cor_secundaria' => 'nullable|string|size:7',
        ];
    }
}
