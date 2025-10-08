<?php

namespace App\Services\Tenant;

use App\Models\Tenant\PesquisaSatisfacao;
use App\Models\Tenant\SolicitacaoServico;
use App\Models\Tenant\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class RelatorioService
{
    /**
     * Gera uma consulta (query) de solicitações de serviço com base nos filtros fornecidos.
     */
    public function gerarRelatorioAtendimentosQuery(array $filters = []): Builder
    {
        $dataInicio = $filters['data_inicio'] ?? null;
        $dataFim = $filters['data_fim'] ?? null;
        $tipoServicoId = $filters['tipo_servico_id'] ?? null;
        $funcionarioId = $filters['funcionario_id'] ?? null;
        $status = $filters['status'] ?? null;
        $bairro = $filters['bairro'] ?? null;

        $query = SolicitacaoServico::with(['cidadao', 'servico.tipoServico', 'atendente']);

        $query->when($dataInicio && $dataFim, function ($q) use ($dataInicio, $dataFim) {
            $q->whereBetween('solicitacoes_servico.created_at', [$dataInicio.' 00:00:00', $dataFim.' 23:59:59']);
        });

        $query->when($tipoServicoId, function ($q) use ($tipoServicoId) {
            $q->whereHas('servico', fn ($sub) => $sub->where('tipo_servico_id', $tipoServicoId));
        });

        $query->when($funcionarioId, fn ($q) => $q->where('atendente_id', $funcionarioId));

        $query->when($status, fn ($q) => $q->where('status', $status));

        // CORRIGIDO: Filtro agora usa a relação 'bairro' do modelo 'cidadao' (User).
        $query->when($bairro, function ($q) use ($bairro) {
            $q->whereHas('cidadao.bairro', function ($bairroQuery) use ($bairro) {
                $bairroQuery->where('nome', $bairro);
            });
        });

        return $query->latest('solicitacoes_servico.created_at');
    }

    /**
     * Calcula as principais estatísticas de atendimentos com base nos filtros.
     * (Este método não precisa de alterações)
     */
    public function calcularEstatisticasAtendimentos(array $filters = []): array
    {
        $query = $this->gerarRelatorioAtendimentosQuery($filters);

        $totalSolicitacoes = $query->clone()->count();

        $tempoMedioHoras = 0;
        if ($totalSolicitacoes > 0) {
            $avgSeconds = $query->clone()
                ->whereNotNull('finalizado_em')
                ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, solicitacoes_servico.created_at, finalizado_em)) as avg_duration')
                ->value('avg_duration');
            $tempoMedioHoras = $avgSeconds ? round($avgSeconds / 3600, 1) : 0;
        }

        $distribuicaoStatus = $query->clone()
            ->reorder()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->orderBy('total', 'desc')
            ->pluck('total', 'status');

        $servicoMaisSolicitado = $query->clone()
            ->reorder()
            ->join('servicos', 'solicitacoes_servico.servico_id', '=', 'servicos.id')
            ->select('servicos.nome', DB::raw('count(*) as total'))
            ->groupBy('servicos.nome')
            ->orderBy('total', 'desc')
            ->first();

        return [
            'totalSolicitacoes' => $totalSolicitacoes,
            'tempoMedioHoras' => $tempoMedioHoras,
            'distribuicaoStatus' => $distribuicaoStatus,
            'servicoMaisSolicitado' => $servicoMaisSolicitado->nome ?? 'N/A',
        ];
    }

    /**
     * Gera uma consulta (query) de pesquisas de satisfação com base nos filtros fornecidos.
     * (Este método não precisa de alterações)
     */
    public function gerarRelatorioSatisfacaoQuery(array $filters = []): Builder
    {
        $dataInicio = $filters['data_inicio'] ?? null;
        $dataFim = $filters['data_fim'] ?? null;
        $tipoServicoId = $filters['tipo_servico_id'] ?? null;
        $funcionarioId = $filters['funcionario_id'] ?? null;

        $query = PesquisaSatisfacao::with(['cidadao:id,name', 'solicitacaoServico.servico']);

        $query->when($dataInicio && $dataFim, function ($q) use ($dataInicio, $dataFim) {
            $q->whereBetween('pesquisas_satisfacao.created_at', [$dataInicio.' 00:00:00', $dataFim.' 23:59:59']);
        });

        $query->whereHas('solicitacaoServico', function ($solicitacaoQuery) use ($tipoServicoId, $funcionarioId) {
            if ($tipoServicoId) {
                $solicitacaoQuery->whereHas('servico', function ($servicoQuery) use ($tipoServicoId) {
                    $servicoQuery->where('tipo_servico_id', $tipoServicoId);
                });
            }
            if ($funcionarioId) {
                $solicitacaoQuery->where('atendente_id', $funcionarioId);
            }
        });

        return $query->latest('pesquisas_satisfacao.created_at');
    }

    /**
     * Calcula as principais estatísticas de satisfação com base nos filtros.
     * (Este método não precisa de alterações)
     */
    public function calcularEstatisticasSatisfacao(array $filters = []): array
    {
        $query = $this->gerarRelatorioSatisfacaoQuery($filters);

        $totalRespostas = $query->clone()->count();
        $notaMedia = $totalRespostas > 0 ? round($query->clone()->avg('nota'), 2) : 0;

        $totalFinalizados = SolicitacaoServico::query()
            ->whereNotNull('finalizado_em')
            ->when(! empty($filters['data_inicio']) && ! empty($filters['data_fim']), function ($q) use ($filters) {
                $q->whereBetween('finalizado_em', [$filters['data_inicio'].' 00:00:00', $filters['data_fim'].' 23:59:59']);
            })
            ->count();

        $taxaResposta = $totalFinalizados > 0 ? round(($totalRespostas / $totalFinalizados) * 100, 1) : 0;

        $distribuicaoNotas = $query->clone()
            ->reorder()
            ->select('nota', DB::raw('count(*) as total'))
            ->groupBy('nota')
            ->orderBy('nota', 'asc')
            ->pluck('total', 'nota');

        $distribuicaoCompleta = ['1' => 0, '2' => 0, '3' => 0, '4' => 0, '5' => 0];
        foreach ($distribuicaoNotas as $nota => $total) {
            $distribuicaoCompleta[$nota] = $total;
        }

        return [
            'totalRespostas' => $totalRespostas,
            'notaMedia' => $notaMedia,
            'taxaResposta' => $taxaResposta,
            'distribuicaoNotas' => $distribuicaoCompleta,
        ];
    }

    /**
     * Gera uma consulta para o relatório de cidadãos.
     * (Este método não precisa de alterações)
     */
    public function gerarRelatorioCidadaosQuery(array $filters = []): Builder
    {
        $busca = $filters['busca'] ?? null;
        $dataInicio = $filters['data_inicio'] ?? null;
        $dataFim = $filters['data_fim'] ?? null;
        $status = $filters['status'] ?? null;

        $query = User::role('Cidadao');

        $query->when($busca, function ($q) use ($busca) {
            $q->where(function ($subQuery) use ($busca) {
                $subQuery->where('name', 'like', '%'.$busca.'%')
                    ->orWhere('cpf', 'like', '%'.$busca.'%');
            });
        });

        $query->when($dataInicio && $dataFim, function ($q) use ($dataInicio, $dataFim) {
            $q->whereBetween('created_at', [$dataInicio.' 00:00:00', $dataFim.' 23:59:59']);
        });

        $query->when(isset($filters['status']) && $filters['status'] !== '', function ($q) use ($status) {
            $q->where('is_active', $status);
        });

        $query->with(['solicitacoes.servico', 'solicitacoes.status', 'solicitacoes.atendente']);
        $query->withCount('solicitacoes');

        return $query->latest();
    }

    /**
     * Gera dados agregados de solicitações por bairro e serviço.
     */
    public function gerarRelatorioDemandasPorBairro(array $filters = [])
    {
        // --- CORREÇÃO APLICADA ---
        $query = DB::connection('tenant')
            ->table('solicitacoes_servico')
            ->join('users', 'solicitacoes_servico.user_id', '=', 'users.id')
            ->join('bairros', 'users.bairro_id', '=', 'bairros.id') // Adicionado JOIN com a tabela bairros
            ->join('servicos', 'solicitacoes_servico.servico_id', '=', 'servicos.id')
            ->join('tipos_servico', 'servicos.tipo_servico_id', '=', 'tipos_servico.id')
            ->select(
                'bairros.nome as bairro', // CORRIGIDO: Seleciona o nome do bairro da tabela 'bairros'
                'tipos_servico.nome as tipo_servico',
                DB::raw('COUNT(solicitacoes_servico.id) as total_solicitacoes')
            )
            ->whereNotNull('users.bairro_id'); // CORRIGIDO: Verifica se o bairro_id não é nulo

        if (! empty($filters['data_inicio']) && ! empty($filters['data_fim'])) {
            $query->whereBetween('solicitacoes_servico.created_at', [
                $filters['data_inicio'].' 00:00:00',
                $filters['data_fim'].' 23:59:59',
            ]);
        }
        if (! empty($filters['tipo_servico_id'])) {
            $query->where('servicos.tipo_servico_id', $filters['tipo_servico_id']);
        }

        return $query->groupBy('bairros.nome', 'tipo_servico') // CORRIGIDO: Agrupa pelo nome do bairro
            ->orderBy('total_solicitacoes', 'desc')
            ->get();
    }

    /**
     * Gera dados de tendências de solicitações por tipo de serviço.
     * (Este método não precisa de alterações)
     */
    public function gerarAnaliseDeTendencias(array $filters = [])
    {
        $query = DB::connection('tenant')
            ->table('solicitacoes_servico')
            ->join('servicos', 'solicitacoes_servico.servico_id', '=', 'servicos.id')
            ->join('tipos_servico', 'servicos.tipo_servico_id', '=', 'tipos_servico.id')
            ->select(
                'tipos_servico.nome as tipo_servico',
                DB::raw('DATE(solicitacoes_servico.created_at) as data'),
                DB::raw('COUNT(solicitacoes_servico.id) as total')
            );

        if (! empty($filters['data_inicio']) && ! empty($filters['data_fim'])) {
            $query->whereBetween('solicitacoes_servico.created_at', [
                $filters['data_inicio'].' 00:00:00',
                $filters['data_fim'].' 23:59:59',
            ]);
        }

        return $query->groupBy('tipo_servico', 'data')
            ->orderBy('data', 'asc')
            ->get();
    }
}
