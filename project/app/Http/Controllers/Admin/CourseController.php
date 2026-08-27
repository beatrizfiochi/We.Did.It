<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\Newsletter;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

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

        return redirect()->route('admin.courses.index')->with('success', 'Oferta formativa atualizada com sucesso.');
    }

    /**
     * Remove uma oferta formativa.
     */
    public function destroy(Course $course): RedirectResponse
    {
        $course->delete();

        return redirect()->route('admin.courses.index')->with('success', 'Oferta formativa removida com sucesso.');
    }
}
