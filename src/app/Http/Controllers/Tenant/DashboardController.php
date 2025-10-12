<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\DashboardPreference;
use App\Models\Tenant\GabineteVirtualMensagem;
use App\Models\Tenant\Servico;
use App\Models\Tenant\SolicitacaoServico;
use App\Models\Tenant\SugestaoProjetoLei;
use App\Models\Tenant\User;
use App\Models\Tenant\Vaga;
use App\Services\Tenant\RelatorioService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Exibe o dashboard apropriado com base no papel do usuário.
     */
    public function __invoke(Request $request, RelatorioService $relatorioService): Response
    {
        if (Gate::allows('view-cidadao-dashboard')) {
            return $this->showCidadaoDashboard();
        }

        return $this->showAdminDashboard($relatorioService);
    }

    /**
     * Monta e retorna a view do Portal Pessoal para o Cidadão.
     */
    private function showCidadaoDashboard(): Response
    {
        $this->authorize('view-cidadao-dashboard');
        $user = Auth::user();
        $query = SolicitacaoServico::where('user_id', $user->id);
        $solicitacoes = $query->with([
            'servico', 'status', 'pesquisa_satisfacao',
            'historicos' => fn ($q_hist) => $q_hist->with('causer:id,name')->latest(),
        ])
            ->latest('solicitacoes_servico.created_at')
            ->paginate(10)
            ->withQueryString();

        $solicitacoes->getCollection()->transform(function ($solicitacao) {
            $solicitacao->activity = $solicitacao->historicos;
            unset($solicitacao->historicos);

            return $solicitacao;
        });

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
     * Monta e retorna a view do Dashboard Administrativo com base nas preferências do usuário.
     */
    private function showAdminDashboard(RelatorioService $relatorioService): Response
    {
        $this->authorize('view-admin-dashboard');

        $user = Auth::user();
        $userRole = $user->roles()->first();

        if (! $userRole) {
            return Inertia::render('Tenant/Dashboard', [
                'widgetData' => [], 'layout' => [], 'canCustomize' => $user->can('customize-dashboard'),
            ]);
        }

        $allPreferences = DashboardPreference::where('role_id', $userRole->id)->orderBy('order')->get();

        // CORREÇÃO: Filtra o layout para enviar APENAS os widgets visíveis para o front-end.
        $layout = $allPreferences->where('is_visible', true)->values();

        $widgetData = [];
        $visibleWidgets = $layout->pluck('widget_identifier'); // Agora pega os identificadores do layout já filtrado

        // Função para filtrar usuários que são cidadãos (excluindo admins, etc.)
        $cidadaosQuery = fn () => User::whereHas('roles', fn ($q) => $q->where('name', 'Cidadao'));

        foreach ($visibleWidgets as $widgetIdentifier) {
            match ($widgetIdentifier) {
                'metric.atendimentosHoje' => $widgetData['atendimentosHoje'] ??= SolicitacaoServico::whereDate('created_at', today())->count(),
                'metric.solicitacoesPendentes' => $widgetData['solicitacoesPendentes'] ??= SolicitacaoServico::whereHas('status', fn ($q) => $q->where('is_final', false))->count(),
                'metric.novosCidadaosHoje' => $widgetData['novosCidadaosHoje'] ??= $cidadaosQuery()->whereDate('created_at', today())->count(),
                'metric.totalCidadaos' => $widgetData['totalCidadaos'] ??= $cidadaosQuery()->count(),
                'metric.mensagensNaoLidas' => $widgetData['mensagensNaoLidas'] ??= GabineteVirtualMensagem::where('status', '!=', 'lido')->count(),
                'metric.vagasAbertas' => $widgetData['vagasAbertas'] ??= Vaga::where('status', 'aberta')->count(),
                'metric.sugestoesPendentes' => $widgetData['sugestoesPendentes'] ??= SugestaoProjetoLei::where('status', 'pendente')->count(),
                'metric.tempoMedio', 'metric.satisfacaoMedia' => $this->loadKpiData($widgetData, $relatorioService),
                'chart.atendimentos7d' => $this->loadChartData($widgetData),
                'chart.novosCidadaos30d' => $this->loadNovosCidadaosChartData($widgetData, $cidadaosQuery),
                'chart.solicitacoesPorStatus' => $this->loadSolicitacoesPorStatusChartData($widgetData),
                'chart.demandasPorBairro' => $this->loadDemandasPorBairroChartData($widgetData),

                'list.solicitacoesRecentes' => $widgetData['solicitacoesRecentes'] ??= SolicitacaoServico::with('cidadao:id,name', 'servico:id,nome')->whereHas('status', fn ($q) => $q->where('is_final', false))->latest()->limit(5)->get(),
                'list.ultimosCidadaosCadastrados' => $widgetData['ultimosCidadaosCadastrados'] ??= $cidadaosQuery()->latest()->limit(5)->get(['id', 'name', 'created_at']),
                'list.ultimasMensagensGabinete' => $widgetData['ultimasMensagensGabinete'] ??= GabineteVirtualMensagem::with('user:id,name')->latest()->limit(5)->get(),
                default => null,
            };
        }

        return Inertia::render('Tenant/Dashboard', [
            'widgetData' => $widgetData,
            'layout' => $layout,
            'canCustomize' => $user->can('customize-dashboard'),
        ]);
    }

    private function loadKpiData(array &$widgetData, RelatorioService $relatorioService): void
    {
        if (! isset($widgetData['kpis'])) {
            $statsAtendimento = $relatorioService->calcularEstatisticasAtendimentos(['data_inicio' => now()->subDays(6)->startOfDay()->toDateTimeString(), 'data_fim' => today()]);
            $statsSatisfacao = $relatorioService->calcularEstatisticasSatisfacao(['data_inicio' => now()->subDays(29)->startOfDay()->toDateTimeString(), 'data_fim' => today()]);
            $widgetData['kpis'] = [
                'tempoMedioFinalizacao' => $statsAtendimento['tempoMedioHoras'],
                'notaMediaSatisfacao' => $statsSatisfacao['notaMedia'],
            ];
        }
    }

    private function loadChartData(array &$widgetData): void
    {
        if (! isset($widgetData['atendimentosChartData'])) {
            $inicioSemana = now()->subDays(6)->startOfDay();
            $atendimentosPorDia = SolicitacaoServico::where('created_at', '>=', $inicioSemana)
                ->groupBy('date')->orderBy('date', 'asc')
                ->get([DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total')])
                ->pluck('total', 'date');
            $labels = [];
            $values = [];
            for ($i = 6; $i >= 0; $i--) {
                $data = now()->subDays($i)->toDateString();
                $labels[] = Carbon::parse($data)->format('d/m');
                $values[] = $atendimentosPorDia[$data] ?? 0;
            }
            $widgetData['atendimentosChartData'] = ['labels' => $labels, 'values' => $values];
        }
    }

    private function loadNovosCidadaosChartData(array &$widgetData, callable $cidadaosQuery): void
    {
        if (! isset($widgetData['novosCidadaosChartData'])) {
            $inicioPeriodo = now()->subDays(29)->startOfDay();
            $cidadaosPorDia = $cidadaosQuery()->where('created_at', '>=', $inicioPeriodo) // CORRIGIDO
                ->groupBy('date')->orderBy('date', 'asc')
                ->get([DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total')])
                ->pluck('total', 'date');
            $labels = [];
            $values = [];
            for ($i = 29; $i >= 0; $i--) {
                $data = now()->subDays($i)->toDateString();
                $labels[] = ($i % 5 === 0) ? Carbon::parse($data)->format('d/m') : '';
                $values[] = $cidadaosPorDia[$data] ?? 0;
            }
            $widgetData['novosCidadaosChartData'] = ['labels' => $labels, 'values' => $values];
        }
    }

    private function loadSolicitacoesPorStatusChartData(array &$widgetData): void
    {
        if (! isset($widgetData['solicitacoesPorStatusChartData'])) {
            $data = SolicitacaoServico::query()
                ->join('status_solicitacao', 'solicitacoes_servico.status_id', '=', 'status_solicitacao.id')
                ->select('status_solicitacao.nome as status_nome', DB::raw('count(solicitacoes_servico.id) as total'))
                ->groupBy('status_solicitacao.nome')
                ->orderByDesc('total')
                ->limit(5) // Limita aos 5 status mais comuns para um gráfico de pizza mais limpo
                ->pluck('total', 'status_nome');

            $widgetData['solicitacoesPorStatusChartData'] = [
                'labels' => $data->keys()->all(),
                'values' => $data->values()->all(),
            ];
        }
    }

    /**
     * Carrega dados para o gráfico de demandas por bairro.
     * ATENÇÃO: Este método assume que a tabela 'users' possui uma coluna 'bairro'.
     * Adapte o nome da coluna se necessário.
     */
    private function loadDemandasPorBairroChartData(array &$widgetData): void
    {
        if (! isset($widgetData['demandasPorBairroChartData'])) {
            $data = SolicitacaoServico::query()
                // 1. Junta com a tabela de usuários para obter o user_id
                ->join('users', 'solicitacoes_servico.user_id', '=', 'users.id')
                // 2. Junta com a tabela de bairros usando a chave estrangeira bairro_id
                ->join('bairros', 'users.bairro_id', '=', 'bairros.id')
                // Garante que não estamos contando usuários sem bairro definido
                ->whereNotNull('users.bairro_id')
                // 3. Seleciona o nome do bairro e a contagem
                ->select('bairros.nome as bairro_nome', DB::raw('count(solicitacoes_servico.id) as total'))
                ->groupBy('bairro_nome')
                ->orderByDesc('total')
                ->limit(7)
                ->pluck('total', 'bairro_nome');

            $widgetData['demandasPorBairroChartData'] = [
                'labels' => $data->keys()->all(),
                'values' => $data->values()->all(),
            ];
        }
    }
}
