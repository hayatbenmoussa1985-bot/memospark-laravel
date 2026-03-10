<?php

use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\DeckController;
use App\Http\Controllers\User\LibraryController;
use App\Http\Controllers\User\ParentController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\StudyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User Space Routes — /user/*
|--------------------------------------------------------------------------
|
| These routes require authentication.
| Middleware: web, auth
| Prefix: /user
| Name prefix: user.
|
| Both parent and learner portals are handled here,
| with role-specific middleware where needed.
|
*/

// ══════════════════════════════════════════════
// Dashboard (role-based)
// ══════════════════════════════════════════════
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// ══════════════════════════════════════════════
// Profile
// ══════════════════════════════════════════════
Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::put('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');

// ══════════════════════════════════════════════
// Learner: Decks
// ══════════════════════════════════════════════
Route::prefix('decks')->name('decks.')->group(function () {
    Route::get('/', [DeckController::class, 'index'])->name('index');
    Route::get('/create', [DeckController::class, 'create'])->name('create');
    Route::post('/', [DeckController::class, 'store'])->name('store');
    Route::get('/{deck}', [DeckController::class, 'show'])->name('show');
    Route::get('/{deck}/edit', [DeckController::class, 'edit'])->name('edit');
    Route::put('/{deck}', [DeckController::class, 'update'])->name('update');
    Route::delete('/{deck}', [DeckController::class, 'destroy'])->name('destroy');
    // Cards within deck
    Route::post('/{deck}/cards', [DeckController::class, 'storeCard'])->name('cards.store');
    Route::delete('/{deck}/cards/{card}', [DeckController::class, 'destroyCard'])->name('cards.destroy');
});

// ══════════════════════════════════════════════
// Learner: Study
// ══════════════════════════════════════════════
Route::prefix('study')->name('study.')->group(function () {
    Route::get('/due', [StudyController::class, 'due'])->name('due');
    Route::get('/start/{deck}', [StudyController::class, 'start'])->name('start');
    Route::post('/review', [StudyController::class, 'review'])->name('review');
    Route::post('/next-card', [StudyController::class, 'nextCard'])->name('next-card');
    Route::post('/complete', [StudyController::class, 'complete'])->name('complete');
});

// ══════════════════════════════════════════════
// Learner: Library
// ══════════════════════════════════════════════
Route::prefix('library')->name('library.')->group(function () {
    Route::get('/', [LibraryController::class, 'index'])->name('index');
    Route::get('/favorites', [LibraryController::class, 'favorites'])->name('favorites');
    Route::get('/{deck}', [LibraryController::class, 'show'])->name('show');
    Route::post('/{deck}/favorite', [LibraryController::class, 'favorite'])->name('favorite');
    Route::delete('/{deck}/favorite', [LibraryController::class, 'unfavorite'])->name('unfavorite');
});

// ══════════════════════════════════════════════
// Parent Portal (parent middleware)
// ══════════════════════════════════════════════
Route::middleware('parent')->prefix('parent')->name('parent.')->group(function () {
    Route::get('/', [ParentController::class, 'dashboard'])->name('dashboard');

    // Children
    Route::get('/children', [ParentController::class, 'children'])->name('children');
    Route::get('/children/{child}', [ParentController::class, 'childDetail'])->name('children.show');

    // Revision Plans
    Route::get('/plans', [ParentController::class, 'plans'])->name('plans.index');
    Route::get('/plans/create', [ParentController::class, 'createPlan'])->name('plans.create');
    Route::post('/plans', [ParentController::class, 'storePlan'])->name('plans.store');

    // Messages
    Route::get('/messages', [ParentController::class, 'messages'])->name('messages');
    Route::get('/messages/{child}', [ParentController::class, 'conversation'])->name('messages.show');
    Route::post('/messages/{child}', [ParentController::class, 'sendMessage'])->name('messages.send');
});
