<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Legislatura;
use App\Models\Tenant\Politico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class LegislaturaController extends Controller
{
    public function index()
    {
        return inertia('Tenant/Memoria/Legislaturas/Index', [
            'legislaturas' => Legislatura::withCount('mandatos')->orderBy('data_inicio', 'desc')->paginate(10),
        ]);
    }

    public function create()
    {
        // Para a criação, não precisamos passar nenhum dado extra.
        return inertia('Tenant/Memoria/Legislaturas/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'texto_destaque' => 'nullable|string',
            'foto_principal' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('foto_principal')) {
            $validated['foto_principal_path'] = $request->file('foto_principal')->store('legislaturas', 'public');
        }

        Legislatura::create($validated);

        // --- CORREÇÃO APLICADA AQUI ---
        // Redireciona para a página de listagem com uma mensagem de sucesso.
        return Redirect::route('admin.legislaturas.index')->with('success', 'Legislatura criada com sucesso.');
    }

    public function edit(Legislatura $legislatura)
    {
        // Carrega os relacionamentos necessários para a edição.
        $legislatura->load('mandatos.politico');

        return inertia('Tenant/Memoria/Legislaturas/Form', [
            'legislatura' => $legislatura,
            'politicos' => Politico::orderBy('nome_politico')->get(['id', 'nome_politico']),
        ]);
    }

    public function update(Request $request, Legislatura $legislatura)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'texto_destaque' => 'nullable|string',
            'foto_principal' => 'sometimes|nullable|image|max:2048',
        ]);

        if ($request->hasFile('foto_principal')) {
            if ($legislatura->foto_principal_path) {
                Storage::disk('public')->delete($legislatura->foto_principal_path);
            }
            $validated['foto_principal_path'] = $request->file('foto_principal')->store('legislaturas', 'public');
        }

        $legislatura->update($validated);

        // Após atualizar, o correto é continuar na mesma página.
        return Redirect::route('admin.legislaturas.index')->with('success', 'Legislatura atualizada.');
    }

    public function destroy(Legislatura $legislatura)
    {
        if ($legislatura->foto_principal_path) {
            Storage::disk('public')->delete($legislatura->foto_principal_path);
        }
        $legislatura->delete();

        return Redirect::route('admin.legislaturas.index')->with('success', 'Legislatura excluída.');
    }
}
