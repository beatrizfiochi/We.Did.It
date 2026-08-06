<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\News;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<News>
 */
class NewsFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::inRandomOrder()->value('id'),
            'title' => fake()->sentence(6),
            'description' => fake()->paragraphs(3, true),
            'image' => fake()->boolean(70) ? 'news/'.fake()->uuid().'.jpg' : null,
            'status' => fake()->randomElement(['received', 'received', 'received', 'accepted', 'accepted', 'refused']),
        ];
    }
}
