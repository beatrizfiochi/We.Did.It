<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Concerns\NotifiesManagers;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewsRequest;
use App\Mail\NewSubmissionReceived;
use App\Models\Category;
use App\Models\News;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class NewsSubmissionController extends Controller
{
    use NotifiesManagers;

    /**
     * Display the public news submission form.
     */
    public function create(): Response
    {
        return Inertia::render('News/InsertForm', [
            'categories' => Category::all(['id', 'name']),
        ]);
    }

    /**
     * Store a submitted news article for approval.
     */
    public function store(StoreNewsRequest $request): RedirectResponse
    {
        // os consentimentos são validados mas não guardados (decisão do cliente).
        // O except() é explícito de propósito: sem ele as chaves chegavam ao
        // create() e eram descartadas em silêncio por não estarem no #[Fillable],
        // o que se parte no dia em que alguém ligar o Model::shouldBeStrict().
        $data = $request->safe()->except(['terms_conditions', 'image_rights']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('news', 'public');
        }

        $news = News::create($data);

        $this->notifyManagers(new NewSubmissionReceived(
            type: 'Notícia',
            title: $news->title,
            categoryName: $news->category?->name,
        ), $news->id);

        return back()->with('success', 'A tua notícia foi enviada para aprovação.');
    }
}
