<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\Rule;

class EmpresaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $empresas = Empresa::with('user')->latest()->paginate(10);
        return Inertia::render('Tenant/Empresas/Index', compact('empresas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Tenant/Empresas/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // CORREÇÃO: Especificamos a conexão 'tenant' para a regra 'unique'.
        // Isso garante que a validação ocorra no banco de dados do tenant,
        // onde a tabela 'empresas' existe.
        $request->validate([
            'nome_fantasia' => 'required|string|max:255',
            'razao_social' => 'nullable|string|max:255',
            'cnpj' => 'nullable|string|max:18|unique:tenant.empresas,cnpj',
            'email' => 'required|email|max:255|unique:tenant.empresas,email',
            'telefone' => 'nullable|string|max:20',
            'endereco' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();
        $data['user_id'] = Auth::id(); // Associa o usuário que está cadastrando

        Empresa::create($data);

        return redirect()->route('admin.empresas.index')->with('success', 'Empresa cadastrada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Empresa $empresa): Response
    {
        $empresa->load('user', 'vagas');
        return Inertia::render('Tenant/Empresas/Show', compact('empresa'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Empresa $empresa): Response
    {
        return Inertia::render('Tenant/Empresas/Edit', compact('empresa'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Empresa $empresa)
    {
        // CORREÇÃO: A mesma lógica é aplicada aqui para a atualização.
        // A regra 'unique' agora verifica na conexão 'tenant', ignorando o registro atual.
        $request->validate([
            'nome_fantasia' => 'required|string|max:255',
            'razao_social' => 'nullable|string|max:255',
            'cnpj' => 'nullable|string|max:18|unique:tenant.empresas,cnpj,' . $empresa->id,
            'email' => 'required|email|max:255|unique:tenant.empresas,email,' . $empresa->id,
            'telefone' => 'nullable|string|max:20',
            'endereco' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $empresa->update($request->all());

        return redirect()->route('admin.empresas.index')->with('success', 'Empresa atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Empresa $empresa)
    {
        // Adicionar verificação se a empresa possui vagas antes de excluir
        if ($empresa->vagas()->count() > 0) {
            return redirect()->route('admin.empresas.index')->with('error', 'Não é possível excluir uma empresa que possui vagas cadastradas.');
        }

        $empresa->delete();

        return redirect()->route('admin.empresas.index')->with('success', 'Empresa excluída com sucesso.');
    }
}
