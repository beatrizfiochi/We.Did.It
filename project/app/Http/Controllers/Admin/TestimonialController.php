<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ModeratesSubmissions;
use App\Http\Controllers\Concerns\StoresImages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateTestimonialCategoryRequest;
use App\Http\Requests\Admin\UpdateTestimonialRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TestimonialController extends Controller
{
    use ModeratesSubmissions, StoresImages;

    /**
     * List the submitted testimonials for moderation.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Testimonials/Index', [
            'testimonials' => Testimonial::with('category:id,name')
                ->when(
                    $request->string('status')->toString(),
                    fn ($query, $status) => $query->where('status', $status),
                )
                ->latest()
                ->get(),
            'filters' => ['status' => $request->string('status')->toString()],
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Update the content of a submitted testimonial.
     */
    public function update(UpdateTestimonialRequest $request, Testimonial $testimonial): RedirectResponse
    {
        // o status não vem nas rules: editar conteúdo não muda o estado
        // 'images' fora: são ficheiros, quem os grava é o storeImages() a seguir
        $data = $request->safe()->except(['images']);

        $testimonial->update($data);

        if ($request->hasFile('images')) {
            $this->storeImages($testimonial, $request->file('images'), 'testimonials');
        }

        ActivityLog::record($testimonial, 'updated');

        return back()->with('success', 'Testemunho atualizado com sucesso.');
    }

    /**
     * Approve a submitted testimonial.
     */
    public function approve(Testimonial $testimonial): RedirectResponse
    {
        return $this->changeStatus($testimonial, 'accepted', 'Testemunho aprovado.');
    }

    /**
     * Refuse a submitted testimonial.
     */
    public function refuse(Testimonial $testimonial): RedirectResponse
    {
        return $this->changeStatus($testimonial, 'refused', 'Testemunho recusado.');
    }

    /**
     * Set the category of a submitted testimonial.
     *
     * Existe à parte do update() porque categorizar é feito a partir da
     * listagem, sem passar pelo formulário de conteúdo completo.
     */
    public function category(UpdateTestimonialCategoryRequest $request, Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update($request->validated());

        ActivityLog::record($testimonial, 'updated');

        return back()->with('success', 'Testemunho categorizado com sucesso.');
    }
}
