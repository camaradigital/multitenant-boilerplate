<?php

// CONTROLLER DO CIDADÃO PARA PAINEL PESSOAL DO CIDADÃO

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Servico;
use App\Models\Tenant\SolicitacaoServico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MeuPainelController extends Controller
{
    /**
     * Exibe o painel pessoal do cidadão com suas solicitações.
     *
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view-cidadao-dashboard');

        $user = Auth::user();
        $query = SolicitacaoServico::where('user_id', $user->id);

        // Lógica de Filtros (sem alterações)
        if ($request->filled('busca')) {
            $busca = $request->input('busca');
            $query->where(function ($q) use ($busca) {
                $q->where('id', 'like', "%{$busca}%")
                    ->orWhereHas('servico', function ($q_servico) use ($busca) {
                        $q_servico->where('nome', 'like', "%{$busca}%");
                    });
            });
        }
        if ($request->filled('status') && $request->input('status') !== 'todos') {
            $isFinal = $request->input('status') === 'finalizados';
            $query->whereHas('status', function ($q_status) use ($isFinal) {
                $q_status->where('is_final', $isFinal);
            });
        }

        // Buscamos as solicitações paginadas, carregando os relacionamentos necessários.
        $solicitacoesPaginadas = $query->with([
            'servico',
            'status',
            'pesquisa_satisfacao',
            'historicos' => function ($query) {
                $query->with('causer:id,name')->latest();
            },
        ])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Renomeia a propriedade 'historicos' para 'activity' para compatibilidade com o frontend.
        $solicitacoesPaginadas->getCollection()->transform(function ($solicitacao) {
            if ($solicitacao->relationLoaded('historicos')) {
                $solicitacao->activity = $solicitacao->historicos;
                unset($solicitacao->historicos);
            }

            return $solicitacao;
        });

        // 2. CORREÇÃO: Faltava retornar a view do Inertia com os dados.
        return Inertia::render('Tenant/PortalPessoal/Index', [
            'solicitacoes' => $solicitacoesPaginadas,
            'filtros' => $request->only('busca', 'status'),
        ]);
    }

    /**
     * Exibe a página para o cidadão criar uma nova solicitação de serviço.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        // 1. AUTORIZAÇÃO: Verifica se o usuário pode criar uma solicitação.
        $this->authorize('create', SolicitacaoServico::class);

        // Busca apenas os serviços que estão ATIVOS e permitem solicitação ONLINE.
        $servicosDisponiveis = Servico::where('is_active', true)
            ->where('permite_solicitacao_online', true)
            ->with('tipoServico:id,nome')
            ->get(['id', 'nome', 'descricao', 'tipo_servico_id']);

        return Inertia::render('Tenant/PortalPessoal/Create', [
            'servicos' => $servicosDisponiveis,
        ]);
    }

    /**
     * Permite que um usuário avalie uma solicitação de serviço.
     */
    public function avaliar(Request $request, SolicitacaoServico $solicitacao)
    {
        // 1. AUTORIZAÇÃO: Usa o método 'avaliar' da Policy para verificar a permissão.
        $this->authorize('avaliar', $solicitacao);

        $request->validate([
            'nota' => 'required|integer|min:1|max:5',
            'comentario' => 'nullable|string|max:1000',
        ]);

        // 2. REMOVIDO: A verificação de propriedade foi movida para a Policy.
        // if ($solicitacao->user_id !== auth()->id()) {
        //     abort(403, 'Acesso não autorizado.');
        // }

        // Validações de regra de negócio permanecem no controller.
        if (! $solicitacao->status->is_final) {
            return redirect()->back()->withErrors(['geral' => 'Você só pode avaliar solicitações que já foram finalizadas.']);
        }
        if ($solicitacao->pesquisa_satisfacao()->exists()) {
            return redirect()->back()->withErrors(['geral' => 'Esta solicitação já foi avaliada.']);
        }

        $solicitacao->pesquisa_satisfacao()->create([
            'user_id' => auth()->id(),
            'nota' => $request->input('nota'),
            'comentario' => $request->input('comentario'),
        ]);

        return redirect()->back()->with('success', 'Avaliação enviada com sucesso!');
    }
}
