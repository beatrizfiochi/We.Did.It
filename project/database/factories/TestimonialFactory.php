<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::inRandomOrder()->value('id'),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'title' => fake()->sentence(5),
            'description' => fake()->paragraphs(2, true),
            'image' => fake()->boolean(60) ? 'testimonials/'.fake()->uuid().'.jpg' : null,
            'status' => fake()->randomElement(['received', 'received', 'received', 'accepted', 'accepted', 'refused']),
        ];
    }
}
