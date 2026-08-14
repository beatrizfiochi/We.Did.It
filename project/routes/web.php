<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\NewsSubmissionController;
use App\Http\Controllers\Public\TestimonialSubmissionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;



// Main routes that return Inertia responses



// this renders all the pages available through Inertia
Route::get('/', function () {
    return Inertia::render('Welcome');
});




// this renders dashboard which is guarded by auth 
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


// public news submission form (SCRUM-77), rendered by the News/InsertForm page
Route::get('/noticias/nova', [NewsSubmissionController::class, 'create'])->name('news.create');

Route::post('/noticias', [NewsSubmissionController::class, 'store'])->name('news.store');


// public testimonial submission form (SCRUM-90), rendered by the Testimonials/InsertForm page
Route::get('/testemunhos/novo', [TestimonialSubmissionController::class, 'create'])->name('testimonials.create');

Route::post('/testemunhos', [TestimonialSubmissionController::class, 'store'])->name('testimonials.store');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('users/create', [RegisteredUserController::class, 'create'])
        ->name('users.create');

    Route::post('users', [RegisteredUserController::class, 'store'])
        ->name('users.store');

    // CRUD de categorias (SCRUM-89). O ->parameters() é obrigatório: sem ele o
    // parâmetro chama-se {categoria} e o route model binding não resolve a Category.
    Route::resource('categorias', CategoryController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['categorias' => 'category'])
        ->names('categories');
});

require __DIR__.'/auth.php';
