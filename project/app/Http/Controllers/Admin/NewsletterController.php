<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNewsletterRequest;
use App\Http\Requests\Admin\UpdateNewsletterRequest;
use App\Http\Requests\UpdateNewsletterCoursesRequest;
use App\Models\ActivityLog;
use App\Models\Course;
use App\Models\Newsletter;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class NewsletterController extends Controller
{
    /**
     * Lista as newsletters, da edição mais recente para a mais antiga.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Newsletters/Index', [
            'newsletters' => Newsletter::orderByDesc('edition')
                ->get([
                    'id',
                    'title',
                    'edition',
                    'date',
                    'period_start',
                    'period_end',
                    'status'
                ]),
        ]);
    }

    /**
     * Guarda uma nova newsletter.
     */
    public function store(StoreNewsletterRequest $request): RedirectResponse
    {
        $newsletter = Newsletter::create([
            ...$request->validated(),
            'status' => Newsletter::RASCUNHO,
        ]);

        ActivityLog::record($newsletter, 'created');

        return back()->with('success', 'Newsletter criada como rascunho.');
    }

    /**
     * Atualiza uma newsletter existente.
     *
     * Não impede a edição de uma newsletter já publicada — esse bloqueio é a
     * SCRUM-116, na Sprint 5.
     */
    public function update(UpdateNewsletterRequest $request, Newsletter $newsletter): RedirectResponse
    {
        $data = $request->validated();

        $newsletter->update($data);

        ActivityLog::record($newsletter, 'updated');

        $message = $newsletter->status
            ? 'Rascunho guardado com sucesso.'
            : 'Newsletter atualizada com sucesso.'; //status = false -> atualizada 

        return back()->with('success', $message);
    }


    /**
     * Remove uma newsletter.
     */
    public function destroy(Newsletter $newsletter): RedirectResponse
    {
        $newsletter->delete();

        ActivityLog::record($newsletter, 'removed');

        return back()->with('success', 'Newsletter removida com sucesso.');
    }

    /**
     * Mostra a newsletter com todos os conteúdos selecionados, para o gestor
     * poder decidir se está bem antes de finalizar (SCRUM-15, ecrã da SCRUM-114).
     */
    public function preview(Newsletter $newsletter): Response
    {
        $newsletter->load([
            'news' => fn($q) => $q->with('category')->orderByPivot('order'),
            'testimonials' => fn($q) => $q->with('category')->orderByPivot('order'),
            'calendars' => fn($q) => $q->orderBy('date'),
            'courses',
        ]);

        // start_date é string livre vinda da API externa, não dá para ordenar em SQL
        $newsletter->setRelation('courses', $newsletter->courses
            ->sortBy(fn(Course $course) => $course->start_date_for_sorting)
            ->values());

        return Inertia::render('Admin/Newsletters/Preview', [
            'newsletter' => $newsletter,
        ]);
    }

    /**
     * Mostra o ecrã de seleção das ofertas formativas para esta newsletter.
     */
    public function editCourses(Newsletter $newsletter): Response
    {
        $newsletter->load('courses:id');

        // start_date é uma string vinda da API externa, não uma coluna de data,
        // por isso ordenamos em PHP (start_date_for_sorting) em vez de orderBy() no banco.
        $courses = Course::get(['id', 'title', 'start_date'])
            ->sortBy(fn(Course $course) => $course->start_date_for_sorting)
            ->values();

        return Inertia::render('Admin/Newsletters/Courses', [
            'newsletter' => $newsletter->only(['id', 'title', 'edition']),
            'courses' => $courses,
            'course_ids' => $newsletter->courses->pluck('id'),
        ]);
    }

    /**
     * Guarda as ofertas formativas selecionadas para esta newsletter.
     */
    public function updateCourses(UpdateNewsletterCoursesRequest $request, Newsletter $newsletter): RedirectResponse
    {
        $newsletter->courses()->sync($request->validated()['course_ids'] ?? []);

        return redirect()->route('admin.newsletters.courses.edit', $newsletter)
            ->with('success', 'Ofertas formativas da newsletter atualizadas com sucesso.');
    }
}
