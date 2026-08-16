<?php

namespace Tests\Feature\Admin;

use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TestimonialModerationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Maria Silva',
            'email' => 'maria@exemplo.pt',
            'title' => 'A formação mudou o meu percurso',
            'description' => str_repeat('Foi uma experiência muito positiva. ', 5),
            'category_id' => '',
        ], $overrides);
    }

    public function test_guests_cannot_moderate_testimonials(): void
    {
        $testimonial = Testimonial::factory()->create(['status' => 'received']);

        $this->get(route('admin.testimonials.index'))->assertRedirect(route('login'));
        $this->put(route('admin.testimonials.update', $testimonial), $this->validPayload())
            ->assertRedirect(route('login'));
        $this->patch(route('admin.testimonials.approve', $testimonial))
            ->assertRedirect(route('login'));
        $this->patch(route('admin.testimonials.refuse', $testimonial))
            ->assertRedirect(route('login'));

        $this->assertSame('received', $testimonial->fresh()->status);
    }

    public function test_the_list_shows_the_submitted_testimonials(): void
    {
        Testimonial::factory()->create(['name' => 'Maria Silva']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.testimonials.index'))
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Admin/Testimonials/Index')
                    ->has('testimonials.data', 1)
                    ->where('testimonials.data.0.name', 'Maria Silva')
            );
    }

    public function test_testimonials_can_be_approved(): void
    {
        $testimonial = Testimonial::factory()->create(['status' => 'received']);
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('admin.testimonials.approve', $testimonial));

        $this->assertSame('accepted', $testimonial->fresh()->status);
        $this->assertDatabaseHas('logs', [
            'user_id' => $user->id,
            'table_name' => 'testimonials',
            'record_id' => $testimonial->id,
            'operation' => 'updated',
        ]);
    }

    public function test_testimonials_can_be_refused(): void
    {
        $testimonial = Testimonial::factory()->create(['status' => 'received']);

        $this->actingAs(User::factory()->create())
            ->patch(route('admin.testimonials.refuse', $testimonial));

        $this->assertSame('refused', $testimonial->fresh()->status);
    }

    public function test_testimonial_content_can_be_updated(): void
    {
        $testimonial = Testimonial::factory()->create();

        $this->actingAs(User::factory()->create())
            ->put(route('admin.testimonials.update', $testimonial), $this->validPayload([
                'title' => 'Um título corrigido pela moderação',
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame('Um título corrigido pela moderação', $testimonial->fresh()->title);
    }

    public function test_updating_the_content_cannot_change_the_status(): void
    {
        $testimonial = Testimonial::factory()->create(['status' => 'received']);

        $this->actingAs(User::factory()->create())
            ->put(route('admin.testimonials.update', $testimonial), $this->validPayload([
                'status' => 'accepted',
            ]));

        $this->assertSame('received', $testimonial->fresh()->status);
    }

    public function test_a_new_image_replaces_the_old_one_on_disk(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('testimonials/antiga.jpg', 'conteudo');

        $testimonial = Testimonial::factory()->create(['image' => 'testimonials/antiga.jpg']);

        $this->actingAs(User::factory()->create())
            ->put(route('admin.testimonials.update', $testimonial), $this->validPayload([
                'image' => UploadedFile::fake()->create('nova.jpg', 100, 'image/jpeg'),
            ]))
            ->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing('testimonials/antiga.jpg');
        Storage::disk('public')->assertExists($testimonial->fresh()->image);
    }
}
