<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewsRequest;
use App\Models\Category;
use App\Models\News;
use Inertia\Inertia;

class NewsSubmissionController extends Controller
{
    public function create() {
        return Inertia::render('Public/NewsForm', [
            'categories' => Category::all(['id', 'name']),
        ]);
    }

    public function store(StoreNewsRequest $request) {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('news', 'public');
        }

        News::create($data);

        return back()->with('success', 'A tua notícia foi enviada para aprovação.');
    }
}
