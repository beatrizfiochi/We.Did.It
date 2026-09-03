<?php

namespace Tests\Feature\Admin;

use App\Models\Newsletter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Finalizar a newsletter (SCRUM-116).
 *
 * Publicar deixa de ser um campo do formulário e passa a ser uma ação própria,
 * que bloqueia a edição a partir daí — o conteúdo tem de continuar a
 * corresponder ao que foi distribuído.
 *
 * A pré-visualização fica deliberadamente de fora do bloqueio: é o que permite
 * voltar a gerar o PDF de uma edição publicada (SCRUM-122).
 */
class NewsletterPublishTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_publish(): void
    {
        $newsletter = Newsletter::factory()->create();

        $this->patch(route('admin.newsletters.publish', $newsletter))
            ->assertRedirect(route('login'));

        $this->assertTrue($newsletter->fresh()->is_draft);
    }

    public function test_publishing_changes_the_state(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();

        $response = $this->actingAs($admin)
            ->from(route('admin.newsletters.index'))
            ->patch(route('admin.newsletters.publish', $newsletter));

        $response->assertRedirect(route('admin.newsletters.index'));
        $response->assertSessionHas('success');

        $this->assertFalse($newsletter->fresh()->is_draft);
    }

    public function test_publishing_is_written_to_the_activity_log(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();

        $this->actingAs($admin)->patch(route('admin.newsletters.publish', $newsletter));

        $this->assertDatabaseHas('logs', [
            'user_id' => $admin->id,
            'table_name' => 'newsletters',
            'record_id' => $newsletter->id,
            'operation' => 'updated',
        ]);
    }

    public function test_an_already_published_newsletter_cannot_be_published_again(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->published()->create();

        $this->actingAs($admin)
            ->patch(route('admin.newsletters.publish', $newsletter))
            ->assertForbidden();
    }

    /**
     * Todos os caminhos de escrita ficam fechados depois de publicada. Sem isto,
     * bastava esquecer a guarda num método para o conteúdo poder mudar por baixo
     * de uma edição já distribuída.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function writeRoutes(): array
    {
        return [
            'editar a newsletter' => ['put', 'admin.newsletters.update'],
            'remover a newsletter' => ['delete', 'admin.newsletters.destroy'],
            'ecrã de notícias' => ['get', 'admin.newsletters.news.edit'],
            'guardar notícias' => ['put', 'admin.newsletters.news.update'],
            'ecrã de testemunhos' => ['get', 'admin.newsletters.testimonials.edit'],
            'guardar testemunhos' => ['put', 'admin.newsletters.testimonials.update'],
            'ecrã de formações' => ['get', 'admin.newsletters.courses.edit'],
            'guardar formações' => ['put', 'admin.newsletters.courses.update'],
            'ecrã da agenda' => ['get', 'admin.newsletters.calendars.edit'],
            'guardar agenda' => ['put', 'admin.newsletters.calendars.update'],
        ];
    }

    #[DataProvider('writeRoutes')]
    public function test_a_published_newsletter_refuses_every_write(string $method, string $route): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->published()->create();

        $this->actingAs($admin)
            ->{$method}(route($route, $newsletter))
            ->assertForbidden();
    }

    /**
     * O que a SCRUM-122 precisa: uma edição publicada continua a poder ser
     * pré-visualizada, que é como se volta a gerar o PDF.
     */
    public function test_a_published_newsletter_can_still_be_previewed(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->published()->create();

        $this->actingAs($admin)
            ->get(route('admin.newsletters.preview', $newsletter))
            ->assertOk();
    }

    /**
     * A pré-visualização de uma publicada avisa o gestor de que o PDF é gerado
     * dos conteúdos atuais (SCRUM-122). Nos rascunhos não há aviso.
     */
    public function test_the_preview_marks_a_published_newsletter_with_its_publish_date(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.newsletters.preview', Newsletter::factory()->create()))
            ->assertInertia(fn (Assert $page) => $page->where('publishedAt', null));

        $this->actingAs($admin)
            ->get(route('admin.newsletters.preview', Newsletter::factory()->published()->create()))
            ->assertInertia(fn (Assert $page) => $page->whereNot('publishedAt', null));
    }

    public function test_the_listing_shows_drafts_and_published_alike(): void
    {
        $admin = User::factory()->create();
        Newsletter::factory()->create();
        Newsletter::factory()->published()->create();

        $this->actingAs($admin)
            ->get(route('admin.newsletters.index'))
            ->assertOk();

        $this->assertSame(1, Newsletter::drafts()->count());
        $this->assertSame(1, Newsletter::published()->count());
    }
}
