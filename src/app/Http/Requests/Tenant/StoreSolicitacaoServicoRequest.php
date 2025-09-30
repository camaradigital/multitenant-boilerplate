<?php

namespace App\Http\Requests\Tenant;

use App\Models\Central\Tenant;
use App\Models\Tenant\Servico;
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
        $servicoId = $this->input('servico_id');
        $servico = $servicoId ? Servico::find($servicoId) : null;

        $rules = [
            'servico_id' => 'required|exists:tenant.servicos,id',
            'observacoes' => 'nullable|string|max:5000',
        ];

        // Se o usuário logado é um Cidadão, aplicamos as regras do portal.
        if ($user && $user->hasRole('Cidadao')) {
            $rules['documentos'] = 'nullable|array';
            // Adicionado .doc e .docx para permitir upload de currículos, por exemplo.
            $rules['documentos.*'] = 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240'; // Max 10MB
        }
        // Se for um funcionário/admin, o ID do cidadão é obrigatório.
        else {
            $rules['user_id'] = 'required|exists:tenant.users,id';
        }

        // ** LÓGICA DE VALIDAÇÃO CONDICIONAL PARA SERVIÇOS JURÍDICOS **
        // Verifica se o serviço selecionado é jurídico para aplicar a regra de renda.
        // Isso agora funciona tanto para Cidadãos quanto para Funcionários.
        if ($servico && $servico->is_juridico) {
            $tenant = Tenant::current(); // Pega a instância do tenant atual

            // Se o tenant EXIGE a informação de renda, o campo se torna obrigatório.
            if ($tenant && $tenant->exigir_renda_juridico) {
                $rules['renda_familiar'] = 'required|numeric|min:0';
            } else {
                $rules['renda_familiar'] = 'nullable|numeric|min:0';
            }
        }

        return $rules;
    }
}
