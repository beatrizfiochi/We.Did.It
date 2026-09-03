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

    public function configure(): static
    {
        return $this->afterCreating(function (Testimonial $testimonial) {
            // mantém a coluna antiga e a tabela nova a dizer o mesmo:
            // é o estado em que a migração de dados deixa a produção
            if ($testimonial->image) {
                $testimonial->images()->create(['path' => $testimonial->image, 'order' => 1]);
            }
        });
    }

    public function withImages(int $count = 3): static
    {
        return $this->state(['image' => null])->afterCreating(function (Testimonial $testimonial) use ($count) {
            $paths = collect(range(1, $count))->map(fn ($order) => [
                'path' => 'testimonials/'.fake()->uuid().'.jpg',
                'order' => $order,
            ]);

            $testimonial->images()->createMany($paths->all());
            $testimonial->update(['image' => $paths->first()['path']]);
        });
    }
}
