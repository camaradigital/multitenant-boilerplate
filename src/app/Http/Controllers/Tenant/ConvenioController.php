<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Convenio;
use App\Models\Tenant\Entidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ConvenioController extends Controller
{
    public function index()
    {
        return inertia('Tenant/Convenios/Index', [
            'convenios' => Convenio::with('entidade')->latest()->paginate(15),
            'entidades' => Entidade::where('is_active', true)->get(['id', 'nome']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'entidade_id' => 'required|exists:tenant.entidades,id',
            'objeto' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'data_inicio' => 'required|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'status' => 'required|in:Vigente,Encerrado,Cancelado',
        ]);

        Convenio::create(array_merge($validated, [
            'registrado_por_user_id' => Auth::id(),
        ]));

        return Redirect::route('admin.convenios.index')->with('success', 'Convênio registrado com sucesso.');
    }

    public function update(Request $request, Convenio $convenio)
    {
        $validated = $request->validate([
            'entidade_id' => 'required|exists:tenant.entidades,id',
            'objeto' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'data_inicio' => 'required|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'status' => 'required|in:Vigente,Encerrado,Cancelado',
        ]);

        $convenio->update($validated);

        return Redirect::route('admin.convenios.index')->with('success', 'Convênio atualizado com sucesso.');
    }

    public function destroy(Convenio $convenio)
    {
        $convenio->delete();
        return Redirect::route('admin.convenios.index')->with('success', 'Convênio excluído com sucesso.');
    }
}
