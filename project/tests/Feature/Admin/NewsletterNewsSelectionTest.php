<?php

namespace Tests\Feature\Admin;

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
}
