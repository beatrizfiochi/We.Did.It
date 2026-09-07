<?php

namespace Tests\Feature\Admin;

use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class NewsModerationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Abertura das inscrições para o próximo ano',
            'description' => str_repeat('Detalhes sobre as inscrições. ', 5),
            'category_id' => '',
            'event_start_date' => '2026-05-12',
        ], $overrides);
    }

    public function test_guests_cannot_moderate_news(): void
    {
        $news = News::factory()->create(['status' => 'received']);

        $this->get(route('admin.news.index'))->assertRedirect(route('login'));
        $this->put(route('admin.news.update', $news), $this->validPayload())
            ->assertRedirect(route('login'));
        $this->patch(route('admin.news.approve', $news))->assertRedirect(route('login'));
        $this->patch(route('admin.news.refuse', $news))->assertRedirect(route('login'));

        $this->assertSame('received', $news->fresh()->status);
    }

    public function test_the_list_shows_the_submitted_news(): void
    {
        News::factory()->create(['title' => 'Uma notícia qualquer']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.news.index'))
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Admin/News/Index')
                    ->has('news', 1)
                    ->where('news.0.title', 'Uma notícia qualquer')
            );
    }

    public function test_the_list_includes_every_submitted_image(): void
    {
        // não só a coluna espelho: o modal de moderação mostra as imagens
        // todas, para o gestor poder remover uma sem recusar a submissão
        // inteira (SCRUM-142)
        News::factory()->withImages(3)->create();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.news.index'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has('news.0.images', 3)
                    ->has('news.0.images.0.path')
            );
    }

    public function test_removing_one_image_does_not_change_the_moderation_status(): void
    {
        // o pedido do SCRUM-142: uma imagem imprópria não obriga a recusar a
        // notícia inteira. A remoção em si é a admin.images.destroy que já
        // existe (ImageDeletionTest); aqui só confirma que o estado da
        // moderação não é tocado por ela.
        $news = News::factory()->withImages(3)->create(['status' => 'received']);
        $imagem = $news->images()->first();

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.images.destroy', $imagem))
            ->assertSessionHasNoErrors();

        $news->refresh();

        $this->assertSame('received', $news->status);
        $this->assertSame(2, $news->images()->count());
    }

    public function test_the_list_can_be_filtered_by_status(): void
    {
        News::factory()->create(['status' => 'received']);
        News::factory()->create(['status' => 'accepted']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.news.index', ['status' => 'accepted']))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has('news', 1)
                    ->where('news.0.status', 'accepted')
            );
    }

    public function test_news_can_be_approved(): void
    {
        $news = News::factory()->create(['status' => 'received']);
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('admin.news.approve', $news));

        $this->assertSame('accepted', $news->fresh()->status);
        $this->assertDatabaseHas('logs', [
            'user_id' => $user->id,
            'table_name' => 'news',
            'record_id' => $news->id,
            'operation' => 'updated',
        ]);
    }

    public function test_news_can_be_refused(): void
    {
        $news = News::factory()->create(['status' => 'received']);

        $this->actingAs(User::factory()->create())
            ->patch(route('admin.news.refuse', $news));

        $this->assertSame('refused', $news->fresh()->status);
    }

    public function test_news_content_can_be_updated(): void
    {
        $news = News::factory()->create(['status' => 'received']);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('admin.news.update', $news), $this->validPayload([
                'title' => 'Um título corrigido pela moderação',
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame('Um título corrigido pela moderação', $news->fresh()->title);
        $this->assertDatabaseHas('logs', [
            'user_id' => $user->id,
            'table_name' => 'news',
            'record_id' => $news->id,
            'operation' => 'updated',
        ]);
    }

    public function test_updating_the_content_cannot_change_the_status(): void
    {
        // o status está no #[Fillable] do model, por isso a única coisa que o
        // protege é estar fora das rules do UpdateNewsRequest
        $news = News::factory()->create(['status' => 'received']);

        $this->actingAs(User::factory()->create())
            ->put(route('admin.news.update', $news), $this->validPayload([
                'status' => 'accepted',
            ]));

        $this->assertSame('received', $news->fresh()->status);
    }

    public function test_a_new_image_is_added_alongside_the_existing_one(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('news/antiga.jpg', 'conteudo');

        // a factory já cria a linha em images sozinha quando 'image' vem preenchido
        $news = News::factory()->create(['image' => 'news/antiga.jpg']);

        $this->actingAs(User::factory()->create())
            ->put(route('admin.news.update', $news), $this->validPayload([
                'images' => [UploadedFile::fake()->create('nova.jpg', 100, 'image/jpeg')],
            ]))
            ->assertSessionHasNoErrors();

        // já não se substitui, acrescenta-se: a antiga fica, a nova entra
        Storage::disk('public')->assertExists('news/antiga.jpg');
        $this->assertSame(2, $news->fresh()->images()->count());
    }

    /**
     * As linhas em images e a coluna espelho têm de entrar juntas.
     *
     * A moderação chama o storeImages() sem transação à volta, ao contrário
     * dos formulários públicos — se o espelho falhasse depois das linhas já
     * gravadas, ficavam linhas a apontar para ficheiros que o catch apagou.
     */
    public function test_a_failure_writing_the_mirror_rolls_back_the_images(): void
    {
        Storage::fake('public');
        $this->withoutExceptionHandling();

        $news = News::factory()->create(['image' => null]);

        // 1ª escrita: o update do conteúdo no controlador.
        // 2ª escrita: o syncImageMirror, que é o que queremos ver falhar.
        $escritas = 0;
        News::updating(function () use (&$escritas) {
            $escritas++;

            if ($escritas === 2) {
                throw new \RuntimeException('falha simulada ao gravar o espelho');
            }
        });

        try {
            $this->actingAs(User::factory()->create())
                ->put(route('admin.news.update', $news), $this->validPayload([
                    'images' => [UploadedFile::fake()->create('nova.jpg', 100, 'image/jpeg')],
                ]));

            $this->fail('A exceção do espelho devia ter subido.');
        } catch (\RuntimeException $e) {
            $this->assertSame('falha simulada ao gravar o espelho', $e->getMessage());
        }

        $this->assertSame(2, $escritas);
        $this->assertSame(0, $news->fresh()->images()->count());
        $this->assertNull($news->fresh()->image);
        Storage::disk('public')->assertDirectoryEmpty('news');
    }

    /**
     * As mensagens por ficheiro estavam traduzidas só nas submissões públicas,
     * e um moderador que anexasse um PDF via o inglês do Laravel, com o nome
     * cru do campo: "The images.0 field must be an image."
     */
    public function test_the_per_file_messages_are_in_portuguese(): void
    {
        Storage::fake('public');

        $news = News::factory()->create(['image' => null]);

        $this->actingAs(User::factory()->create())
            ->put(route('admin.news.update', $news), $this->validPayload([
                'images' => [UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf')],
            ]))
            ->assertSessionHasErrors('images.0');

        $this->assertContains(
            'Cada ficheiro tem de ser uma imagem.',
            session('errors')->get('images.0'),
        );
    }

    /** O campo chamava-se 'image' antes da SCRUM-140: recusa em vez de ignorar. */
    public function test_the_old_singular_field_is_rejected_instead_of_ignored(): void
    {
        Storage::fake('public');

        $news = News::factory()->create(['image' => null]);

        $this->actingAs(User::factory()->create())
            ->put(route('admin.news.update', $news), $this->validPayload([
                'image' => UploadedFile::fake()->create('nova.jpg', 100, 'image/jpeg'),
            ]))
            ->assertSessionHasErrors('image');

        $this->assertSame(0, $news->fresh()->images()->count());
    }

    public function test_the_limit_counts_images_already_saved(): void
    {
        Storage::fake('public');

        // 3 já gravadas: não sobra nenhuma vaga
        $news = News::factory()->withImages(3)->create();

        $response = $this->actingAs(User::factory()->create())
            ->put(route('admin.news.update', $news), $this->validPayload([
                'images' => [UploadedFile::fake()->create('nova.jpg', 100, 'image/jpeg')],
            ]));

        $response->assertSessionHasErrors('images');
        $this->assertSame(
            'Esta submissão só pode ter 3 imagens. Remove uma antes de acrescentar.',
            session('errors')->get('images')[0],
        );
        $this->assertSame(3, $news->fresh()->images()->count());
    }

    public function test_updating_without_an_image_keeps_the_current_one(): void
    {
        Storage::fake('public');
        $news = News::factory()->create(['image' => 'news/atual.jpg']);

        $this->actingAs(User::factory()->create())
            ->put(route('admin.news.update', $news), $this->validPayload());

        $this->assertSame('news/atual.jpg', $news->fresh()->image);
    }

    /**
     * As mesmas regras de data no lado da moderação (SCRUM-145).
     *
     * Está aqui porque já aconteceu uma vez o contrário: as mensagens por
     * ficheiro foram traduzidas nas submissões públicas e ficaram por
     * traduzir nas edições, e só se descobriu na revisão.
     */
    public function test_the_moderation_validates_the_event_dates(): void
    {
        $news = News::factory()->create();
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->put(route('admin.news.update', $news), $this->validPayload([
                'event_start_date' => '2026-05-12',
                'event_end_date' => '2026-05-10',
            ]))
            ->assertSessionHasErrors('event_end_date');

        $payload = $this->validPayload();
        unset($payload['event_start_date']);

        $this->actingAs($admin)
            ->put(route('admin.news.update', $news), $payload)
            ->assertSessionHasErrors('event_start_date');
    }

    /** Como na submissão: fim igual ao início é um dia único, e grava null. */
    public function test_the_moderation_stores_a_null_end_when_it_equals_the_start(): void
    {
        $news = News::factory()->create();

        $this->actingAs(User::factory()->create())
            ->put(route('admin.news.update', $news), $this->validPayload([
                'event_start_date' => '2026-05-12',
                'event_end_date' => '2026-05-12',
            ]))
            ->assertSessionHasNoErrors();

        $this->assertNull($news->fresh()->event_end_date);
    }

    public function test_the_moderation_can_correct_the_event_dates(): void
    {
        $news = News::factory()->create(['event_start_date' => '2026-01-01', 'event_end_date' => null]);

        $this->actingAs(User::factory()->create())
            ->put(route('admin.news.update', $news), $this->validPayload([
                'event_start_date' => '2026-05-12',
                'event_end_date' => '2026-05-15',
            ]))
            ->assertSessionHasNoErrors();

        $news->refresh();

        $this->assertSame('2026-05-12', $news->event_start_date->format('Y-m-d'));
        $this->assertSame('2026-05-15', $news->event_end_date->format('Y-m-d'));
    }

    public function test_the_content_is_validated(): void
    {
        $news = News::factory()->create();

        $this->actingAs(User::factory()->create())
            ->put(route('admin.news.update', $news), $this->validPayload([
                // 4 caracteres: abaixo do min:5 do UpdateNewsRequest
                'title' => 'Curt',
                'description' => 'Demasiado curta.',
            ]))
            ->assertSessionHasErrors(['title', 'description']);
    }
}
