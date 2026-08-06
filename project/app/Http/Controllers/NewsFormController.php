<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class NewsFormController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function allNews()
    {
        return Inertia::render('News/InsertForm', [
            'news' => News::all(),
        ]);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function storeNews(Request $request)
    {

        // variable validated holds an array with all camps requested, validating each one
        $validated = $request->validate([
            'title' => 'required|string|min:10|max:255',
            'description' => 'required|string|min:100|max:1050',
            'image' => 'nullable|image|mimes:jpg,jpeg,png',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        /* if category_id has a value, keep it, otherwise replace with null,
        option "nenhuma" from <NewsForm/> holds no value, so it's passed as null to DB */
        $validated['category_id'] = $validated['category_id'] ?: null;

        $imagePath = null;

        // if request has a file in image camp, the path gets stored in the image column
        if ($request->hasFile('image')) {
            $imagePath = Storage::disk('public')->putFile('image', $request->file('image'));
        }

        $news = News::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'image' => $imagePath, // image is saved with the path
            'category_id' => $validated['category_id'],
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
