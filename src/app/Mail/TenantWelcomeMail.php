<?php

namespace App\Mail;

use App\Models\Central\Tenant; // Adicionado
use App\Models\Tenant\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public Tenant $tenant; // Adicionado
    public User $user;
    public string $token;

    // Construtor atualizado para receber o Tenant
    public function __construct(Tenant $tenant, User $user, string $token)
    {
        $this->tenant = $tenant;
        $this->user = $user;
        $this->token = $token;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bem-vindo(a) ao Portal do Cidadão',
        );
    }

    public function content(): Content
    {
        // ---- LÓGICA DE URL CORRIGIDA ----
        // 1. Gera o caminho relativo para o reset de senha
        $path = route('password.reset', [
            'token' => $this->token,
            'email' => $this->user->getEmailForPasswordReset(),
        ], false); // O 'false' garante que a URL seja relativa (sem domínio)

        // 2. Monta a URL final usando o domínio do tenant
        $url = $this->tenant->getDomainUrl() . $path;

        return new Content(
            view: 'emails.tenant.welcome',
            with: ['url' => $url],
        );
    }
}
