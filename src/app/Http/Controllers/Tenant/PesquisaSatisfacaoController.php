<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\PesquisaSatisfacao;
use App\Models\Tenant\SolicitacaoServico;
use App\Models\Tenant\StatusSolicitacao;
use App\Models\Tenant\User;
use App\Notifications\Tenant\AvaliacaoNegativaRecebida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redirect;

class PesquisaSatisfacaoController extends Controller
{
    /**
     * Armazena uma nova pesquisa de satisfação.
     *
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
        if (! $statusAtual || ! $statusAtual->is_final) {
            return Redirect::route('portalcidadao.meu-painel')->withErrors(['geral' => 'Você só pode avaliar solicitações que já foram finalizadas.']);
        }

        // Garante que a solicitação ainda não foi avaliada.
        if (PesquisaSatisfacao::where('solicitacao_servico_id', $solicitacao->id)->exists()) {
            return Redirect::route('portalcidadao.meu-painel')->withErrors(['geral' => 'Esta solicitação já foi avaliada.']);
        }

        // 3. Criação da pesquisa de satisfação.
        // ** A CORREÇÃO ESTÁ AQUI: Atribui o resultado a `$pesquisa` **
        $pesquisa = $solicitacao->pesquisa_satisfacao()->create([
            'user_id' => Auth::id(),
            'nota' => $validatedData['nota'],
            'comentario' => $validatedData['comentario'],
        ]);

        // --- LÓGICA DE NOTIFICAÇÃO ---
        // Agora a variável $pesquisa existe e pode ser usada.
        if ($pesquisa->nota <= 2) {
            $admins = User::role('Admin Tenant')->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new AvaliacaoNegativaRecebida($pesquisa));
            }
        }
        // --- FIM DA LÓGICA ---

        // 4. Redirecionamento com mensagem de sucesso.
        return Redirect::route('portalcidadao.meu-painel')->with('success', 'Obrigado pela sua avaliação!');
    }
}
