<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\SugestaoProjetoLei;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SugestaoProjetoLeiController extends Controller
{
    public function __construct()
    {
        // Aplicando a policy para proteger os métodos de resource padrão (index, show, destroy)
        $this->authorizeResource(SugestaoProjetoLei::class, 'sugestao');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sugestoes = SugestaoProjetoLei::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where('titulo', 'like', "%{$search}%")
                    ->orWhere('cidadao_nome', 'like', "%{$search}%")
                    ->orWhere('protocolo', 'like', "%{$search}%");
            })
            ->when($request->input('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Tenant/Sugestoes/Index', [
            'sugestoes' => $sugestoes,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(SugestaoProjetoLei $sugestao)
    {
        // A autorização 'view' já foi aplicada pelo authorizeResource
        return Inertia::render('Tenant/Sugestoes/Show', [
            'sugestao' => $sugestao,
        ]);
    }

    /**
     * Update the status of the specified resource in storage.
     */
    public function updateStatus(Request $request, SugestaoProjetoLei $sugestao)
    {
        // CORREÇÃO: Autorização específica para gerenciar o status.
        // Isso chama o método `manageStatus` na SugestaoProjetoLeiPolicy.
        $this->authorize('manageStatus', $sugestao);

        $validated = $request->validate([
            'status' => 'required|in:Recebida,Em Análise,Arquivada,Aprovada',
        ]);

        $sugestao->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Status da sugestão atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SugestaoProjetoLei $sugestao)
    {
        // A autorização 'delete' já foi verificada pelo authorizeResource
        $sugestao->delete();

        return redirect()->route('admin.sugestoes.index')->with('success', 'Sugestão excluída com sucesso.');
    }
}
