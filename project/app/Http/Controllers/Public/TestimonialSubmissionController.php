<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Concerns\NotifiesManagers;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTestimonialRequest;
use App\Mail\NewSubmissionReceived;
use App\Models\Category;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TestimonialSubmissionController extends Controller
{
    use NotifiesManagers;

    /**
     * Display the public testimonial submission form.
     */
    public function create(): Response
    {
        return Inertia::render('Testimonials/TestimonialForm', [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Store a submitted testimonial for approval.
     */
    public function store(StoreTestimonialRequest $request): RedirectResponse
    {
        // os consentimentos são validados mas não guardados (decisão do cliente).
        // O except() é explícito de propósito: sem ele as chaves chegavam ao
        // create() e eram descartadas em silêncio por não estarem no #[Fillable],
        // o que se parte no dia em que alguém ligar o Model::shouldBeStrict().
        $data = $request->safe()->except(['terms_conditions', 'image_rights']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('testimonials', 'public');
        }

        // o status não vem do request: o default da migration é 'received'
        $testimonial = Testimonial::create($data);

        $this->notifyManagers(new NewSubmissionReceived(
            type: 'Testemunho',
            title: $testimonial->title,
            authorName: $testimonial->name,
            categoryName: $testimonial->category?->name,
        ), $testimonial->id);

        return back()->with('success', 'O teu testemunho foi enviado para aprovação.');
    }
}
