<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Servico;
use App\Models\Tenant\TipoServico;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServicoController extends Controller
{
    public function index()
    {
        return Inertia::render('Tenant/Servicos/Index', [
            'servicos' => Servico::with('tipoServico')->latest()->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Tenant/Servicos/Create', [
            'tiposServico' => TipoServico::where('is_active', true)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'tipo_servico_id' => 'required|exists:tipos_servico,id',
            'regras_limite' => 'nullable|array',
            'regras_limite.limite' => 'nullable|required_with:regras_limite|integer|min:1',
            'regras_limite.periodo' => 'nullable|required_with:regras_limite|string|in:diario,semanal,mensal,anual',
        ]);

        Servico::create($validated);

        return redirect()->route('servicos.index')->with('success', 'Serviço criado com sucesso.');
    }

    public function edit(Servico $servico)
    {
        return Inertia::render('Tenant/Servicos/Edit', [
            'servico' => $servico,
            'tiposServico' => TipoServico::where('is_active', true)->get(),
        ]);
    }

    public function update(Request $request, Servico $servico)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'tipo_servico_id' => 'required|exists:tipos_servico,id',
            'regras_limite' => 'nullable|array',
            'regras_limite.limite' => 'nullable|required_with:regras_limite|integer|min:1',
            'regras_limite.periodo' => 'nullable|required_with:regras_limite|string|in:diario,semanal,mensal,anual',
        ]);

        $servico->update($validated);

        return redirect()->route('servicos.index')->with('success', 'Serviço atualizado com sucesso.');
    }

    public function destroy(Servico $servico)
    {
        $servico->delete();
        return redirect()->route('servicos.index')->with('success', 'Serviço excluído com sucesso.');
    }
}
