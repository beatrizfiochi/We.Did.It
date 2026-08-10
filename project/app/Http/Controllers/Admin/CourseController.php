<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    /**
     * Lista as ofertas formativas.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Courses/Index', [
            'courses' => Course::orderBy('start_date')->get(),
        ]);
    }

    /**
     * Mostra o formulário de criação de uma nova oferta formativa.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Courses/Create');
    }

    /**
     * Guarda uma nova oferta formativa.
     */
    public function store(StoreCourseRequest $request): RedirectResponse
    {
        Course::create($request->validated());

        return redirect()->route('admin.courses.index')->with('success', 'Oferta formativa criada com sucesso.');
    }

    /**
     * Mostra o formulário de edição de uma oferta formativa.
     */
    public function edit(Course $course): Response
    {
        return Inertia::render('Admin/Courses/Edit', [
            'course' => $course,
        ]);
    }

    /**
     * Atualiza uma oferta formativa existente.
     */
    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $course->update($request->validated());

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
