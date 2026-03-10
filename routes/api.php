<?php

use App\Http\Controllers\Api\V1\AdConfigController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BadgeController;
use App\Http\Controllers\Api\V1\CardController;
use App\Http\Controllers\Api\V1\DeckController;
use App\Http\Controllers\Api\V1\LibraryController;
use App\Http\Controllers\Api\V1\MessageController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OcrController;
use App\Http\Controllers\Api\V1\ParentController;
use App\Http\Controllers\Api\V1\StudySessionController;
use App\Http\Controllers\Api\V1\UploadController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 Routes — /api/v1/
|--------------------------------------------------------------------------
|
| Routes for the iOS/mobile app.
| ForceJsonResponse middleware is applied globally via bootstrap/app.php.
|
*/

Route::prefix('v1')->group(function () {

    // ══════════════════════════════════════════════
    // PUBLIC (no auth required)
    // ══════════════════════════════════════════════

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/signup', [AuthController::class, 'signup']);
        Route::post('/social/google', [AuthController::class, 'socialGoogle']);
        Route::post('/social/apple', [AuthController::class, 'socialApple']);
    });

    // Ad config (public — called by mobile app before auth)
    Route::get('/config/ads', [AdConfigController::class, 'getConfig']);

    // n8n Webhook (secured via X-API-Key header, not Sanctum)
    Route::post('/webhooks/n8n', [WebhookController::class, 'n8n']);

    // ══════════════════════════════════════════════
    // AUTHENTICATED (Sanctum token required)
    // ══════════════════════════════════════════════

    Route::middleware('auth:sanctum')->group(function () {

        // ── Auth ──────────────────────────────────
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/change-password', [AuthController::class, 'changePassword']);
        Route::post('/auth/delete-account', [AuthController::class, 'deleteAccount']);

        // ── Users ─────────────────────────────────
        Route::get('/users/{uuid}', [UserController::class, 'show']);
        Route::patch('/users/{uuid}', [UserController::class, 'update']);
        Route::get('/users/{uuid}/stats', [UserController::class, 'stats']);
        Route::get('/users/{uuid}/decks-with-due-cards', [UserController::class, 'decksWithDueCards']);
        Route::post('/users/{uuid}/push-token', [UserController::class, 'pushToken']);

        // ── Decks ─────────────────────────────────
        Route::get('/decks', [DeckController::class, 'index']);
        Route::post('/decks', [DeckController::class, 'store']);
        Route::get('/decks/search', [DeckController::class, 'search']);
        Route::get('/decks/favorites', [DeckController::class, 'favorites']);
        Route::get('/decks/{uuid}', [DeckController::class, 'show']);
        Route::put('/decks/{uuid}', [DeckController::class, 'update']);
        Route::delete('/decks/{uuid}', [DeckController::class, 'destroy']);
        Route::post('/decks/{uuid}/favorite', [DeckController::class, 'favorite']);
        Route::delete('/decks/{uuid}/favorite', [DeckController::class, 'unfavorite']);

        // ── Cards ─────────────────────────────────
        Route::get('/decks/{uuid}/cards', [CardController::class, 'index']);
        Route::post('/decks/{uuid}/cards', [CardController::class, 'store']);
        Route::get('/cards/{uuid}', [CardController::class, 'show']);
        Route::put('/cards/{uuid}', [CardController::class, 'update']);
        Route::delete('/cards/{uuid}', [CardController::class, 'destroy']);
        Route::post('/cards/{uuid}/review', [CardController::class, 'review']);

        // ── Library (public decks) ────────────────
        Route::get('/library/categories', [LibraryController::class, 'categories']);
        Route::get('/library/decks', [LibraryController::class, 'decks']);
        Route::get('/library/decks/featured', [LibraryController::class, 'featured']);
        Route::get('/library/decks/{uuid}', [LibraryController::class, 'showDeck']);
        Route::get('/library/decks/{uuid}/cards', [LibraryController::class, 'deckCards']);
        Route::post('/library/decks/{uuid}/start', [LibraryController::class, 'startDeck']);
        Route::post('/library/cards/{id}/review', [LibraryController::class, 'reviewCard']);

        // ── Study Sessions ────────────────────────
        Route::post('/study-sessions', [StudySessionController::class, 'store']);
        Route::put('/study-sessions/{id}', [StudySessionController::class, 'update']);

        // ── Parent ────────────────────────────────
        Route::middleware('parent')->prefix('parent')->group(function () {
            Route::get('/children', [ParentController::class, 'children']);
            Route::get('/children/{id}/stats', [ParentController::class, 'childStats']);
            Route::post('/revision-plans', [ParentController::class, 'storeRevisionPlan']);
            Route::get('/revision-plans', [ParentController::class, 'revisionPlans']);
        });

        // ── Badges ────────────────────────────────
        Route::get('/badges', [BadgeController::class, 'index']);
        Route::get('/badges/mine', [BadgeController::class, 'mine']);

        // ── Messages ──────────────────────────────
        Route::get('/messages', [MessageController::class, 'index']);
        Route::get('/messages/{userId}', [MessageController::class, 'show']);
        Route::post('/messages', [MessageController::class, 'store']);

        // ── Notifications ─────────────────────────
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

        // ── Uploads ───────────────────────────────
        Route::post('/uploads/avatar', [UploadController::class, 'avatar']);
        Route::post('/uploads/image', [UploadController::class, 'image']);
        Route::post('/uploads/audio', [UploadController::class, 'audio']);

        // ── OCR / AI ──────────────────────────────
        Route::middleware('subscribed')->group(function () {
            Route::post('/ocr/upload', [OcrController::class, 'upload']);
            Route::get('/ocr/jobs/{id}', [OcrController::class, 'status']);
        });
    });
});
