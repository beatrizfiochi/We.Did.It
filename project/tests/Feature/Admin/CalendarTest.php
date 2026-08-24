<?php

namespace Tests\Feature\Admin;

use App\Models\Calendar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_manage_the_calendar(): void
    {
        $event = Calendar::factory()->create();

        $this->get(route('admin.calendar.index'))->assertRedirect(route('login'));
        $this->post(route('admin.calendar.store'), ['title' => 'Sessão', 'date' => '2026-09-01'])
            ->assertRedirect(route('login'));
        $this->put(route('admin.calendar.update', $event), ['title' => 'Outro', 'date' => '2026-09-01'])
            ->assertRedirect(route('login'));
        $this->delete(route('admin.calendar.destroy', $event))->assertRedirect(route('login'));

        $this->assertDatabaseCount('calendars', 1);
    }

    public function test_the_events_are_listed_by_date(): void
    {
        Calendar::factory()->create(['title' => 'Segundo', 'date' => '2026-10-01']);
        Calendar::factory()->create(['title' => 'Primeiro', 'date' => '2026-09-01']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.calendar.index'))
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Admin/Calendar/Index')
                    ->has('events', 2)
                    ->where('events.0.title', 'Primeiro')
                    ->where('events.1.title', 'Segundo')
            );
    }

    public function test_an_event_can_be_created(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.calendar.store'), [
                'title' => 'Sessão de esclarecimento',
                'date' => '2026-09-15',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('calendars', ['title' => 'Sessão de esclarecimento']);
        $this->assertDatabaseHas('logs', [
            'user_id' => $user->id,
            'table_name' => 'calendars',
            'record_id' => Calendar::first()->id,
            'operation' => 'created',
        ]);
    }

    public function test_an_event_can_be_updated(): void
    {
        $event = Calendar::factory()->create(['title' => 'Título antigo']);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('admin.calendar.update', $event), [
                'title' => 'Título novo',
                'date' => '2026-09-20',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('Título novo', $event->fresh()->title);
        $this->assertDatabaseHas('logs', [
            'user_id' => $user->id,
            'table_name' => 'calendars',
            'record_id' => $event->id,
            'operation' => 'updated',
        ]);
    }

    public function test_an_event_can_be_removed(): void
    {
        $event = Calendar::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->delete(route('admin.calendar.destroy', $event))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('calendars', ['id' => $event->id]);

        // o log fica, apesar de o registo ter desaparecido
        $this->assertDatabaseHas('logs', [
            'user_id' => $user->id,
            'table_name' => 'calendars',
            'record_id' => $event->id,
            'operation' => 'removed',
        ]);
    }

    public function test_the_title_and_the_date_are_required(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.calendar.store'), ['title' => '', 'date' => ''])
            ->assertSessionHasErrors(['title', 'date']);

        $this->assertDatabaseCount('calendars', 0);
    }

    public function test_an_invalid_date_is_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.calendar.store'), [
                'title' => 'Sessão',
                'date' => 'trinta e um de fevereiro',
            ])
            ->assertSessionHasErrors('date');
    }

    public function test_a_past_date_is_accepted(): void
    {
        // a agenda também serve para registar o que já aconteceu
        $this->actingAs(User::factory()->create())
            ->post(route('admin.calendar.store'), [
                'title' => 'Sessão que já decorreu',
                'date' => '2020-01-15',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('calendars', ['title' => 'Sessão que já decorreu']);
    }
}
