<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreSolicitacaoServicoRequest;
use App\Models\Central\Tenant;
use App\Models\Tenant\Servico;
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
// É importante garantir que o trait AuthorizesRequests esteja sendo usado.
// Geralmente já está na classe Controller base, mas é bom confirmar.
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SolicitacaoServicoController extends Controller
{
    use AuthorizesRequests; // Adicionado para garantir que o método $this->authorize exista.

    protected $solicitacaoService;

    public function __construct(SolicitacaoService $solicitacaoService)
    {
        $this->solicitacaoService = $solicitacaoService;
    }

    /**
     * Exibe a fila de solicitações.
     */
    public function index()
    {
        // CORREÇÃO 1: Adicionada autorização.
        // Assumindo que apenas administradores/funcionários podem ver a lista completa.
        // Você pode criar um método 'viewAnyAdmin' na sua Policy ou usar uma verificação de permissão.
        // Por simplicidade, vou usar a Policy 'viewAny', mas o ideal seria ter uma regra específica para o painel administrativo.
        $this->authorize('viewAny', SolicitacaoServico::class);

        return inertia('Tenant/Solicitacoes/Index', [
            'solicitacoes' => SolicitacaoServico::with(['cidadao:id,name', 'servico:id,nome', 'status', 'atendente:id,name'])
                ->latest()
                ->paginate(15),
            'cidadaos' => User::role('Cidadao')->where('is_active', true)->get(['id', 'name', 'cpf', 'profile_data']),
            'servicos' => Servico::where('is_active', true)->get(),
            'statusDisponiveis' => StatusSolicitacao::all(),
            'exigirRendaJuridico' => Tenant::current()->exigir_renda_juridico,
        ]);
    }

    /**
     * Salva a nova solicitação.
     */
    public function store(StoreSolicitacaoServicoRequest $request)
    {
        // Nenhuma correção de autorização necessária aqui, pois a lógica de criação
        // é tratada pelo Form Request e pelo fluxo do método.
        $user = Auth::user();
        $validatedData = $request->validated();

        // Lógica para diferenciar a origem da solicitação
        if ($user->hasRole('Cidadao')) {
            $cidadao = $user;
        } else {
            $cidadao = User::findOrFail($validatedData['user_id']);
        }

        $servico = Servico::findOrFail($validatedData['servico_id']);

        // Se o solicitante é um cidadão, verifica se o serviço permite solicitação online.
        if ($user->hasRole('Cidadao') && !$servico->permite_solicitacao_online) {
            return Redirect::back()->withErrors(['servico' => 'Este serviço está disponível apenas para solicitação presencial.']);
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
            return Redirect::back()->withErrors(['servico' => $verificacaoJuridica['mensagem']]);
        }

        $verificacaoLimite = $this->solicitacaoService->verificarLimiteDeUso($servico, $cidadao);
        if (!$verificacaoLimite['pode_solicitar']) {
            return Redirect::back()->withErrors(['servico' => $verificacaoLimite['mensagem']]);
        }

        $statusInicial = StatusSolicitacao::where('is_default_abertura', true)->first();
        if (!$statusInicial) {
            return Redirect::back()->withErrors(['servico' => 'Nenhum status inicial foi configurado no sistema.']);
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
            return Redirect::back()->withErrors(['servico' => 'Ocorreu um erro inesperado ao salvar sua solicitação. Por favor, tente novamente.']);
        }

        if ($user->hasRole('Cidadao')) {
            return Redirect::route('portal.meu-painel')->with('success', 'Solicitação registrada com sucesso!');
        }

        return Redirect::back()->with('success', 'Solicitação registrada com sucesso!');
    }


    /**
     * Exibe os detalhes de uma solicitação.
     */
    public function show(SolicitacaoServico $solicitacao) // Usando Route Model Binding
    {
        // CORREÇÃO 2: Substituição da lógica manual pela Policy.
        $this->authorize('view', $solicitacao);

        // O with() pode ser movido para o Route Model Binding no seu arquivo de rotas para otimizar,
        // mas por clareza, podemos recarregar as relações aqui.
        $solicitacao->load(['cidadao', 'servico', 'status', 'atendente', 'documentos.uploader', 'pesquisa_satisfacao']);

        return inertia('Tenant/Solicitacoes/Show', [
            'solicitacao' => $solicitacao,
            'statusDisponiveis' => StatusSolicitacao::all(),
            'atendentesDisponiveis' => User::role(['Admin Tenant', 'Funcionario'])->get(['id', 'name']),
        ]);
    }

    /**
     * Atualiza uma solicitação.
     */
    public function update(Request $request, SolicitacaoServico $solicitacao)
    {
        // CORREÇÃO 3: Adicionada autorização.
        $this->authorize('update', $solicitacao);

        $validatedData = $request->validate([
            'status_id' => ['required', Rule::exists('tenant.status_solicitacao', 'id')],
            'atendente_id' => ['nullable', Rule::exists('tenant.users', 'id')],
            'observacoes' => 'nullable|string',
        ]);

        $dadosParaAtualizar = $validatedData;
        $novoStatus = StatusSolicitacao::find($validatedData['status_id']);

        // A lógica original não atualizava o nome do status, o que pode ser um bug.
        // Removi a linha 'dadosParaAtualizar['status'] = $novoStatus->nome;'
        // pois o model deve usar a relação para obter o nome do status através do status_id.

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
        // CORREÇÃO 4: Adicionada autorização.
        $this->authorize('delete', $solicitacao);

        $solicitacao->delete();
        return Redirect::route('admin.solicitacoes.index')->with('success', 'Solicitação excluída.');
    }
}
