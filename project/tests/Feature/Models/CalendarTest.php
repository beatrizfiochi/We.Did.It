<?php

namespace Tests\Feature\Models;

use App\Models\Calendar;
use App\Models\Newsletter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendar_can_be_created(): void
    {
        $calendar = Calendar::create([
            'date' => '2026-08-15',
            'title' => 'Evento de lançamento',
        ]);

        $this->assertDatabaseHas('calendars', ['title' => 'Evento de lançamento']);
    }

    public function test_calendar_belongs_to_many_newsletters(): void
    {
        $calendar = Calendar::factory()->create();
        $newsletter = Newsletter::factory()->create();

        $newsletter->calendars()->attach($calendar->id);

        $this->assertTrue($calendar->newsletters->contains($newsletter));
        $this->assertTrue($newsletter->calendars->contains($calendar));
    }
}
