<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\DashboardPreference;
use App\Models\Tenant\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DashboardCustomizationController extends Controller
{
    /**
     * A lista mestre de widgets padrão.
     * O 'group' define a qual categoria o widget pertence.
     *
     * @var array
     */
    private const DEFAULT_WIDGETS = [
        // --- Ações Rápidas (Botões) ---
        ['widget_identifier' => 'action.novoAtendimento', 'group' => 'actions'],
        ['widget_identifier' => 'action.novoCidadao', 'group' => 'actions'],
        ['widget_identifier' => 'action.novaVaga', 'group' => 'actions'],
        ['widget_identifier' => 'action.verRelatorios', 'group' => 'actions'],
        ['widget_identifier' => 'action.gerirFuncionarios', 'group' => 'actions'],
        ['widget_identifier' => 'action.gerirServicos', 'group' => 'actions'],

        // --- Métricas Principais (Cards de Estatísticas) ---
        ['widget_identifier' => 'metric.atendimentosHoje', 'group' => 'metrics'],
        ['widget_identifier' => 'metric.solicitacoesPendentes', 'group' => 'metrics'],
        ['widget_identifier' => 'metric.novosCidadaosHoje', 'group' => 'metrics'],
        ['widget_identifier' => 'metric.totalCidadaos', 'group' => 'metrics'],
        ['widget_identifier' => 'metric.mensagensNaoLidas', 'group' => 'metrics'],
        ['widget_identifier' => 'metric.vagasAbertas', 'group' => 'metrics'],
        ['widget_identifier' => 'metric.sugestoesPendentes', 'group' => 'metrics'],
        ['widget_identifier' => 'metric.tempoMedio', 'group' => 'metrics'],
        ['widget_identifier' => 'metric.satisfacaoMedia', 'group' => 'metrics'],

        // --- Gráficos (Visualização de Dados) ---
        ['widget_identifier' => 'chart.atendimentos7d', 'group' => 'main'],
        ['widget_identifier' => 'chart.novosCidadaos30d', 'group' => 'main'],
        ['widget_identifier' => 'chart.solicitacoesPorStatus', 'group' => 'main'],
        ['widget_identifier' => 'chart.demandasPorBairro', 'group' => 'main'],

        // --- Listas Dinâmicas ---
        ['widget_identifier' => 'list.solicitacoesRecentes', 'group' => 'side'],
        ['widget_identifier' => 'list.ultimosCidadaosCadastrados', 'group' => 'side'],
        ['widget_identifier' => 'list.ultimasMensagensGabinete', 'group' => 'side'],
    ];

    /**
     * NOVO: Define a estrutura das categorias de widgets.
     * Para adicionar uma nova categoria, basta adicioná-la aqui.
     */
    private function getWidgetGroups(): array
    {
        return [
            'actions' => ['title' => 'Ações Principais', 'prefixes' => ['action.']],
            'metrics' => ['title' => 'Métricas', 'prefixes' => ['metric.']],
            'main' => ['title' => 'Gráficos', 'prefixes' => ['chart.']],
            'side' => ['title' => 'Listas e Links', 'prefixes' => ['list.', 'link.']],
            // Exemplo de como adicionar uma nova categoria no futuro:
            // 'reports' => ['title' => 'Relatórios Rápidos', 'prefixes' => ['report.']],
        ];
    }

    /**
     * Mostra a página de personalização da dashboard.
     */
    public function edit(Request $request): Response
    {
        Gate::authorize('customize-dashboard');

        $allRoles = Role::where('guard_name', 'tenant')
            ->where('name', '!=', 'Cidadao')
            ->get();

        $selectedRoleId = $request->input('role');
        $selectedRole = null;
        $preferences = [];

        if ($selectedRoleId) {
            $selectedRole = Role::where('id', $selectedRoleId)
                ->where('name', '!=', 'Cidadao')
                ->firstOrFail();

            $this->ensurePreferencesForRole($selectedRole->id);

            $preferences = DashboardPreference::where('role_id', $selectedRole->id)
                ->orderBy('order', 'asc')
                ->get();
        }

        return Inertia::render('Tenant/Dashboard/Customize', [
            'allRoles' => $allRoles,
            'selectedRole' => $selectedRole,
            'preferences' => $preferences,
            'availableLinks' => $this->getAvailableLinkWidgets(),
            // NOVO: Passa a estrutura de grupos para o frontend.
            'widgetGroups' => $this->getWidgetGroups(),
        ]);
    }

    /**
     * Atualiza as preferências da dashboard para um determinado papel.
     */
    public function update(Request $request)
    {
        Gate::authorize('customize-dashboard');

        $validated = $request->validate([
            'role_id' => ['required', Rule::exists(Role::class, 'id')->where('guard_name', 'tenant')],
            'widgets' => 'required|array',
            'widgets.*.id' => ['nullable', Rule::exists(DashboardPreference::class, 'id')],
            'widgets.*.widget_identifier' => 'required|string',
            'widgets.*.is_visible' => 'required|boolean',
            'widgets.*.order' => 'required|integer',
            'widgets.*.settings' => 'required|array',
        ]);

        $roleIdToUpdate = $validated['role_id'];
        $processedIds = [];

        foreach ($validated['widgets'] as $widgetData) {
            $preference = DashboardPreference::updateOrCreate(
                [
                    'id' => $widgetData['id'] ?? null,
                    'role_id' => $roleIdToUpdate,
                ],
                [
                    'widget_identifier' => $widgetData['widget_identifier'],
                    'is_visible' => $widgetData['is_visible'],
                    'order' => $widgetData['order'],
                    'settings' => $widgetData['settings'],
                    'role_id' => $roleIdToUpdate,
                ]
            );
            $processedIds[] = $preference->id;
        }

        DashboardPreference::where('role_id', $roleIdToUpdate)
            ->whereNotIn('id', $processedIds)
            ->where('widget_identifier', 'like', 'link.%')
            ->delete();

        return redirect()->route('admin.dashboard.customize.edit', ['role' => $roleIdToUpdate])
            ->with('success', 'Dashboard atualizada com sucesso!');
    }

    /**
     * Garante que os widgets fixos (não-links) existam para o perfil.
     */
    private function ensurePreferencesForRole(int $roleId): void
    {
        $existingIdentifiers = DashboardPreference::where('role_id', $roleId)
            ->pluck('widget_identifier')
            ->all();

        $missingWidgets = collect(self::DEFAULT_WIDGETS)->filter(function ($widget) use ($existingIdentifiers) {
            return ! in_array($widget['widget_identifier'], $existingIdentifiers);
        });

        if ($missingWidgets->isEmpty()) {
            return;
        }

        $maxOrder = DashboardPreference::where('role_id', $roleId)->max('order') ?? 0;

        $newPreferencesToInsert = [];
        foreach ($missingWidgets as $widget) {
            $maxOrder++;
            $newPreferencesToInsert[] = [
                'role_id' => $roleId,
                'widget_identifier' => $widget['widget_identifier'],
                'is_visible' => true,
                'order' => $maxOrder,
                // ATUALIZADO: Monta o JSON de settings com base na chave 'group'
                'settings' => json_encode(['group' => $widget['group']]),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (! empty($newPreferencesToInsert)) {
            DashboardPreference::insert($newPreferencesToInsert);
        }
    }

    /**
     * Busca, filtra e formata todas as rotas do painel admin que podem ser usadas como links.
     */
    private function getAvailableLinkWidgets(): array
    {
        $allRoutes = Route::getRoutes()->getRoutes();
        $availableLinks = [];

        $ignoreList = [
            'admin.dashboard.customize.edit',
            'admin.dashboard.customize.update',
            'admin.documentos.download',
            'admin.documentos.store',
            'admin.documentos.destroy',
        ];

        foreach ($allRoutes as $route) {
            $routeName = $route->getName();

            if (
                $routeName &&
                Str::startsWith($routeName, 'admin.') &&
                in_array('GET', $route->methods()) &&
                ! in_array($routeName, $ignoreList) &&
                ! Str::contains($routeName, ['show', 'edit', 'create'])
            ) {
                $friendlyName = Str::of($routeName)
                    ->after('admin.')
                    ->replace('.', ' ')
                    ->replace('-', ' ')
                    ->title()
                    ->replace('Index', '')
                    ->trim();

                $availableLinks[] = [
                    'identifier' => 'link.'.$routeName,
                    'name' => $routeName,
                    'friendly_name' => (string) $friendlyName,
                ];
            }
        }

        usort($availableLinks, fn ($a, $b) => strcmp($a['friendly_name'], $b['friendly_name']));

        return $availableLinks;
    }
}
