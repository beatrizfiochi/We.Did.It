<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    /**
     * List the categories for the management screen.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Categories/Index', [
            // as contagens permitem ao ecrã desativar o botão de remover
            'categories' => Category::withCount(['news', 'testimonials'])
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Store a new category.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $category = Category::create($request->validated());

        ActivityLog::record($category, 'created');

        return back()->with('success', 'Categoria criada com sucesso.');
    }

    /**
     * Update an existing category.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        ActivityLog::record($category, 'updated');

        return back()->with('success', 'Categoria atualizada com sucesso.');
    }

    /**
     * Remove a category, as long as nothing depends on it.
     */
    public function destroy(Category $category): RedirectResponse
    {
        // a foreign key é onDelete('restrict'): sem esta guarda, apagar uma
        // categoria em uso rebentava com QueryException em vez de dar erro de form
        if ($category->news()->exists() || $category->testimonials()->exists()) {
            return back()->withErrors([
                'category' => 'Não é possível remover uma categoria que está a ser usada.',
            ]);
        }

        $category->delete();

        ActivityLog::record($category, 'removed');

        return back()->with('success', 'Categoria removida com sucesso.');
    }
}
