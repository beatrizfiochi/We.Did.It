<?php

namespace Tests\Feature\Admin;

use App\Models\Calendar;
use App\Models\Newsletter;
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

        $this->get(route('admin.calendar.index'))->assertRedirect(route('login'));
        $this->post(route('admin.calendar.store'), $this->validPayload())->assertRedirect(route('login'));
        $this->put(route('admin.calendar.update', $calendar), $this->validPayload())->assertRedirect(route('login'));
        $this->delete(route('admin.calendar.destroy', $calendar))->assertRedirect(route('login'));

        $this->assertDatabaseCount('calendars', 1);
    }

    public function test_authenticated_users_can_see_the_calendar_list(): void
    {
        $admin = User::factory()->create();
        Calendar::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('admin.calendar.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('Admin/Calendar/Index')
            ->has('events', 3)
        );
    }

    public function test_authenticated_users_can_create_an_event(): void
    {
        $admin = User::factory()->create();

        // o controller responde com back(): o from() é o que faz o redirect
        // voltar para a listagem, como acontece no browser
        $response = $this->actingAs($admin)
            ->from(route('admin.calendar.index'))
            ->post(route('admin.calendar.store'), $this->validPayload([
                'title' => 'Feira de emprego',
            ]));

        $this->assertDatabaseHas('calendars', ['title' => 'Feira de emprego']);
        $response->assertRedirect(route('admin.calendar.index'));
        $response->assertSessionHas('success');
    }

    public function test_creating_an_event_requires_the_mandatory_fields(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.calendar.store'), []);

        $response->assertSessionHasErrors(['date', 'title']);
        $this->assertDatabaseCount('calendars', 0);
    }

    public function test_authenticated_users_can_update_an_event(): void
    {
        $admin = User::factory()->create();
        $calendar = Calendar::factory()->create(['title' => 'Título antigo']);

        $response = $this->actingAs($admin)
            ->from(route('admin.calendar.index'))
            ->put(route('admin.calendar.update', $calendar), $this->validPayload([
                'title' => 'Título atualizado',
            ]));

        $this->assertDatabaseHas('calendars', ['id' => $calendar->id, 'title' => 'Título atualizado']);
        $response->assertRedirect(route('admin.calendar.index'));
        $response->assertSessionHas('success');
    }

    public function test_authenticated_users_can_delete_an_event(): void
    {
        $admin = User::factory()->create();
        $calendar = Calendar::factory()->create();

        $response = $this->actingAs($admin)
            ->from(route('admin.calendar.index'))
            ->delete(route('admin.calendar.destroy', $calendar));

        $this->assertDatabaseMissing('calendars', ['id' => $calendar->id]);
        $response->assertRedirect(route('admin.calendar.index'));
        $response->assertSessionHas('success');
    }

    public function test_creating_an_event_can_associate_it_with_newsletters(): void
    {
        $admin = User::factory()->create();
        $newsletters = Newsletter::factory()->count(2)->create();

        $response = $this->actingAs($admin)->post(route('admin.calendar.store'), $this->validPayload([
            'newsletter_ids' => $newsletters->pluck('id')->all(),
        ]));

        $response->assertSessionHas('success');
        $calendar = Calendar::firstWhere('title', $this->validPayload()['title']);
        $this->assertEqualsCanonicalizing($newsletters->pluck('id')->all(), $calendar->newsletters->pluck('id')->all());
    }

    public function test_updating_an_event_syncs_its_newsletters(): void
    {
        $admin = User::factory()->create();
        $calendar = Calendar::factory()->create();
        [$oldNewsletter, $newNewsletter] = Newsletter::factory()->count(2)->create();
        $calendar->newsletters()->attach($oldNewsletter->id);

        $response = $this->actingAs($admin)->put(route('admin.calendar.update', $calendar), $this->validPayload([
            'newsletter_ids' => [$newNewsletter->id],
        ]));

        $response->assertSessionHas('success');
        $this->assertEqualsCanonicalizing([$newNewsletter->id], $calendar->fresh()->newsletters->pluck('id')->all());
    }

    public function test_updating_an_event_without_newsletter_ids_leaves_them_untouched(): void
    {
        $admin = User::factory()->create();
        $calendar = Calendar::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $calendar->newsletters()->attach($newsletter->id);

        $this->actingAs($admin)->put(route('admin.calendar.update', $calendar), $this->validPayload());

        $this->assertEqualsCanonicalizing([$newsletter->id], $calendar->newsletters()->get()->pluck('id')->all());
    }

    public function test_sending_an_empty_newsletter_ids_list_detaches_all_newsletters(): void
    {
        $admin = User::factory()->create();
        $calendar = Calendar::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $calendar->newsletters()->attach($newsletter->id);

        $this->actingAs($admin)->put(route('admin.calendar.update', $calendar), $this->validPayload([
            'newsletter_ids' => [],
        ]));

        $this->assertCount(0, $calendar->newsletters()->get());
    }

    public function test_a_partial_update_only_changes_the_sent_field(): void
    {
        $admin = User::factory()->create();
        $calendar = Calendar::factory()->create([
            'date' => '2026-09-15',
            'title' => 'Título original',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.calendar.update', $calendar), [
            'date' => '2026-10-01',
        ]);

        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseHas('calendars', [
            'id' => $calendar->id,
            'date' => '2026-10-01',
            'title' => 'Título original',
        ]);
    }
}
