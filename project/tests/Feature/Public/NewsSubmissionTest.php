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
            'event_start_date' => '2026-05-12',
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

    /**
     * O campo chamava-se 'image' antes da SCRUM-140.
     *
     * Enquanto o servidor se limitava a ignorá-lo, um formulário desatualizado
     * submetia com sucesso e a fotografia desaparecia sem erro nenhum — e o
     * consentimento de imagem deixava de ser avaliado, porque o
     * exclude_without:images não encontrava o campo.
     */
    public function test_the_old_singular_field_is_rejected_instead_of_ignored(): void
    {
        Storage::fake('public');

        $this->post(route('news.store'), $this->validPayload([
            'image' => UploadedFile::fake()->image('foto.jpg'),
            'image_rights' => 'on',
        ]))->assertSessionHasErrors('image');

        $this->assertDatabaseCount('news', 0);
    }

    /**
     * O cliente pediu na reunião de 02/09 que a notícia diga quando o evento
     * aconteceu, que é diferente de quando foi submetida (SCRUM-145).
     */
    public function test_the_event_date_is_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['event_start_date']);

        $this->post(route('news.store'), $payload)
            ->assertSessionHasErrors('event_start_date');

        $this->assertDatabaseCount('news', 0);
    }

    public function test_the_end_date_cannot_be_before_the_start(): void
    {
        $this->post(route('news.store'), $this->validPayload([
            'event_start_date' => '2026-05-12',
            'event_end_date' => '2026-05-10',
        ]))->assertSessionHasErrors('event_end_date');

        $this->assertDatabaseCount('news', 0);
    }

    /**
     * O fim a null é o que distingue um dia único de um intervalo — não há
     * coluna a dizer qual é qual, e por isso o null tem de ficar garantido.
     */
    public function test_a_single_day_event_stores_a_null_end_date(): void
    {
        $this->post(route('news.store'), $this->validPayload([
            'event_start_date' => '2026-05-12',
        ]))->assertSessionHasNoErrors();

        $news = News::first();

        $this->assertSame('2026-05-12', $news->event_start_date->format('Y-m-d'));
        $this->assertNull($news->event_end_date);
    }

    /**
     * O after_or_equal aceita fim == início, e o formulário nunca produz isso
     * porque deixa o campo vazio. Um pedido feito à mão produzia, e o cartão
     * da newsletter escrevia "12 a 12 de maio de 2026".
     */
    public function test_an_end_date_equal_to_the_start_is_stored_as_null(): void
    {
        $this->post(route('news.store'), $this->validPayload([
            'event_start_date' => '2026-05-12',
            'event_end_date' => '2026-05-12',
        ]))->assertSessionHasNoErrors();

        $news = News::first();

        $this->assertSame('2026-05-12', $news->event_start_date->format('Y-m-d'));
        $this->assertNull($news->event_end_date);
    }

    public function test_an_event_date_in_the_future_is_rejected(): void
    {
        $this->post(route('news.store'), $this->validPayload([
            'event_start_date' => now()->addDay()->format('Y-m-d'),
        ]))->assertSessionHasErrors('event_start_date');

        $this->assertDatabaseCount('news', 0);
    }

    public function test_a_date_range_is_stored_as_given(): void
    {
        $this->post(route('news.store'), $this->validPayload([
            'event_start_date' => '2026-05-12',
            'event_end_date' => '2026-05-15',
        ]))->assertSessionHasNoErrors();

        $news = News::first();

        $this->assertSame('2026-05-12', $news->event_start_date->format('Y-m-d'));
        $this->assertSame('2026-05-15', $news->event_end_date->format('Y-m-d'));
    }

    public function test_the_consents_are_not_stored(): void
    {
        $this->post(route('news.store'), $this->validPayload());

        $atributos = News::first()->getAttributes();

        $this->assertArrayNotHasKey('terms_conditions', $atributos);
        $this->assertArrayNotHasKey('image_rights', $atributos);
    }
}
