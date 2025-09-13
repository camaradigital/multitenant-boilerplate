<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Servico;
use App\Models\Tenant\TipoServico;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class ServicoController extends Controller
{

    use AuthorizesRequests; // Trait de autorização utilizado na classe
    /**
     * Exibe uma lista dos recursos com filtros de busca.
     */
    public function index(Request $request)
    {
        $this->authorize('gerenciar servicos');

        $query = Servico::with('tipoServico');

        if ($request->filled('busca')) {
            $busca = $request->input('busca');
            $query->where('nome', 'like', "%{$busca}%");
        }

        $servicos = $query->latest()->paginate(10)->withQueryString();
        $tiposServico = TipoServico::where('is_active', true)->get(['id', 'nome']);

        return Inertia::render('Tenant/Servicos/Index', [
            'servicos' => $servicos,
            'tiposServico' => $tiposServico,
            'filtros' => $request->only('busca'),
        ]);
    }

    /**
     * Armazena um novo serviço, incluindo as regras de limite.
     */
    public function store(Request $request)
    {
        $this->authorize('gerenciar servicos');

        $validatedData = $request->validate([
            'nome' => 'required|string|max:255',
            'tipo_servico_id' => 'required|exists:tenant.tipos_servico,id',
            'descricao' => 'nullable|string',
            'is_active' => 'required|boolean',
            'permite_solicitacao_online' => 'required|boolean',
            'regras_limite.ativo' => 'sometimes|boolean',
            'regras_limite.quantidade' => 'nullable|required_if:regras_limite.ativo,true|integer|min:1',
            'regras_limite.periodo' => 'nullable|required_if:regras_limite.ativo,true|in:dia,semana,mes,ano',
        ]);

        // Se a opção 'ativo' das regras de limite não estiver marcada ou não existir, anula o campo.
        if (!($validatedData['regras_limite']['ativo'] ?? false)) {
            $validatedData['regras_limite'] = null;
        }

        Servico::create($validatedData);

        return Redirect::route('admin.servicos.index')->with('success', 'Serviço criado com sucesso.');
    }

    /**
     * Exibe um recurso específico (usado para APIs ou modais).
     */
    public function show(Servico $servico)
    {
        $this->authorize('gerenciar servicos');
        $servico->load('tipoServico');
        return response()->json($servico);
    }

    /**
     * Atualiza um serviço existente.
     */
    public function update(Request $request, Servico $servico)
    {
        $this->authorize('gerenciar servicos');

        $validatedData = $request->validate([
            'nome' => 'required|string|max:255',
            'tipo_servico_id' => 'required|exists:tenant.tipos_servico,id',
            'descricao' => 'nullable|string',
            'is_active' => 'required|boolean',
            'permite_solicitacao_online' => 'required|boolean',
            'regras_limite.ativo' => 'sometimes|boolean',
            'regras_limite.quantidade' => 'nullable|required_if:regras_limite.ativo,true|integer|min:1',
            'regras_limite.periodo' => 'nullable|required_if:regras_limite.ativo,true|in:dia,semana,mes,ano',
        ]);

        // Lógica para anular ou manter as regras de limite.
        if (!($validatedData['regras_limite']['ativo'] ?? false)) {
            $validatedData['regras_limite'] = null;
        } else {
            // Garante que 'ativo' seja sempre um booleano no banco.
            $validatedData['regras_limite']['ativo'] = true;
        }

        $servico->update($validatedData);

        return Redirect::back()->with('success', 'Serviço atualizado com sucesso.');
    }

    /**
     * Remove um serviço, com verificação de segurança.
     */
    public function destroy(Servico $servico)
    {
        $this->authorize('gerenciar servicos');

        // Verifica se o serviço já foi utilizado em alguma solicitação.
        if ($servico->solicitacoes()->exists()) {
            return Redirect::back()->with('error', 'Não é possível excluir este serviço, pois ele já possui solicitações vinculadas. Considere desativá-lo no menu de edição.');
        }

        $servico->delete();

        return Redirect::route('admin.servicos.index')->with('success', 'Serviço excluído com sucesso.');
    }
}

