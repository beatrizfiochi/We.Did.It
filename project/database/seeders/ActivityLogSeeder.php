<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        ActivityLog::factory(20)->create();
    }
}
