<?php

namespace App\Mail\Central;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $leadName;

    public string $body;

    public string $ctaUrl;

    public string $ctaText;

    public string $unsubscribeUrl;

    public function __construct(
        array $data,
        public ?string $attachmentPath = null,
        public ?string $attachmentName = null
    ) {
        $this->leadName = $data['leadName'];
        $this->body = $data['body'];
        $this->ctaUrl = $data['ctaUrl'];
        $this->ctaText = $data['ctaText'];
        $this->unsubscribeUrl = $data['unsubscribeUrl'];
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Uma Novidade Para a Gestão da Sua Câmara',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.central.campaign',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if (! $this->attachmentPath) {
            return [];
        }

        // AQUI ESTÁ A CORREÇÃO:
        // Em vez de 'fromPath', usamos 'fromStorage'.
        // Isso instrui o Laravel a buscar o arquivo no disco de storage padrão
        // usando o caminho relativo (ex: 'attachments/arquivo.pdf').
        // Isso resolve problemas de permissão e de caminho absoluto.
        return [
            Attachment::fromStorage($this->attachmentPath)
                ->as($this->attachmentName),
        ];
    }
}
