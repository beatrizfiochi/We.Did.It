<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateNewsRequest;
use App\Models\Category;
use App\Models\News;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class NewsController extends Controller
{
    /**
     * Lista as notícias recebidas para o gestor rever.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/News/Index', [
            'news' => News::with('category:id,name')->latest()->get(),
        ]);
    }

    /**
     * Mostra o formulário de edição de uma notícia.
     */
    public function edit(News $news): Response
    {
        return Inertia::render('Admin/News/Edit', [
            'news' => $news->load('category:id,name'),
            'categories' => Category::all(['id', 'name']),
        ]);
    }

    /**
     * Atualiza o conteúdo de uma notícia.
     */
    public function update(UpdateNewsRequest $request, News $news): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('news', 'public');
        } else {
            unset($data['image']);
        }

        $news->update($data);

        return redirect()->route('admin.news.index')->with('success', 'Notícia atualizada com sucesso.');
    }

    /**
     * Aprova uma notícia recebida, tornando-a elegível para a newsletter.
     */
    public function approve(News $news): RedirectResponse
    {
        $news->update(['status' => 'accepted']);

        return redirect()->route('admin.news.index')->with('success', 'Notícia aprovada com sucesso.');
    }

    /**
     * Recusa uma notícia recebida.
     */
    public function refuse(News $news): RedirectResponse
    {
        $news->update(['status' => 'refused']);

        return redirect()->route('admin.news.index')->with('success', 'Notícia recusada com sucesso.');
    }
}
