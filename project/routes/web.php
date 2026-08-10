<?php

use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\CourseController;
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

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);

});




// this renders dashboard which is guarded by auth 
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


// public news submission form (SCRUM-77), rendered by the News/InsertForm page
Route::get('/noticias/nova', [NewsSubmissionController::class, 'create'])->name('news.create');

Route::post('/noticias', [NewsSubmissionController::class, 'store'])->name('news.store');


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
});

require __DIR__.'/auth.php';
