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
