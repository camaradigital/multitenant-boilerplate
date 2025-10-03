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
use Illuminate\Validation\Rule; // Importação necessária para tipagem de Closure

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
            $query->where('atendente_id', $user->id);
        }

        return $query;
    }

    // --- NOVO: Mapeamento de Status para Ícones ---
    protected function getStatusIcon(string $statusNome): string
    {
        // Você pode ajustar a lógica aqui para mapear seus status específicos
        return match ($statusNome) {
            'Pendente', 'Em Análise' => 'Hourglass',
            'Finalizado', 'Resolvido' => 'CheckCircle',
            'Aguardando Documentos' => 'FileText',
            'Cancelado', 'Sem Solução' => 'XCircle',
            default => 'Loader', // Ícone padrão
        };
    }
    // --- FIM NOVO ---

    /**
     * Exibe a fila de solicitações.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', SolicitacaoServico::class);

        $user = Auth::user();

        // --- NOVO: Cálculo de Estatísticas ---
        $statsQueryBase = $this->buildBaseQuery($request);
        $totalCount = $statsQueryBase->count();

        $statusStats = SolicitacaoServico::select('status_id', DB::raw('count(*) as contagem'))
            ->whereIn('id', $statsQueryBase->select('id')) // Aplica os mesmos filtros de permissão/query base
            ->groupBy('status_id')
            ->get();

        $estatisticas = [
            [
                'nome' => 'Total',
                'contagem' => $totalCount,
                'cor' => '#0e7490', // Ciano escuro
                'icone' => 'ListChecks',
            ],
        ];

        foreach ($statusStats as $stat) {
            $status = StatusSolicitacao::find($stat->status_id);
            if ($status) {
                $estatisticas[] = [
                    'nome' => $status->nome,
                    'contagem' => (int) $stat->contagem,
                    'cor' => $status->cor, // Assumindo que a cor está na Model StatusSolicitacao
                    'icone' => $this->getStatusIcon($status->nome),
                ];
            }
        }
        // --- FIM NOVO ---

        // Configura a query para a listagem principal
        $query = $this->buildBaseQuery($request)->with(['cidadao:id,name', 'servico:id,nome,tipo_servico_id', 'status', 'atendente:id,name']);

        $solicitacoes = $query->latest()->paginate(15)->withQueryString();

        $solicitacoes->getCollection()->transform(function ($solicitacao) use ($user) {
            $solicitacao->can = [
                'view' => $user->can('view', $solicitacao),
                'delete' => $user->can('delete', $solicitacao),
                'update' => $user->can('update', $solicitacao),
            ];

            return $solicitacao;
        });

        return inertia('Tenant/Solicitacoes/Index', [
            'solicitacoes' => $solicitacoes,
            'categorias' => TipoServico::orderBy('nome')->get(),
            'statuses' => StatusSolicitacao::orderBy('nome')->get(),
            'filters' => $request->only(['categoria', 'search', 'status']),
            'estatisticas' => $estatisticas, // NOVO: Passando as estatísticas para a view
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
            // Carregamos a relação 'cidadao' para ter o objeto do utilizador a quem notificar.
            $solicitacao->load('cidadao');
            // Passamos a solicitação e o NOME do novo status diretamente.
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
}
