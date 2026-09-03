<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Concerns\NotifiesManagers;
use App\Http\Controllers\Concerns\StoresImages;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTestimonialRequest;
use App\Mail\NewSubmissionReceived;
use App\Models\Category;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TestimonialSubmissionController extends Controller
{
    use NotifiesManagers, StoresImages;

    /**
     * Display the public testimonial submission form.
     */
    public function create(): Response
    {
        return Inertia::render('Testimonials/InsertForm', [
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
        // O images está aqui pela mesma razão: são ficheiros, e quem os grava
        // é o storeImages() logo a seguir, não o create().
        $data = $request->safe()->except(['terms_conditions', 'image_rights', 'images']);

        // ou entra o testemunho com as imagens todas, ou não entra nada: um
        // testemunho gravado sem as fotos dá "enviado com sucesso" a quem
        // submeteu e chega ao gestor sem o que a pessoa escolheu
        $testimonial = DB::transaction(function () use ($request, $data) {
            $testimonial = Testimonial::create($data);

            if ($request->hasFile('images')) {
                $this->storeImages($testimonial, $request->file('images'), 'testimonials');
            }

            return $testimonial;
        });

        $this->notifyManagers(new NewSubmissionReceived(
            type: 'Testemunho',
            title: $testimonial->title,
            authorName: $testimonial->name,
            categoryName: $testimonial->category?->name,
        ), $testimonial->id);

        return back()->with('success', 'O teu testemunho foi enviado para aprovação.');
    }
}
