<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Politico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PoliticoController extends Controller
{
    /**
     * Aplica a PoliticoPolicy aos métodos do resource controller.
     */
    public function __construct()
    {
        $this->authorizeResource(Politico::class, 'politico');
    }

    public function index()
    {
        return inertia('Tenant/Memoria/Politicos/Index', [
            'politicos' => Politico::orderBy('nome_politico')->paginate(10),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome_completo' => 'required|string|max:255',
            'nome_politico' => 'required|string|max:100',
            'partido' => 'nullable|string|max:50',
            'biografia' => 'nullable|string',
            'foto' => 'nullable|image|max:1024',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('tenant.politicos', 'email'),
            ],
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto_path'] = $request->file('foto')->store('politicos', 'public');
        }

        Politico::create($validated);

        return Redirect::route('admin.politicos.index')->with('success', 'Político cadastrado.');
    }

    public function update(Request $request, Politico $politico)
    {
        $validated = $request->validate([
            'nome_completo' => 'required|string|max:255',
            'nome_politico' => 'required|string|max:100',
            'partido' => 'nullable|string|max:50',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('tenant.politicos')->ignore($politico->id),
            ],
            'biografia' => 'nullable|string',
            'foto' => 'nullable|image|max:1024',
        ]);

        $isInCurrentLegislatura = $politico->mandatos()->whereHas('legislatura', function ($query) {
            $query->where('is_atual', true);
        })->exists();

        if ($isInCurrentLegislatura && empty($validated['email'])) {
            throw ValidationException::withMessages([
                'email' => 'O e-mail é obrigatório para políticos que fazem parte da legislatura atual e não pode ser removido.',
            ]);
        }

        if ($request->hasFile('foto')) {
            if ($politico->foto_path) {
                Storage::disk('public')->delete($politico->foto_path);
            }
            $validated['foto_path'] = $request->file('foto')->store('politicos', 'public');
        }

        $politico->update($validated);

        return Redirect::route('admin.politicos.index')->with('success', 'Político atualizado.');
    }

    public function destroy(Politico $politico)
    {
        // A autorização e as regras de negócio de exclusão agora são tratadas pela PoliticoPolicy.
        if ($politico->foto_path) {
            Storage::disk('public')->delete($politico->foto_path);
        }
        $politico->delete();

        return Redirect::route('admin.politicos.index')->with('success', 'Político excluído.');
    }
}
