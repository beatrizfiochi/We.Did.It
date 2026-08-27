<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            // AdminUserSeeder e não UserSeeder: é este que lê o ADMIN_EMAIL e o
            // ADMIN_PASSWORD do .env, como o .env.example documenta, e que o deploy
            // vai usar para criar a conta de demonstração (SCRUM-119). O UserSeeder
            // tinha o email fixo em código e ignorava a configuração.
            AdminUserSeeder::class,
            CategorySeeder::class,
            NewsSeeder::class,
            TestimonialSeeder::class,
            CourseSeeder::class,
            CalendarSeeder::class,
            NewsletterSeeder::class,
            ActivityLogSeeder::class,
        ]);
    }
}
