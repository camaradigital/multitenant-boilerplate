<?php

// src/app/Http/Requests/Tenant/UpsertBairroRequest.php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertBairroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $bairroId = $this->route('bairro') ? $this->route('bairro')->id : null;

        return [
            'nome' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tenant.bairros', 'nome')->ignore($bairroId),
            ],
            'tipo_logradouro' => [
                'required',
                'string',
                Rule::in(['zona urbana', 'zona rural']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.unique' => 'O nome do bairro já está em uso.',
            'tipo_logradouro.in' => 'O tipo de logradouro deve ser "zona urbana" ou "zona rural".',
        ];
    }

    /**
     * Prepare the data for validation.
     * Standardizes the 'nome' field (uppercase and trimmed) before the unique check.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('nome')) {
            $this->merge([
                'nome' => mb_strtoupper(trim($this->input('nome'))),
            ]);
        }
    }
}
