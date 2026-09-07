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
            'event_start_date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'event_end_date' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (News $news) {
            // mantém a coluna antiga e a tabela nova a dizer o mesmo:
            // é o estado em que a migração de dados deixa a produção
            if ($news->image) {
                $news->images()->create(['path' => $news->image, 'order' => 1]);
            }
        });
    }

    public function withImages(int $count = 3): static
    {
        return $this->state(['image' => null])->afterCreating(function (News $news) use ($count) {
            $paths = collect(range(1, $count))->map(fn ($order) => [
                'path' => 'news/'.fake()->uuid().'.jpg',
                'order' => $order,
            ]);

            $news->images()->createMany($paths->all());
            $news->update(['image' => $paths->first()['path']]);
        });
    }

    public function withDateRange(): static
    {
        return $this->state(function () {
            $inicio = fake()->dateTimeBetween('-6 months', '-1 week');

            // pelo menos um dia depois, e não dateTimeBetween($inicio, ...):
            // esse devolvia a mesma data cerca de 1 em 200 vezes, e um
            // intervalo de um dia só não é um intervalo — era um teste
            // instável à espera de acontecer
            $fim = (clone $inicio)->modify('+'.fake()->numberBetween(1, 3).' days');

            return [
                'event_start_date' => $inicio->format('Y-m-d'),
                'event_end_date' => $fim->format('Y-m-d'),
            ];
        });
    }
}
