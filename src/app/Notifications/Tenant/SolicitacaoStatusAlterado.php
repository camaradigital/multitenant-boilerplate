<?php

namespace App\Notifications\Tenant;

use App\Models\Tenant\SolicitacaoServico;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SolicitacaoStatusAlterado extends Notification
{
    use Queueable;

    public $solicitacao;

    public $novoStatusNome;

    /**
     * Altera o construtor para aceitar o nome do novo status
     */
    public function __construct(SolicitacaoServico $solicitacao, string $novoStatusNome)
    {
        $this->solicitacao = $solicitacao;
        $this->novoStatusNome = $novoStatusNome;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'solicitacao_id' => $this->solicitacao->id,
            'titulo' => 'Status da Solicitação Alterado',
            'mensagem' => 'O status da sua solicitação #'.$this->solicitacao->id.' foi atualizado para: '.$this->novoStatusNome,
            'url' => route('portal.solicitacoes.show', $this->solicitacao->id, false),
        ];
    }
}
