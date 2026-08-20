<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class TestimonialSubmissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Testimonial/TestimonialForm', [
            'categories' => Category::all(['id', 'name'])
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:15'],
            'email' => ['required', 'email', 'regex:/^[a-zA-Z0-9._%+-]+@(cesae|cesaedigital)\.[a-zA-Z-]+$/i'],
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'description' => ['required', 'min:100', 'max:1050', 'string'],
            // the form has a "Nenhuma" option with an empty value, so no category is valid
            'category_id' => ['nullable', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);


        // checks whats under the position/key ['image'] of the array and saves in storage
        if ($request->hasFile('image')) {
            $validated['image'] = Storage::disk('public')->putFile('image', $request->file('image'));
        }

        Testimonial::create($validated);

        return back()->with('success', 'Testemunho foi enviado para aprovação.');
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
