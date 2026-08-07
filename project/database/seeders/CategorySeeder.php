<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create(['name' => 'Formação']);
        Category::create(['name' => 'Eventos']);
        Category::create(['name' => 'Institucional']);
        Category::create(['name' => 'Comunidade']);
    }
}
