<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Entidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class EntidadeController extends Controller
{
    /**
     * Aplica a EntidadePolicy a todos os métodos do resource controller.
     */
    public function __construct()
    {
        $this->authorizeResource(Entidade::class, 'entidade');
    }

    public function index()
    {
        return inertia('Tenant/Entidades/Index', [
            'entidades' => Entidade::latest()->paginate(15),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'categoria' => 'required|string|max:100',
            'cnpj' => 'nullable|string|max:18|unique:tenant.entidades,cnpj',
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'endereco' => 'nullable|string',
            'descricao' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        Entidade::create(array_merge($validated, [
            'registrado_por_user_id' => Auth::id(),
        ]));

        return Redirect::route('admin.entidades.index')->with('success', 'Entidade registrada com sucesso.');
    }

    public function update(Request $request, Entidade $entidade)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'categoria' => 'required|string|max:100',
            'cnpj' => 'nullable|string|max:18|unique:tenant.entidades,cnpj,'.$entidade->id,
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'endereco' => 'nullable|string',
            'descricao' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $entidade->update($validated);

        return Redirect::route('admin.entidades.index')->with('success', 'Entidade atualizada com sucesso.');
    }

    public function destroy(Entidade $entidade)
    {
        // A autorização e as regras de negócio de exclusão agora são tratadas pela EntidadePolicy.
        $entidade->delete();

        return Redirect::route('admin.entidades.index')->with('success', 'Entidade excluída com sucesso.');
    }
}
