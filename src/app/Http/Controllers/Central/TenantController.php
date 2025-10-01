<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreTenantRequest;
use App\Http\Requests\Central\UpdateTenantRequest;
use App\Models\Central\Tenant;
use App\Services\Central\TenantManagerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage; // Importe a classe Storage
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    /**
     * O construtor aplica a autorização do resource para todos os métodos.
     */
    public function __construct()
    {
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
     */
    public function store(StoreTenantRequest $request, TenantManagerService $tenantManager): RedirectResponse
    {
        $validatedData = $request->validated();

        // --- INÍCIO DA MODIFICAÇÃO ---
        // Verifica se um arquivo de logotipo foi enviado
        if ($request->hasFile('logotipo')) {
            // Salva o arquivo em 'public/tenants/logos' e armazena o caminho
            $path = $request->file('logotipo')->store('tenants/logos', 'public');
            // Atribui o caminho do arquivo para a coluna 'logotipo_url' do banco
            $validatedData['logotipo_url'] = $path;
        }
        // --- FIM DA MODIFICAÇÃO ---

        $tenantDefaults = [
            'permite_cadastro_cidade_externa' => false,
            'limite_renda_juridico' => 0,
        ];

        $tenantData = array_merge($validatedData, $tenantDefaults);

        try {
            $tenantManager->create($tenantData);
        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'Falha ao criar o tenant: '.$e->getMessage()]);
        }

        return redirect()->route('central.tenants.index')->with('success', 'Tenant criado com sucesso!');
    }

    /**
     * Exibe os detalhes de um tenant específico.
     */
    public function show(Tenant $tenant): Response
    {
        return Inertia::render('Central/Tenants/Show', [
            'tenant' => $tenant,
        ]);
    }

    /**
     * Mostra o formulário para editar um tenant existente.
     */
    public function edit(Tenant $tenant): Response
    {
        return Inertia::render('Central/Tenants/Edit', [
            'tenant' => $tenant,
        ]);
    }

    /**
     * Atualiza o tenant especificado no banco de dados.
     */
    public function update(UpdateTenantRequest $request, Tenant $tenant): RedirectResponse
    {
        $validatedData = $request->validated();

        // --- INÍCIO DA MODIFICAÇÃO ---
        // Verifica se um novo arquivo de logotipo foi enviado
        if ($request->hasFile('logotipo')) {
            // 1. Deleta o logotipo antigo, se ele existir
            if ($tenant->logotipo_url) {
                Storage::disk('public')->delete($tenant->logotipo_url);
            }

            // 2. Salva o novo logotipo e atualiza o caminho
            $path = $request->file('logotipo')->store('tenants/logos', 'public');
            $validatedData['logotipo_url'] = $path;
        }
        // --- FIM DA MODIFICAÇÃO ---

        $tenant->update($validatedData);

        return redirect()->route('central.tenants.index')->with('success', 'Câmara atualizada com sucesso!');
    }

    /**
     * Remove o tenant especificado do banco de dados.
     */
    public function destroy(Tenant $tenant): RedirectResponse
    {
        // --- INÍCIO DA MODIFICAÇÃO ---
        // Deleta o arquivo de logotipo associado ao tenant, se existir
        if ($tenant->logotipo_url) {
            Storage::disk('public')->delete($tenant->logotipo_url);
        }
        // --- FIM DA MODIFICAÇÃO ---

        // Adicione aqui a lógica para deletar o banco de dados do tenant se necessário
        // Ex: DB::statement('DROP DATABASE ' . $tenant->database_name);

        $tenant->delete();

        return redirect()->route('central.tenants.index')->with('success', 'Câmara excluída com sucesso!');
    }
}
