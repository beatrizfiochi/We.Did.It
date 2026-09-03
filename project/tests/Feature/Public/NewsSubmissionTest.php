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
            'images' => [UploadedFile::fake()->image('foto.jpg')],
        ]))->assertSessionHasErrors('image_rights');

        $this->assertDatabaseCount('news', 0);
    }

    public function test_an_authorised_image_is_accepted(): void
    {
        Storage::fake('public');

        $this->post(route('news.store'), $this->validPayload([
            'images' => [UploadedFile::fake()->image('foto.jpg')],
            'image_rights' => 'on',
        ]))->assertSessionHasNoErrors();

        $news = News::first();

        $this->assertNotNull($news);
        $this->assertSame(1, $news->images()->count());
        $this->assertSame($news->images()->value('path'), $news->image);
    }

    public function test_up_to_three_images_are_stored_in_order(): void
    {
        Storage::fake('public');

        $this->post(route('news.store'), $this->validPayload([
            'images' => [
                UploadedFile::fake()->image('a.jpg'),
                UploadedFile::fake()->image('b.jpg'),
                UploadedFile::fake()->image('c.jpg'),
            ],
            'image_rights' => 'on',
        ]))->assertSessionHasNoErrors();

        $news = News::first();

        $this->assertSame(3, $news->images()->count());
        $this->assertSame([1, 2, 3], $news->images()->orderBy('order')->pluck('order')->all());
        // a coluna antiga continua a espelhar a primeira, para os ecrãs que ainda a leem
        $this->assertSame($news->images()->orderBy('order')->value('path'), $news->image);
    }

    public function test_a_fourth_image_is_rejected(): void
    {
        Storage::fake('public');

        $this->post(route('news.store'), $this->validPayload([
            'images' => [
                UploadedFile::fake()->image('a.jpg'),
                UploadedFile::fake()->image('b.jpg'),
                UploadedFile::fake()->image('c.jpg'),
                UploadedFile::fake()->image('d.jpg'),
            ],
            'image_rights' => 'on',
        ]))->assertSessionHasErrors('images');

        $this->assertDatabaseCount('news', 0);
    }

    public function test_an_invalid_file_among_valid_ones_rejects_the_whole_submission(): void
    {
        Storage::fake('public');

        $this->post(route('news.store'), $this->validPayload([
            'images' => [
                UploadedFile::fake()->image('a.jpg'),
                UploadedFile::fake()->create('b.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->image('c.jpg'),
            ],
            'image_rights' => 'on',
        ]))->assertSessionHasErrors('images.1');

        $this->assertDatabaseCount('news', 0);
        // nenhum dos três ficheiros pode ficar órfão no disco: a validação
        // falha antes de o storeImages() correr, por isso nada devia ter sido gravado
        Storage::disk('public')->assertDirectoryEmpty('news');
    }

    public function test_the_consents_are_not_stored(): void
    {
        $this->post(route('news.store'), $this->validPayload());

        $atributos = News::first()->getAttributes();

        $this->assertArrayNotHasKey('terms_conditions', $atributos);
        $this->assertArrayNotHasKey('image_rights', $atributos);
    }
}
