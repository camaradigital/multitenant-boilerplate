<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class RelatorioAtendimentoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Geralmente usado para verificar permissões.
        // Como o foco é validação, retornamos true.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        // Estas são as regras compartilhadas pelos relatórios de Atendimento e Satisfação.
        return [
            'data_inicio' => 'nullable|date_format:Y-m-d',
            'data_fim' => 'nullable|date_format:Y-m-d|after_or_equal:data_inicio',
            'tipo_servico_id' => 'nullable|integer|exists:tenant.tipos_servico,id',
            'funcionario_id' => 'nullable|integer|exists:tenant.users,id',
            'status' => 'nullable|string|max:50',
        ];
    }
}
