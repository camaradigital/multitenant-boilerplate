<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TipoServico;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TipoServicoController extends Controller
{
    public function index()
    {
        return Inertia::render('Tenant/TiposServico/Index', [
            'tiposServico' => TipoServico::latest()->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Tenant/TiposServico/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
        ]);
        TipoServico::create($validated);
        return redirect()->route('tipos-servico.index')->with('success', 'Tipo de Serviço criado.');
    }

    public function edit(TipoServico $tiposServico)
    {
        return Inertia::render('Tenant/TiposServico/Edit', [
            'tipoServico' => $tiposServico,
        ]);
    }

    public function update(Request $request, TipoServico $tiposServico)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
        ]);
        $tiposServico->update($validated);
        return redirect()->route('tipos-servico.index')->with('success', 'Tipo de Serviço atualizado.');
    }

    public function destroy(TipoServico $tiposServico)
    {
        // Adicionar lógica para verificar se há serviços usando este tipo antes de excluir
        $tiposServico->delete();
        return redirect()->route('tipos-servico.index')->with('success', 'Tipo de Serviço excluído.');
    }
}
