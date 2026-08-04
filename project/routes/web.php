<?php

use App\Http\Controllers\NewsFormController;
use App\Http\Controllers\ProfileController;
use App\Models\Category;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;



// Main routes that return Inertia responses



// this renders all the pages available through Inertia
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});




// this renders dashboard which is guarded by auth 
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


// this renders the form to insert news
Route::get('/newsForm', function () {
    return Inertia::render('News/InsertForm', [ // renders visual page 
        'categories' => Category::select('id', 'name')->get(), // categories gets pulled from DataBase
    ]);
})->name('newsForm');

Route::post('/newsForm', [NewsFormController::class, 'storeNews'])->name('news.store');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
