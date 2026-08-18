<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateTestimonialCategoryRequest;
use App\Models\Category;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
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

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTestimonialCategoryRequest $request, Testimonial $testimonial): RedirectResponse
    {
        //dd($request->hasFile('image'), $request->all());

        $data = $request->validated();

        if ($request->hasFile('image')) {
            $oldImage = $testimonial->image;

            // store new image
            $data['image'] = $request->file('image')->store('testimonials', 'public');

            // deletes old image
            if ($oldImage) {
                Storage::disk('public')->delete($oldImage);
            }

            //dd($data['image'], $testimonial->fresh());
        } else {
            unset($data['image']);
        }


        $testimonial->update($data);

        return redirect()->route('admin.testimonials.index')->with('success', 'Testemunho atualizado com sucesso.');
    }
}
