<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use App\Models\Tenant\Servico;
use App\Models\Tenant\SolicitacaoServico;
use App\Models\Tenant\StatusSolicitacao;
use App\Models\Tenant\User;
use App\Services\Tenant\SolicitacaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // 1. Import do DB Facade
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

class SolicitacaoServicoController extends Controller
{
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
        return inertia('Tenant/Solicitacoes/Index', [
            // O nome do relacionamento 'cidadao' parece correto, pois funciona aqui.
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
    public function store(Request $request)
    {
        $user = Auth::user();

        // Lógica para diferenciar a origem da solicitação
        if ($user->hasRole('Cidadao')) {
            // Validação para os dados vindos do portal do cidadão
            $request->validate([
                'servico_id' => 'required|exists:tenant.servicos,id',
                'observacoes' => 'nullable|string|max:5000',
                'documentos' => 'nullable|array',
                'documentos.*' => 'file|mimes:jpg,jpeg,png,pdf|max:10240', // Valida cada arquivo: max 10MB
            ]);
            $cidadao = $user;
        } else {
            // Funcionário criando para um cidadão (via Painel Admin)
            $request->validate([
                'user_id' => 'required|exists:tenant.users,id',
                'servico_id' => 'required|exists:tenant.servicos,id',
                'observacoes' => 'nullable|string',
                'renda_familiar' => 'nullable|numeric|min:0',
            ]);
            $cidadao = User::findOrFail($request->user_id);
        }

        $servico = Servico::findOrFail($request->servico_id);

        // Se o solicitante é um cidadão, verifica se o serviço permite solicitação online.
        if ($user->hasRole('Cidadao') && !$servico->permite_solicitacao_online) {
            return Redirect::back()->withErrors(['servico' => 'Este serviço está disponível apenas para solicitação presencial.']);
        }

        if ($servico->is_juridico && $request->filled('renda_familiar')) {
            $profileData = $cidadao->profile_data ?? [];
            $profileData['renda_familiar'] = $request->input('renda_familiar');
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

        // --- CORREÇÃO APLICADA AQUI ---
        // Usamos uma transação para garantir que a solicitação e seus arquivos sejam salvos juntos.
        DB::beginTransaction();
        try {
            // 1. A solicitação é criada E SEU RESULTADO É GUARDADO na variável $solicitacao.
            $solicitacao = SolicitacaoServico::create([
                'user_id' => $cidadao->id,
                'servico_id' => $servico->id,
                'status_id' => $statusInicial->id,
                'atendente_id' => $user->hasRole('Cidadao') ? null : $user->id,
                'observacoes' => $request->observacoes,
            ]);

            // 2. Com a variável $solicitacao definida, agora podemos salvar os arquivos associados a ela.
            if ($request->hasFile('documentos')) {
                foreach ($request->file('documentos') as $arquivo) {
                    $caminho = $arquivo->store('solicitacoes/' . $solicitacao->id, 'public');

                    $solicitacao->documentos()->create([
                        'path' => $caminho,
                        'nome_original' => $arquivo->getClientOriginalName(),
                        'user_id' => $user->id, // Guarda quem fez o upload
                        'mime_type' => $arquivo->getMimeType(), // Obtém o tipo do arquivo (ex: "image/jpeg")
                        'tamanho' => $arquivo->getSize(), // Obtém o tamanho do arquivo em bytes
                    ]);
                }
            }

            // Se tudo correu bem, confirmamos as operações no banco de dados.
            DB::commit();

        } catch (\Exception $e) {
            // Se qualquer erro ocorreu, desfazemos todas as operações.
            DB::rollBack();

            // É uma boa prática registrar o erro real para depuração futura.
            Log::error('Falha ao criar solicitação: ' . $e->getMessage());

            // Retornamos uma mensagem de erro genérica para o usuário.
            return Redirect::back()->withErrors(['servico' => 'Ocorreu um erro inesperado ao salvar sua solicitação. Por favor, tente novamente.']);
        }

        // Redirecionamento correto para o painel do cidadão, melhorando a experiência com Inertia.
        if ($user->hasRole('Cidadao')) {
            return Redirect::route('portal.meu-painel')->with('success', 'Solicitação registrada com sucesso!');
        }

        // Redirecionamento para funcionários que criam solicitações.
        return Redirect::back()->with('success', 'Solicitação registrada com sucesso!');
    }

    /**
     * Exibe os detalhes de uma solicitação.
     *
     * --- CORREÇÃO APLICADA AQUI ---
     * Trocamos o Route Model Binding por uma busca explícita com with().
     * Isso garante que todos os relacionamentos sejam carregados de forma consistente,
     * assim como é feito no método index().
     */
    public function show($id) // Alterado para receber o ID
    {
        // Buscamos a solicitação e já carregamos todos os dados relacionados de uma vez
        $solicitacao = SolicitacaoServico::with([
            'cidadao',
            'servico',
            'status',
            'atendente',
            'documentos.uploader',
            'pesquisa_satisfacao'
        ])->findOrFail($id);

        // A verificação de autorização continua a mesma
        if (Auth::user()->hasRole('Cidadao') && $solicitacao->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado.');
        }

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
        $validatedData = $request->validate([
            'status_id' => [
                'required',
                Rule::exists('tenant.status_solicitacao', 'id'),
            ],
            'atendente_id' => [
                'nullable',
                Rule::exists('tenant.users', 'id'),
            ],
            'observacoes' => 'nullable|string',
        ]);

        $dadosParaAtualizar = $validatedData;
        $novoStatus = StatusSolicitacao::find($validatedData['status_id']);

        if ($novoStatus) {
            $dadosParaAtualizar['status'] = $novoStatus->nome;
        }

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
        // Para habilitar a autorização, crie uma Policy: php artisan make:policy SolicitacaoServicoPolicy --model=Tenant/SolicitacaoServico
        // $this->authorize('delete', $solicitacao);

        $solicitacao->delete();
        return Redirect::route('admin.solicitacoes.index')->with('success', 'Solicitação excluída.');
    }
}
