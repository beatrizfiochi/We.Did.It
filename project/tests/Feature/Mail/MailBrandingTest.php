<?php

namespace Tests\Feature\Mail;

use App\Mail\AdminWelcome;
use App\Mail\NewSubmissionReceived;
use Illuminate\Mail\Mailable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * A marca dos emails (SCRUM-137).
 *
 * O tema por defeito do Laravel troca o nome da aplicação por um logo alojado
 * em laravel.com sempre que config('app.name') é "Laravel" — foi o que saiu
 * nos emails da demonstração enquanto o .env do servidor esteve por acertar.
 *
 * As vistas em resources/views/vendor/mail/ é que substituem esse tema. Se
 * alguém as apagar num merge, a aplicação volta silenciosamente ao tema do
 * Laravel e nada mais falha — daí este teste.
 */
class MailBrandingTest extends TestCase
{
    /**
     * @return array<string, array{0: Mailable}>
     */
    public static function mailables(): array
    {
        return [
            'boas-vindas ao administrador' => [
                new AdminWelcome('Ana Silva', 'Beatriz Fiochi'),
            ],
            'aviso de nova submissão' => [
                new NewSubmissionReceived(
                    type: 'Notícia',
                    title: 'Abertura das inscrições',
                ),
            ],
        ];
    }

    #[DataProvider('mailables')]
    public function test_the_email_carries_the_cesae_logo(Mailable $mail): void
    {
        $rendered = $mail->render();

        $this->assertStringContainsString('images/logo-email.png', $rendered);

        // muitos clientes bloqueiam imagens remotas: para boa parte de quem
        // recebe, o alt é o cabeçalho
        $this->assertStringContainsString('alt="We Did It"', $rendered);
    }

    #[DataProvider('mailables')]
    public function test_the_email_has_nothing_from_the_default_laravel_theme(Mailable $mail): void
    {
        $rendered = $mail->render();

        $this->assertStringNotContainsString('laravel.com', $rendered);
        $this->assertStringNotContainsString('All rights reserved', $rendered);
        $this->assertStringContainsString('Todos os direitos reservados', $rendered);
    }
}
