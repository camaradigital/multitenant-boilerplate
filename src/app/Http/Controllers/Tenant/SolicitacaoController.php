<?php
namespace App\Http\Controllers\Tenant;

use App\Events\Tenant\ServiceStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Servico;
use App\Models\Tenant\SolicitacaoServico;
use App\Services\Tenant\ServicoLimiterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SolicitacaoController extends Controller
{
    /**
     * Exibe a lista de solicitações.
     * Para Cidadãos, mostra apenas as suas.
     * Para Funcionários/Admins, mostra todas.
     */
    public function index()
    {
        $user = Auth::user();
        $query = SolicitacaoServico::with(['cidadao:id,name', 'servico:id,nome'])->latest();

        if ($user->hasRole('Cidadao')) {
            $query->where('user_id', $user->id);
        }

        return Inertia::render('Tenant/Solicitacoes/Index', [
            'solicitacoes' => $query->get(),
        ]);
    }

    /**
     * Exibe os detalhes de uma única solicitação.
     */
    public function show(SolicitacaoServico $solicitacao)
    {
        // Garante que o cidadão só possa ver suas próprias solicitações
        if (Auth::user()->hasRole('Cidadao') && $solicitacao->user_id !== Auth::id()) {
            abort(403);
        }

        return Inertia::render('Tenant/Solicitacoes/Show', [
            'solicitacao' => $solicitacao->load(['cidadao', 'servico', 'atendente']),
        ]);
    }

    /**
     * Atualiza o status de uma solicitação. (Ação do Funcionário)
     */
    public function update(Request $request, SolicitacaoServico $solicitacao)
    {
        $this->authorize('update', $solicitacao); // Precisaremos criar esta Policy depois

        $validated = $request->validate([
            'status' => 'required|string|in:Em Atendimento,Concluído,Cancelado',
            'observacoes' => 'nullable|string',
        ]);

        $solicitacao->update([
            'status' => $validated['status'],
            'observacoes' => $validated['observacoes'],
            'atendente_id' => Auth::id(),
            'concluido_em' => $validated['status'] === 'Concluído' ? now() : null,
        ]);

        // Dispara o evento para notificar o cidadão
        event(new ServiceStatusUpdated($solicitacao));

        return redirect()->route('solicitacoes.show', $solicitacao->id)->with('success', 'Status da solicitação atualizado!');
    }

    /**
     * Armazena uma nova solicitação feita por um cidadão.
     */
    public function store(Request $request, ServicoLimiterService $limiter)
    {
        $request->validate(['servico_id' => 'required|exists:servicos,id']);
        $servico = Servico::findOrFail($request->servico_id);
        $cidadao = Auth::user();

        $limiter->podeSolicitar($servico, $cidadao);

        DB::transaction(function () use ($servico, $cidadao) {
            $solicitacao = SolicitacaoServico::create([
                'user_id' => $cidadao->id,
                'servico_id' => $servico->id,
            ]);
            LogUsoServico::create([
                'user_id' => $cidadao->id,
                'servico_id' => $servico->id,
                'solicitacao_servico_id' => $solicitacao->id,
            ]);
        });

        return back()->with('success', 'Serviço solicitado com sucesso!');
    }
}
