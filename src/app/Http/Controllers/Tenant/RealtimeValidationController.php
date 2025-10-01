<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RealtimeValidationController extends Controller
{
    public function validateField(Request $request)
    {
        // Pega o nome do campo e o valor da requisição
        $fieldName = $request->input('field');
        $value = $request->input('value');

        // Calcula a data máxima permitida para o cadastro (hoje - 16 anos)
        $maxDate = now()->subYears(16)->format('Y-m-d');

        // Define as regras de validação disponíveis para cada campo
        $rules = [
            'cpf' => ['required', 'cpf'],
            'cnpj' => ['required', 'cnpj'],
            'telefone' => ['required', 'telefone_com_ddd'],
            'celular' => ['required', 'celular_com_ddd'],
            'cep' => ['required', 'formato_cep'],
            'email' => ['required', 'email', 'unique:users,email'],

            // --- REGRA ATUALIZADA AQUI ---
            'data_nascimento' => [
                'required',
                'date_format:d/m/Y',
                'before_or_equal:'.$maxDate,
            ],
        ];

        // --- MENSAGEM DE ERRO PERSONALIZADA ---
        $messages = [
            'data_nascimento.before_or_equal' => 'Você deve ter pelo menos 16 anos para se cadastrar.',
        ];

        // Verifica se o campo enviado existe nas regras definidas
        if (! isset($rules[$fieldName])) {
            return response()->json(['error' => 'Campo de validação desconhecido.'], 400);
        }

        // Cria o validador com as mensagens personalizadas
        $validator = Validator::make(
            [$fieldName => $value], // Dados a serem validados
            [$fieldName => $rules[$fieldName]], // Regras a serem aplicadas
            $messages // <-- Adiciona as mensagens personalizadas aqui
        );

        // Se a validação falhar, retorna o erro em formato JSON com status 422
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Se a validação for bem-sucedida, retorna uma resposta de sucesso
        return response()->json(['success' => 'O campo é válido.'], 200);
    }
}
