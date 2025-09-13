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

        $query = SolicitacaoServico::with(['cidadao', 'servico.tipoServico', 'atendente']);

        // --- CORREÇÃO APLICADA ---
        // Especificamos a tabela 'solicitacoes_servico' para a coluna 'created_at'
        // para evitar ambiguidade quando a consulta for usada com JOINs.
        $query->when($dataInicio && $dataFim, function ($q) use ($dataInicio, $dataFim) {
            $q->whereBetween('solicitacoes_servico.created_at', [$dataInicio . ' 00:00:00', $dataFim . ' 23:59:59']);
        });

        $query->when($tipoServicoId, function ($q) use ($tipoServicoId) {
            $q->whereHas('servico', function ($subQuery) use ($tipoServicoId) {
                $subQuery->where('tipo_servico_id', $tipoServicoId);
            });
        });

        $query->when($funcionarioId, function ($q) use ($funcionarioId) {
            $q->where('atendente_id', $funcionarioId);
        });

        $query->when($status, function ($q) use ($status) {
            $q->where('status', $status);
        });

        // Também é uma boa prática ser explícito aqui.
        return $query->latest('solicitacoes_servico.created_at');
    }

    /**
     * Calcula as principais estatísticas de atendimentos com base nos filtros.
     *
     * @param array $filters
     * @return array
     */
    public function calcularEstatisticasAtendimentos(array $filters = []): array
    {
        $query = $this->gerarRelatorioAtendimentosQuery($filters);

        $totalSolicitacoes = $query->clone()->count();

        $tempoMedioHoras = 0;
        if ($totalSolicitacoes > 0) {
            // --- CORREÇÃO APLICADA ---
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
     */
    public function gerarRelatorioSatisfacaoQuery(array $filters = []): Builder
    {
        $dataInicio = $filters['data_inicio'] ?? null;
        $dataFim = $filters['data_fim'] ?? null;
        $tipoServicoId = $filters['tipo_servico_id'] ?? null;
        $funcionarioId = $filters['funcionario_id'] ?? null;

        $query = PesquisaSatisfacao::with(['cidadao:id,name', 'solicitacaoServico.servico']);

        // Filtra pelo período em que a pesquisa foi respondida.
        $query->when($dataInicio && $dataFim, function ($q) use ($dataInicio, $dataFim) {
            $q->whereBetween('pesquisas_satisfacao.created_at', [$dataInicio . ' 00:00:00', $dataFim . ' 23:59:59']);
        });

        // Filtra por tipo de serviço ou funcionário através do relacionamento com a solicitação.
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
     *
     * @param array $filters
     * @return array
     */
    public function calcularEstatisticasSatisfacao(array $filters = []): array
    {
        $query = $this->gerarRelatorioSatisfacaoQuery($filters);

        $totalRespostas = $query->clone()->count();

        $notaMedia = $totalRespostas > 0 ? round($query->clone()->avg('nota'), 2) : 0;

        $totalFinalizados = SolicitacaoServico::query()
            ->whereNotNull('finalizado_em')
            ->when(!empty($filters['data_inicio']) && !empty($filters['data_fim']), function ($q) use ($filters) {
                $q->whereBetween('finalizado_em', [$filters['data_inicio'] . ' 00:00:00', $filters['data_fim'] . ' 23:59:59']);
            })
            ->count();

        $taxaResposta = $totalFinalizados > 0 ? round(($totalRespostas / $totalFinalizados) * 100, 1) : 0;

        $distribuicaoNotas = $query->clone()
            ->reorder()
            ->select('nota', DB::raw('count(*) as total'))
            ->groupBy('nota')
            ->orderBy('nota', 'asc')
            ->pluck('total', 'nota');

        // Garante que todas as notas (de 1 a 5) existam no array para o gráfico.
        $distribuicaoCompleta = [ '1' => 0, '2' => 0, '3' => 0, '4' => 0, '5' => 0 ];
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

    public function gerarRelatorioCidadaosQuery(array $filters = []): Builder
    {
        $busca = $filters['busca'] ?? null;
        $dataInicio = $filters['data_inicio'] ?? null;
        $dataFim = $filters['data_fim'] ?? null;
        $status = $filters['status'] ?? null;

        $query = User::role('Cidadao');

        $query->when($busca, function ($q) use ($busca) {
            $q->where(function ($subQuery) use ($busca) {
                $subQuery->where('name', 'like', '%' . $busca . '%')
                         ->orWhere('cpf', 'like', '%' . $busca . '%');
            });
        });

        $query->when($dataInicio && $dataFim, function ($q) use ($dataInicio, $dataFim) {
            $q->whereBetween('created_at', [$dataInicio . ' 00:00:00', $dataFim . ' 23:59:59']);
        });

        $query->when(isset($filters['status']) && $filters['status'] !== '', function ($q) use ($status) {
            $q->where('is_active', $status);
        });

        // --- ATUALIZAÇÃO IMPORTANTE ---
        // Garante que o histórico de solicitações e seus detalhes sejam carregados
        // de forma otimizada para a geração do PDF.
        $query->with(['solicitacoes.servico', 'solicitacoes.status', 'solicitacoes.atendente']);

        // Mantém a contagem de solicitações para a visualização na web.
        $query->withCount('solicitacoes');

        return $query->latest();
    }
}

