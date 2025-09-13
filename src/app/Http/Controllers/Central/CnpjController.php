<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * CnpjController lida com as operações relacionadas a consulta de CNPJ via API.
 */
class CnpjController extends Controller
{
    /**
     * Consulta os dados de um CNPJ na API pública e retorna uma resposta JSON.
     *
     * @param string $cnpj O CNPJ a ser consultado.
     * @return \Illuminate\Http\JsonResponse
     */
    public function consultarCnpj(string $cnpj)
    {
        // Remove qualquer formatação do CNPJ para deixar apenas os números.
        $cnpjLimpo = preg_replace('/[^0-9]/', '', $cnpj);

        // Validação básica do CNPJ
        if (strlen($cnpjLimpo) !== 14) {
            return response()->json(['error' => 'Formato de CNPJ inválido.'], 400);
        }

        // Pega a URL da API do arquivo .env.
        $apiUrl = env('CNPJA_API_URL', 'https://open.cnpja.com/office/');

        try {
            // Realiza a chamada GET para a API de consulta de CNPJ.
            $response = Http::get($apiUrl . $cnpjLimpo);

            // Se a requisição foi bem-sucedida, retorna os dados em JSON.
            if ($response->successful()) {
                return response()->json($response->json());
            }

            // Se a API retornar 404, o CNPJ não foi encontrado.
            if ($response->status() == 404) {
                return response()->json(['error' => 'CNPJ não encontrado na base de dados.'], 404);
            }

            // Para outros erros da API, retorna uma mensagem genérica com o status de erro.
            Log::warning('Falha na API de CNPJ. Status: ' . $response->status() . '. CNPJ: ' . $cnpjLimpo);
            return response()->json(
                ['error' => 'Falha ao consultar o CNPJ. O serviço externo pode estar indisponível.'],
                $response->status()
            );

        } catch (\Exception $e) {
            // Captura exceções de conexão (timeout, erro de DNS, etc.).
            Log::error('Erro de conexão com a API de CNPJ: ' . $e->getMessage());
            return response()->json(['error' => 'Não foi possível conectar ao serviço de consulta de CNPJ.'], 500);
        }
    }
}
