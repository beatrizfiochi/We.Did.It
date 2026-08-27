<?php

namespace Tests\Feature\Admin;

use App\Models\Calendar;
use App\Models\Course;
use App\Models\News;
use App\Models\Newsletter;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Cobertura do registo de operações na tabela logs (SCRUM-105).
 *
 * Os CRUD de agenda, categorias e newsletters já tinham os seus testes de log nos
 * próprios ficheiros. Este cobre o que faltava: as ofertas formativas, a associação
 * de conteúdos a uma newsletter e a criação de administradores.
 *
 * Não há teste ao comando courses:import: ele não regista de propósito, porque o
 * record() usa auth()->id() e na consola isso é null.
 */
class ActivityLogCoverageTest extends TestCase
{
    use RefreshDatabase;

    private function coursePayload(array $overrides = []): array
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

    public function test_the_three_course_operations_are_written_to_the_activity_log(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.courses.store'), $this->coursePayload());
        $course = Course::firstWhere('title', 'Curso de Excel Avançado');

        $this->actingAs($admin)->put(route('admin.courses.update', $course), $this->coursePayload([
            'title' => 'Título atualizado',
        ]));

        $this->actingAs($admin)->delete(route('admin.courses.destroy', $course));

        foreach (['created', 'updated', 'removed'] as $operacao) {
            $this->assertDatabaseHas('logs', [
                'user_id' => $admin->id,
                'table_name' => 'courses',
                'record_id' => $course->id,
                'operation' => $operacao,
            ]);
        }
    }

    public function test_the_log_of_a_removed_course_survives_the_delete(): void
    {
        $admin = User::factory()->create();
        $course = Course::factory()->create();

        $this->actingAs($admin)->delete(route('admin.courses.destroy', $course));

        // o record() vem depois do delete(): o Eloquent mantém os atributos na
        // instância e record_id não é chave estrangeira, por isso a linha fica
        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
        $this->assertDatabaseHas('logs', [
            'table_name' => 'courses',
            'record_id' => $course->id,
            'operation' => 'removed',
        ]);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function contentSelectionProvider(): array
    {
        return [
            'notícias' => ['admin.newsletters.news.update', 'news_ids'],
            'testemunhos' => ['admin.newsletters.testimonials.update', 'testimonial_ids'],
            'ofertas formativas' => ['admin.newsletters.courses.update', 'course_ids'],
            'agenda' => ['admin.newsletters.calendars.update', 'calendar_ids'],
        ];
    }

    #[DataProvider('contentSelectionProvider')]
    public function test_associating_content_is_written_to_the_activity_log(string $rota, string $chave): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();

        $id = match ($chave) {
            'news_ids' => News::factory()->create(['status' => 'accepted'])->id,
            'testimonial_ids' => Testimonial::factory()->create(['status' => 'accepted'])->id,
            'course_ids' => Course::factory()->create()->id,
            'calendar_ids' => Calendar::factory()->create()->id,
        };

        $this->actingAs($admin)->put(route($rota, $newsletter), [$chave => [$id]]);

        // o log é sobre a newsletter, não sobre o conteúdo associado
        $this->assertDatabaseHas('logs', [
            'user_id' => $admin->id,
            'table_name' => 'newsletters',
            'record_id' => $newsletter->id,
            'operation' => 'updated',
        ]);
    }

    public function test_creating_an_administrator_is_written_to_the_activity_log(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Nova Gestora',
            'email' => 'nova@cesae.pt',
            'password' => 'palavra-passe-segura',
            'password_confirmation' => 'palavra-passe-segura',
        ]);

        $novo = User::firstWhere('email', 'nova@cesae.pt');

        // user_id é quem criou, record_id é o administrador novo — é isso que
        // torna o log útil: saber quem deu acesso a quem
        $this->assertDatabaseHas('logs', [
            'user_id' => $admin->id,
            'table_name' => 'users',
            'record_id' => $novo->id,
            'operation' => 'created',
        ]);
        $this->assertNotSame($admin->id, $novo->id);
    }

    public function test_every_log_entry_records_who_did_it(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $course = Course::factory()->create();

        $this->actingAs($admin)->post(route('admin.courses.store'), $this->coursePayload());
        $this->actingAs($admin)->put(route('admin.newsletters.courses.update', $newsletter), [
            'course_ids' => [$course->id],
        ]);

        // sem isto, o log podia estar a gravar user_id null em metade dos casos
        // e os testes acima passavam na mesma
        $this->assertDatabaseMissing('logs', ['user_id' => null]);
        $this->assertGreaterThan(0, $admin->activityLogs()->count());
    }
}
