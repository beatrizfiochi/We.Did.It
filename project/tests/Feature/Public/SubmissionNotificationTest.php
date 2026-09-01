<?php

namespace Tests\Feature\Public;

use App\Mail\NewSubmissionReceived;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SubmissionNotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function testimonial_payload(): array
    {
        return [
            'terms_conditions' => 'on',
            'name' => 'Maria Silva',
            'email' => 'maria@exemplo.pt',
            'title' => 'A formação mudou o meu percurso',
            'description' => str_repeat('Foi uma experiência muito positiva. ', 5),
            'category_id' => '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function newsPayload(): array
    {
        return [
            'terms_conditions' => 'on',
            'title' => 'Abertura das inscrições para o próximo ano',
            'description' => str_repeat('Detalhes sobre as inscrições. ', 5),
            'category_id' => '',
        ];
    }

    public function test_a_testimonial_submission_notifies_the_managers(): void
    {
        Mail::fake();
        $manager = User::factory()->create();

        $this->post(route('testimonials.store'), $this->testimonial_payload());

        Mail::assertSent(
            NewSubmissionReceived::class,
            fn(NewSubmissionReceived $mail) => $mail->hasTo($manager->email)
                && $mail->type === 'Testemunho'
                && $mail->authorName === 'Maria Silva',
        );
    }

    public function test_a_news_submission_notifies_the_managers(): void
    {
        Mail::fake();
        $manager = User::factory()->create();

        $this->post(route('news.store'), $this->newsPayload());

        Mail::assertSent(
            NewSubmissionReceived::class,
            fn(NewSubmissionReceived $mail) => $mail->hasTo($manager->email)
                && $mail->type === 'Notícia'
                // as notícias não têm autor
                && $mail->authorName === null,
        );
    }

    public function test_every_active_manager_is_notified(): void
    {
        Mail::fake();
        $first = User::factory()->create();
        $second = User::factory()->create();

        $this->post(route('testimonials.store'), $this->testimonial_payload());

        // um email só, com os dois em To:
        Mail::assertSentCount(1);
        Mail::assertSent(
            NewSubmissionReceived::class,
            fn(NewSubmissionReceived $mail) => $mail->hasTo($first->email)
                && $mail->hasTo($second->email),
        );
    }

    public function test_inactive_users_are_not_notified(): void
    {
        Mail::fake();
        $active = User::factory()->create(['status' => true]);
        $inactive = User::factory()->create(['status' => false]);

        $this->post(route('testimonials.store'), $this->testimonial_payload());

        Mail::assertSent(
            NewSubmissionReceived::class,
            fn(NewSubmissionReceived $mail) => $mail->hasTo($active->email)
                && ! $mail->hasTo($inactive->email),
        );
    }

    public function test_nothing_is_sent_when_the_submission_is_invalid(): void
    {
        Mail::fake();
        User::factory()->create();

        $this->post(route('testimonials.store'), ['name' => '', 'email' => '']);

        Mail::assertNothingSent();
    }

    public function test_a_submission_is_saved_even_with_no_managers_to_notify(): void
    {
        Mail::fake();

        // sem utilizadores na base, o Mail::to() receberia uma coleção vazia
        $this->assertSame(0, User::count());

        $this->post(route('testimonials.store'), $this->testimonial_payload())
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('testimonials', 1);
        Mail::assertNothingSent();
    }
}
