<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class TenantResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * O token para redefinição de senha.
     *
     * @var string
     */
    public $token;

    /**
     * A URL completa para redefinição de senha.
     *
     * @var string
     */
    public $url;

    /**
     * Cria uma nova instância da notificação.
     *
     * @param  string  $token
     * @param  string  $url
     * @return void
     */
    public function __construct($token, $url)
    {
        $this->token = $token;
        $this->url = $url;
    }

    /**
     * Obtém os canais de entrega da notificação.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Constrói a representação de e-mail da notificação.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(Lang::get('Notificação de Redefinição de Senha'))
            ->line(Lang::get('Você está recebendo este e-mail porque recebemos uma solicitação de redefinição de senha para sua conta.'))
            ->action(Lang::get('Redefinir Senha'), $this->url)
            ->line(Lang::get('Este link de redefinição de senha irá expirar em :count minutos.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
            ->line(Lang::get('Se você não solicitou uma redefinição de senha, nenhuma ação adicional é necessária.'));
    }
}
