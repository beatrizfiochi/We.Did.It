<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\News;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_see_the_category_list(): void
    {
        Category::create(['name' => 'Tecnologia']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Admin/Categories/Index')
                    ->has('categories', 1)
                    ->where('categories.0.name', 'Tecnologia')
                    ->where('categories.0.news_count', 0)
            );
    }

    public function test_guests_cannot_manage_categories(): void
    {
        $category = Category::create(['name' => 'Tecnologia']);

        $this->get(route('admin.categories.index'))
            ->assertRedirect(route('login'));

        $this->post(route('admin.categories.store'), ['name' => 'Nova'])
            ->assertRedirect(route('login'));

        $this->put(route('admin.categories.update', $category), ['name' => 'Outra'])
            ->assertRedirect(route('login'));

        $this->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('categories', 1);
    }

    public function test_authenticated_users_can_create_a_category(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.categories.store'), ['name' => 'Tecnologia'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('categories', ['name' => 'Tecnologia']);
    }

    public function test_creating_a_category_is_logged(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.categories.store'), ['name' => 'Tecnologia']);

        $this->assertDatabaseHas('logs', [
            'user_id' => $user->id,
            'table_name' => 'categories',
            'record_id' => Category::first()->id,
            'operation' => 'created',
        ]);
    }

    public function test_category_name_is_required(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.categories.store'), ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('categories', 0);
    }

    public function test_category_name_must_have_at_least_two_characters(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.categories.store'), ['name' => 'a'])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('categories', 0);
    }

    public function test_a_category_can_have_a_short_name(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.categories.store'), ['name' => 'TI'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('categories', ['name' => 'TI']);
    }

    public function test_category_name_must_be_unique(): void
    {
        Category::create(['name' => 'Tecnologia']);

        $this->actingAs(User::factory()->create())
            ->post(route('admin.categories.store'), ['name' => 'Tecnologia'])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('categories', 1);
    }

    public function test_authenticated_users_can_update_a_category(): void
    {
        $category = Category::create(['name' => 'Tecnologia']);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('admin.categories.update', $category), ['name' => 'Inovação'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Inovação']);
        $this->assertDatabaseHas('logs', [
            'user_id' => $user->id,
            'table_name' => 'categories',
            'record_id' => $category->id,
            'operation' => 'updated',
        ]);
    }

    public function test_a_category_can_be_saved_without_changing_its_name(): void
    {
        $category = Category::create(['name' => 'Tecnologia']);

        // a regra unique tem de ignorar a própria linha, senão isto falhava
        $this->actingAs(User::factory()->create())
            ->put(route('admin.categories.update', $category), ['name' => 'Tecnologia'])
            ->assertSessionHasNoErrors();
    }

    public function test_an_unused_category_can_be_deleted(): void
    {
        $category = Category::create(['name' => 'Tecnologia']);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->delete(route('admin.categories.destroy', $category))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        $this->assertDatabaseHas('logs', [
            'user_id' => $user->id,
            'table_name' => 'categories',
            'record_id' => $category->id,
            'operation' => 'removed',
        ]);
    }

    public function test_a_category_used_by_news_cannot_be_deleted(): void
    {
        $category = Category::create(['name' => 'Tecnologia']);
        News::factory()->create(['category_id' => $category->id]);

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.categories.destroy', $category))
            ->assertSessionHasErrors('category');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_a_category_used_by_testimonials_cannot_be_deleted(): void
    {
        $category = Category::create(['name' => 'Tecnologia']);
        Testimonial::factory()->create(['category_id' => $category->id]);

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.categories.destroy', $category))
            ->assertSessionHasErrors('category');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_a_failed_deletion_is_not_logged(): void
    {
        $category = Category::create(['name' => 'Tecnologia']);
        News::factory()->create(['category_id' => $category->id]);

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.categories.destroy', $category));

        $this->assertDatabaseMissing('logs', [
            'record_id' => $category->id,
            'table_name' => 'categories',
            'operation' => 'removed',
        ]);
    }
}
