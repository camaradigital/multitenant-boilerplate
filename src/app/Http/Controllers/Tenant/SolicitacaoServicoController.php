<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreSolicitacaoServicoRequest;
use App\Models\Central\Tenant;
use App\Models\Tenant\Servico;
use App\Models\Tenant\SolicitacaoServico;
use App\Models\Tenant\StatusSolicitacao;
use App\Models\Tenant\TipoServico;
use App\Models\Tenant\User;
use App\Notifications\Tenant\SolicitacaoStatusAlterado;
use App\Services\Tenant\SolicitacaoService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

class SolicitacaoServicoController extends Controller
{
    use AuthorizesRequests;

    protected $solicitacaoService;

    public function __construct(SolicitacaoService $solicitacaoService)
    {
        $this->solicitacaoService = $solicitacaoService;
    }

    /**
     * Constrói a query base com base nos filtros e permissões.
     */
    protected function buildBaseQuery(Request $request): Builder
    {
        $user = Auth::user();
        $query = SolicitacaoServico::query();

        // 1. Filtro por Categoria (TipoServico)
        if ($request->filled('categoria') && $request->input('categoria') !== 'Todos') {
            $query->whereHas('servico.categoria', function ($q) use ($request) {
                $q->where('nome', $request->input('categoria'));
            });
        }

        // 2. Filtro de busca por nome do cidadão (e outros campos se necessário)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('cidadao', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                })
                    ->orWhereHas('servico', function ($q) use ($search) {
                        $q->where('nome', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('atendente', function ($q) use ($search) {
                        $q->where('name', 'like', '%'.$search.'%');
                    });
            });
        }

        // 3. Filtro por status
        if ($request->filled('status')) {
            $query->whereHas('status', function ($q) use ($request) {
                $q->where('nome', $request->input('status'));
            });
        }

        // 4. Filtros de Permissão
        if ($user->can('supervisionar solicitacoes juridicas')) {
            $query->whereHas('servico', function ($q) {
                $q->where('is_juridico', true);
            });
        } elseif ($user->hasAnyRole(['Funcionario', 'Advogado Coordenador']) && ! $user->hasRole('Admin Tenant')) {
            // Esta condição original foi removida da query base para a lista principal,
            // pois agora a lógica de "atendimento atual" cuida disso de forma mais explícita.
            // Se precisar forçar a visualização apenas dos próprios atendimentos em outras
            // circunstâncias, a lógica pode ser reintroduzida aqui ou ajustada.
        }

        return $query;
    }

    protected function getStatusIcon(string $statusNome): string
    {
        // Você pode ajustar a lógica aqui para mapear seus status específicos
        return match ($statusNome) {
            'Andamento' => 'Hourglass',
            'Concluído' => 'CheckCircle',
            'Cancelado' => 'XCircle',
            'Aguardando' => 'FileText',
            default => 'ListChecks',
        };
    }

