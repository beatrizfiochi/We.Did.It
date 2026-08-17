<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TestimonialCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_any_testimonial_management_route(): void
    {
        $testimonial = Testimonial::factory()->create();
        $category = Category::create(['name' => 'Testemunho interno']);

        $this->get(route('admin.testimonials.index'))->assertRedirect(route('login'));
        $this->patch(route('admin.testimonials.category', $testimonial), ['category_id' => $category->id])
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('testimonials', ['id' => $testimonial->id, 'category_id' => $testimonial->category_id]);
    }

    public function test_authenticated_users_can_see_the_testimonial_list_with_categories(): void
    {
        $admin = User::factory()->create();
        Testimonial::factory()->count(3)->create();
        Category::create(['name' => 'Testemunho externo']);

        $response = $this->actingAs($admin)->get(route('admin.testimonials.index'));

        $response->assertOk();
        // a listagem é paginada, por isso os registos vêm em testimonials.data
        $response->assertInertia(fn (Assert $page) => $page->component('Admin/Testimonials/Index')
            ->has('testimonials.data', 3)
            ->has('categories')
        );
    }

    public function test_authenticated_users_can_categorize_a_testimonial(): void
    {
        $admin = User::factory()->create();
        $testimonial = Testimonial::factory()->create(['category_id' => null]);
        $category = Category::create(['name' => 'Testemunho interno']);

        // o controller responde com back(): o from() é o que faz o redirect
        // voltar para a listagem, como acontece no browser
        $response = $this->actingAs($admin)
            ->from(route('admin.testimonials.index'))
            ->patch(route('admin.testimonials.category', $testimonial), [
                'category_id' => $category->id,
            ]);

        $this->assertDatabaseHas('testimonials', ['id' => $testimonial->id, 'category_id' => $category->id]);
        $response->assertRedirect(route('admin.testimonials.index'));
        $response->assertSessionHas('success');
    }

    public function test_categorizing_a_testimonial_requires_an_existing_category(): void
    {
        $admin = User::factory()->create();
        $testimonial = Testimonial::factory()->create();

        $response = $this->actingAs($admin)->patch(route('admin.testimonials.category', $testimonial), [
            'category_id' => 99999,
        ]);

        $response->assertSessionHasErrors(['category_id']);
    }
}
