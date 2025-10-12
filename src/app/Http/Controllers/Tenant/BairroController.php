<?php

// src/app/Http/Controllers/Tenant/BairroController.php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpsertBairroRequest;
use App\Models\Tenant\Bairro;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BairroController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Bairro::class, 'bairro');
    }

    public function index(): Response
    {
        $bairros = Bairro::query()
            ->select(['id', 'nome', 'tipo_logradouro', 'created_at'])
            ->orderBy('nome')
            ->paginate(10);

        return Inertia::render('Tenant/Bairros/Index', [
            'bairros' => $bairros,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Tenant/Bairros/Form');
    }

    public function store(UpsertBairroRequest $request): RedirectResponse
    {
        Bairro::create($request->validated());

        // Rota corrigida
        return redirect()->route('admin.bairros.index')
            ->with('success', 'Bairro criado com sucesso.');
    }

    public function edit(Bairro $bairro): Response
    {
        return Inertia::render('Tenant/Bairros/Form', [
            'bairro' => $bairro,
        ]);
    }

    public function update(UpsertBairroRequest $request, Bairro $bairro): RedirectResponse
    {
        $bairro->update($request->validated());

        // Rota corrigida
        return redirect()->route('admin.bairros.index')
            ->with('success', 'Bairro atualizado com sucesso.');
    }

    public function destroy(Bairro $bairro): RedirectResponse
    {
        $bairro->delete();

        // Rota corrigida
        return redirect()->route('admin.bairros.index')
            ->with('success', 'Bairro excluído com sucesso.');
    }
}
