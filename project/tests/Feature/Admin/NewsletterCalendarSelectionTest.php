<?php

namespace Tests\Feature\Admin;

use App\Models\Calendar;
use App\Models\Newsletter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class NewsletterCalendarSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_the_calendar_selection_screen(): void
    {
        $newsletter = Newsletter::factory()->create();

        $this->get(route('admin.newsletters.calendar.edit', $newsletter))->assertRedirect(route('login'));
        $this->put(route('admin.newsletters.calendar.update', $newsletter), ['calendar_ids' => []])
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_see_the_calendar_selection_screen(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $events = Calendar::factory()->count(3)->create();
        $newsletter->calendars()->attach($events->first()->id);

        $response = $this->actingAs($admin)->get(route('admin.newsletters.calendar.edit', $newsletter));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('Admin/Newsletters/Calendar')
            ->where('newsletter.id', $newsletter->id)
            ->has('calendars', 3)
            ->where('calendar_ids', [$events->first()->id])
        );
    }

    public function test_the_calendar_list_is_ordered_chronologically(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $october = Calendar::factory()->create(['date' => '2026-10-01']);
        $february = Calendar::factory()->create(['date' => '2026-02-01']);
        $september = Calendar::factory()->create(['date' => '2026-09-01']);

        $response = $this->actingAs($admin)->get(route('admin.newsletters.calendar.edit', $newsletter));

        $response->assertInertia(fn (Assert $page) => $page->component('Admin/Newsletters/Calendar')
            ->where('calendars.0.id', $february->id)
            ->where('calendars.1.id', $september->id)
            ->where('calendars.2.id', $october->id)
        );
    }

    public function test_authenticated_users_can_select_calendar_events_for_a_newsletter(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $events = Calendar::factory()->count(3)->create();

        $response = $this->actingAs($admin)->put(route('admin.newsletters.calendar.update', $newsletter), [
            'calendar_ids' => $events->pluck('id')->all(),
        ]);

        $this->assertEqualsCanonicalizing($events->pluck('id')->all(), $newsletter->fresh()->calendars->pluck('id')->all());
        $response->assertRedirect(route('admin.newsletters.calendar.edit', $newsletter));
        $response->assertSessionHas('success');
    }

    public function test_submitting_without_calendar_ids_clears_the_selection(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $event = Calendar::factory()->create();
        $newsletter->calendars()->attach($event->id);

        $this->actingAs($admin)->put(route('admin.newsletters.calendar.update', $newsletter), []);

        $this->assertCount(0, $newsletter->calendars()->get());
    }

    public function test_selecting_a_calendar_event_that_does_not_exist_fails_validation(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();

        $response = $this->actingAs($admin)->put(route('admin.newsletters.calendar.update', $newsletter), [
            'calendar_ids' => [99999],
        ]);

        $response->assertSessionHasErrors('calendar_ids.0');
    }
}
