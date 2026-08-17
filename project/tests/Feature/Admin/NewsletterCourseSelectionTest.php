<?php

namespace Tests\Feature\Admin;

use App\Models\Course;
use App\Models\Newsletter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class NewsletterCourseSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_the_course_selection_screen(): void
    {
        $newsletter = Newsletter::factory()->create();

        $this->get(route('admin.newsletters.courses.edit', $newsletter))->assertRedirect(route('login'));
        $this->put(route('admin.newsletters.courses.update', $newsletter), ['course_ids' => []])
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_see_the_course_selection_screen(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $courses = Course::factory()->count(3)->create();
        $newsletter->courses()->attach($courses->first()->id);

        $response = $this->actingAs($admin)->get(route('admin.newsletters.courses.edit', $newsletter));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('Admin/Newsletters/Courses')
            ->where('newsletter.id', $newsletter->id)
            ->has('courses', 3)
            ->where('course_ids', [$courses->first()->id])
        );
    }

    public function test_the_course_list_is_ordered_chronologically_not_alphabetically(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        // Datas sem zero à esquerda: alfabeticamente "2026-10-1" vem antes de "2026-9-1",
        // mas cronologicamente setembro é antes de outubro.
        $october = Course::factory()->create(['start_date' => '2026-10-1']);
        $february = Course::factory()->create(['start_date' => '2026-2-1']);
        $september = Course::factory()->create(['start_date' => '2026-9-1']);

        $response = $this->actingAs($admin)->get(route('admin.newsletters.courses.edit', $newsletter));

        $response->assertInertia(fn (Assert $page) => $page->component('Admin/Newsletters/Courses')
            ->where('courses.0.id', $february->id)
            ->where('courses.1.id', $september->id)
            ->where('courses.2.id', $october->id)
        );
    }

    public function test_authenticated_users_can_select_courses_for_a_newsletter(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $courses = Course::factory()->count(3)->create();

        $response = $this->actingAs($admin)->put(route('admin.newsletters.courses.update', $newsletter), [
            'course_ids' => $courses->pluck('id')->all(),
        ]);

        $this->assertEqualsCanonicalizing($courses->pluck('id')->all(), $newsletter->fresh()->courses->pluck('id')->all());
        $response->assertRedirect(route('admin.newsletters.courses.edit', $newsletter));
        $response->assertSessionHas('success');
    }

    public function test_submitting_without_course_ids_clears_the_selection(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $course = Course::factory()->create();
        $newsletter->courses()->attach($course->id);

        $this->actingAs($admin)->put(route('admin.newsletters.courses.update', $newsletter), []);

        $this->assertCount(0, $newsletter->courses()->get());
    }

    public function test_selecting_a_course_that_does_not_exist_fails_validation(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();

        $response = $this->actingAs($admin)->put(route('admin.newsletters.courses.update', $newsletter), [
            'course_ids' => [99999],
        ]);

        $response->assertSessionHasErrors('course_ids.0');
    }
}
