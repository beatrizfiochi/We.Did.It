<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\News;
use App\Models\Newsletter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class NewsletterNewsSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_see_the_news_selection_screen(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        News::factory()->count(3)->create(['status' => 'accepted']);

        $response = $this->actingAs($admin)->get(route('admin.newsletters.news.edit', $newsletter));
        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('Admin/Newsletters/News')->has('news', 3));
    }

    public function test_the_screen_brings_the_categories_for_the_period_and_category_filter(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        Category::create(['name' => 'Tecnologia']);
        Category::create(['name' => 'Eventos']);

        $response = $this->actingAs($admin)->get(route('admin.newsletters.news.edit', $newsletter));

        $response->assertInertia(fn (Assert $page) => $page->component('Admin/Newsletters/News')->has('categories', 2));
    }

    public function test_guests_cannot_access_the_news_selection(): void
    {
        $newsletter = Newsletter::factory()->create();

        $this->get(route('admin.newsletters.news.edit', $newsletter))->assertRedirect(route('login'));
        $this->put(route('admin.newsletters.news.update', $newsletter), ['news_ids' => []])
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_select_news_for_a_newsletter(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $news = News::factory()->count(3)->create(['status' => 'accepted']);

        $response = $this->actingAs($admin)
            ->from(route('admin.newsletters.news.edit', $newsletter))
            ->put(route('admin.newsletters.news.update', $newsletter), [
                'news_ids' => $news->pluck('id')->all(),
            ]);

        $this->assertEqualsCanonicalizing(
            $news->pluck('id')->all(),
            $newsletter->fresh()->news->pluck('id')->all()
        );
        $response->assertRedirect(route('admin.newsletters.news.edit', $newsletter));
        $response->assertSessionHas('success');
    }

    public function test_the_chosen_order_is_kept(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $primeira = News::factory()->create(['status' => 'accepted']);
        $segunda = News::factory()->create(['status' => 'accepted']);

        // enviadas ao contrário da ordem dos ids, para o teste não passar por acaso
        $this->actingAs($admin)->put(route('admin.newsletters.news.update', $newsletter), [
            'news_ids' => [$segunda->id, $primeira->id],
        ]);

        $this->assertSame(
            [$segunda->id, $primeira->id],
            $newsletter->fresh()->news()->orderByPivot('order')->pluck('news.id')->all()
        );
    }

    public function test_a_new_selection_replaces_the_previous_one(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $antiga = News::factory()->create(['status' => 'accepted']);
        $nova = News::factory()->create(['status' => 'accepted']);
        $newsletter->news()->attach($antiga->id, ['order' => 1]);

        $this->actingAs($admin)->put(route('admin.newsletters.news.update', $newsletter), [
            'news_ids' => [$nova->id],
        ]);

        $this->assertSame([$nova->id], $newsletter->fresh()->news->pluck('id')->all());
    }

    public function test_submitting_without_news_ids_clears_the_selection(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $news = News::factory()->create(['status' => 'accepted']);
        $newsletter->news()->attach($news->id, ['order' => 1]);

        $this->actingAs($admin)->put(route('admin.newsletters.news.update', $newsletter), []);

        $this->assertCount(0, $newsletter->news()->get());
    }

    public function test_news_that_are_not_approved_cannot_be_selected(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $porModerar = News::factory()->create(['status' => 'received']);
        $recusada = News::factory()->create(['status' => 'refused']);

        // a regra vive no servidor e não só no ecrã: um pedido feito à mão ao
        // endpoint não pode meter na newsletter uma notícia que ninguém aprovou
        $response = $this->actingAs($admin)->put(route('admin.newsletters.news.update', $newsletter), [
            'news_ids' => [$porModerar->id, $recusada->id],
        ]);

        $response->assertSessionHasErrors(['news_ids.0', 'news_ids.1']);
        $this->assertCount(0, $newsletter->news()->get());
    }

    public function test_selecting_news_that_does_not_exist_fails_validation(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();

        $response = $this->actingAs($admin)->put(route('admin.newsletters.news.update', $newsletter), [
            'news_ids' => [99999],
        ]);

        $response->assertSessionHasErrors('news_ids.0');
    }

    // ---- Escolha das imagens que saem, por edição (SCRUM-143) ----

    public function test_the_screen_sends_each_news_images_and_the_current_choice(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $news = News::factory()->withImages(3)->create(['status' => 'accepted']);
        $chosen = $news->images->pluck('id')->take(2)->all();
        $newsletter->news()->attach($news->id, ['order' => 1, 'image_ids' => $chosen]);

        $response = $this->actingAs($admin)->get(route('admin.newsletters.news.edit', $newsletter));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('news.0.images', 3)
            ->where('selected_images.'.$news->id, $chosen)
        );
    }

    public function test_the_chosen_images_are_saved_in_the_pivot(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $news = News::factory()->withImages(3)->create(['status' => 'accepted']);
        $chosen = $news->images->pluck('id')->take(2)->all();

        $this->actingAs($admin)->put(route('admin.newsletters.news.update', $newsletter), [
            'news_ids' => [$news->id],
            'image_ids' => [$news->id => $chosen],
        ]);

        $this->assertSame($chosen, $newsletter->fresh()->news->first()->pivot->image_ids);
    }

    public function test_the_image_order_is_the_order_they_were_chosen(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $news = News::factory()->withImages(3)->create(['status' => 'accepted']);
        $reversed = $news->images->pluck('id')->reverse()->values()->all();

        $this->actingAs($admin)->put(route('admin.newsletters.news.update', $newsletter), [
            'news_ids' => [$news->id],
            'image_ids' => [$news->id => $reversed],
        ]);

        $this->assertSame($reversed, $newsletter->fresh()->news->first()->pivot->image_ids);
    }

    public function test_changing_the_image_selection_replaces_the_previous(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $news = News::factory()->withImages(3)->create(['status' => 'accepted']);
        $ids = $news->images->pluck('id')->all();
        $newsletter->news()->attach($news->id, ['order' => 1, 'image_ids' => [$ids[0], $ids[1]]]);

        $this->actingAs($admin)->put(route('admin.newsletters.news.update', $newsletter), [
            'news_ids' => [$news->id],
            'image_ids' => [$news->id => [$ids[2]]],
        ]);

        $this->assertSame([$ids[2]], $newsletter->fresh()->news->first()->pivot->image_ids);
    }

    public function test_a_news_can_be_selected_with_no_images(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $news = News::factory()->withImages(3)->create(['status' => 'accepted']);

        $this->actingAs($admin)->put(route('admin.newsletters.news.update', $newsletter), [
            'news_ids' => [$news->id],
            'image_ids' => [$news->id => []],
        ]);

        $this->assertSame([], $newsletter->fresh()->news->first()->pivot->image_ids);
    }

    public function test_images_that_belong_to_another_news_are_dropped(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $news = News::factory()->withImages(2)->create(['status' => 'accepted']);
        $other = News::factory()->withImages(2)->create(['status' => 'accepted']);

        $mine = $news->images->first()->id;
        $foreign = $other->images->first()->id;

        $this->actingAs($admin)->put(route('admin.newsletters.news.update', $newsletter), [
            'news_ids' => [$news->id],
            'image_ids' => [$news->id => [$mine, $foreign]],
        ]);

        $this->assertSame([$mine], $newsletter->fresh()->news->first()->pivot->image_ids);
    }

    public function test_more_than_three_images_per_news_is_rejected(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $news = News::factory()->create(['status' => 'accepted']);

        $response = $this->actingAs($admin)->put(route('admin.newsletters.news.update', $newsletter), [
            'news_ids' => [$news->id],
            'image_ids' => [$news->id => [1, 2, 3, 4]],
        ]);

        $response->assertSessionHasErrors('image_ids.'.$news->id);
    }

    public function test_the_preview_shows_only_the_chosen_images(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $news = News::factory()->withImages(3)->create(['status' => 'accepted']);
        $chosen = $news->images->pluck('id')->take(2)->all();
        $newsletter->news()->attach($news->id, ['order' => 1, 'image_ids' => $chosen]);

        $response = $this->actingAs($admin)->get(route('admin.newsletters.preview', $newsletter));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('newsletter.news.0.images', 2)
            ->where('newsletter.news.0.images.0.id', $chosen[0])
            ->where('newsletter.news.0.images.1.id', $chosen[1])
        );
    }

    public function test_the_preview_hides_the_image_when_none_were_chosen(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $news = News::factory()->withImages(3)->create(['status' => 'accepted']);
        $newsletter->news()->attach($news->id, ['order' => 1, 'image_ids' => []]);

        $response = $this->actingAs($admin)->get(route('admin.newsletters.preview', $newsletter));

        $response->assertInertia(fn (Assert $page) => $page->has('newsletter.news.0.images', 0));
    }

    public function test_a_draft_without_a_saved_choice_keeps_the_mirror_image(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $news = News::factory()->withImages(3)->create(['status' => 'accepted']);
        // pivot sem image_ids: o estado das edições anteriores ao SCRUM-143
        $newsletter->news()->attach($news->id, ['order' => 1]);

        $response = $this->actingAs($admin)->get(route('admin.newsletters.preview', $newsletter));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('newsletter.news.0.images', 1)
            ->where('newsletter.news.0.images.0.path', $news->fresh()->image)
        );
    }

    // ---- Filtro por período pela data do evento (SCRUM-145) ----

    /**
     * O cliente sublinhou isto na reunião de 02/09: o filtro por período tem
     * de olhar para quando o evento aconteceu, não para quando a notícia foi
     * submetida.
     *
     * O filtro em si é JavaScript e não tem rede. O que se pode travar é o
     * degrau antes: que a coluna sai do editNews, que restringe as colunas à
     * mão. Sem ela, o filtro compara contra undefined — as notícias somem da
     * lista e não há erro nenhum a dizer porquê.
     */
    public function test_the_selection_screen_carries_the_event_date(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();

        News::factory()->create([
            'status' => 'accepted',
            'event_start_date' => '2026-05-12',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.newsletters.news.edit', $newsletter))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->where('news.0.event_start_date', fn ($data) => str_starts_with($data, '2026-05-12'))
            );
    }

    /**
     * A data do evento é independente da de submissão: uma notícia submetida
     * hoje pode ser sobre um evento de há meses. É esse o caso que o filtro
     * tem de saber distinguir, e é o que se partia se alguém voltasse a usar
     * o created_at.
     */
    public function test_the_event_date_is_independent_of_the_submission_date(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();

        $news = News::factory()->create([
            'status' => 'accepted',
            'event_start_date' => '2026-01-20',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.newsletters.news.edit', $newsletter))
            ->assertInertia(function (Assert $page) use ($news) {
                $page->where('news.0.event_start_date', fn ($data) => str_starts_with($data, '2026-01-20'));

                $this->assertNotSame(
                    $news->created_at->format('Y-m-d'),
                    '2026-01-20',
                    'A notícia tem de ter sido submetida noutro dia, senão o teste não distingue as duas datas.'
                );
            });
    }
}
