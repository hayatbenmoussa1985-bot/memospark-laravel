<?php

use App\Http\Controllers\Api\V1\ActivityLogController;
use App\Http\Controllers\Api\V1\AdConfigController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BadgeController;
use App\Http\Controllers\Api\V1\CardController;
use App\Http\Controllers\Api\V1\ChildrenController;
use App\Http\Controllers\Api\V1\DeckController;
use App\Http\Controllers\Api\V1\FolderController;
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
| WrapApiResponse middleware wraps responses in { data: T } for mobile.
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
        Route::post('/firebase-sync', [AuthController::class, 'firebaseSync']);
        Route::post('/complete-profile', [AuthController::class, 'completeProfile']);
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
        Route::delete('/auth/me', [AuthController::class, 'deleteAccount']);

        // ── Users ─────────────────────────────────
        Route::get('/users/{uuid}', [UserController::class, 'show']);
        Route::patch('/users/{uuid}', [UserController::class, 'update']);
        Route::put('/users/{uuid}', [UserController::class, 'update']);
        Route::post('/users/{uuid}', [UserController::class, 'update']);
        Route::get('/users/{uuid}/stats', [UserController::class, 'stats']);
        Route::get('/users/{uuid}/decks-with-due-cards', [UserController::class, 'decksWithDueCards']);
        Route::post('/users/{uuid}/push-token', [UserController::class, 'pushToken']);
        Route::get('/users/{uuid}/notification-preferences', [UserController::class, 'notificationPreferences']);
        Route::put('/users/{uuid}/notification-preferences', [UserController::class, 'updateNotificationPreferences']);

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

        // ── Library (public deck browsing + organization) ──
        Route::prefix('library')->group(function () {
            Route::get('/categories', [LibraryController::class, 'categories']);
            Route::get('/categories/{slug}', [LibraryController::class, 'showCategory']);
            Route::get('/decks', [LibraryController::class, 'decks']);
            Route::get('/decks/featured', [LibraryController::class, 'featured']);
            Route::get('/decks/{identifier}', [LibraryController::class, 'showDeck']);
            Route::get('/decks/{identifier}/cards', [LibraryController::class, 'deckCards']);
            Route::post('/decks/{identifier}/start', [LibraryController::class, 'startDeck']);
            Route::post('/decks/{deckId}/rate', [LibraryController::class, 'rateDeck']);
            Route::get('/decks/{deckId}/my-rating', [LibraryController::class, 'myRating']);
            Route::get('/cards/{id}', [LibraryController::class, 'showCard']);
            Route::post('/cards/{id}/review', [LibraryController::class, 'reviewCard']);
            Route::post('/cards/{cardId}/answer-qcm', [LibraryController::class, 'answerQcm']);
            Route::get('/progress', [LibraryController::class, 'globalProgress']);
            Route::get('/progress/{identifier}', [LibraryController::class, 'deckProgress']);
            Route::post('/progress/{identifier}/start', [LibraryController::class, 'startDeck']);
            Route::get('/progress/{identifier}/study', [LibraryController::class, 'studyCards']);
            Route::post('/progress/{identifier}/complete', [LibraryController::class, 'completeDeckStudy']);
            Route::post('/progress/{identifier}/review', [LibraryController::class, 'reviewCard']);
            Route::get('/favorites', [LibraryController::class, 'favorites']);
            Route::post('/favorites/{deckId}', [LibraryController::class, 'addFavorite']);
            Route::delete('/favorites/{deckId}', [LibraryController::class, 'removeFavorite']);
            Route::get('/badges', [LibraryController::class, 'badges']);
            Route::get('/badges/earned', [LibraryController::class, 'earnedBadges']);
            Route::get('/badges/{slug}', [LibraryController::class, 'showBadge']);
            Route::post('/reports', [LibraryController::class, 'submitReport']);
            Route::get('/tree', [FolderController::class, 'tree']);
            Route::get('/assignments', [FolderController::class, 'assignments']);
            Route::get('/folders', [FolderController::class, 'index']);
            Route::post('/folders', [FolderController::class, 'store']);
            Route::patch('/folders/{id}', [FolderController::class, 'update']);
            Route::delete('/folders/{id}', [FolderController::class, 'destroy']);
            Route::post('/organize', [FolderController::class, 'organize']);
        });

        // ── Library-public aliases (backward compat for mobile) ──
        Route::prefix('library-public')->group(function () {
            Route::get('/categories', [LibraryController::class, 'categories']);
            Route::get('/categories/{slug}', [LibraryController::class, 'showCategory']);
            Route::get('/decks', [LibraryController::class, 'decks']);
            Route::get('/decks/featured', [LibraryController::class, 'featured']);
            Route::get('/decks/{identifier}', [LibraryController::class, 'showDeck']);
            Route::get('/decks/{identifier}/cards', [LibraryController::class, 'deckCards']);
            Route::post('/decks/{deckId}/rate', [LibraryController::class, 'rateDeck']);
            Route::get('/decks/{deckId}/my-rating', [LibraryController::class, 'myRating']);
            Route::get('/cards/{id}', [LibraryController::class, 'showCard']);
            Route::post('/cards/{cardId}/answer-qcm', [LibraryController::class, 'answerQcm']);
            Route::get('/progress', [LibraryController::class, 'globalProgress']);
            Route::get('/progress/{identifier}', [LibraryController::class, 'deckProgress']);
            Route::post('/progress/{identifier}/start', [LibraryController::class, 'startDeck']);
            Route::get('/progress/{identifier}/study', [LibraryController::class, 'studyCards']);
            Route::post('/progress/{identifier}/complete', [LibraryController::class, 'completeDeckStudy']);
            Route::post('/progress/{identifier}/review', [LibraryController::class, 'reviewCard']);
            Route::get('/favorites', [LibraryController::class, 'favorites']);
            Route::post('/favorites/{deckId}', [LibraryController::class, 'addFavorite']);
            Route::delete('/favorites/{deckId}', [LibraryController::class, 'removeFavorite']);
            Route::get('/badges', [LibraryController::class, 'badges']);
            Route::get('/badges/earned', [LibraryController::class, 'earnedBadges']);
            Route::get('/badges/{slug}', [LibraryController::class, 'showBadge']);
            Route::post('/reports', [LibraryController::class, 'submitReport']);
        });

        // ── Study Sessions ────────────────────────
        Route::post('/study-sessions', [StudySessionController::class, 'store']);
        Route::put('/study-sessions/{id}', [StudySessionController::class, 'update']);

        // ── Children ──────────────────────────────
        Route::get('/children/{childId}', [ChildrenController::class, 'show']);
        Route::get('/children/{childId}/stats', [ChildrenController::class, 'stats']);
        Route::get('/children/{childId}/badges', [ChildrenController::class, 'badges']);
        Route::get('/children/{childId}/activities', [ChildrenController::class, 'activities']);
        Route::put('/children/{childId}', [ChildrenController::class, 'update']);
        Route::patch('/children/{childId}', [ChildrenController::class, 'update']);

        // ── Learners (alias for children) ─────────
        Route::get('/learners/{learnerId}', [ChildrenController::class, 'show']);
        Route::put('/learners/{learnerId}', [ChildrenController::class, 'update']);
        Route::patch('/learners/{learnerId}', [ChildrenController::class, 'update']);

        // ── Parent (authenticated parent routes) ──
        Route::middleware('parent')->prefix('parent')->group(function () {
            Route::get('/children', [ParentController::class, 'children']);
            Route::get('/linked-children', [ParentController::class, 'children']);
            Route::get('/children/{id}/stats', [ParentController::class, 'childStats']);
            Route::post('/revision-plans', [ParentController::class, 'storeRevisionPlan']);
            Route::get('/revision-plans', [ParentController::class, 'revisionPlans']);
        });

        // ── Parents (profile-based routes) ────────
        Route::get('/parents/{parentId}', [ParentController::class, 'show']);
        Route::get('/parents/{parentId}/dashboard', [ParentController::class, 'dashboard']);
        Route::get('/parents/{parentId}/children', [ParentController::class, 'parentChildren']);

        // ── Parent-child links ────────────────────
        Route::post('/parent-child-links', [ParentController::class, 'linkChild']);
        Route::delete('/parent-child-links/{parentId}/{childId}', [ParentController::class, 'unlinkChild']);

        // ── Revision Plans ────────────────────────
        Route::post('/revision-plans', [ParentController::class, 'storeRevisionPlan']);
        Route::get('/revision-plans/child/{childId}', [ParentController::class, 'childRevisionPlans']);
        Route::patch('/revision-plans/{planId}', [ParentController::class, 'updateRevisionPlan']);

        // ── Invitations ───────────────────────────
        Route::post('/invitations', [ParentController::class, 'linkChild']);

        // ── Feedbacks ─────────────────────────────
        Route::post('/feedbacks', [ParentController::class, 'sendFeedback']);
        Route::get('/feedbacks/user/{userId}', [ParentController::class, 'userFeedback']);

        // ── Activity Logs ─────────────────────────
        Route::get('/activity-logs', [ActivityLogController::class, 'index']);
        Route::post('/activity-logs', [ActivityLogController::class, 'store']);
        Route::get('/activity-logs/{id}', [ActivityLogController::class, 'show']);

        // ── Badges ────────────────────────────────
        Route::get('/badges', [BadgeController::class, 'index']);
        Route::get('/badges/mine', [BadgeController::class, 'mine']);
        Route::get('/badges/earned', [BadgeController::class, 'mine']);
        Route::get('/badges/{slug}', [BadgeController::class, 'show']);
        Route::post('/badges/assign', [BadgeController::class, 'assign']);
        Route::post('/badges', [ParentController::class, 'awardBadge']);

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
