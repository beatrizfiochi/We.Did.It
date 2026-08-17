<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateTestimonialCategoryRequest;
use App\Models\Category;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TestimonialController extends Controller
{
    /**
     * Lista os testemunhos para o gestor categorizar.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Testimonials/Index', [
            'testimonials' => Testimonial::with('category:id,name')->latest()->get(),
            'categories' => Category::all(['id', 'name']),
        ]);
    }

    /**
     * Define a categoria de um testemunho.
     */
    public function category(UpdateTestimonialCategoryRequest $request, Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update($request->validated());

        return redirect()->route('admin.testimonials.index')->with('success', 'Testemunho categorizado com sucesso.');
    }
}
