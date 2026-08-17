<?php

use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\NewsletterController;
use App\Http\Controllers\Admin\TestimonialController;
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

Route::post('/noticias', [NewsSubmissionController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('news.store');

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

    // CRUD das ofertas formativas. O URL é em português como o resto do admin,
    // mas os nomes das rotas mantêm-se em inglês (admin.courses.*).
    Route::resource('cursos', CourseController::class)
        ->except('show')
        ->parameters(['cursos' => 'course'])
        ->names('courses');

    // Moderação de notícias e testemunhos (SCRUM-86).
    // PATCH para aprovar/recusar/categorizar, que mudam um campo; PUT para o
    // update, que substitui o conteúdo do registo.
    Route::get('noticias', [NewsController::class, 'index'])->name('news.index');
    Route::get('noticias/{news}/editar', [NewsController::class, 'edit'])->name('news.edit');
    Route::put('noticias/{news}', [NewsController::class, 'update'])->name('news.update');
    Route::patch('noticias/{news}/aprovar', [NewsController::class, 'approve'])->name('news.approve');
    Route::patch('noticias/{news}/recusar', [NewsController::class, 'refuse'])->name('news.refuse');

    // Gestão da agenda (SCRUM-100), incluindo as newsletters onde cada evento entra
    Route::resource('agenda', CalendarController::class)
        ->except('show')
        ->parameters(['agenda' => 'calendar'])
        ->names('calendar');

    Route::get('testemunhos', [TestimonialController::class, 'index'])->name('testimonials.index');
    Route::put('testemunhos/{testimonial}', [TestimonialController::class, 'update'])->name('testimonials.update');
    Route::patch('testemunhos/{testimonial}/aprovar', [TestimonialController::class, 'approve'])->name('testimonials.approve');
    Route::patch('testemunhos/{testimonial}/recusar', [TestimonialController::class, 'refuse'])->name('testimonials.refuse');
    Route::patch('testemunhos/{testimonial}/categoria', [TestimonialController::class, 'category'])->name('testimonials.category');

    // Seleção das ofertas formativas incluídas numa newsletter
    Route::get('newsletters/{newsletter}/cursos', [NewsletterController::class, 'editCourses'])->name('newsletters.courses.edit');
    Route::put('newsletters/{newsletter}/cursos', [NewsletterController::class, 'updateCourses'])->name('newsletters.courses.update');
});

require __DIR__.'/auth.php';
