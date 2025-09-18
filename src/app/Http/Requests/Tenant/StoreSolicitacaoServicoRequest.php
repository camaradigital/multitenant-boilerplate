<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreSolicitacaoServicoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // A lógica de autorização mais complexa (se o serviço pode ser solicitado, etc.)
        // é mantida no controller, aqui apenas permitimos que a validação prossiga.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $user = Auth::user();

        // Se o usuário logado é um Cidadão, aplicamos as regras do portal.
        if ($user && $user->hasRole('Cidadao')) {
            return [
                'servico_id' => 'required|exists:tenant.servicos,id',
                'observacoes' => 'nullable|string|max:5000',
                'documentos' => 'nullable|array',
                'documentos.*' => 'file|mimes:jpg,jpeg,png,pdf|max:10240', // Max 10MB por arquivo
            ];
        }

        // Se for um funcionário/admin criando a solicitação, aplicamos outras regras.
        return [
            'user_id' => 'required|exists:tenant.users,id',
            'servico_id' => 'required|exists:tenant.servicos,id',
            'observacoes' => 'nullable|string',
            'renda_familiar' => 'nullable|numeric|min:0',
        ];
    }
}
