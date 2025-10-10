<?php

namespace App\Mail\Tenant;

use App\Models\Tenant\SugestaoProjetoLei;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NovaSugestaoProjetoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $sugestao;

    /**
     * Create a new message instance.
     */
    public function __construct(SugestaoProjetoLei $sugestao)
    {
        $this->sugestao = $sugestao;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nova Sugestão de Projeto de Lei Recebida - Protocolo: '.$this->sugestao->protocolo,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tenant.nova_sugestao_projeto',
            with: [
                'sugestao' => $this->sugestao,
                'url' => route('admin.sugestoes.show', $this->sugestao),
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
        if ($this->sugestao->anexo_path) {
            return [
                \Illuminate\Mail\Mailables\Attachment::fromStorageDisk('public', $this->sugestao->anexo_path),
            ];
        }

        return [];
    }
}
