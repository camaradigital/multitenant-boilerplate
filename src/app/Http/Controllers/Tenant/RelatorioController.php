<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\RelatorioAtendimentoRequest;
use App\Http\Requests\Tenant\RelatorioCidadaosRequest;
use App\Models\Central\Tenant;
use App\Models\Tenant\Relatorio; // Classe "conceitual" para a policy
use App\Models\Tenant\TipoServico;
use App\Models\Tenant\User;
use App\Services\Tenant\RelatorioService;
use App\Exports\Tenant\SolicitacoesExport;
use App\Exports\Tenant\CidadaosExport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class RelatorioController extends Controller
{
    protected RelatorioService $relatorioService;

    public function __construct(RelatorioService $relatorioService)
    {
        $this->relatorioService = $relatorioService;
    }

    /**
     * Obtém dados comuns para as views de relatórios.
     * @return array
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
        $fileName = 'relatorio_atendimentos_' . now()->format('Y-m-d_His') . '.xlsx';

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

        return $pdf->stream('relatorio_atendimentos_' . now()->format('Y-m-d') . '.pdf');
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
        $fileName = 'relatorio_cidadaos_' . now()->format('Y-m-d_His') . '.xlsx';

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

        return $pdf->stream('relatorio_cidadaos_' . now()->format('Y-m-d') . '.pdf');
    }
}
