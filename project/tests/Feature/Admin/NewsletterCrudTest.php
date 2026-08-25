<?php

namespace Tests\Feature\Admin;

use App\Models\Newsletter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * CRUD da newsletter (SCRUM-102).
 *
 * Não há teste ao index: ele renderiza Admin/Newsletters/Index, que é o ecrã da
 * SCRUM-106 da Leida e ainda não existe. Testá-lo agora daria 500, como os
 * testes da agenda que a SCRUM-133 teve de limpar. Fica do lado de quem faz o ecrã.
 */
class NewsletterCrudTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Newsletter de setembro',
            'edition' => 12,
            'date' => '2026-09-01',
            'period_start' => '2026-08-24',
            'period_end' => '2026-08-28',
        ], $overrides);
    }

    public function test_guests_cannot_access_any_newsletter_route(): void
    {
        $newsletter = Newsletter::factory()->create();

        $this->get(route('admin.newsletters.index'))->assertRedirect(route('login'));
        $this->post(route('admin.newsletters.store'), $this->validPayload())->assertRedirect(route('login'));
        $this->put(route('admin.newsletters.update', $newsletter), $this->validPayload())->assertRedirect(route('login'));
        $this->delete(route('admin.newsletters.destroy', $newsletter))->assertRedirect(route('login'));
        $this->get(route('admin.newsletters.preview', $newsletter))->assertRedirect(route('login'));

        $this->assertDatabaseCount('newsletters', 1);
    }

    public function test_authenticated_users_can_create_a_newsletter(): void
    {
        $admin = User::factory()->create();

        // o controller responde com back(): o from() é o que faz o redirect
        // voltar para a listagem, como acontece no browser
        $response = $this->actingAs($admin)
            ->from(route('admin.newsletters.index'))
            ->post(route('admin.newsletters.store'), $this->validPayload());

        $this->assertDatabaseHas('newsletters', [
            'title' => 'Newsletter de setembro',
            'edition' => 12,
        ]);
        $response->assertRedirect(route('admin.newsletters.index'));
        $response->assertSessionHas('success');
    }

    public function test_a_new_newsletter_is_born_as_a_draft(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.newsletters.store'), $this->validPayload());

        $newsletter = Newsletter::firstWhere('edition', 12);

        $this->assertTrue($newsletter->is_draft);
        $this->assertSame(Newsletter::RASCUNHO, $newsletter->status);
    }

    public function test_the_status_cannot_be_chosen_by_whoever_submits_the_form(): void
    {
        $admin = User::factory()->create();

        // publicar é a SCRUM-116; ninguém deve conseguir criar uma newsletter
        // já publicada a partir do formulário
        $this->actingAs($admin)->post(route('admin.newsletters.store'), $this->validPayload([
            'status' => Newsletter::PUBLICADA,
        ]));

        $this->assertTrue(Newsletter::firstWhere('edition', 12)->is_draft);
    }

    public function test_the_status_cannot_be_changed_by_whoever_submits_the_form(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create([
            'status' => Newsletter::RASCUNHO,
            'edition' => 12,
        ]);

        // o formulário de edição envia um campo status, mas publicar não é gravar
        // um campo: tem de bloquear a edição e, na Sprint 5, gerar o PDF. Por isso
        // a publicação é a SCRUM-116, numa rota própria, e o status nunca entra
        // por aqui. Se um dia este teste falhar, alguém acrescentou 'status' às
        // regras do UpdateNewsletterRequest — é o desenho a mudar, não a completar-se.
        $this->actingAs($admin)->put(route('admin.newsletters.update', $newsletter), $this->validPayload([
            'status' => Newsletter::PUBLICADA,
        ]));

        $this->assertTrue($newsletter->fresh()->is_draft);
    }

    public function test_creating_a_newsletter_requires_the_mandatory_fields(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.newsletters.store'), []);

        $response->assertSessionHasErrors(['title', 'edition', 'date', 'period_start', 'period_end']);
        $this->assertDatabaseCount('newsletters', 0);
    }

    public function test_updating_a_newsletter_requires_the_mandatory_fields(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();

        $response = $this->actingAs($admin)->put(route('admin.newsletters.update', $newsletter), []);

        $response->assertSessionHasErrors(['title', 'edition', 'date', 'period_start', 'period_end']);
    }


    public function test_the_title_needs_at_least_five_characters(): void
    {
        $admin = User::factory()->create();

        // acompanha as notícias e os testemunhos, que já exigiam min:5;
        // sem isto passava um título como "a", que não diz nada no arquivo
        $response = $this->actingAs($admin)->post(route('admin.newsletters.store'), $this->validPayload([
            'title' => 'abc',
        ]));

        $response->assertSessionHasErrors('title');
        $this->assertDatabaseCount('newsletters', 0);
    }

    public function test_the_edition_must_be_unique(): void
    {
        $admin = User::factory()->create();
        Newsletter::factory()->create(['edition' => 12]);

        $response = $this->actingAs($admin)->post(route('admin.newsletters.store'), $this->validPayload());

        $response->assertSessionHasErrors('edition');
        $this->assertDatabaseCount('newsletters', 1);
    }

    public function test_the_period_end_cannot_be_before_the_period_start(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.newsletters.store'), $this->validPayload([
            'period_end' => '2026-08-01',
        ]));

        $response->assertSessionHasErrors('period_end');
        $this->assertDatabaseCount('newsletters', 0);
    }

    public function test_authenticated_users_can_update_a_newsletter(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create(['edition' => 12]);

        $response = $this->actingAs($admin)
            ->from(route('admin.newsletters.index'))
            ->put(route('admin.newsletters.update', $newsletter), $this->validPayload([
                'title' => 'Título atualizado',
            ]));

        $this->assertDatabaseHas('newsletters', [
            'id' => $newsletter->id,
            'title' => 'Título atualizado',
        ]);
        $response->assertRedirect(route('admin.newsletters.index'));
        $response->assertSessionHas('success');
    }

    public function test_saving_without_changing_the_edition_is_allowed(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create(['edition' => 12]);

        // sem o ->ignore() no Rule::unique, a newsletter dava erro de edição
        // duplicada contra ela própria
        $response = $this->actingAs($admin)->put(route('admin.newsletters.update', $newsletter), $this->validPayload([
            'title' => 'Outro título',
        ]));

        $response->assertSessionHasNoErrors();
        $this->assertSame('Outro título', $newsletter->fresh()->title);
    }

    public function test_the_edition_of_another_newsletter_cannot_be_reused(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create(['edition' => 12]);
        Newsletter::factory()->create(['edition' => 13]);

        $response = $this->actingAs($admin)->put(route('admin.newsletters.update', $newsletter), $this->validPayload([
            'edition' => 13,
        ]));

        $response->assertSessionHasErrors('edition');
        $this->assertSame(12, $newsletter->fresh()->edition);
    }

    public function test_authenticated_users_can_delete_a_newsletter(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();

        $response = $this->actingAs($admin)
            ->from(route('admin.newsletters.index'))
            ->delete(route('admin.newsletters.destroy', $newsletter));

        $this->assertDatabaseCount('newsletters', 0);
        $response->assertRedirect(route('admin.newsletters.index'));
        $response->assertSessionHas('success');
    }

    public function test_authenticated_users_can_save_an_existing_newsletter_as_draft(): void
    {
        $admin = User::factory()->create();

        $newsletter = Newsletter::factory()->create([
            'status' => false,
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.newsletter.index'))
            ->put(
                route('admin.newsletters.update', $newsletter),
                [
                    'title' => $newsletter->title,
                    'date' => $newsletter->date,
                    'edition' => $newsletter->edition,
                    'period_start' => $newsletter->period_start,
                    'period_end' => $newsletter->period_end,
                    'status' => true,
                ]
            );

        $response->assertRedirect(route('admin.newsletters.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('newsletter', [
            'id' => $newsletter->id,
            'status' => true,
        ]);
    }

    public function test_authenticated_users_can_publish_an_existing_newsletter(): void
    {
        $admin = User::factory()->create();

        $newsletter = Newsletter::factory()->create([
            'status' => true,
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.newsletter.index'))
            ->put(
                route('admin.newsletters.update', $newsletter),
                [
                    'title' => $newsletter->title,
                    'date' => $newsletter->date,
                    'edition' => $newsletter->edition,
                    'period_start' => $newsletter->period_start,
                    'period_end' => $newsletter->period_end,
                    'status' => false,
                ]
            );

        $response->assertRedirect(route('admin.newsletters.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('newsletter', [
            'id' => $newsletter->id,
            'status' => Newsletter::PUBLICADA,
        ]);
    }


    public function test_the_three_operations_are_written_to_the_activity_log(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.newsletters.store'), $this->validPayload());
        $newsletter = Newsletter::firstWhere('edition', 12);

        $this->actingAs($admin)->put(route('admin.newsletters.update', $newsletter), $this->validPayload([
            'title' => 'Título atualizado',
        ]));

        $this->actingAs($admin)->delete(route('admin.newsletters.destroy', $newsletter));

        foreach (['created', 'updated', 'removed'] as $operacao) {
            $this->assertDatabaseHas('logs', [
                'user_id' => $admin->id,
                'table_name' => 'newsletters',
                'record_id' => $newsletter->id,
                'operation' => $operacao,
            ]);
        }
    }
}
