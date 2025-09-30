<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class RelatorioCidadaosRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        // Regras específicas para o relatório de Cidadãos.
        return [
            'busca' => 'nullable|string|max:255',
            'data_inicio' => 'nullable|date_format:Y-m-d',
            'data_fim' => 'nullable|date_format:Y-m-d|after_or_equal:data_inicio',
            'status' => 'nullable|boolean',
        ];
    }
}
