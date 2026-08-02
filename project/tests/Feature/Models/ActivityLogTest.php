<?php

namespace Tests\Feature\Models;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_log_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $log = ActivityLog::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($log->user->is($user));
        $this->assertTrue($user->activityLogs->contains($log));
    }
}
