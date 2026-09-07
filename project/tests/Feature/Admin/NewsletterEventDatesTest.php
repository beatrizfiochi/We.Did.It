<?php

namespace Tests\Feature\Admin;

use App\Models\News;
use App\Models\Newsletter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * A data do evento tem de chegar aos ecrãs da newsletter (SCRUM-145).
 *
 * O cartão da notícia mostra-a, mas isso é JSX e não há como travá-lo por
 * teste. O que se pode travar é o degrau antes: que a data sai do servidor.
 * Se alguém restringir as colunas de uma destas consultas — como o editNews
 * já faz — o cartão fica sem data e ninguém dá por isso, porque a página
 * continua a abrir na mesma.
 */
class NewsletterEventDatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_preview_carries_the_event_dates_of_each_news(): void
    {
        $newsletter = Newsletter::factory()->create();
        $news = News::factory()->create([
            'status' => 'accepted',
            'event_start_date' => '2026-05-12',
            'event_end_date' => '2026-05-15',
        ]);
        $newsletter->news()->attach($news, ['order' => 1]);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.newsletters.preview', $newsletter))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->where('newsletter.news.0.event_start_date', fn ($data) => str_starts_with($data, '2026-05-12'))
                    ->where('newsletter.news.0.event_end_date', fn ($data) => str_starts_with($data, '2026-05-15'))
            );
    }

    /**
     * Num evento de um dia só o fim vem a null, e é isso que o cartão usa para
     * decidir entre "12 de maio" e "12 a 15 de maio".
     */
    public function test_a_single_day_event_reaches_the_preview_with_a_null_end(): void
    {
        $newsletter = Newsletter::factory()->create();
        $news = News::factory()->create([
            'status' => 'accepted',
            'event_start_date' => '2026-06-09',
            'event_end_date' => null,
        ]);
        $newsletter->news()->attach($news, ['order' => 1]);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.newsletters.preview', $newsletter))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->where('newsletter.news.0.event_end_date', null)
                    ->where('newsletter.news.0.event_start_date', fn ($data) => str_starts_with($data, '2026-06-09'))
            );
    }

    /**
     * O estado withDateRange da factory existe para os testes não terem de
     * escrever duas datas coerentes à mão de cada vez.
     */
    public function test_the_factory_range_state_produces_an_end_after_the_start(): void
    {
        // 30 e não um: o dateTimeBetween original devolvia a mesma data cerca
        // de 1 em 200 vezes, e uma corrida só não apanhava isso
        foreach (range(1, 30) as $i) {
            $news = News::factory()->withDateRange()->create();

            $this->assertNotNull($news->event_end_date);
            $this->assertTrue(
                $news->event_end_date->greaterThan($news->event_start_date),
                'O withDateRange gerou um intervalo de um dia só, que não é um intervalo.'
            );
        }
    }
}
