<?php

namespace Tests\Feature\Public;

use App\Models\Category;
use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class NewsSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Uma notícia qualquer com título válido',
            'description' => str_repeat('Um parágrafo com bastante conteúdo. ', 5),
            // o cliente exige consentimento explícito; é validado mas não guardado
            'terms_conditions' => 'on',
        ], $overrides);
    }

    public function test_the_form_is_public_and_lists_the_categories(): void
    {
        Category::create(['name' => 'Formação']);

        $this->get(route('news.create'))
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('News/InsertForm')
                    ->has('categories', 1)
                    ->where('categories.0.name', 'Formação')
            );
    }

    public function test_a_visitor_can_submit_a_news_article(): void
    {
        $response = $this->post(route('news.store'), $this->validPayload());

        $this->assertDatabaseHas('news', ['title' => $this->validPayload()['title']]);
        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_the_honeypot_field_blocks_the_submission(): void
    {
        $response = $this->post(route('news.store'), $this->validPayload([
            'website' => 'http://spam.example.com',
        ]));

        $response->assertSessionHasErrors('website');
        $this->assertDatabaseCount('news', 0);
    }

    public function test_the_honeypot_field_is_not_required(): void
    {
        $response = $this->post(route('news.store'), $this->validPayload(['website' => '']));

        $response->assertSessionDoesntHaveErrors('website');
        $this->assertDatabaseCount('news', 1);
    }

    public function test_submissions_are_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('news.store'), $this->validPayload(['title' => "Notícia número {$i} com título válido"]))
                ->assertStatus(302);
        }

        $response = $this->post(route('news.store'), $this->validPayload(['title' => 'Notícia extra que deve ser bloqueada']));

        $response->assertStatus(429);
        $this->assertDatabaseCount('news', 5);
    }

    public function test_the_submission_requires_accepting_the_privacy_policy(): void
    {
        $payload = $this->validPayload();
        unset($payload['terms_conditions']);

        $this->post(route('news.store'), $payload)
            ->assertSessionHasErrors('terms_conditions');

        $this->assertDatabaseCount('news', 0);
    }

    public function test_an_image_requires_authorising_its_use(): void
    {
        Storage::fake('public');

        $this->post(route('news.store'), $this->validPayload([
            'image' => UploadedFile::fake()->image('foto.jpg'),
        ]))->assertSessionHasErrors('image_rights');

        $this->assertDatabaseCount('news', 0);
    }

    public function test_an_authorised_image_is_accepted(): void
    {
        Storage::fake('public');

        $this->post(route('news.store'), $this->validPayload([
            'image' => UploadedFile::fake()->image('foto.jpg'),
            'image_rights' => 'on',
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseCount('news', 1);
    }

    public function test_the_consents_are_not_stored(): void
    {
        $this->post(route('news.store'), $this->validPayload());

        $atributos = News::first()->getAttributes();

        $this->assertArrayNotHasKey('terms_conditions', $atributos);
        $this->assertArrayNotHasKey('image_rights', $atributos);
    }
}
