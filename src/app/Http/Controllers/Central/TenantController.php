<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreTenantRequest;
use App\Http\Requests\Central\UpdateTenantRequest;
use App\Models\Central\Tenant;
use App\Services\Central\TenantManagerService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    /**
     * O construtor aplica a autorização do resource para todos os métodos.
     */
    public function __construct()
    {
        // Garante que as políticas de autorização sejam aplicadas a todos os métodos do resource.
        $this->authorizeResource(Tenant::class, 'tenant');
    }

    /**
     * Exibe uma lista dos tenants.
     */
    public function index(): Response
    {
        return Inertia::render('Central/Tenants/List', [
            'tenants' => Tenant::all(),
        ]);
    }

    /**
     * Mostra o formulário para criar um novo tenant.
     */
    public function create(): Response
    {
        return Inertia::render('Central/Tenants/Create');
    }

    /**
     * Armazena um novo tenant no banco de dados.
     *
     * @param StoreTenantRequest $request
     * @param TenantManagerService $tenantManager
     * @return RedirectResponse
     */
    public function store(StoreTenantRequest $request, TenantManagerService $tenantManager): RedirectResponse
    {
        // A validação agora é feita pelo StoreTenantRequest.
        // Pegamos apenas os dados validados.
        $validatedData = $request->validated();

        // Defina os valores padrão para as configurações específicas do tenant
        $tenantDefaults = [
            'permite_cadastro_cidade_externa' => false,
            'limite_renda_juridico' => 0,
        ];

        // Junte os dados validados do formulário com os valores padrão
        $tenantData = array_merge($validatedData, $tenantDefaults);

        try {
            // Crie o tenant com o conjunto completo de dados
            $tenantManager->create($tenantData);

        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'Falha ao criar o tenant: ' . $e->getMessage()]);
        }

        return redirect()->route('central.tenants.index')->with('success', 'Tenant criado com sucesso!');
    }

    /**
     * Exibe os detalhes de um tenant específico.
     */
    public function show(Tenant $tenant): Response
    {
        return Inertia::render('Central/Tenants/Show', [
            'tenant' => $tenant
        ]);
    }

    /**
     * Mostra o formulário para editar um tenant existente.
     */
    public function edit(Tenant $tenant): Response
    {
        return Inertia::render('Central/Tenants/Edit', [
            'tenant' => $tenant
        ]);
    }

    /**
     * Atualiza o tenant especificado no banco de dados.
     *
     * @param UpdateTenantRequest $request
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function update(UpdateTenantRequest $request, Tenant $tenant): RedirectResponse
    {
        // A validação agora é feita pelo UpdateTenantRequest.
        // O método validated() retorna os dados validados.
        $tenant->update($request->validated());

        return redirect()->route('central.tenants.index')->with('success', 'Câmara atualizada com sucesso!');
    }

    /**
     * Remove o tenant especificado do banco de dados.
     */
    public function destroy(Tenant $tenant): RedirectResponse
    {
        // Adicione aqui a lógica para deletar o banco de dados do tenant se necessário
        // Ex: DB::statement('DROP DATABASE ' . $tenant->database_name);

        $tenant->delete();

        return redirect()->route('central.tenants.index')->with('success', 'Câmara excluída com sucesso!');
    }
}
