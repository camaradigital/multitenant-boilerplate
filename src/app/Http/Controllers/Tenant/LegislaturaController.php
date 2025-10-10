<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Legislatura;
use App\Models\Tenant\Politico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class LegislaturaController extends Controller
{
    /**
     * Adiciona o middleware de autorização para o resource.
     */
    public function __construct()
    {
        // Garante que as permissões (policies) sejam aplicadas a todos os métodos.
        $this->authorizeResource(Legislatura::class, 'legislatura');
    }

    public function index()
    {
        return Inertia::render('Tenant/Memoria/Legislaturas/Index', [
            'legislaturas' => Legislatura::withCount('mandatos')->orderBy('data_inicio', 'desc')->paginate(10),
        ]);
    }

    public function create()
    {
        return Inertia::render('Tenant/Memoria/Legislaturas/Form', [
            'politicos' => Politico::orderBy('nome_politico')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'titulo' => 'required|string|max:255',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'texto_destaque' => 'nullable|string',
            'foto_principal' => 'nullable|image|max:2048',
            'is_atual' => 'required|boolean',
        ]);

        $legislatura = DB::transaction(function () use ($validatedData, $request) {
            // Se a nova legislatura for marcada como atual, desmarca todas as outras.
            if ($validatedData['is_atual']) {
                Legislatura::where('is_atual', true)->update(['is_atual' => false]);
            }

            if ($request->hasFile('foto_principal')) {
                $validatedData['foto_principal_path'] = $request->file('foto_principal')->store('legislaturas', 'public');
            }
            unset($validatedData['foto_principal']);

            // Cria a nova legislatura
            return Legislatura::create($validatedData);
        });

        // Redireciona para a página de edição para que o usuário possa adicionar os membros.
        return Redirect::route('admin.legislaturas.edit', $legislatura->id)
            ->with('success', 'Legislatura criada. Adicione agora a composição.');
    }

    public function edit(Legislatura $legislatura)
    {
        $legislatura->load('mandatos.politico');

        return Inertia::render('Tenant/Memoria/Legislaturas/Form', [
            'legislatura' => $legislatura,
            'politicos' => Politico::orderBy('nome_politico')->get(['id', 'nome_politico']),
        ]);
    }

    public function update(Request $request, Legislatura $legislatura)
    {
        $validatedData = $request->validate([
            'titulo' => 'required|string|max:255',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'texto_destaque' => 'nullable|string',
            'foto_principal' => 'sometimes|nullable|image|max:2048',
            'is_atual' => 'required|boolean',
        ]);

        DB::transaction(function () use ($validatedData, $request, $legislatura) {
            // Se esta legislatura for marcada como atual, desmarca as outras.
            if ($validatedData['is_atual']) {
                Legislatura::where('id', '!=', $legislatura->id)
                    ->where('is_atual', true)
                    ->update(['is_atual' => false]);
            }

            if ($request->hasFile('foto_principal')) {
                if ($legislatura->foto_principal_path) {
                    Storage::disk('public')->delete($legislatura->foto_principal_path);
                }
                $validatedData['foto_principal_path'] = $request->file('foto_principal')->store('legislaturas', 'public');
            }
            unset($validatedData['foto_principal']);

            $legislatura->update($validatedData);
        });

        // Retorna para a mesma página (edição) com a mensagem de sucesso.
        return back()->with('success', 'Legislatura atualizada com sucesso.');
    }

    public function destroy(Legislatura $legislatura)
    {
        if ($legislatura->foto_principal_path) {
            Storage::disk('public')->delete($legislatura->foto_principal_path);
        }
        $legislatura->delete();

        return Redirect::route('admin.legislaturas.index')->with('success', 'Legislatura excluída com sucesso.');
    }
}
