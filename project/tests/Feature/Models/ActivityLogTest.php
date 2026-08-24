<?php

namespace Tests\Feature\Models;

use App\Models\ActivityLog;
use App\Models\Calendar;
use App\Models\Category;
use App\Models\News;
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

    public function test_record_logs_the_operation_for_the_authenticated_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::create(['name' => 'Tecnologia']);

        ActivityLog::record($category, 'created');

        $this->assertDatabaseHas('logs', [
            'user_id' => $user->id,
            'table_name' => 'categories',
            'record_id' => $category->id,
            'operation' => 'created',
        ]);
    }

    public function test_record_reads_the_table_name_from_the_model(): void
    {
        $this->actingAs(User::factory()->create());

        ActivityLog::record(News::factory()->create(), 'updated');
        ActivityLog::record(Calendar::factory()->create(), 'updated');

        $this->assertSame(
            ['news', 'calendars'],
            ActivityLog::orderBy('id')->pluck('table_name')->all(),
        );
    }

    public function test_record_keeps_the_log_after_the_record_is_deleted(): void
    {
        $this->actingAs(User::factory()->create());

        $calendar = Calendar::factory()->create();
        $id = $calendar->id;

        $calendar->delete();
        ActivityLog::record($calendar, 'removed');

        // record_id não é chave estrangeira, por isso o log sobrevive ao apagamento
        $this->assertDatabaseMissing('calendars', ['id' => $id]);
        $this->assertDatabaseHas('logs', [
            'record_id' => $id,
            'table_name' => 'calendars',
            'operation' => 'removed',
        ]);
    }
}
