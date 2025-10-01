<?php

namespace App\Services\Tenant;

use App\Models\Tenant\Servico;
use App\Models\Tenant\SolicitacaoServico;
use App\Models\Tenant\StatusSolicitacao;
use App\Models\Tenant\User;
use Carbon\Carbon;
use Spatie\Multitenancy\Models\Tenant; // <-- MELHORIA: Import explícito para clareza.

class SolicitacaoService
{
    /**
     * Verifica se um cidadão pode solicitar um serviço jurídico com base na renda.
     *
     * @return array ['pode_solicitar' => bool, 'mensagem' => string]
     */
    public function verificarAcessoJuridico(Servico $servico, User $cidadao): array
    {
        if (! $servico->is_juridico) {
            return ['pode_solicitar' => true, 'mensagem' => ''];
        }

        $tenant = Tenant::current();

        // Se o tenant NÃO EXIGE renda, permite o acesso imediatamente.
        if (! $tenant->exigir_renda_juridico) {
            return ['pode_solicitar' => true, 'mensagem' => ''];
        }

        // A partir daqui, a lógica só roda se a exigência estiver ativa.
        $limiteRenda = $tenant->limite_renda_juridico;
        if (is_null($limiteRenda) || (float) $limiteRenda <= 0) {
            return ['pode_solicitar' => true, 'mensagem' => ''];
        }

        $rendaCidadao = $cidadao->profile_data['renda_familiar'] ?? null;

        if (is_null($rendaCidadao)) {
            return [
                'pode_solicitar' => false,
                'mensagem' => 'Para solicitar serviços jurídicos, é necessário informar a renda familiar no cadastro do cidadão.',
            ];
        }

        if ((float) $rendaCidadao > (float) $limiteRenda) {
            return [
                'pode_solicitar' => false,
                'mensagem' => 'Este serviço está disponível apenas para cidadãos com renda familiar de até R$ '.number_format($limiteRenda, 2, ',', '.').'.',
            ];
        }

        return ['pode_solicitar' => true, 'mensagem' => ''];
    }

    /**
     * Verifica se um cidadão pode solicitar um determinado serviço com base nas regras de limite.
     *
     * @return array ['pode_solicitar' => bool, 'mensagem' => string]
     */
    public function verificarLimiteDeUso(Servico $servico, User $cidadao): array
    {
        $regras = $servico->regras_limite;

        if (! $regras || ! isset($regras['ativo']) || ! $regras['ativo']) {
            return ['pode_solicitar' => true, 'mensagem' => ''];
        }

        $quantidadeMaxima = $regras['quantidade'];
        $periodo = $regras['periodo'];

        $dataInicio = match ($periodo) {
            'dia' => Carbon::now()->startOfDay(),
            'semana' => Carbon::now()->startOfWeek(),
            'mes' => Carbon::now()->startOfMonth(),
            'ano' => Carbon::now()->startOfYear(),
            default => null,
        };

        if (! $dataInicio) {
            return ['pode_solicitar' => true, 'mensagem' => 'Período de regra inválido.'];
        }

        // --- MELHORIA: A contagem agora desconsidera solicitações canceladas ---
        // Buscamos o ID do status que é considerado de cancelamento.
        // Assumindo que você tenha um status chamado 'Cancelado' ou similar,
        // e que você pode criar um parâmetro para definir qual status é o de cancelamento.
        // Por simplicidade aqui, vamos buscar pelo nome 'Cancelado'.
        $statusCanceladoId = StatusSolicitacao::where('nome', 'Cancelado')->value('id');

        $query = SolicitacaoServico::where('servico_id', $servico->id)
            ->where('user_id', $cidadao->id)
            ->where('created_at', '>=', $dataInicio);

        // Se encontrarmos um status de cancelamento, o excluímos da contagem.
        if ($statusCanceladoId) {
            $query->where('status_id', '!=', $statusCanceladoId);
        }

        $usoAtual = $query->count();
        // --- FIM DA MELHORIA ---

        if ($usoAtual >= $quantidadeMaxima) {
            return [
                'pode_solicitar' => false,
                'mensagem' => "Limite de {$quantidadeMaxima} solicitações por {$periodo} para este serviço foi atingido.",
            ];
        }

        return ['pode_solicitar' => true, 'mensagem' => ''];
    }
}
