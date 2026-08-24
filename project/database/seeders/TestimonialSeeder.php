<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        // pelo mesmo motivo do NewsSeeder: garantir que há sempre pelo menos um
        // testemunho aprovado para o NewsletterSeeder associar
        Testimonial::factory(1)->create(['status' => 'accepted']);

        Testimonial::factory(random_int(3, 4))->create();
    }
}
