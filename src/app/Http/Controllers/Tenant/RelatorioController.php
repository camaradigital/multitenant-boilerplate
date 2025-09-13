<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use App\Models\Tenant\TipoServico;
use App\Models\Tenant\User;
use App\Services\Tenant\RelatorioService;
use App\Exports\Tenant\SolicitacoesExport;
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

    public function atendimentos(Request $request)
    {
        // CORREÇÃO: Adiciona a conexão 'tenant' às regras de validação 'exists'.
        $filters = $request->validate([
            'data_inicio' => 'nullable|date_format:Y-m-d',
            'data_fim' => 'nullable|date_format:Y-m-d|after_or_equal:data_inicio',
            'tipo_servico_id' => 'nullable|integer|exists:tenant.tipos_servico,id',
            'funcionario_id' => 'nullable|integer|exists:tenant.users,id',
            'status' => 'nullable|string|max:50',
        ]);

        $tiposServico = TipoServico::orderBy('nome')->get(['id', 'nome']);

        // CORREÇÃO: Remove 'Super Admin' e usa 'Funcionario' conforme a estrutura de papéis do tenant.
        $funcionarios = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['Funcionario', 'Admin Tenant']);
        })->orderBy('name')->get(['id', 'name']);

        $solicitacoes = $this->relatorioService
            ->gerarRelatorioAtendimentosQuery($filters)
            ->paginate(15)
            ->withQueryString();

        $estatisticas = $this->relatorioService->calcularEstatisticasAtendimentos($filters);

        return Inertia::render('Tenant/Relatorios/Atendimentos', [
            'solicitacoes' => $solicitacoes,
            'tiposServico' => $tiposServico,
            'funcionarios' => $funcionarios,
            'filtros' => $filters,
            'estatisticas' => $estatisticas,
        ]);
    }

    /**
     * Exporta o relatório de atendimentos para XLSX.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportarAtendimentos(Request $request)
    {
         $filters = $request->validate([
            'data_inicio' => 'nullable|date_format:Y-m-d',
            'data_fim' => 'nullable|date_format:Y-m-d|after_or_equal:data_inicio',
            'tipo_servico_id' => 'nullable|integer|exists:tenant.tipos_servico,id',
            'funcionario_id' => 'nullable|integer|exists:tenant.users,id',
            'status' => 'nullable|string|max:50',
        ]);

        $fileName = 'relatorio_atendimentos_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new SolicitacoesExport($filters), $fileName);
    }

    /**
     * Exporta o relatório de atendimentos para PDF.
     */
    public function exportarAtendimentosPDF(Request $request)
    {
        $filters = $request->validate([
            'data_inicio' => 'nullable|date_format:Y-m-d',
            'data_fim' => 'nullable|date_format:Y-m-d|after_or_equal:data_inicio',
            'tipo_servico_id' => 'nullable|integer|exists:tenant.tipos_servico,id',
            'funcionario_id' => 'nullable|integer|exists:tenant.users,id',
            'status' => 'nullable|string|max:50',
        ]);

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

    public function satisfacao(Request $request)
    {
        // CORREÇÃO: Adiciona a conexão 'tenant' às regras de validação 'exists'.
        $filters = $request->validate([
            'data_inicio' => 'nullable|date_format:Y-m-d',
            'data_fim' => 'nullable|date_format:Y-m-d|after_or_equal:data_inicio',
            'tipo_servico_id' => 'nullable|integer|exists:tenant.tipos_servico,id',
            'funcionario_id' => 'nullable|integer|exists:tenant.users,id',
        ]);

        $tiposServico = TipoServico::orderBy('nome')->get(['id', 'nome']);

        // CORREÇÃO: Remove 'Super Admin' e usa 'Funcionario' conforme a estrutura de papéis do tenant.
        $funcionarios = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['Funcionario', 'Admin Tenant']);
        })->orderBy('name')->get(['id', 'name']);

        $pesquisas = $this->relatorioService
            ->gerarRelatorioSatisfacaoQuery($filters)
            ->paginate(10)
            ->withQueryString();

        $estatisticas = $this->relatorioService->calcularEstatisticasSatisfacao($filters);

        return Inertia::render('Tenant/Relatorios/Satisfacao', [
            'pesquisas' => $pesquisas,
            'tiposServico' => $tiposServico,
            'funcionarios' => $funcionarios,
            'filtros' => $filters,
            'estatisticas' => $estatisticas,
        ]);
    }

    /**
     * Exibe a página do relatório de cidadãos.
     *
     * @param Request $request
     * @return \Inertia\Response
     */

    public function cidadaos(Request $request)
    {
        $filters = $request->validate([
            'busca' => 'nullable|string|max:255',
            'data_inicio' => 'nullable|date_format:Y-m-d',
            'data_fim' => 'nullable|date_format:Y-m-d|after_or_equal:data_inicio',
            'status' => 'nullable|boolean',
        ]);

        $cidadaos = $this->relatorioService
            ->gerarRelatorioCidadaosQuery($filters)
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Tenant/Relatorios/Cidadaos', [
            'cidadaos' => $cidadaos,
            'filtros' => $filters,
        ]);
    }

    /**
     * Exporta o relatório de cidadãos para XLSX.
     */
    public function exportarCidadaos(Request $request)
    {
        $filters = $request->validate([
            'busca' => 'nullable|string|max:255',
            'data_inicio' => 'nullable|date_format:Y-m-d',
            'data_fim' => 'nullable|date_format:Y-m-d|after_or_equal:data_inicio',
            'status' => 'nullable|boolean',
        ]);

        $fileName = 'relatorio_cidadaos_' . now()->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new CidadaosExport($filters), $fileName);
    }

    /**
     * Exporta o relatório de cidadãos para PDF com detalhes das solicitações.
     */
    public function exportarCidadaosPDF(Request $request)
    {
        $filters = $request->validate([
            'busca' => 'nullable|string|max:255',
            'data_inicio' => 'nullable|date_format:Y-m-d',
            'data_fim' => 'nullable|date_format:Y-m-d|after_or_equal:data_inicio',
            'status' => 'nullable|boolean',
        ]);

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

