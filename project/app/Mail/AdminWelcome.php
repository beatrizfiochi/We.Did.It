<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Boas-vindas a um administrador criado por outro administrador (SCRUM-118).
 *
 * Não leva a palavra-passe nem um link para a definir. Quem cria a conta é que
 * escolhe a palavra-passe e a comunica à pessoa — pô-la aqui deixava-a numa caixa
 * de correio para sempre. Quem a perder usa a recuperação do Breeze.
 *
 * O nome de quem criou a conta vai no email de propósito: sem isso, a pessoa
 * recebe um acesso que não pediu e não sabe a quem perguntar.
 */
class AdminWelcome extends Mailable
{
    /**
     * @param  string  $name  o administrador novo, a quem o email é dirigido
     * @param  string  $createdBy  quem lhe criou a conta
     */
    public function __construct(
        public string $name,
        public string $createdBy,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'A tua conta no '.config('app.name').' está criada');
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(markdown: 'emails.admin.welcome');
    }
}
