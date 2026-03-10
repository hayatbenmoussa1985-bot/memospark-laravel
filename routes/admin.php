<?php

use App\Http\Controllers\Admin\AdmobController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeckController;
use App\Http\Controllers\Admin\LibraryController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes — /admin/*
|--------------------------------------------------------------------------
|
| These routes require authentication + admin role.
| Middleware: web, auth, admin (EnsureIsAdmin)
| Prefix: /admin
| Name prefix: admin.
|
*/

// ══════════════════════════════════════════════
// Dashboard
// ══════════════════════════════════════════════
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// ══════════════════════════════════════════════
// Users (permission: manage_users)
// ══════════════════════════════════════════════
Route::middleware('permission:manage_users')->prefix('users')->name('users.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/{user}', [UserController::class, 'show'])->name('show');
    Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
    Route::put('/{user}', [UserController::class, 'update'])->name('update');
    Route::post('/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('toggle-active');
    Route::post('/{user}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');
});

// ══════════════════════════════════════════════
// Decks (permission: manage_decks)
// ══════════════════════════════════════════════
Route::middleware('permission:manage_decks')->prefix('decks')->name('decks.')->group(function () {
    Route::get('/', [DeckController::class, 'index'])->name('index');
    Route::get('/{deck}', [DeckController::class, 'show'])->name('show');
    Route::get('/{deck}/edit', [DeckController::class, 'edit'])->name('edit');
    Route::put('/{deck}', [DeckController::class, 'update'])->name('update');
    Route::post('/{deck}/toggle-featured', [DeckController::class, 'toggleFeatured'])->name('toggle-featured');
    Route::delete('/{deck}', [DeckController::class, 'destroy'])->name('destroy');
});

// ══════════════════════════════════════════════
// Library (permission: manage_library)
// ══════════════════════════════════════════════
Route::middleware('permission:manage_library')->prefix('library')->name('library.')->group(function () {
    Route::get('/', [LibraryController::class, 'index'])->name('index');
    // Categories
    Route::get('/categories/create', [LibraryController::class, 'createCategory'])->name('categories.create');
    Route::post('/categories', [LibraryController::class, 'storeCategory'])->name('categories.store');
    Route::get('/categories/{category}/edit', [LibraryController::class, 'editCategory'])->name('categories.edit');
    Route::put('/categories/{category}', [LibraryController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}', [LibraryController::class, 'destroyCategory'])->name('categories.destroy');
});

// ══════════════════════════════════════════════
// Subscriptions (permission: manage_subscriptions)
// ══════════════════════════════════════════════
Route::middleware('permission:manage_subscriptions')->prefix('subscriptions')->name('subscriptions.')->group(function () {
    Route::get('/', [SubscriptionController::class, 'index'])->name('index');
    Route::get('/plans/{plan}/edit', [SubscriptionController::class, 'editPlan'])->name('plans.edit');
    Route::put('/plans/{plan}', [SubscriptionController::class, 'updatePlan'])->name('plans.update');
});

// ══════════════════════════════════════════════
// Reports (permission: manage_reports)
// ══════════════════════════════════════════════
Route::middleware('permission:manage_reports')->prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/{report}', [ReportController::class, 'show'])->name('show');
    Route::post('/{report}/resolve', [ReportController::class, 'resolve'])->name('resolve');
    Route::post('/{report}/dismiss', [ReportController::class, 'dismiss'])->name('dismiss');
});

// ══════════════════════════════════════════════
// Blog (permission: manage_blog)
// ══════════════════════════════════════════════
Route::middleware('permission:manage_blog')->prefix('blog')->name('blog.')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('index');
    Route::get('/create', [BlogController::class, 'create'])->name('create');
    Route::post('/', [BlogController::class, 'store'])->name('store');
    Route::get('/{post}/edit', [BlogController::class, 'edit'])->name('edit');
    Route::put('/{post}', [BlogController::class, 'update'])->name('update');
    Route::delete('/{post}', [BlogController::class, 'destroy'])->name('destroy');
});

// ══════════════════════════════════════════════
// Notifications (permission: manage_notifications)
// ══════════════════════════════════════════════
Route::middleware('permission:manage_notifications')->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/create', [NotificationController::class, 'create'])->name('create');
    Route::post('/', [NotificationController::class, 'store'])->name('store');
});

// ══════════════════════════════════════════════
// Analytics (permission: view_analytics)
// ══════════════════════════════════════════════
Route::middleware('permission:view_analytics')->prefix('analytics')->name('analytics.')->group(function () {
    Route::get('/', [AnalyticsController::class, 'index'])->name('index');
});

// ══════════════════════════════════════════════
// AdMob / Ads (permission: manage_settings)
// ══════════════════════════════════════════════
Route::middleware('permission:manage_settings')->prefix('ads/admob')->name('admob.')->group(function () {
    Route::get('/', [AdmobController::class, 'index'])->name('index');
    Route::put('/', [AdmobController::class, 'update'])->name('update');
});

// ══════════════════════════════════════════════
// Settings (permission: manage_settings)
// ══════════════════════════════════════════════
Route::middleware('permission:manage_settings')->prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [SettingController::class, 'index'])->name('index');
    Route::put('/', [SettingController::class, 'update'])->name('update');
});

// ══════════════════════════════════════════════
// Audit Log (super_admin only)
// ══════════════════════════════════════════════
Route::middleware('super_admin')->prefix('audit-log')->name('audit-log.')->group(function () {
    Route::get('/', [AuditLogController::class, 'index'])->name('index');
    Route::get('/{auditLog}', [AuditLogController::class, 'show'])->name('show');
});

// ══════════════════════════════════════════════
// Permissions (super_admin only)
// ══════════════════════════════════════════════
Route::middleware('super_admin')->prefix('permissions')->name('permissions.')->group(function () {
    Route::get('/', [PermissionController::class, 'index'])->name('index');
    Route::get('/{user}/edit', [PermissionController::class, 'edit'])->name('edit');
    Route::put('/{user}', [PermissionController::class, 'update'])->name('update');
    Route::post('/promote', [PermissionController::class, 'promote'])->name('promote');
    Route::post('/{user}/demote', [PermissionController::class, 'demote'])->name('demote');
});
