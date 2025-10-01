<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Empresa;
use App\Models\Tenant\Vaga;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VagaController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Aplica a VagaPolicy a todos os métodos do resource controller.
        // Isto centraliza as regras de permissão e torna o código mais limpo.
        $this->authorizeResource(Vaga::class, 'vaga');
    }

    /**
     * Display a listing of the resource for the admin panel.
     */
    public function index(): Response
    {
        // ATUALIZAÇÃO: Adicionado withCount('candidaturas') para carregar a contagem
        // de candidaturas de forma eficiente, sem precisar de carregar todos os modelos.
        $vagas = Vaga::with('empresa')->withCount('candidaturas')->latest()->paginate(10);
        $empresas = Empresa::where('is_active', true)->orderBy('nome_fantasia')->get();

        return Inertia::render('Tenant/Vagas/Index', compact('vagas', 'empresas'));
    }

    /**
     * Show the form for creating a new resource.
     * (Este método pode não ser usado se a criação for feita via modal no Index)
     */
    public function create(): Response
    {
        $empresas = Empresa::where('is_active', true)->orderBy('nome_fantasia')->get();

        return Inertia::render('Tenant/Vagas/Create', compact('empresas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'empresa_id' => 'required|exists:tenant.empresas,id',
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string',
            'responsabilidades' => 'nullable|string',
            'requisitos' => 'nullable|string',
            'salario' => 'nullable|numeric|min:0',
            'tipo_contratacao' => 'required|string|max:255',
            'localizacao' => 'required|string|max:255',
            'status' => 'required|in:aberta,fechada,pausada',
            'data_expiracao' => 'nullable|date',
        ]);

        Vaga::create($request->all());

        return redirect()->route('admin.vagas.index')->with('success', 'Vaga criada com sucesso.');
    }

    /**
     * Display the specified resource for the admin panel.
     */
    public function show(Vaga $vaga): Response
    {
        $vaga->load('empresa', 'candidaturas.user');

        return Inertia::render('Tenant/Vagas/Show', compact('vaga'));
    }

    /**
     * Show the form for editing the specified resource.
     * (Este método pode não ser usado se a edição for feita via modal no Index)
     */
    public function edit(Vaga $vaga): Response
    {
        $empresas = Empresa::where('is_active', true)->orderBy('nome_fantasia')->get();

        return Inertia::render('Tenant/Vagas/Edit', [
            'vaga' => $vaga,
            'empresas' => $empresas,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vaga $vaga)
    {
        $request->validate([
            'empresa_id' => 'required|exists:tenant.empresas,id',
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string',
            'responsabilidades' => 'nullable|string',
            'requisitos' => 'nullable|string',
            'salario' => 'nullable|numeric|min:0',
            'tipo_contratacao' => 'required|string|max:255',
            'localizacao' => 'required|string|max:255',
            'status' => 'required|in:aberta,fechada,pausada',
            'data_expiracao' => 'nullable|date',
        ]);

        $vaga->update($request->all());

        return redirect()->route('admin.vagas.index')->with('success', 'Vaga atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vaga $vaga)
    {
        $vaga->delete();

        return redirect()->route('admin.vagas.index')->with('success', 'Vaga excluída com sucesso.');
    }

    /**
     * Display a public listing of the resource.
     */
    public function indexPublic(): Response
    {
        $vagas = Vaga::with('empresa')
            ->where('status', 'aberta')
            ->where(function ($query) {
                $query->whereNull('data_expiracao')
                    ->orWhere('data_expiracao', '>=', now());
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Tenant/Portal/Publico/Vagas/Index', compact('vagas'));
    }

    /**
     * Display the specified public resource.
     */
    public function showPublic(Vaga $vaga): Response
    {
        if ($vaga->status !== 'aberta' || ($vaga->data_expiracao && $vaga->data_expiracao < now())) {
            abort(404);
        }

        $vaga->load('empresa');

        return Inertia::render('Tenant/Portal/Publico/Vagas/Show', compact('vaga'));
    }
}
