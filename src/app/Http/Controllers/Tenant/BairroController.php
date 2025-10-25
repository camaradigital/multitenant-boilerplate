<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpsertBairroRequest;
use App\Models\Tenant\Bairro;
use Illuminate\Http\RedirectResponse;
// ADICIONE ESTA LINHA PARA CORRIGIR O ERRO
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BairroController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Bairro::class, 'bairro');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $bairros = Bairro::orderBy('nome')->paginate(10);

        return Inertia::render('Tenant/Bairros/Index', [
            'bairros' => $bairros,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UpsertBairroRequest $request): RedirectResponse
    {
        Bairro::create($request->validated());

        return redirect()->route('admin.bairros.index')->with('success', 'Bairro criado com sucesso.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpsertBairroRequest $request, Bairro $bairro): RedirectResponse
    {
        $bairro->update($request->validated());

        return redirect()->route('admin.bairros.index')->with('success', 'Bairro atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bairro $bairro): RedirectResponse
    {
        $bairro->delete();

        return redirect()->route('admin.bairros.index')->with('success', 'Bairro excluído com sucesso.');
    }

    /**
     * Search for bairros.
     * A assinatura do método foi corrigida para usar a classe Request correta.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $term = $request->query('term');
        $bairros = Bairro::where('nome', 'LIKE', $term . '%')
                          ->select('id', 'nome')
                          ->take(10) // Limita a 10 resultados para performance
                          ->get();

        return response()->json($bairros);
    }
}
