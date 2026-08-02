<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->value('id'),
            'table_name' => fake()->randomElement(['news', 'testimonials', 'courses', 'newsletters', 'calendars']),
            'record_id' => fake()->numberBetween(1, 20),
            'operation' => fake()->randomElement(['created', 'updated', 'removed']),
        ];
    }
}
