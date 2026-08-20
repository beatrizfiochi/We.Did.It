<?php

namespace Tests\Feature\Public;

use App\Models\Category;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TestimonialSubmissionTest extends TestCase
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

    public function test_the_form_is_public_and_lists_the_categories(): void
    {
        Category::create(['name' => 'Formação']);

        $this->get(route('testimonials.create'))
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Testimonial/TestimonialForm')
                    ->has('categories', 1)
                    ->where('categories.0.name', 'Formação')
            );
    }

    public function test_a_visitor_can_submit_a_testimonial(): void
    {
        $this->post(route('testimonials.store'), $this->validPayload())
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('testimonials', [
            'name' => 'Maria Silva',
            'email' => 'maria@exemplo.pt',
            'status' => 'received',
        ]);
    }

    public function test_a_submitted_testimonial_always_starts_as_received(): void
    {
        // o status não está nas rules, por isso não pode ser forçado pelo request
        $this->post(route('testimonials.store'), $this->validPayload([
            'status' => 'accepted',
        ]));

        $this->assertSame('received', Testimonial::first()->status);
    }

    public function test_name_and_email_are_required(): void
    {
        $this->post(route('testimonials.store'), $this->validPayload([
            'name' => '',
            'email' => '',
        ]))->assertSessionHasErrors(['name', 'email']);

        $this->assertDatabaseCount('testimonials', 0);
    }

    public function test_the_email_must_be_valid(): void
    {
        $this->post(route('testimonials.store'), $this->validPayload([
            'email' => 'isto-nao-e-um-email',
        ]))->assertSessionHasErrors('email');
    }

    public function test_the_title_and_description_have_length_limits(): void
    {
        $this->post(route('testimonials.store'), $this->validPayload([
            'title' => 'Curto',
            'description' => 'Demasiado curta.',
        ]))->assertSessionHasErrors(['title', 'description']);
    }

    public function test_an_empty_category_is_stored_as_null(): void
    {
        $this->post(route('testimonials.store'), $this->validPayload(['category_id' => '']));

        $this->assertNull(Testimonial::first()->category_id);
    }

    public function test_an_unknown_category_is_rejected(): void
    {
        $this->post(route('testimonials.store'), $this->validPayload(['category_id' => 999]))
            ->assertSessionHasErrors('category_id');
    }

    public function test_the_image_is_stored_on_the_public_disk(): void
    {
        Storage::fake('public');

        // create() com o mime à mão em vez de image(): o image() precisa da
        // extensão GD, que não está instalada, e a validação olha para o mime
        $this->post(route('testimonials.store'), $this->validPayload([
            'image' => UploadedFile::fake()->create('testemunho.jpg', 100, 'image/jpeg'),
        ]))->assertSessionHasNoErrors();

        $path = Testimonial::first()->image;

        $this->assertStringStartsWith('testimonials/', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_a_file_that_is_not_an_image_is_rejected(): void
    {
        Storage::fake('public');

        $this->post(route('testimonials.store'), $this->validPayload([
            'image' => UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf'),
        ]))->assertSessionHasErrors('image');

        $this->assertDatabaseCount('testimonials', 0);
    }

    public function test_an_image_over_five_megabytes_is_rejected(): void
    {
        Storage::fake('public');

        $this->post(route('testimonials.store'), $this->validPayload([
            'image' => UploadedFile::fake()->create('grande.jpg', 5121, 'image/jpeg'),
        ]))->assertSessionHasErrors('image');
    }

    public function test_the_honeypot_field_blocks_the_submission(): void
    {
        $response = $this->post(route('testimonials.store'), $this->validPayload([
            'website' => 'http://spam.example.com',
        ]));

        $response->assertSessionHasErrors('website');
        $this->assertDatabaseCount('testimonials', 0);
    }

    public function test_the_honeypot_field_is_not_required(): void
    {
        $response = $this->post(route('testimonials.store'), $this->validPayload(['website' => '']));

        $response->assertSessionDoesntHaveErrors('website');
        $this->assertDatabaseCount('testimonials', 1);
    }

    public function test_submissions_are_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('testimonials.store'), $this->validPayload([
                'title' => "Testemunho numero {$i} com titulo valido",
            ]))->assertStatus(302);
        }

        $response = $this->post(route('testimonials.store'), $this->validPayload([
            'title' => 'Testemunho extra que deve ser bloqueado',
        ]));

        $response->assertStatus(429);
        $this->assertDatabaseCount('testimonials', 5);
    }
}
