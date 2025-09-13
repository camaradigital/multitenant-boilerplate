<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use App\Models\Tenant\User as TenantUser;
use App\Services\Central\TenantManagerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;

class TenantController extends Controller
{
    protected $tenantManager;

    public function __construct(TenantManagerService $tenantManager)
    {
        $this->tenantManager = $tenantManager;
        // Garante que as políticas de autorização sejam aplicadas a todos os métodos do resource.
        $this->authorizeResource(Tenant::class, 'tenant');
    }

    public function index()
    {
        return Inertia::render('Central/Tenants/List', [
            'tenants' => Tenant::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Central/Tenants/Create');
    }

    public function edit(Tenant $tenant)
    {
        return Inertia::render('Central/Tenants/Edit', [
            'tenant' => $tenant
        ]);
    }

    public function store(Request $request, TenantManagerService $tenantManager)
    {
        // 1. Valide APENAS os dados que realmente vêm do formulário do Super Admin
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subdomain' => 'required|string|lowercase|alpha_dash|max:50|unique:tenants,subdomain',
            'cnpj' => 'required|string|size:18|unique:tenants,cnpj',
            'admin_email' => 'required|email|max:255',
            'endereco_cep' => 'nullable|string|max:9',
            'endereco_logradouro' => 'nullable|string|max:255',
            'endereco_numero' => 'nullable|string|max:255',
            'endereco_complemento' => 'nullable|string|max:255',
            'endereco_bairro' => 'nullable|string|max:255',
            'endereco_cidade' => 'nullable|string|max:255',
            'endereco_estado' => 'nullable|string|max:2',
            'logotipo_url' => 'nullable|url|max:255',
            'site_url' => 'nullable|url|max:255',
            'cor_primaria' => 'nullable|string|size:7',
            'cor_secundaria' => 'nullable|string|size:7',
        ]);

        // 2. Defina os valores padrão para as configurações específicas do tenant
        $tenantDefaults = [
            'permite_cadastro_cidade_externa' => false, // Padrão é não permitir
            'limite_renda_juridico' => 0,             // Padrão é sem limite/zero
        ];

        // 3. Junte os dados validados do formulário com os valores padrão
        $tenantData = array_merge($validated, $tenantDefaults);

        try {
            // 4. Crie o tenant com o conjunto completo de dados
            $tenantManager->create($tenantData);

        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'Falha ao criar o tenant: ' . $e->getMessage()]);
        }

        return redirect()->route('central.tenants.index')->with('success', 'Tenant criado com sucesso!');
    }

        public function update(Request $request, Tenant $tenant)
    {
        // Valide apenas os campos que o Super Admin pode editar
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cnpj' => 'required|string|size:18|unique:tenants,cnpj,' . $tenant->id,
            'subdomain' => 'required|string|lowercase|alpha_dash|max:50|unique:tenants,subdomain,' . $tenant->id,
            'admin_email' => 'required|email|max:255',
            'endereco_cep' => 'nullable|string|max:9',
            'endereco_logradouro' => 'nullable|string|max:255',
            'endereco_numero' => 'nullable|string|max:255',
            'endereco_complemento' => 'nullable|string|max:255',
            'endereco_bairro' => 'nullable|string|max:255',
            'endereco_cidade' => 'nullable|string|max:255',
            'endereco_estado' => 'nullable|string|max:2',
            'logotipo_url' => 'nullable|url|max:255',
            'site_url' => 'nullable|url|max:255',
            'cor_primaria' => 'nullable|string|size:7',
            'cor_secundaria' => 'nullable|string|size:7',
            // As regras para 'permite_cadastro...' e 'limite_renda...' foram removidas.
        ]);

        $tenant->update($validated);

        return redirect()->route('central.tenants.index')->with('success', 'Câmara atualizada com sucesso!');
    }

    public function destroy(Tenant $tenant)
    {
        // Adicione aqui a lógica para deletar o banco de dados do tenant se necessário
        // Ex: DB::statement('DROP DATABASE ' . $tenant->database_name);

        $tenant->delete();

        return redirect()->route('central.tenants.index')->with('success', 'Câmara excluída com sucesso!');
    }

    public function show(Tenant $tenant)
    {
        return Inertia::render('Central/Tenants/Show', [
            'tenant' => $tenant
        ]);
    }

}


