<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use App\Services\Central\TenantManagerService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TenantController extends Controller
{
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
        // Validação atualizada para incluir o CNPJ
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'subdomain' => 'required|string|lowercase|alpha_dash|max:50|unique:tenants,subdomain', // Corrigido
            'cnpj' => 'required|string|size:18|unique:tenants,cnpj', // Ex: 00.000.000/0000-00
            'admin_email' => 'required|email|max:255',
        ]);

        try {
            $tenantManager->create(
                $validated['nome'],
                $validated['subdomain'], // Corrigido
                $validated['admin_email'],
                $validated['cnpj']
            );
        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'Falha ao criar o tenant: ' . $e->getMessage()]);
        }

        return redirect()->route('central.tenants.index')->with('success', 'Tenant criado com sucesso!');
    }

        public function update(Request $request, Tenant $tenant)
    {
        // Valide os dados (semelhante ao método store)
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'cnpj' => 'required|string|size:18|unique:tenants,cnpj,' . $tenant->id,
            'subdomain' => 'required|string|lowercase|alpha_dash|max:50|unique:tenants,subdomain,' . $tenant->id,
            'admin_email' => 'required|email|max:255',
            // Adicione as outras regras de validação aqui...
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
