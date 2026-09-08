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
                    ->has('testimonials', 1)
                    ->where('testimonials.0.name', 'Maria Silva')
            );
    }

    public function test_the_list_includes_every_submitted_image(): void
    {
        // não só a coluna espelho: o modal de moderação mostra as imagens
        // todas, para o gestor poder remover uma sem recusar a submissão
        // inteira (SCRUM-142)
        Testimonial::factory()->withImages(3)->create();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.testimonials.index'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has('testimonials.0.images', 3)
                    ->has('testimonials.0.images.0.path')
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

    /**
     * Um testemunho leva uma imagem só. A acumulação continua a ser testada
     * do lado das notícias, no NewsModerationTest, onde o limite são três.
     */
    public function test_a_second_image_is_refused(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('testimonials/antiga.jpg', 'conteudo');

        // a factory já cria a linha em images sozinha quando 'image' vem preenchido
        $testimonial = Testimonial::factory()->create(['image' => 'testimonials/antiga.jpg']);

        $this->actingAs(User::factory()->create())
            ->put(route('admin.testimonials.update', $testimonial), $this->validPayload([
                'images' => [UploadedFile::fake()->create('nova.jpg', 100, 'image/jpeg')],
            ]))
            ->assertSessionHasErrors('images');

        // a que já lá estava não é tocada
        Storage::disk('public')->assertExists('testimonials/antiga.jpg');
        $this->assertSame(1, $testimonial->fresh()->images()->count());
    }

    public function test_the_limit_counts_images_already_saved(): void
    {
        Storage::fake('public');

        // a única vaga já está ocupada
        $testimonial = Testimonial::factory()->withImages(1)->create();

        $response = $this->actingAs(User::factory()->create())
            ->put(route('admin.testimonials.update', $testimonial), $this->validPayload([
                'images' => [UploadedFile::fake()->create('nova.jpg', 100, 'image/jpeg')],
            ]));

        $response->assertSessionHasErrors('images');
        $this->assertSame(
            'Esta submissão só pode ter uma imagem. Remove uma antes de acrescentar.',
            session('errors')->get('images')[0],
        );
        $this->assertSame(1, $testimonial->fresh()->images()->count());
    }
}