    /**
     * Exibe a fila de solicitações.
     * MÉTODO TOTALMENTE ATUALIZADO PARA LÓGICA DE FILA
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', SolicitacaoServico::class);

        $user = Auth::user();
        $categoriaNome = $request->query('categoria');

        // --- LÓGICA DE FILA ---

        // Buscando os status-chave. Adicione `firstOrFail()` para garantir que eles existam.
        $statusEmAndamento = StatusSolicitacao::where('nome', 'Andamento')->firstOrFail();
        $statusInicial = StatusSolicitacao::where('is_default_abertura', true)->firstOrFail();

        // 1. VERIFICAR SE O USUÁRIO JÁ TEM UM ATENDIMENTO ATIVO
        $atendimentoAtualQuery = SolicitacaoServico::where('atendente_id', $user->id)
            ->where('status_id', $statusEmAndamento->id);

        if ($categoriaNome && $categoriaNome !== 'Todos') {
            $atendimentoAtualQuery->whereHas('servico.categoria', fn ($q) => $q->where('nome', $categoriaNome));
        }

        $atendimentoAtual = $atendimentoAtualQuery->with([
            'cidadao:id,name', 'servico:id,nome', 'status',
        ])->first();

        // 2. ENCONTRAR O PRÓXIMO DA FILA (APENAS SE O USUÁRIO NÃO TIVER UM ATENDIMENTO ATIVO E UMA CATEGORIA ESTIVER SELECIONADA)
        $proximaSolicitacao = null;
        if (! $atendimentoAtual && $categoriaNome && $categoriaNome !== 'Todos') {
            $proximaSolicitacao = SolicitacaoServico::whereNull('atendente_id')
                ->where('status_id', $statusInicial->id)
                ->whereHas('servico.categoria', fn ($q) => $q->where('nome', $categoriaNome))
                ->orderBy('created_at', 'asc') // LÓGICA FIFO: O mais antigo primeiro
                ->with(['cidadao:id,name', 'servico:id,nome', 'status'])
                ->first();
        }

        // --- LÓGICA DE LISTAGEM E ESTATÍSTICAS (MANTIDA E ADAPTADA) ---

        // 3. CÁLCULO DE ESTATÍSTICAS
        $statsQueryBase = $this->buildBaseQuery(new Request($request->except(['categoria']))); // Estatísticas não devem ser filtradas por categoria
        $totalCount = (clone $statsQueryBase)->count();
        $statusStats = (clone $statsQueryBase)
            ->select('status_id', DB::raw('count(*) as contagem'))
            ->groupBy('status_id')
            ->get();

        $estatisticas = [['nome' => 'Total', 'contagem' => $totalCount, 'cor' => '#0e7490', 'icone' => 'ListChecks']];
        foreach ($statusStats as $stat) {
            $status = StatusSolicitacao::find($stat->status_id);
            if ($status) {
                $estatisticas[] = [
                    'nome' => $status->nome,
                    'contagem' => (int) $stat->contagem,
                    'cor' => $status->cor,
                    'icone' => $this->getStatusIcon($status->nome),
                ];
            }
        }

        // 4. MONTAR A QUERY PARA O RESTANTE DA FILA
        $queryFilaRestante = $this->buildBaseQuery($request)
            ->with(['cidadao:id,name', 'servico:id,nome,tipo_servico_id', 'status', 'atendente:id,name']);

        // Excluir os itens que já estão sendo exibidos nos cards principais
        if ($atendimentoAtual) {
            $queryFilaRestante->where('id', '!=', $atendimentoAtual->id);
        }
        if ($proximaSolicitacao) {
            $queryFilaRestante->where('id', '!=', $proximaSolicitacao->id);
        }

        // Ordena a fila restante por ordem de chegada
        $filaRestante = $queryFilaRestante->orderBy('created_at', 'asc')->paginate(10)->withQueryString();

        $filaRestante->getCollection()->transform(function ($solicitacao) use ($user) {
            $solicitacao->can = [
                'view' => $user->can('view', $solicitacao),
                'delete' => $user->can('delete', $solicitacao),
                'update' => $user->can('update', $solicitacao),
            ];

            return $solicitacao;
        });

        // 5. RENDERIZA A VIEW COM OS NOVOS PROPS
        return inertia('Tenant/Solicitacoes/Index', [
            'atendimentoAtual' => $atendimentoAtual,
            'proximaSolicitacao' => $proximaSolicitacao,
            'filaRestante' => $filaRestante, // Prop renomeado para maior clareza
            'categorias' => TipoServico::orderBy('nome')->get(),
            'statuses' => StatusSolicitacao::orderBy('nome')->get(),
            'filters' => $request->only(['categoria', 'search', 'status']),
            'estatisticas' => $estatisticas,
        ]);
    }

    /**
     * Exibe o formulário para criar uma nova solicitação.
     */
    public function create()
    {
        $this->authorize('create', SolicitacaoServico::class);

        return inertia('Tenant/Solicitacoes/Create', [
            'cidadaos' => User::role('Cidadao')->where('is_active', true)->get(['id', 'name', 'cpf', 'profile_data']),
            'servicos' => Servico::where('is_active', true)->get(),
            'exigirRendaJuridico' => Tenant::current()->exigir_renda_juridico,
        ]);
    }

