<?php

namespace App\Notifications\Tenant;

use App\Models\Tenant\PesquisaSatisfacao;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AvaliacaoNegativaRecebida extends Notification
{
    use Queueable;

    public $pesquisaSatisfacao;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(PesquisaSatisfacao $pesquisaSatisfacao)
    {
        $this->pesquisaSatisfacao = $pesquisaSatisfacao;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        // ** A CORREÇÃO FINAL ESTÁ AQUI **
        // O nome da relação segue a convenção camelCase: solicitacaoServico
        $this->pesquisaSatisfacao->load('solicitacaoServico.cidadao');
        $solicitacao = $this->pesquisaSatisfacao->solicitacaoServico;

        return [
            'solicitacao_id' => $solicitacao->id,
            'titulo' => 'Avaliação Negativa Recebida',
            'mensagem' => 'A solicitação #'.$solicitacao->id.' para '.$solicitacao->cidadao->name.' recebeu uma nota '.$this->pesquisaSatisfacao->nota.'/5.',
            'url' => route('admin.solicitacoes.show', $solicitacao->id, false),
        ];
    }
}
