<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ModeratesSubmissions;
use App\Http\Controllers\Concerns\StoresImages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateNewsRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\News;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NewsController extends Controller
{
    use ModeratesSubmissions, StoresImages;

    /**
     * List the submitted news for moderation.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Admin/News/Index', [
            // 'images' também vem: a listagem mostra-as todas no modal de
            // detalhe, não só a espelho (SCRUM-142)
            'news' => News::with(['category:id,name', 'images'])
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
     * Update the content of a submitted news article.
     */
    public function update(UpdateNewsRequest $request, News $news): RedirectResponse
    {
        // o status não vem nas rules: editar conteúdo não muda o estado
        // 'images' fora: são ficheiros, quem os grava é o storeImages() a seguir
        $data = $request->safe()->except(['images']);

        $news->update($data);

        if ($request->hasFile('images')) {
            $this->storeImages($news, $request->file('images'), 'news');
        }

        ActivityLog::record($news, 'updated');

        return back()->with('success', 'Notícia atualizada com sucesso.');
    }

    /**
     * Approve a submitted news article.
     */
    public function approve(News $news): RedirectResponse
    {
        return $this->changeStatus($news, 'accepted', 'Notícia aprovada.');
    }

    /**
     * Refuse a submitted news article.
     */
    public function refuse(News $news): RedirectResponse
    {
        return $this->changeStatus($news, 'refused', 'Notícia recusada.');
    }
}
