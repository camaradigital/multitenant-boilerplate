<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;
use Spatie\Multitenancy\Models\Tenant; // Importe o modelo Tenant

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
        // Garante que temos um tenant atual no contexto
        if (! $tenant = Tenant::current()) {
            // Se não houver tenant, não podemos gerar a URL correta.
            // Você pode querer logar um erro aqui.
            // Por enquanto, vamos retornar uma mensagem de erro simples.
            return (new MailMessage)
                ->subject(Lang::get('Erro na Criação de Convite'))
                ->line(Lang::get('Não foi possível gerar um link de convite. Por favor, contate o suporte.'));
        }

        // Constrói a URL usando o domínio do tenant atual
        $url = 'https://'.$tenant->domain.route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false);

        return (new MailMessage)
            ->subject(Lang::get('Bem-vindo(a) ao Portal da sua Câmara Digital'))
            ->line(Lang::get('Você foi convidado a acessar o nosso portal. Para começar, por favor, crie uma senha clicando no botão abaixo.'))
            ->action(Lang::get('Criar Senha'), $url)
            ->line(Lang::get('Este link para criação de senha irá expirar em :count minutos.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
            ->line(Lang::get('Se você não esperava este convite, nenhuma ação é necessária.'));
    }
}
