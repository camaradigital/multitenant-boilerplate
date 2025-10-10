<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Comissao;
use App\Models\Tenant\Legislatura;
use App\Models\Tenant\Politico;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ComissaoController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Comissao::class, 'comissao');
    }

    public function index()
    {
        $legislaturaAtual = Legislatura::atual()->first();
        $comissoes = $legislaturaAtual ? $legislaturaAtual->comissoes()->with('membros.politico')->get() : collect();

        return Inertia::render('Tenant/Comissoes/Index', [
            'legislaturaAtual' => $legislaturaAtual,
            'comissoes' => $comissoes,
            'politicos' => Politico::orderBy('nome_politico')->get(),
        ]);
    }

    public function store(Request $request)
    {
        // ***** CORREÇÃO APLICADA AQUI *****
        $request->validate([
            'legislatura_id' => [
                'required',
                // Força a validação a usar a conexão 'tenant' e a tabela 'legislaturas'
                Rule::exists('tenant.legislaturas', 'id'),
            ],
            'nome' => 'required|string|max:255',
            'tipo' => 'required|in:Permanente,Temporária,Especial',
            'descricao' => 'nullable|string',
        ]);

        Comissao::create($request->all());

        return redirect()->route('admin.comissoes.index')->with('success', 'Comissão criada com sucesso.');
    }

    public function update(Request $request, Comissao $comissao)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'tipo' => 'required|in:Permanente,Temporária,Especial',
            'descricao' => 'nullable|string',
        ]);

        $comissao->update($request->all());

        return redirect()->route('admin.comissoes.index')->with('success', 'Comissão atualizada com sucesso.');
    }

    public function destroy(Comissao $comissao)
    {
        $comissao->delete();

        return redirect()->route('admin.comissoes.index')->with('success', 'Comissão removida com sucesso.');
    }

    public function adicionarMembro(Request $request, Comissao $comissao)
    {

        $this->authorize('update', $comissao);

        $request->validate([
            'politico_id' => [
                'required',
                Rule::exists('tenant.politicos', 'id'),
            ],
            'cargo' => 'required|in:Presidente,Vice-Presidente,Relator,Membro',
        ]);

        $comissao->membros()->create($request->all());

        return back()->with('success', 'Membro adicionado com sucesso.');
    }

    public function removerMembro(Comissao $comissao, $membroId)
    {
        $this->authorize('update', $comissao);

        $comissao->membros()->where('id', $membroId)->delete();

        return back()->with('success', 'Membro removido com sucesso.');
    }
}
