<?php

use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\NewsletterController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\NewsSubmissionController;
use Illuminate\Foundation\Application;
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

Route::post('/noticias', [NewsSubmissionController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('news.store');


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

    // CRUD das ofertas formativas
    Route::resource('courses', CourseController::class)->except('show');

    // CRUD dos eventos da agenda
    Route::resource('calendars', CalendarController::class)->except('show');

    // Gestão das notícias recebidas: editar/categorizar, aprovar e recusar
    Route::get('news', [NewsController::class, 'index'])->name('news.index');
    Route::get('news/{news}/edit', [NewsController::class, 'edit'])->name('news.edit');
    Route::put('news/{news}', [NewsController::class, 'update'])->name('news.update');
    Route::patch('news/{news}/approve', [NewsController::class, 'approve'])->name('news.approve');
    Route::patch('news/{news}/refuse', [NewsController::class, 'refuse'])->name('news.refuse');

    // Categorização dos testemunhos recebidos
    Route::get('testimonials', [TestimonialController::class, 'index'])->name('testimonials.index');
    Route::patch('testimonials/{testimonial}/category', [TestimonialController::class, 'category'])->name('testimonials.category');

    // Seleção das ofertas formativas incluídas numa newsletter
    Route::get('newsletters/{newsletter}/courses', [NewsletterController::class, 'editCourses'])->name('newsletters.courses.edit');
    Route::put('newsletters/{newsletter}/courses', [NewsletterController::class, 'updateCourses'])->name('newsletters.courses.update');
});

require __DIR__.'/auth.php';
