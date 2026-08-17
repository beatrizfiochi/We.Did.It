<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class NewsCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_any_news_management_route(): void
    {
        $news = News::factory()->create();

        $this->get(route('admin.news.index'))->assertRedirect(route('login'));
        $this->get(route('admin.news.edit', $news))->assertRedirect(route('login'));
        $this->put(route('admin.news.update', $news), ['title' => 'x', 'description' => 'y'])->assertRedirect(route('login'));
        $this->patch(route('admin.news.approve', $news))->assertRedirect(route('login'));
        $this->patch(route('admin.news.refuse', $news))->assertRedirect(route('login'));

        $this->assertDatabaseHas('news', ['id' => $news->id, 'status' => $news->status]);
    }

    public function test_authenticated_users_can_see_the_news_list(): void
    {
        $admin = User::factory()->create();
        News::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('admin.news.index'));

        $response->assertOk();
        // a listagem é paginada, por isso os registos vêm em news.data
        $response->assertInertia(fn (Assert $page) => $page->component('Admin/News/Index')
            ->has('news.data', 3)
        );
    }

    public function test_authenticated_users_can_see_the_edit_form_with_categories(): void
    {
        $admin = User::factory()->create();
        $news = News::factory()->create();
        Category::create(['name' => 'Divulgação']);

        $response = $this->actingAs($admin)->get(route('admin.news.edit', $news));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('Admin/News/Edit')
            ->where('news.id', $news->id)
            ->has('categories')
        );
    }

    public function test_authenticated_users_can_categorize_a_news_when_editing(): void
    {
        $admin = User::factory()->create();
        $news = News::factory()->create(['category_id' => null]);
        $category = Category::create(['name' => 'Estágios']);

        // o controller responde com back(): o from() é o que faz o redirect
        // voltar para a listagem, como acontece no browser
        $response = $this->actingAs($admin)
            ->from(route('admin.news.index'))
            ->put(route('admin.news.update', $news), [
                'title' => $news->title,
                'description' => $news->description,
                'category_id' => $category->id,
            ]);

        $this->assertDatabaseHas('news', ['id' => $news->id, 'category_id' => $category->id]);
        $response->assertRedirect(route('admin.news.index'));
        $response->assertSessionHas('success');
    }

    public function test_updating_a_news_requires_the_mandatory_fields(): void
    {
        $admin = User::factory()->create();
        $news = News::factory()->create();

        $response = $this->actingAs($admin)->put(route('admin.news.update', $news), []);

        $response->assertSessionHasErrors(['title', 'description']);
    }

    public function test_authenticated_users_can_approve_a_news(): void
    {
        $admin = User::factory()->create();
        $news = News::factory()->create(['status' => 'received']);

        $response = $this->actingAs($admin)
            ->from(route('admin.news.index'))
            ->patch(route('admin.news.approve', $news));

        $this->assertDatabaseHas('news', ['id' => $news->id, 'status' => 'accepted']);
        $response->assertRedirect(route('admin.news.index'));
        $response->assertSessionHas('success');
    }

    public function test_authenticated_users_can_refuse_a_news(): void
    {
        $admin = User::factory()->create();
        $news = News::factory()->create(['status' => 'received']);

        $response = $this->actingAs($admin)
            ->from(route('admin.news.index'))
            ->patch(route('admin.news.refuse', $news));

        $this->assertDatabaseHas('news', ['id' => $news->id, 'status' => 'refused']);
        $response->assertRedirect(route('admin.news.index'));
        $response->assertSessionHas('success');
    }
}
