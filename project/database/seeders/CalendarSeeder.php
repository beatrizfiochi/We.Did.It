<?php

namespace Database\Seeders;

use App\Models\Calendar;
use Illuminate\Database\Seeder;

class CalendarSeeder extends Seeder
{
    public function run(): void
    {
        Calendar::factory(12)->create();
    }
}
