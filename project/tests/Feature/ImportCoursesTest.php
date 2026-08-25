<?php

namespace Tests\Feature;

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportCoursesTest extends TestCase
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

    public function test_import_creates_the_courses_found_on_the_source_site(): void
    {
        $this->fakeSourceWith($this->fixtureHtml());

        $this->artisan('courses:import')
            ->expectsOutputToContain('9 cursos encontrados, 9 criados, 0 atualizados.')
            ->assertExitCode(0);

        $this->assertDatabaseCount('courses', 9);
        $this->assertDatabaseHas('courses', [
            'title' => 'CESAE Júnior',
            'url' => 'https://www.cesaedigital.pt/curso/lista/cesae_junior_prt_v1',
            'location' => 'Porto',
            'schedule' => 'Laboral',
            'start_date' => '2026-06-29',
            'price' => '100,00€',
            'status' => 'received',
        ]);
    }

    public function test_running_the_import_twice_does_not_duplicate_courses(): void
    {
        $this->fakeSourceWith($this->fixtureHtml());

        $this->artisan('courses:import')->assertExitCode(0);
        $this->artisan('courses:import')
            ->expectsOutputToContain('9 cursos encontrados, 0 criados, 9 atualizados.')
            ->assertExitCode(0);

        $this->assertDatabaseCount('courses', 9);
    }

    public function test_a_course_already_approved_by_the_manager_does_not_revert_to_received(): void
    {
        $this->fakeSourceWith($this->fixtureHtml());

        $this->artisan('courses:import')->assertExitCode(0);

        $course = Course::where('title', 'CESAE Júnior')->firstOrFail();
        $course->update(['status' => 'accepted']);

        $this->artisan('courses:import')->assertExitCode(0);

        $this->assertSame('accepted', $course->fresh()->status);
    }

    public function test_a_course_with_an_unreadable_start_date_is_imported_anyway(): void
    {
        $html = <<<'HTML'
            <article>
                <a href="/curso/lista/curso-sem-data">
                    <figure><img src="/img.jpg" /></figure>
                    <div class="text-holder">
                        <span class="label">Online, Pós Laboral</span>
                        <h1>Curso sem data marcada</h1>
                        <span class="date">A anunciar</span>
                        <div class="price">Gratuito</div>
                        <div>Brevemente</div>
                    </div>
                </a>
            </article>
            HTML;

        $this->fakeSourceWith($html);

        $this->artisan('courses:import')
            ->expectsOutputToContain('1 cursos encontrados, 1 criados, 0 atualizados.')
            ->assertExitCode(0);

        $this->assertDatabaseHas('courses', [
            'title' => 'Curso sem data marcada',
            'start_date' => 'A anunciar',
            'status' => 'received',
        ]);
    }

    public function test_a_card_with_two_label_elements_does_not_leak_the_price_badge_into_location(): void
    {
        $html = <<<'HTML'
            <article>
                <a href="/curso/lista/curso-com-selo">
                    <figure>
                        <span class="label bg-primary text-light">Valor por semana</span>
                        <img src="/img.jpg" />
                    </figure>
                    <div class="text-holder">
                        <span class="label">Porto, Laboral</span>
                        <h1>Curso com selo de preço</h1>
                        <span class="date">A anunciar</span>
                        <div class="price">100,00€</div>
                        <div></div>
                    </div>
                </a>
            </article>
            HTML;

        $this->fakeSourceWith($html);

        $this->artisan('courses:import')->assertExitCode(0);

        $this->assertDatabaseHas('courses', [
            'title' => 'Curso com selo de preço',
            'location' => 'Porto',
            'schedule' => 'Laboral',
        ]);
    }
}
