<?php

namespace App\Mail;

use App\Models\Central\Tenant;
use App\Models\Tenant\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantWelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Tenant $tenant;
    public User $user;
    public string $token;

    /**
     * Create a new message instance.
     *
     * @param Tenant $tenant
     * @param User $user
     * @param string $token
     */
    public function __construct(Tenant $tenant, User $user, string $token)
    {
        $this->tenant = $tenant;
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bem-vindo(a) ao Portal da sua Câmara Digital',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Constrói a URL de redefinição de senha para o domínio do tenant
        $domain = $this->tenant->subdomain . '.' . config('app.central_domain');
        $scheme = config('app.env') === 'production' ? 'https' : 'http';

        $url = $scheme . "://" . $domain . route('password.reset', [
            'token' => $this->token,
            'email' => $this->user->getEmailForPasswordReset(),
        ], false);

        return new Content(
            view: 'emails.tenant.welcome',
            // Passa todos os dados necessários para o template
            with: [
                'url' => $url,
                'userName' => $this->user->name,
                'tenantName' => $this->tenant->name,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

