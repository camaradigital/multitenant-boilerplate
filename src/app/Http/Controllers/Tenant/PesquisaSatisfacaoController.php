<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\SolicitacaoServico;
use App\Models\Tenant\StatusSolicitacao;
use App\Models\Tenant\PesquisaSatisfacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

class PesquisaSatisfacaoController extends Controller
{
    /**
     * Armazena uma nova pesquisa de satisfação.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tenant\SolicitacaoServico  $solicitacao
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, SolicitacaoServico $solicitacao)
    {
        // 1. Validação dos dados enviados pelo formulário.
        $validatedData = $request->validate([
            'nota' => ['required', 'integer', 'min:1', 'max:5'],
            'comentario' => ['nullable', 'string', 'max:1000'],
        ]);

        // 2. Verificações de segurança e lógica de negócio.

        // Garante que a solicitação pertence ao cidadão logado.
        if ($solicitacao->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado.');
        }

        // Busca o modelo StatusSolicitacao para a verificação de status.
        $statusAtual = StatusSolicitacao::find($solicitacao->status_id);

        // Garante que a solicitação já foi finalizada.
        if (!$statusAtual || !$statusAtual->is_final) {
            return Redirect::route('portal.meu-painel')->withErrors(['geral' => 'Você só pode avaliar solicitações que já foram finalizadas.']);
        }

        // Garante que a solicitação ainda não foi avaliada, usando uma consulta direta para maior robustez.
        if (PesquisaSatisfacao::where('solicitacao_servico_id', $solicitacao->id)->exists()) {
            return Redirect::route('portal.meu-painel')->withErrors(['geral' => 'Esta solicitação já foi avaliada.']);
        }

        // 3. Criação da pesquisa de satisfação, usando o nome correto do relacionamento.
        $solicitacao->pesquisa_satisfacao()->create([
            'user_id' => Auth::id(),
            'nota' => $validatedData['nota'],
            'comentario' => $validatedData['comentario'],
        ]);

        // 4. Redirecionamento com mensagem de sucesso para a página correta.
        return Redirect::route('portal.meu-painel')->with('success', 'Obrigado pela sua avaliação!');
    }
}

