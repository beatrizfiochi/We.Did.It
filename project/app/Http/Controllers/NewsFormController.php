<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Controller;
use Inertia\Inertia;

class NewsFormController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function allNews()
    {
        return Inertia::render('InsertForm', [
            'news' => News::all(),
        ]);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function storeNews(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string| max: 255',
            'description' => 'required |string| max: 1050',
            'image' => 'nullable|image|mimes:jpg,jpeg,png',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        /* if category_id has a value, keep it, otherwise replace with null,
        option "nenhuma" from <NewsForm/> holds no value, so it's passed as null to DB */
        $validated['category_id'] = $validated['category_id'] ?: null;

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = Storage::disk('public')->putFile('image', $request->file('image'));
        }

        $news = News::create([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $request->image,
            'category' => $request->category_id,
            'status' => 'received'
        ]);

        return redirect()->route('newsForm')->with('success', 'Notícia enviada');
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
