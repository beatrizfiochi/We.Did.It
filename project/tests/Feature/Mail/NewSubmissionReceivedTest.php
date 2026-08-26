<?php

namespace Tests\Feature\Mail;

use App\Mail\NewSubmissionReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewSubmissionReceivedTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_subject_says_the_type_and_the_title(): void
    {
        $mail = new NewSubmissionReceived(
            type: 'Notícia',
            title: 'Abertura das inscrições',
        );

        $this->assertSame(
            'Nova submissão: Notícia — Abertura das inscrições',
            $mail->envelope()->subject,
        );
    }

    public function test_a_news_submission_renders_without_an_author(): void
    {
        $mail = new NewSubmissionReceived(
            type: 'Notícia',
            title: 'Abertura das inscrições',
            categoryName: 'Formação',
        );

        $rendered = $mail->render();

        $this->assertStringContainsString('Abertura das inscrições', $rendered);
        $this->assertStringContainsString('Formação', $rendered);
        // as notícias não têm autor, ao contrário dos testemunhos
        $this->assertStringNotContainsString('Submetido por', $rendered);
    }

    public function test_a_testimonial_submission_shows_the_author(): void
    {
        $rendered = (new NewSubmissionReceived(
            type: 'Testemunho',
            title: 'A formação mudou o meu percurso',
            authorName: 'Maria Silva',
        ))->render();

        $this->assertStringContainsString('Maria Silva', $rendered);
        $this->assertStringContainsString('um novo testemunho', $rendered);
    }

    public function test_a_submission_without_a_category_says_nenhuma(): void
    {
        $rendered = (new NewSubmissionReceived(
            type: 'Notícia',
            title: 'Abertura das inscrições',
        ))->render();

        $this->assertStringContainsString('Nenhuma', $rendered);
    }

    public function test_the_button_points_to_the_matching_moderation_screen(): void
    {
        $noticia = new NewSubmissionReceived(
            type: 'Notícia',
            title: 'Abertura das inscrições',
        );

        $testemunho = new NewSubmissionReceived(
            type: 'Testemunho',
            title: 'A formação mudou-me a vida',
            authorName: 'Ana',
        );

        // enquanto os ecrãs de moderação não existiam, o botão apontava ao
        // dashboard e o gestor tinha de lá chegar à mão (SCRUM-88 → SCRUM-86)
        $this->assertStringContainsString(route('admin.news.index'), $noticia->render());
        $this->assertStringContainsString(route('admin.testimonials.index'), $testemunho->render());
    }

    public function test_the_submitted_content_is_not_included(): void
    {
        // o email avisa; a moderação faz-se no painel
        $rendered = (new NewSubmissionReceived(
            type: 'Testemunho',
            title: 'Um título qualquer',
            authorName: 'Maria Silva',
        ))->render();

        $this->assertStringContainsString('não vai neste email', $rendered);
    }
}
