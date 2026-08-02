<?php

namespace Database\Factories;

use App\Models\Calendar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Calendar>
 */
class CalendarFactory extends Factory
{
    public function definition(): array
    {
        return [
            'date' => fake()->dateTimeBetween('-1 month', '+2 months'),
            'title' => fake()->sentence(3),
        ];
    }
}
