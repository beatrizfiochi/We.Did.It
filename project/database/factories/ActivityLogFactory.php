<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\Calendar;
use App\Models\Course;
use App\Models\News;
use App\Models\Newsletter;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    public function definition(): array
    {
        $tables = [
            'news' => News::class,
            'testimonials' => Testimonial::class,
            'courses' => Course::class,
            'newsletters' => Newsletter::class,
            'calendars' => Calendar::class,
        ];

        $tableName = fake()->randomElement(array_keys($tables));

        return [
            'user_id' => User::factory(),
            'table_name' => $tableName,
            'record_id' => $tables[$tableName]::inRandomOrder()->value('id'),
            'operation' => fake()->randomElement(['created', 'updated', 'removed']),
        ];
    }
}
