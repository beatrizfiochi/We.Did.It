<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Aviso aos gestores de que chegou uma submissão nova pelo formulário público.
 *
 * Serve as notícias e os testemunhos: o conteúdo é o mesmo, muda só o tipo.
 * Não leva a descrição submetida — é conteúdo por moderar e pode ser abusivo.
 * O email avisa; a moderação faz-se no painel.
 */
class NewSubmissionReceived extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  string  $type  'Notícia' ou 'Testemunho', como aparece no assunto
     * @param  string|null  $authorName  só existe nos testemunhos
     */
    public function __construct(
        public string $type,
        public string $title,
        public ?string $authorName = null,
        public ?string $categoryName = null,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nova submissão: {$this->type} — {$this->title}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.submissions.received',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
