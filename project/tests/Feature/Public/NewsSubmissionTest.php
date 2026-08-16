<?php

namespace Tests\Feature\Public;

use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Uma notícia qualquer com título válido',
            'description' => str_repeat('Um parágrafo com bastante conteúdo. ', 5),
        ], $overrides);
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
}
