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
            'status' => fake()->boolean(80),
        ];
    }
}
