<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\StatusSolicitacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

class StatusSolicitacaoController extends Controller
{
    /**
     * Aplica a StatusSolicitacaoPolicy aos métodos do resource controller.
     */
    public function __construct()
    {
        $this->authorizeResource(StatusSolicitacao::class, 'statusSolicitacao');
    }

    /**
     * Exibe a lista de status.
     */
    public function index()
    {
        return inertia('Tenant/StatusSolicitacao/Index', [
            'status' => StatusSolicitacao::latest()->paginate(10),
        ]);
    }

    /**
     * Salva um novo status.
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
            if ($request->boolean('is_default_abertura')) {
                StatusSolicitacao::query()->update(['is_default_abertura' => false]);
            }
            StatusSolicitacao::create($request->all());
        });

        return Redirect::route('admin.status-solicitacao.index')
            ->with('success', 'Status criado com sucesso.');
    }

    /**
     * Atualiza um status.
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
     * Remove um status.
     */
    public function destroy(StatusSolicitacao $statusSolicitacao)
    {
        // A autorização e a lógica de negócio agora são tratadas pela policy.
        $statusSolicitacao->delete();

        return Redirect::route('admin.status-solicitacao.index')
            ->with('success', 'Status excluído com sucesso.');
    }
}