    /**
     * Salva a nova solicitação.
     */
    public function store(StoreSolicitacaoServicoRequest $request)
    {
        $user = Auth::user();
        $validatedData = $request->validated();

        if ($user->hasRole('Cidadao')) {
            $cidadao = $user;
        } else {
            $cidadao = User::findOrFail($validatedData['user_id']);
        }

        $servico = Servico::findOrFail($validatedData['servico_id']);

        if ($user->hasRole('Cidadao') && ! $servico->permite_solicitacao_online) {
            return Redirect::back()->withErrors(['servico_id' => 'Este serviço está disponível apenas para solicitação presencial.']);
        }

        if ($servico->is_juridico && isset($validatedData['renda_familiar'])) {
            $profileData = $cidadao->profile_data ?? [];
            $profileData['renda_familiar'] = $validatedData['renda_familiar'];
            $cidadao->profile_data = $profileData;
            $cidadao->save();
            $cidadao->refresh();
        }

        $verificacaoJuridica = $this->solicitacaoService->verificarAcessoJuridico($servico, $cidadao);
        if (! $verificacaoJuridica['pode_solicitar']) {
            return Redirect::back()->withErrors(['servico_id' => $verificacaoJuridica['mensagem']]);
        }

        $verificacaoLimite = $this->solicitacaoService->verificarLimiteDeUso($servico, $cidadao);
        if (! $verificacaoLimite['pode_solicitar']) {
            return Redirect::back()->withErrors(['servico_id' => $verificacaoLimite['mensagem']]);
        }

        $statusInicial = StatusSolicitacao::where('is_default_abertura', true)->first();
        if (! $statusInicial) {
            return Redirect::back()->withErrors(['servico_id' => 'Nenhum status inicial foi configurado no sistema.']);
        }

        DB::beginTransaction();
        try {
            $solicitacao = SolicitacaoServico::create([
                'user_id' => $cidadao->id,
                'servico_id' => $servico->id,
                'status_id' => $statusInicial->id,
                'atendente_id' => $user->hasRole('Cidadao') ? null : $user->id,
                'observacoes' => $validatedData['observacoes'] ?? null,
            ]);

            if ($request->hasFile('documentos')) {
                foreach ($request->file('documentos') as $arquivo) {
                    $caminho = $arquivo->store('solicitacoes/'.$solicitacao->id, 'tenant_private');
                    $solicitacao->documentos()->create([
                        'path' => $caminho,
                        'nome_original' => $arquivo->getClientOriginalName(),
                        'user_id' => $user->id,
                        'mime_type' => $arquivo->getMimeType(),
                        'tamanho' => $arquivo->getSize(),
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Falha ao criar solicitação: '.$e->getMessage());

            return Redirect::back()->withErrors(['servico_id' => 'Ocorreu um erro inesperado ao salvar sua solicitação. Por favor, tente novamente.']);
        }

        if ($user->hasRole('Cidadao')) {
            return Redirect::route('portal.meu-painel')->with('success', 'Solicitação registrada com sucesso!');
        }

        return Redirect::route('admin.solicitacoes.index')->with('success', 'Solicitação registrada com sucesso!');
    }

    /**
     * Exibe os detalhes de uma solicitação.
     */
    public function show(SolicitacaoServico $solicitacao)
    {
        $this->authorize('view', $solicitacao);

        $solicitacao->load(['cidadao', 'servico', 'status', 'atendente', 'documentos.uploader', 'pesquisa_satisfacao']);

        return inertia('Tenant/Solicitacoes/Show', [
            'solicitacao' => $solicitacao,
            'statusDisponiveis' => StatusSolicitacao::all(),
            'atendentesDisponiveis' => User::role(['Admin Tenant', 'Funcionario', 'Advogado Coordenador'])->get(['id', 'name']),
        ]);
    }

    /**
     * Atualiza uma solicitação.
     */
    public function update(Request $request, SolicitacaoServico $solicitacao)
    {
        $this->authorize('update', $solicitacao);

        $validatedData = $request->validate([
            'status_id' => ['required', Rule::exists('tenant.status_solicitacao', 'id')],
            'atendente_id' => ['nullable', Rule::exists('tenant.users', 'id')],
            'observacoes' => 'nullable|string',
        ]);

        $statusAntigoId = $solicitacao->status_id;

        $dadosParaAtualizar = $validatedData;
        $novoStatus = StatusSolicitacao::find($validatedData['status_id']);

        if ($request->filled('observacoes')) {
            $novaObservacao = "\n\n--- ".Auth::user()->name.' em '.now()->format('d/m/Y H:i')." ---\n".$request->observacoes;
            $dadosParaAtualizar['observacoes'] = $solicitacao->observacoes.$novaObservacao;
        }

        if ($novoStatus->is_final && is_null($solicitacao->finalizado_em)) {
            $dadosParaAtualizar['finalizado_em'] = now();
        } elseif (! $novoStatus->is_final) {
            $dadosParaAtualizar['finalizado_em'] = null;
        }

        $solicitacao->update($dadosParaAtualizar);

        if ($statusAntigoId != $validatedData['status_id']) {
            $solicitacao->load('cidadao');
            $solicitacao->cidadao->notify(new SolicitacaoStatusAlterado($solicitacao, $novoStatus->nome));
        }

        return Redirect::route('admin.solicitacoes.show', $solicitacao->id)->with('success', 'Solicitação atualizada com sucesso.');
    }

    /**
     * Exclui uma solicitação.
     */
    public function destroy(SolicitacaoServico $solicitacao)
    {
        $this->authorize('delete', $solicitacao);

        $solicitacao->delete();

        return Redirect::route('admin.solicitacoes.index')->with('success', 'Solicitação excluída.');
    }

    /**
     * NOVO MÉTODO: Atribui a solicitação ao atendente logado.
     * Lembre-se de adicionar a rota para este método.
     * Ex: Route::post('solicitacoes/{solicitacao}/atender', [SolicitacaoServicoController::class, 'atender'])->name('solicitacoes.atender');
     */
    public function atender(Request $request, SolicitacaoServico $solicitacao)
    {
        $this->authorize('update', $solicitacao);

        $statusEmAndamento = StatusSolicitacao::where('nome', 'Andamento')->firstOrFail();
        $user = Auth::user();

        // TRANSAÇÃO PARA EVITAR RACE CONDITION (dois atendentes pegando ao mesmo tempo)
        try {
            DB::transaction(function () use ($solicitacao, $statusEmAndamento, $user) {
                // Bloqueia a linha no banco para garantir que ninguém mais a altere
                $solicitacaoParaAtender = SolicitacaoServico::where('id', $solicitacao->id)->lockForUpdate()->first();

                // Verifica se outro atendente não pegou a solicitação no último segundo
                if ($solicitacaoParaAtender->atendente_id !== null) {
                    throw new \Exception('Esta solicitação já foi atribuída a outro atendente.');
                }

                // Verifica se o atendente já não possui outra em andamento na mesma categoria
                $categoriaId = $solicitacaoParaAtender->servico->tipo_servico_id;
                $atendimentoExistente = SolicitacaoServico::where('atendente_id', $user->id)
                    ->where('status_id', $statusEmAndamento->id)
                    ->whereHas('servico', fn ($q) => $q->where('tipo_servico_id', $categoriaId))
                    ->exists();

                if ($atendimentoExistente) {
                    throw new \Exception('Você já possui um atendimento em andamento nesta categoria.');
                }

                $solicitacaoParaAtender->update([
                    'atendente_id' => $user->id,
                    'status_id' => $statusEmAndamento->id,
                ]);
            });

        } catch (\Exception $e) {
            return back()->withErrors(['message' => $e->getMessage()]);
        }

        // Redireciona para a página de detalhes do atendimento
        return Redirect::route('admin.solicitacoes.show', $solicitacao->id)
            ->with('success', 'Atendimento iniciado com sucesso!');
    }
}
