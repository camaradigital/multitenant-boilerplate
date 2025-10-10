<?php

namespace App\Notifications\Tenant;

use App\Models\Tenant\Legislatura;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LembreteRenovacaoMesaNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Legislatura $legislatura;

    /**
     * Create a new notification instance.
     */
    public function __construct(Legislatura $legislatura)
    {
        $this->legislatura = $legislatura;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Envia por e-mail e salva no banco de dados (para o sininho no painel)
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Lembrete: Renovação da Mesa Diretora')
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line('Este é um lembrete automático sobre a renovação da Mesa Diretora.')
            ->line('A legislatura atual, "'.$this->legislatura->titulo.'", está se aproximando do final de seu primeiro biênio.')
            ->line('É necessário realizar o processo de eleição e atualização dos cargos da Mesa Diretora (Presidente, Vice-Presidente, etc.) no sistema para o próximo biênio.')
            ->action('Gerenciar Legislaturas', route('admin.legislaturas.index'))
            ->line('Obrigado por usar nossa aplicação!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'titulo' => 'Atenção: Renovar Mesa Diretora',
            'mensagem' => 'A legislatura "'.$this->legislatura->titulo.'" está no período de renovação da Mesa Diretora. Atualize os cargos no sistema.',
            'url' => route('admin.legislaturas.edit', $this->legislatura->id),
        ];
    }
}
