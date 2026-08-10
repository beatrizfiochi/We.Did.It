<?php

namespace Tests\Feature\Admin;

use App\Models\Calendar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CalendarCrudTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'date' => '2026-09-15',
            'title' => 'Webinar de orientação vocacional',
        ], $overrides);
    }

    public function test_guests_cannot_access_any_calendar_route(): void
    {
        $calendar = Calendar::factory()->create();

        $this->get(route('admin.calendars.index'))->assertRedirect(route('login'));
        $this->get(route('admin.calendars.create'))->assertRedirect(route('login'));
        $this->post(route('admin.calendars.store'), $this->validPayload())->assertRedirect(route('login'));
        $this->get(route('admin.calendars.edit', $calendar))->assertRedirect(route('login'));
        $this->put(route('admin.calendars.update', $calendar), $this->validPayload())->assertRedirect(route('login'));
        $this->delete(route('admin.calendars.destroy', $calendar))->assertRedirect(route('login'));

        $this->assertDatabaseCount('calendars', 1);
    }

    public function test_authenticated_users_can_see_the_calendar_list(): void
    {
        $admin = User::factory()->create();
        Calendar::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('admin.calendars.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('Admin/Calendars/Index')
            ->has('events', 3)
        );
    }

    public function test_authenticated_users_can_see_the_create_form(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.calendars.create'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('Admin/Calendars/Create'));
    }

    public function test_authenticated_users_can_create_an_event(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.calendars.store'), $this->validPayload([
            'title' => 'Feira de emprego',
        ]));

        $this->assertDatabaseHas('calendars', ['title' => 'Feira de emprego']);
        $response->assertRedirect(route('admin.calendars.index'));
        $response->assertSessionHas('success');
    }

    public function test_creating_an_event_requires_the_mandatory_fields(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.calendars.store'), []);

        $response->assertSessionHasErrors(['date', 'title']);
        $this->assertDatabaseCount('calendars', 0);
    }

    public function test_authenticated_users_can_see_the_edit_form(): void
    {
        $admin = User::factory()->create();
        $calendar = Calendar::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.calendars.edit', $calendar));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('Admin/Calendars/Edit')
            ->where('event.id', $calendar->id)
        );
    }

    public function test_authenticated_users_can_update_an_event(): void
    {
        $admin = User::factory()->create();
        $calendar = Calendar::factory()->create(['title' => 'Título antigo']);

        $response = $this->actingAs($admin)->put(route('admin.calendars.update', $calendar), $this->validPayload([
            'title' => 'Título atualizado',
        ]));

        $this->assertDatabaseHas('calendars', ['id' => $calendar->id, 'title' => 'Título atualizado']);
        $response->assertRedirect(route('admin.calendars.index'));
        $response->assertSessionHas('success');
    }

    public function test_authenticated_users_can_delete_an_event(): void
    {
        $admin = User::factory()->create();
        $calendar = Calendar::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.calendars.destroy', $calendar));

        $this->assertDatabaseMissing('calendars', ['id' => $calendar->id]);
        $response->assertRedirect(route('admin.calendars.index'));
        $response->assertSessionHas('success');
    }
}
