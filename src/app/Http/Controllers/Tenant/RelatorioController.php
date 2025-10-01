<?php

namespace App\Http\Controllers\Tenant;

use App\Exports\Tenant\CidadaosExport;
use App\Exports\Tenant\SolicitacoesExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\RelatorioAtendimentoRequest;
use App\Http\Requests\Tenant\RelatorioCidadaosRequest; // Classe "conceitual" para a policy
use App\Models\Central\Tenant;
use App\Models\Tenant\Relatorio;
use App\Models\Tenant\TipoServico;
use App\Models\Tenant\User;
use App\Services\Tenant\RelatorioService;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class RelatorioController extends Controller
{
    protected RelatorioService $relatorioService;

    public function __construct(RelatorioService $relatorioService)
    {
        $this->relatorioService = $relatorioService;
    }

    /**
     * Obtém dados comuns para as views de relatórios.
     */
    private function getCommonViewData(): array
    {
        $tiposServico = TipoServico::orderBy('nome')->get(['id', 'nome']);

        $funcionarios = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['Funcionario', 'Admin Tenant']);
        })->orderBy('name')->get(['id', 'name']);

        return compact('tiposServico', 'funcionarios');
    }

    public function atendimentos(RelatorioAtendimentoRequest $request)
    {
        $this->authorize('viewAtendimentos', Relatorio::class);

        $filters = $request->validated();

        $solicitacoes = $this->relatorioService
            ->gerarRelatorioAtendimentosQuery($filters)
            ->paginate(15)
            ->withQueryString();

        $estatisticas = $this->relatorioService->calcularEstatisticasAtendimentos($filters);

        return Inertia::render('Tenant/Relatorios/Atendimentos', array_merge(
            $this->getCommonViewData(),
            [
                'solicitacoes' => $solicitacoes,
                'filtros' => $filters,
                'estatisticas' => $estatisticas,
            ]
        ));
    }

    public function exportarAtendimentos(RelatorioAtendimentoRequest $request)
    {
        $this->authorize('viewAtendimentos', Relatorio::class);

        $filters = $request->validated();
        $fileName = 'relatorio_atendimentos_'.now()->format('Y-m-d_His').'.xlsx';

        return Excel::download(new SolicitacoesExport($filters), $fileName);
    }

    public function exportarAtendimentosPDF(RelatorioAtendimentoRequest $request)
    {
        $this->authorize('viewAtendimentos', Relatorio::class);

        $filters = $request->validated();

        $solicitacoes = $this->relatorioService
            ->gerarRelatorioAtendimentosQuery($filters)
            ->get();

        $data_inicio = isset($filters['data_inicio']) ? \Carbon\Carbon::parse($filters['data_inicio'])->format('d/m/Y') : 'Início';
        $data_fim = isset($filters['data_fim']) ? \Carbon\Carbon::parse($filters['data_fim'])->format('d/m/Y') : 'Fim';

        $pdf = Pdf::loadView('pdf.relatorio-atendimentos', [
            'solicitacoes' => $solicitacoes,
            'data_inicio' => $data_inicio,
            'data_fim' => $data_fim,
            'tenant_name' => Tenant::current()->name,
        ]);

        return $pdf->stream('relatorio_atendimentos_'.now()->format('Y-m-d').'.pdf');
    }

    public function satisfacao(RelatorioAtendimentoRequest $request)
    {
        $this->authorize('viewSatisfacao', Relatorio::class);

        $filters = $request->validated();

        $pesquisas = $this->relatorioService
            ->gerarRelatorioSatisfacaoQuery($filters)
            ->paginate(10)
            ->withQueryString();

        $estatisticas = $this->relatorioService->calcularEstatisticasSatisfacao($filters);

        return Inertia::render('Tenant/Relatorios/Satisfacao', array_merge(
            $this->getCommonViewData(),
            [
                'pesquisas' => $pesquisas,
                'filtros' => $filters,
                'estatisticas' => $estatisticas,
            ]
        ));
    }

    public function cidadaos(RelatorioCidadaosRequest $request)
    {
        $this->authorize('viewCidadaos', Relatorio::class);

        $filters = $request->validated();

        $cidadaos = $this->relatorioService
            ->gerarRelatorioCidadaosQuery($filters)
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Tenant/Relatorios/Cidadaos', [
            'cidadaos' => $cidadaos,
            'filtros' => $filters,
        ]);
    }

    public function exportarCidadaos(RelatorioCidadaosRequest $request)
    {
        $this->authorize('viewCidadaos', Relatorio::class);

        $filters = $request->validated();
        $fileName = 'relatorio_cidadaos_'.now()->format('Y-m-d_His').'.xlsx';

        return Excel::download(new CidadaosExport($filters), $fileName);
    }

    public function exportarCidadaosPDF(RelatorioCidadaosRequest $request)
    {
        $this->authorize('viewCidadaos', Relatorio::class);

        $filters = $request->validated();

        $cidadaos = $this->relatorioService
            ->gerarRelatorioCidadaosQuery($filters)
            ->get();

        $pdf = Pdf::loadView('pdf.relatorio-cidadaos', [
            'cidadaos' => $cidadaos,
            'tenant_name' => Tenant::current()->name,
        ]);

        return $pdf->stream('relatorio_cidadaos_'.now()->format('Y-m-d').'.pdf');
    }

    public function demandasPorBairro(RelatorioAtendimentoRequest $request)
    {
        $this->authorize('viewDemandasPorBairro', Relatorio::class); // Requer uma nova permissão na Policy

        $filters = $request->validated();

        $demandas = $this->relatorioService->gerarRelatorioDemandasPorBairro($filters);

        // Agrupa os dados para facilitar a exibição em gráficos e tabelas
        $dadosMapeamento = $demandas->groupBy('bairro')->map(function ($items, $bairro) {
            return [
                'bairro' => $bairro,
                'total' => $items->sum('total_solicitacoes'),
                'detalhes' => $items,
            ];
        })->sortByDesc('total')->values();

        return Inertia::render('Tenant/Relatorios/DemandasPorBairro', array_merge(
            $this->getCommonViewData(),
            [
                'demandas' => $dadosMapeamento,
                'filtros' => $filters,
            ]
        ));
    }

    public function analiseDeTendencias(RelatorioAtendimentoRequest $request)
    {
        $this->authorize('viewAnaliseDeTendencias', Relatorio::class); // Requer nova permissão

        $filters = $request->validated();
        $tendencias = $this->relatorioService->gerarAnaliseDeTendencias($filters);

        // Formata os dados para o gráfico de linhas
        $chartData = [
            'labels' => $tendencias->pluck('data')->unique()->sort()->values(),
            'datasets' => $tendencias->groupBy('tipo_servico')->map(function ($items, $tipo) {
                return [
                    'label' => $tipo,
                    'data' => $items->pluck('total', 'data'),
                    // Adicione cores dinâmicas se desejar
                ];
            })->values(),
        ];

        return Inertia::render('Tenant/Relatorios/AnaliseDeTendencias', array_merge(
            $this->getCommonViewData(),
            [
                'tendenciasChartData' => $chartData,
                'filtros' => $filters,
            ]
        ));
    }
}
