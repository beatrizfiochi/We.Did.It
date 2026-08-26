<?php

namespace Tests\Feature\Admin;

use App\Models\Calendar;
use App\Models\Newsletter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Seleção dos eventos da agenda para a newsletter (SCRUM-132).
 *
 * Ao contrário das notícias e dos testemunhos, não há teste de moderação — os eventos
 * não têm estado — nem de ordem: o pivot calendar_newsletter não tem coluna order, e a
 * ordem no template vem do orderBy('date') do preview.
 */
class NewsletterCalendarSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_the_calendar_selection(): void
    {
        $newsletter = Newsletter::factory()->create();

        $this->get(route('admin.newsletters.calendars.edit', $newsletter))->assertRedirect(route('login'));
        $this->put(route('admin.newsletters.calendars.update', $newsletter), ['calendar_ids' => []])
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_see_the_calendar_selection_screen(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        Calendar::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('admin.newsletters.calendars.edit', $newsletter));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('Admin/Newsletters/Calendars')
            ->has('calendars', 3)
        );
    }

    public function test_authenticated_users_can_select_events_for_a_newsletter(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $events = Calendar::factory()->count(3)->create();

        $response = $this->actingAs($admin)->put(route('admin.newsletters.calendars.update', $newsletter), [
            'calendar_ids' => $events->pluck('id')->all(),
        ]);

        $this->assertEqualsCanonicalizing(
            $events->pluck('id')->all(),
            $newsletter->fresh()->calendars->pluck('id')->all()
        );
        $response->assertRedirect(route('admin.newsletters.calendars.edit', $newsletter));
        $response->assertSessionHas('success');
    }

    public function test_a_new_selection_replaces_the_previous_one(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $antigo = Calendar::factory()->create();
        $novo = Calendar::factory()->create();
        $newsletter->calendars()->attach($antigo->id);

        $this->actingAs($admin)->put(route('admin.newsletters.calendars.update', $newsletter), [
            'calendar_ids' => [$novo->id],
        ]);

        $this->assertSame([$novo->id], $newsletter->fresh()->calendars->pluck('id')->all());
    }

    public function test_submitting_without_calendar_ids_clears_the_selection(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $event = Calendar::factory()->create();
        $newsletter->calendars()->attach($event->id);

        $this->actingAs($admin)->put(route('admin.newsletters.calendars.update', $newsletter), []);

        $this->assertCount(0, $newsletter->calendars()->get());
    }

    public function test_selecting_an_event_that_does_not_exist_fails_validation(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();

        $response = $this->actingAs($admin)->put(route('admin.newsletters.calendars.update', $newsletter), [
            'calendar_ids' => [99999],
        ]);

        $response->assertSessionHasErrors('calendar_ids.0');
    }
}
