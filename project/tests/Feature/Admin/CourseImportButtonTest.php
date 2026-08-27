<?php

namespace Tests\Feature\Admin;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * O botão "Importar cursos" em /admin/cursos, que dispara a mesma
 * importação do courses:import (SCRUM-123) — só que a partir do admin,
 * para quem não tem acesso a terminal.
 */
class CourseImportButtonTest extends TestCase
{
    use RefreshDatabase;

    private const SOURCE_URL = 'https://www.cesaedigital.pt/fldrSite/pages/coursesList.aspx';

    private function fakeSourceWith(string $html): void
    {
        Http::fake([
            self::SOURCE_URL => Http::response($html, 200),
        ]);
    }

    private function fixtureHtml(): string
    {
        return file_get_contents(base_path('tests/Fixtures/cesae-courses.html'));
    }

    public function test_guests_cannot_trigger_the_import(): void
    {
        $this->post(route('admin.courses.import'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_import_courses_from_the_admin(): void
    {
        $admin = User::factory()->create();
        $this->fakeSourceWith($this->fixtureHtml());

        $response = $this->actingAs($admin)
            ->from(route('admin.courses.index'))
            ->post(route('admin.courses.import'));

        $response->assertRedirect(route('admin.courses.index'));
        $response->assertSessionHas('success', '9 cursos encontrados, 9 criados, 0 atualizados.');
        $this->assertDatabaseCount('courses', 9);
    }

    public function test_an_already_approved_course_does_not_revert_to_received(): void
    {
        $admin = User::factory()->create();
        $this->fakeSourceWith($this->fixtureHtml());

        $this->actingAs($admin)->post(route('admin.courses.import'));

        $course = Course::where('title', 'CESAE Júnior')->firstOrFail();
        $course->update(['status' => 'accepted']);

        $this->actingAs($admin)->post(route('admin.courses.import'));

        $this->assertSame('accepted', $course->fresh()->status);
    }

    public function test_a_source_failure_flashes_an_error_instead_of_a_500(): void
    {
        $admin = User::factory()->create();
        Http::fake([
            self::SOURCE_URL => Http::response('Service Unavailable', 503),
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.courses.index'))
            ->post(route('admin.courses.import'));

        $response->assertRedirect(route('admin.courses.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('courses', 0);
    }
}
