<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\StatusSolicitacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect; // Importar a classe Redirect
use Illuminate\Validation\Rule;

class StatusSolicitacaoController extends Controller
{
    /**
     * Exibe a lista de status, retornando uma resposta Inertia.
     */
    public function index()
    {
        // Renderiza o componente Vue e passa os status paginados como prop.
        return inertia('Tenant/StatusSolicitacao/Index', [
            'status' => StatusSolicitacao::latest()->paginate(10),
        ]);
    }

    /**
     * Salva um novo status no banco de dados.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255|unique:tenant.status_solicitacao,nome',
            'cor' => 'required|string|size:7', // Formato #RRGGBB
            'is_default_abertura' => 'nullable|boolean',
            'is_final' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($request) {
            // Se o novo status for o padrão, desmarca todos os outros.
            if ($request->boolean('is_default_abertura')) {
                StatusSolicitacao::query()->update(['is_default_abertura' => false]);
            }

            StatusSolicitacao::create($request->all());
        });

        // Redireciona para a rota correta usando o nome definido no seu arquivo de rotas.
        return Redirect::route('admin.status-solicitacao.index')
            ->with('success', 'Status criado com sucesso.');
    }

    /**
     * Atualiza um status no banco de dados.
     */
    public function update(Request $request, StatusSolicitacao $statusSolicitacao)
    {
        $request->validate([
            'nome' => ['required', 'string', 'max:255', Rule::unique('tenant.status_solicitacao')->ignore($statusSolicitacao->id)],
            'cor' => 'required|string|size:7',
            'is_default_abertura' => 'nullable|boolean',
            'is_final' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($request, $statusSolicitacao) {
            // Se este status for o padrão, desmarca todos os outros.
            if ($request->boolean('is_default_abertura')) {
                StatusSolicitacao::where('id', '!=', $statusSolicitacao->id)
                    ->update(['is_default_abertura' => false]);
            }

            $statusSolicitacao->update($request->all());
        });

        return Redirect::route('admin.status-solicitacao.index')
            ->with('success', 'Status atualizado com sucesso.');
    }

    /**
     * Remove um status do banco de dados.
     */
    public function destroy(StatusSolicitacao $statusSolicitacao)
    {
        // Regra de segurança: Não permitir exclusão do status padrão de abertura.
        if ($statusSolicitacao->is_default_abertura) {
            return Redirect::back()->with('error', 'Não é possível excluir o status padrão de abertura.');
        }

        // Regra de segurança: Não permitir exclusão se o status estiver em uso.
        if ($statusSolicitacao->solicitacoesServico()->exists()) {
            return Redirect::back()->with('error', 'Não é possível excluir o status, pois ele já está sendo utilizado em solicitações.');
        }

        $statusSolicitacao->delete();

        return Redirect::route('admin.status-solicitacao.index')
            ->with('success', 'Status excluído com sucesso.');
    }
}
