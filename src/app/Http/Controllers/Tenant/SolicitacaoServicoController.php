<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreSolicitacaoServicoRequest;
use App\Models\Central\Tenant;
use App\Models\Tenant\Servico;
use App\Models\Tenant\TipoServico;
use App\Models\Tenant\SolicitacaoServico;
use App\Models\Tenant\StatusSolicitacao;
use App\Models\Tenant\User;
use App\Services\Tenant\SolicitacaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SolicitacaoServicoController extends Controller
{
    use AuthorizesRequests;

    protected $solicitacaoService;

    public function __construct(SolicitacaoService $solicitacaoService)
    {
        $this->solicitacaoService = $solicitacaoService;
    }

    /**
     * Exibe a fila de solicitações.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', SolicitacaoServico::class);

        $user = Auth::user();
        $query = SolicitacaoServico::with(['cidadao:id,name', 'servico:id,nome,tipo_servico_id', 'status', 'atendente:id,name']);

        if ($request->filled('categoria') && $request->input('categoria') !== 'Todos') {
            $query->whereHas('servico.categoria', function ($q) use ($request) {
                $q->where('nome', $request->input('categoria'));
            });
        }
        // Adicionado: Filtro de busca por nome do cidadão
        if ($request->filled('search')) {
            $query->whereHas('cidadao', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('search') . '%');
            });
        }
        // Adicionado: Filtro por status
        if ($request->filled('status')) {
            $query->whereHas('status', function ($q) use ($request) {
                $q->where('nome', $request->input('status'));
            });
        }
        // --- FIM DAS ALTERAÇÕES ---

        if ($user->can('supervisionar solicitacoes juridicas')) {
            $query->whereHas('servico', function ($q) {
                $q->where('is_juridico', true);
            });
        } elseif ($user->hasAnyRole(['Funcionario', 'Advogado Coordenador']) && !$user->hasRole('Admin Tenant')) {
            $query->where('atendente_id', $user->id);
        }

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

        if ($user->hasRole('Cidadao') && !$servico->permite_solicitacao_online) {
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
        if (!$verificacaoJuridica['pode_solicitar']) {
            return Redirect::back()->withErrors(['servico_id' => $verificacaoJuridica['mensagem']]);
        }

        $verificacaoLimite = $this->solicitacaoService->verificarLimiteDeUso($servico, $cidadao);
        if (!$verificacaoLimite['pode_solicitar']) {
            return Redirect::back()->withErrors(['servico_id' => $verificacaoLimite['mensagem']]);
        }

        $statusInicial = StatusSolicitacao::where('is_default_abertura', true)->first();
        if (!$statusInicial) {
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
                    $caminho = $arquivo->store('solicitacoes/' . $solicitacao->id, 'tenant_private');
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
            Log::error('Falha ao criar solicitação: ' . $e->getMessage());
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

        $dadosParaAtualizar = $validatedData;
        $novoStatus = StatusSolicitacao::find($validatedData['status_id']);

        if ($request->filled('observacoes')) {
            $novaObservacao = "\n\n--- " . Auth::user()->name . " em " . now()->format('d/m/Y H:i') . " ---\n" . $request->observacoes;
            $dadosParaAtualizar['observacoes'] = $solicitacao->observacoes . $novaObservacao;
        }

        if ($novoStatus->is_final && is_null($solicitacao->finalizado_em)) {
            $dadosParaAtualizar['finalizado_em'] = now();
        } elseif (!$novoStatus->is_final) {
            $dadosParaAtualizar['finalizado_em'] = null;
        }

        $solicitacao->update($dadosParaAtualizar);

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
