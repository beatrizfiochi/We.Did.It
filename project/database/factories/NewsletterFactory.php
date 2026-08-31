<?php

namespace Database\Factories;

use App\Models\Newsletter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Newsletter>
 */
class NewsletterFactory extends Factory
{
    public function definition(): array
    {
        $periodStart = fake()->dateTimeBetween('-1 year', 'now');
        $periodEnd = (clone $periodStart)->modify('+7 days');

        return [
            'title' => 'Newsletter '.$periodStart->format('F').' '.$periodStart->format('Y'),
            'edition' => fake()->unique()->numberBetween(1, 500),
            'date' => (clone $periodEnd)->modify('+1 day'),
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'path' => null,
            // uma newsletter nasce sempre em rascunho, como no store() do
            // controller. Sortear o estado tornava intermitentes os testes que
            // dependem de a newsletter ser editável (SCRUM-116).
            'status' => Newsletter::RASCUNHO,
        ];
    }

    /**
     * Newsletter já publicada, para os testes de bloqueio de edição.
     */
    public function published(): static
    {
        return $this->state(fn () => ['status' => Newsletter::PUBLICADA]);
    }
}
