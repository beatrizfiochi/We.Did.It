<?php

namespace Tests\Feature\Admin;

use App\Models\Course;
use App\Models\Newsletter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CourseCrudTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Curso de Excel Avançado',
            'url' => 'https://example.com/curso-excel',
            'imageUrl' => null,
            'location' => 'Lisboa',
            'schedule' => 'Segunda-feira 18:00',
            'start_date' => '2026-09-01',
            'price' => '49.90',
            'status' => 'received',
        ], $overrides);
    }

    public function test_guests_cannot_access_any_course_route(): void
    {
        $course = Course::factory()->create();

        $this->get(route('admin.courses.index'))->assertRedirect(route('login'));
        $this->get(route('admin.courses.create'))->assertRedirect(route('login'));
        $this->post(route('admin.courses.store'), $this->validPayload())->assertRedirect(route('login'));
        $this->get(route('admin.courses.edit', $course))->assertRedirect(route('login'));
        $this->put(route('admin.courses.update', $course), $this->validPayload())->assertRedirect(route('login'));
        $this->delete(route('admin.courses.destroy', $course))->assertRedirect(route('login'));

        $this->assertDatabaseCount('courses', 1);
    }

    public function test_authenticated_users_can_see_the_course_list(): void
    {
        $admin = User::factory()->create();
        Course::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('admin.courses.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('Admin/Courses/Index')
            ->has('courses', 3)
        );
    }

    public function test_the_course_list_is_ordered_chronologically_not_alphabetically(): void
    {
        $admin = User::factory()->create();
        // Datas sem zero à esquerda: alfabeticamente "2026-10-1" vem antes de "2026-9-1",
        // mas cronologicamente setembro é antes de outubro.
        $october = Course::factory()->create(['start_date' => '2026-10-1']);
        $february = Course::factory()->create(['start_date' => '2026-2-1']);
        $september = Course::factory()->create(['start_date' => '2026-9-1']);

        $response = $this->actingAs($admin)->get(route('admin.courses.index'));

        $response->assertInertia(fn (Assert $page) => $page->component('Admin/Courses/Index')
            ->where('courses.0.id', $february->id)
            ->where('courses.1.id', $september->id)
            ->where('courses.2.id', $october->id)
        );
    }

    public function test_authenticated_users_can_see_the_create_form(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.courses.create'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('Admin/Courses/Create'));
    }

    public function test_authenticated_users_can_create_a_course(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.courses.store'), $this->validPayload([
            'title' => 'Curso de Marketing Digital',
        ]));

        $this->assertDatabaseHas('courses', ['title' => 'Curso de Marketing Digital']);
        $response->assertRedirect(route('admin.courses.index'));
        $response->assertSessionHas('success');
    }

    public function test_creating_a_course_requires_the_mandatory_fields(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.courses.store'), []);

        $response->assertSessionHasErrors(['title', 'url', 'start_date', 'price', 'status']);
        $this->assertDatabaseCount('courses', 0);
    }

    public function test_authenticated_users_can_see_the_edit_form(): void
    {
        $admin = User::factory()->create();
        $course = Course::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.courses.edit', $course));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('Admin/Courses/Edit')
            ->where('course.id', $course->id)
        );
    }

    public function test_authenticated_users_can_update_a_course(): void
    {
        $admin = User::factory()->create();
        $course = Course::factory()->create(['title' => 'Título antigo']);

        $response = $this->actingAs($admin)->put(route('admin.courses.update', $course), $this->validPayload([
            'title' => 'Título atualizado',
        ]));

        $this->assertDatabaseHas('courses', ['id' => $course->id, 'title' => 'Título atualizado']);
        $response->assertRedirect(route('admin.courses.index'));
        $response->assertSessionHas('success');
    }

    public function test_authenticated_users_can_delete_a_course(): void
    {
        $admin = User::factory()->create();
        $course = Course::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.courses.destroy', $course));

        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
        $response->assertRedirect(route('admin.courses.index'));
        $response->assertSessionHas('success');
    }

    public function test_creating_a_course_can_associate_it_with_newsletters(): void
    {
        $admin = User::factory()->create();
        $newsletters = Newsletter::factory()->count(2)->create();

        $response = $this->actingAs($admin)->post(route('admin.courses.store'), $this->validPayload([
            'title' => 'Curso associado a newsletters',
            'newsletter_ids' => $newsletters->pluck('id')->all(),
        ]));

        $response->assertSessionHas('success');
        $course = Course::firstWhere('title', 'Curso associado a newsletters');
        $this->assertEqualsCanonicalizing($newsletters->pluck('id')->all(), $course->newsletters->pluck('id')->all());
    }

    public function test_updating_a_course_syncs_its_newsletters(): void
    {
        $admin = User::factory()->create();
        $course = Course::factory()->create();
        [$oldNewsletter, $newNewsletter] = Newsletter::factory()->count(2)->create();
        $course->newsletters()->attach($oldNewsletter->id);

        $response = $this->actingAs($admin)->put(route('admin.courses.update', $course), $this->validPayload([
            'newsletter_ids' => [$newNewsletter->id],
        ]));

        $response->assertSessionHas('success');
        $this->assertEqualsCanonicalizing([$newNewsletter->id], $course->fresh()->newsletters->pluck('id')->all());
    }

    public function test_updating_a_course_without_newsletter_ids_leaves_them_untouched(): void
    {
        $admin = User::factory()->create();
        $course = Course::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $course->newsletters()->attach($newsletter->id);

        $this->actingAs($admin)->put(route('admin.courses.update', $course), $this->validPayload());

        $this->assertEqualsCanonicalizing([$newsletter->id], $course->newsletters()->get()->pluck('id')->all());
    }

    public function test_sending_an_empty_newsletter_ids_list_detaches_all_newsletters(): void
    {
        $admin = User::factory()->create();
        $course = Course::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $course->newsletters()->attach($newsletter->id);

        $this->actingAs($admin)->put(route('admin.courses.update', $course), $this->validPayload([
            'newsletter_ids' => [],
        ]));

        $this->assertCount(0, $course->newsletters()->get());
    }

    public function test_a_partial_update_only_changes_the_sent_field(): void
    {
        $admin = User::factory()->create();
        $course = Course::factory()->create([
            'title' => 'Título original',
            'price' => '99.90',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.courses.update', $course), [
            'title' => 'Título atualizado',
        ]);

        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'title' => 'Título atualizado',
            'price' => '99.90',
        ]);
    }

    public function test_the_edit_form_returns_the_associated_newsletter_ids(): void
    {
        $admin = User::factory()->create();
        $course = Course::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $course->newsletters()->attach($newsletter->id);

        $response = $this->actingAs($admin)->get(route('admin.courses.edit', $course));

        $response->assertInertia(fn (Assert $page) => $page->where('course.newsletter_ids', [$newsletter->id]));
    }
}
