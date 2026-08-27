<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\ActivityLog;
use App\Models\Course;
use App\Models\Newsletter;
use App\Services\CourseImporter;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class CourseController extends Controller
{
    /**
     * Lista as ofertas formativas, com as newsletters onde cada uma já entra.
     */
    public function index(): Response
    {
        // start_date é uma string vinda da API externa, não uma coluna de data,
        // por isso ordenamos em PHP (start_date_for_sorting) em vez de orderBy() no banco,
        // que compararia como texto e não cronologicamente.
        $courses = Course::with('newsletters:id,title,edition')->get()
            ->sortBy(fn (Course $course) => $course->start_date_for_sorting)
            ->values();

        return Inertia::render('Admin/Courses/Index', [
            'courses' => $courses,
        ]);
    }

    /**
     * Mostra o formulário de criação de uma nova oferta formativa.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Courses/Create', [
            'newsletters' => Newsletter::orderByDesc('edition')->get(['id', 'title', 'edition']),
        ]);
    }

    /**
     * Guarda uma nova oferta formativa e associa-a às newsletters escolhidas.
     */
    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $newsletterIds = $data['newsletter_ids'] ?? [];
        unset($data['newsletter_ids']);

        $course = Course::create($data);
        $course->newsletters()->sync($newsletterIds);

        ActivityLog::record($course, 'created');

        return redirect()->route('admin.courses.index')->with('success', 'Oferta formativa criada com sucesso.');
    }

    /**
     * Mostra o formulário de edição de uma oferta formativa.
     */
    public function edit(Course $course): Response
    {
        $course->load('newsletters:id');

        return Inertia::render('Admin/Courses/Edit', [
            'course' => [
                ...$course->toArray(),
                'newsletter_ids' => $course->newsletters->pluck('id'),
            ],
            'newsletters' => Newsletter::orderByDesc('edition')->get(['id', 'title', 'edition']),
        ]);
    }

    /**
     * Atualiza uma oferta formativa existente e as newsletters associadas.
     */
    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $data = $request->validated();

        // só mexe nas newsletters associadas se a chave vier no pedido;
        // uma atualização parcial (ex.: só o title) não deve desassociar tudo
        if (array_key_exists('newsletter_ids', $data)) {
            $course->newsletters()->sync($data['newsletter_ids']);
        }
        unset($data['newsletter_ids']);

        $course->update($data);

        ActivityLog::record($course, 'updated');

        return redirect()->route('admin.courses.index')->with('success', 'Oferta formativa atualizada com sucesso.');
    }

    /**
     * Importa as ofertas formativas do site do CESAE Digital (SCRUM-123).
     *
     * Mesma lógica do comando courses:import, via CourseImporter. Não regista
     * no ActivityLog: a coluna record_id é uma chave só, e uma importação
     * mexe em 0 a N cursos de uma vez — não há um registo único para apontar,
     * mesmo problema que os sync() de conteúdo da newsletter resolveram
     * registando a newsletter em vez dos conteúdos. Aqui não há esse ponto
     * de apoio.
     */
    public function import(CourseImporter $importer): RedirectResponse
    {
        try {
            $result = $importer->import();
        } catch (Throwable $e) {
            return redirect()->route('admin.courses.index')
                ->with('error', 'Não foi possível importar os cursos: '.$e->getMessage());
        }

        return redirect()->route('admin.courses.index')->with('success', sprintf(
            '%d cursos encontrados, %d criados, %d atualizados.',
            $result['total'],
            $result['created'],
            $result['updated'],
        ));
    }

    /**
     * Remove uma oferta formativa.
     */
    public function destroy(Course $course): RedirectResponse
    {
        $course->delete();

        // depois do delete(): o Eloquent mantém os atributos na instância e o
        // record_id não é chave estrangeira, por isso a linha do log sobrevive
        ActivityLog::record($course, 'removed');

        return redirect()->route('admin.courses.index')->with('success', 'Oferta formativa removida com sucesso.');
    }
}
