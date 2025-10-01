<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpsertServicoRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição.
     *
     * A lógica de autorização é movida do controller para cá, garantindo que
     * a verificação ocorra antes mesmo da validação.
     */
    public function authorize(): bool
    {
        // Garante que o usuário autenticado tenha a permissão 'gerenciar servicos'.
        return $this->user()->can('gerenciar servicos');
    }

    /**
     * Retorna as regras de validação que se aplicam à requisição.
     *
     * Estas regras são usadas tanto para a criação (store) quanto para a
     * atualização (update) de um serviço.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'tipo_servico_id' => 'required|exists:tenant.tipos_servico,id',
            'descricao' => 'nullable|string',
            'is_active' => 'required|boolean',
            'is_juridico' => 'required|boolean', // <-- Adicione esta linha
            'permite_solicitacao_online' => 'required|boolean',
            'regras_limite.ativo' => 'sometimes|boolean',
            'regras_limite.quantidade' => 'nullable|required_if:regras_limite.ativo,true|integer|min:1',
            'regras_limite.periodo' => 'nullable|required_if:regras_limite.ativo,true|in:dia,semana,mes,ano',
        ];
    }

    /**
     * Obtém os dados validados da requisição e aplica a lógica de negócio.
     *
     * Este método é sobrescrito para manipular o campo 'regras_limite'
     * após a validação, limpando a responsabilidade do controller.
     *
     * @param  mixed|null  $key
     * @param  mixed|null  $default
     * @return mixed
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        // Lógica para anular ou manter as regras de limite.
        // Se 'ativo' não estiver presente ou for falso, todo o campo 'regras_limite' se torna nulo.
        if (! ($data['regras_limite']['ativo'] ?? false)) {
            $data['regras_limite'] = null;
        } else {
            // Garante que 'ativo' seja sempre um booleano no banco, caso o checkbox esteja marcado.
            $data['regras_limite']['ativo'] = true;
        }

        return $data;
    }
}
