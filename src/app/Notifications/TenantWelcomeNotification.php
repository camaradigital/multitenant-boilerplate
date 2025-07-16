<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class TenantWelcomeNotification extends Notification
{
    use Queueable;

    public string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject(Lang::get('Bem-vindo(a) ao Portal do Cidadão'))
            ->line(Lang::get('Você foi convidado a acessar o nosso portal. Para começar, por favor, crie uma senha clicando no botão abaixo.'))
            ->action(Lang::get('Criar Senha'), $url)
            ->line(Lang::get('Este link para criação de senha irá expirar em :count minutos.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
            ->line(Lang::get('Se você não esperava este convite, nenhuma ação é necessária.'));
    }
}
