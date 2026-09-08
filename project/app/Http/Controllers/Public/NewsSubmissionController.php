<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Concerns\NotifiesManagers;
use App\Http\Controllers\Concerns\StoresImages;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewsRequest;
use App\Mail\NewSubmissionReceived;
use App\Models\Category;
use App\Models\Image;
use App\Models\News;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class NewsSubmissionController extends Controller
{
    use NotifiesManagers, StoresImages;

    /**
     * Display the public news submission form.
     */
    public function create(): Response
    {
        return Inertia::render('News/InsertForm', [
            'categories' => Category::all(['id', 'name']),
            'maxImagens' => Image::MAX_POR_SUBMISSAO_NOTICIAS,
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
        // O images está aqui pela mesma razão: são ficheiros, e quem os grava
        // é o storeImages() logo a seguir, não o create().
        $data = $request->safe()->except(['terms_conditions', 'image_rights', 'images']);

        // ou entra a notícia com as imagens todas, ou não entra nada: uma
        // notícia gravada sem as fotos dá "enviado com sucesso" a quem
        // submeteu e chega ao gestor sem o que a pessoa escolheu
        $news = DB::transaction(function () use ($request, $data) {
            $news = News::create($data);

            if ($request->hasFile('images')) {
                $this->storeImages($news, $request->file('images'), 'news');
            }

            return $news;
        });

        $this->notifyManagers(new NewSubmissionReceived(
            type: 'Notícia',
            title: $news->title,
            categoryName: $news->category?->name,
        ), $news->id);

        return back()->with('success', 'A tua notícia foi enviada para aprovação.');
    }
}
