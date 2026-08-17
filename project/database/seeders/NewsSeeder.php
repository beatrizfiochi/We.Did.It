<?php

namespace Database\Seeders;

use App\Models\News;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        // o NewsletterSeeder só associa notícias com status 'accepted'; como o
        // factory sorteia o status, sem estas duas garantidas havia execuções
        // em que nenhuma era aprovada e as newsletters saíam sem notícias
        News::factory(2)->create(['status' => 'accepted']);

        News::factory(random_int(4, 6))->create();
    }
}
