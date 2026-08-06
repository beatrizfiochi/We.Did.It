<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'url' => fake()->url(),
            'imageUrl' => fake()->boolean(70) ? 'courses/'.fake()->uuid().'.jpg' : null,
            'location' => fake()->boolean(80) ? fake()->city() : null,
            'schedule' => fake()->boolean(80) ? fake()->dayOfWeek().' '.fake()->time('H:i') : null,
            'start_date' => fake()->dateTimeBetween('now', '+6 months')->format('Y-m-d'),
            'price' => number_format(fake()->randomFloat(2, 50, 2000), 2, '.', ''),
            'status' => 'received',
        ];
    }
}
