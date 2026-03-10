<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\ContactController;
use App\Http\Controllers\Web\PageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — memospark.net (Site Vitrine)
|--------------------------------------------------------------------------
|
| Public-facing marketing and information pages.
| These routes require no authentication.
|
*/

// ══════════════════════════════════════════════
// Site Vitrine (public)
// ══════════════════════════════════════════════
Route::name('web.')->group(function () {
    Route::get('/', [PageController::class, 'home'])->name('home');
    Route::get('/guide', [PageController::class, 'guide'])->name('guide');

    // Contact
    Route::get('/contact', [ContactController::class, 'show'])->name('contact');
    Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');

    // Help section
    Route::get('/help', [PageController::class, 'help'])->name('help');
    Route::get('/help/get-started', [PageController::class, 'getStarted'])->name('help.get-started');
    Route::get('/help/faq', [PageController::class, 'faq'])->name('help.faq');
    Route::get('/help/video-tutorials', [PageController::class, 'videoTutorials'])->name('help.video-tutorials');

    // Legal
    Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('privacy');
    Route::get('/terms-of-service', [PageController::class, 'terms'])->name('terms');

    // Blog
    Route::get('/blog', [PageController::class, 'blogIndex'])->name('blog.index');
    Route::get('/blog/{slug}', [PageController::class, 'blogShow'])->name('blog.show');
});

// ══════════════════════════════════════════════
// Authenticated redirect (dashboard → user space)
// ══════════════════════════════════════════════
Route::get('/dashboard', function () {
    return redirect()->route('user.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// ══════════════════════════════════════════════
// Breeze profile management (auth required)
// ══════════════════════════════════════════════
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
