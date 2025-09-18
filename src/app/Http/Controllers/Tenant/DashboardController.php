<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Servico;
use App\Models\Tenant\SolicitacaoServico;
use App\Services\Tenant\RelatorioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Exibe o dashboard apropriado com base no papel do usuário.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Services\Tenant\RelatorioService $relatorioService
     * @return \Inertia\Response
     */
    public function __invoke(Request $request, RelatorioService $relatorioService): Response
    {
        // A lógica de decisão foi movida para a Policy/Gate.
        // O controller agora apenas direciona para o método apropriado.
        if (Gate::allows('view-cidadao-dashboard')) {
            return $this->showCidadaoDashboard();
        }

        // Garante que apenas usuários autorizados vejam o dashboard administrativo.
        // A autorização acontece dentro do método showAdminDashboard.
        return $this->showAdminDashboard($relatorioService);
    }

    /**
     * Monta e retorna a view do Portal Pessoal para o Cidadão.
     *
     * @return \Inertia\Response
     */
    private function showCidadaoDashboard(): Response
    {
        $this->authorize('view-cidadao-dashboard');

        $user = Auth::user();

        $query = SolicitacaoServico::where('user_id', $user->id);

        // Carrega a solicitação com todos os seus relacionamentos, incluindo a pesquisa de satisfação.
        $solicitacoes = $query->with([
            'servico',
            'status',
            'pesquisa_satisfacao',
            'historicos' => function ($q_hist) {
                $q_hist->with('causer:id,name')->latest();
            }
        ])
        ->latest('solicitacoes_servico.created_at')
        ->paginate(10)
        ->withQueryString();

        // Renomeia 'historicos' para 'activity' para corresponder ao que o frontend espera.
        $solicitacoes->getCollection()->transform(function ($solicitacao) {
            $solicitacao->activity = $solicitacao->historicos;
            unset($solicitacao->historicos);
            return $solicitacao;
        });

        // Adiciona a verificação que estava faltando
        $canCreateOnlineSolicitacao = Servico::withoutGlobalScopes()
                                            ->where('is_active', true)
                                            ->where('permite_solicitacao_online', true)
                                            ->exists();

        return Inertia::render('Tenant/PortalPessoal/Index', [
            'solicitacoes' => $solicitacoes,
            'canCreateOnlineSolicitacao' => $canCreateOnlineSolicitacao,
        ]);
    }

    /**
     * Monta e retorna a view do Dashboard Administrativo.
     *
     * @param \App\Services\Tenant\RelatorioService $relatorioService
     * @return \Inertia\Response
     */
    private function showAdminDashboard(RelatorioService $relatorioService): Response
    {
        $this->authorize('view-admin-dashboard');

        // --- DASHBOARD ADMINISTRATIVO APRIMORADO ---
        // Define os períodos de tempo para análise
        $hoje = Carbon::today()->toDateString();
        $inicioSemana = Carbon::now()->subDays(6)->startOfDay()->toDateTimeString(); // Últimos 7 dias
        $inicioMes = Carbon::now()->subDays(29)->startOfDay()->toDateTimeString(); // Últimos 30 dias
        $fimPeriodo = Carbon::now()->endOfDay()->toDateTimeString();

        // Calcula os KPIs usando o RelatorioService
        $statsAtendimento = $relatorioService->calcularEstatisticasAtendimentos([
            'data_inicio' => $inicioSemana,
            'data_fim' => $hoje,
        ]);

        $statsSatisfacao = $relatorioService->calcularEstatisticasSatisfacao([
            'data_inicio' => $inicioMes,
            'data_fim' => $hoje,
        ]);

        $kpis = [
            'atendimentosHoje' => SolicitacaoServico::whereDate('created_at', $hoje)->count(),
            'solicitacoesPendentes' => SolicitacaoServico::whereHas('status', fn ($q) => $q->where('is_final', false))->count(),
            'tempoMedioFinalizacao' => $statsAtendimento['tempoMedioHoras'], // KPI dos últimos 7 dias
            'notaMediaSatisfacao' => $statsSatisfacao['notaMedia'],      // KPI dos últimos 30 dias
        ];

        // Prepara os dados para o gráfico de atendimentos (últimos 7 dias)
        $atendimentosPorDia = SolicitacaoServico::query()
            ->whereBetween('created_at', [$inicioSemana, $fimPeriodo])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get([
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as total')
            ])
            ->pluck('total', 'date');

        // Preenche os dias sem atendimentos com 0 para um gráfico contínuo
        $datasLabels = [];
        $datasValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $data = Carbon::now()->subDays($i)->toDateString();
            $datasLabels[] = Carbon::parse($data)->format('d/m');
            $datasValues[] = $atendimentosPorDia[$data] ?? 0;
        }

        $atendimentosChartData = [
            'labels' => $datasLabels,
            'values' => $datasValues,
        ];

        // Busca as últimas solicitações pendentes para uma lista de "A Fazer"
        $solicitacoesRecentes = SolicitacaoServico::with('cidadao:id,name', 'servico:id,nome')
            ->whereHas('status', fn ($q) => $q->where('is_final', false))
            ->latest()
            ->limit(5)
            ->get();

        return Inertia::render('Tenant/Dashboard', [
            'kpis' => $kpis,
            'atendimentosChartData' => $atendimentosChartData,
            'solicitacoesRecentes' => $solicitacoesRecentes,
        ]);
    }
}
