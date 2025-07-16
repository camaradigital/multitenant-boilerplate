<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class CampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public $customBody;
    public $attachmentPath;
    public $attachmentName;

    /**
     * Create a new message instance.
     */
    public function __construct($customBody, $attachmentPath = null, $attachmentName = null)
    {
        $this->customBody = $customBody;
        $this->attachmentPath = $attachmentPath;
        $this->attachmentName = $attachmentName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // O assunto será definido no controller
        return new Envelope();
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.campaign', // Usaremos uma view para o corpo do e-mail
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if ($this->attachmentPath) {
            return [
                Attachment::fromPath($this->attachmentPath)
                    ->as($this->attachmentName),
            ];
        }
        return [];
    }
}
