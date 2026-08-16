<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateNewsletterCoursesRequest;
use App\Models\Course;
use App\Models\Newsletter;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class NewsletterController extends Controller
{
    /**
     * Mostra o ecrã de seleção das ofertas formativas para esta newsletter.
     */
    public function editCourses(Newsletter $newsletter): Response
    {
        $newsletter->load('courses:id');

        // start_date é uma string vinda da API externa, não uma coluna de data,
        // por isso ordenamos em PHP (Carbon::parse) em vez de orderBy() no banco.
        $courses = Course::get(['id', 'title', 'start_date'])
            ->sortBy(fn (Course $course) => Carbon::parse($course->start_date))
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
