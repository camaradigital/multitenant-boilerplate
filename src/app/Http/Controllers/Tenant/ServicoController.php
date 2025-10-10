<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpsertServicoRequest;
use App\Models\Tenant\Servico;
use App\Models\Tenant\TipoServico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class ServicoController extends Controller
{
    /**
     * Aplica a ServicoPolicy aos métodos do resource controller.
     */
    public function __construct()
    {
        $this->authorizeResource(Servico::class, 'servico');
    }

    /**
     * Exibe uma lista dos recursos com filtros de busca.
     */
    public function index(Request $request)
    {
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
     * Armazena um novo serviço.
     */
    public function store(UpsertServicoRequest $request)
    {
        // A autorização 'create' é aplicada automaticamente pelo __construct.
        // Os dados já chegam validados pelo Form Request.
        Servico::create($request->validated());

        return Redirect::route('admin.servicos.index')->with('success', 'Serviço criado com sucesso.');
    }

    /**
     * Exibe um recurso específico (usado para APIs ou modais).
     */
    public function show(Servico $servico)
    {
        // A autorização 'view' é aplicada automaticamente pelo __construct.
        $servico->load('tipoServico');

        return response()->json($servico);
    }

    /**
     * Atualiza um serviço existente.
     */
    public function update(UpsertServicoRequest $request, Servico $servico)
    {
        // A autorização 'update' é aplicada automaticamente pelo __construct.
        // Os dados já chegam validados pelo Form Request.
        $servico->update($request->validated());

        return Redirect::back()->with('success', 'Serviço atualizado com sucesso.');
    }

    /**
     * Remove um serviço, com verificação de segurança.
     */
    public function destroy(Servico $servico)
    {
        // A autorização 'delete' é aplicada automaticamente pelo __construct.

        // Verifica se o serviço já foi utilizado em alguma solicitação.
        if ($servico->solicitacoes()->exists()) {
            return Redirect::back()->with('error', 'Não é possível excluir este serviço, pois ele já possui solicitações vinculadas. Considere desativá-lo no menu de edição.');
        }

        $servico->delete();

        return Redirect::route('admin.servicos.index')->with('success', 'Serviço excluído com sucesso.');
    }
}
