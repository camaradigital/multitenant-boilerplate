<?php

namespace App\Notifications\Tenant;

use App\Models\Tenant\SolicitacaoServico;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SolicitacaoParada extends Notification implements ShouldQueue
{
    use Queueable;

    public SolicitacaoServico $solicitacao;

    public string $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(SolicitacaoServico $solicitacao, string $reason)
    {
        $this->solicitacao = $solicitacao;
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $this->solicitacao->load('cidadao', 'servico');
        $url = url('/admin/solicitacoes/'.$this->solicitacao->id);

        // Constrói a mensagem usando a variável $reason
        $line = 'A solicitação #'.$this->solicitacao->id.' para o serviço "'.$this->solicitacao->servico->nome.'" '.$this->reason;

        return (new MailMessage)
            ->subject('Alerta: Solicitação de Serviço Parada')
            ->line($line)
            ->line("Solicitante: {$this->solicitacao->cidadao->name}")
            ->action('Ver Solicitação', $url)
            ->line('Por favor, verifique a situação.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->solicitacao->load('cidadao', 'servico');

        // Constrói a mensagem da notificação usando a variável $reason
        $message = 'A solicitação #'.$this->solicitacao->id.' do cidadão "'.$this->solicitacao->cidadao->name.'" '.$this->reason;

        return [
            'solicitacao_id' => $this->solicitacao->id,
            'titulo' => 'Alerta: Solicitação Parada',
            'mensagem' => $message,
            'url' => '/admin/solicitacoes/'.$this->solicitacao->id,
        ];
    }
}
