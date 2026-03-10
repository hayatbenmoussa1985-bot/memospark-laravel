<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuid, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'locale',
        'timezone',
        'avatar_path',
        'date_of_birth',
        'school_level',
        'apple_user_id',
        'google_id',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'apple_user_id',
        'google_id',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'is_active' => 'boolean',
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'date_of_birth' => 'date',
            'password' => 'hashed',
        ];
    }

    // ──────────────────────────────────────────────
    // Role helpers
    // ──────────────────────────────────────────────

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isAdmin(): bool
    {
        return $this->role->isAdmin();
    }

    public function isParent(): bool
    {
        return $this->role === UserRole::Parent;
    }

    public function isChild(): bool
    {
        return $this->role === UserRole::Child;
    }

    public function isLearner(): bool
    {
        return $this->role === UserRole::Learner;
    }

    // ──────────────────────────────────────────────
    // Permission helpers (for admins)
    // ──────────────────────────────────────────────

    /**
     * Check if the user has a specific permission.
     * Super admins bypass all permission checks.
     */
    public function hasPermission(string $slug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->adminPermissions()->where('permission_slug', $slug)->exists();
    }

    // ──────────────────────────────────────────────
    // Subscription helpers
    // ──────────────────────────────────────────────

    /**
     * Check if the user has an active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription() !== null;
    }

    /**
     * Get the user's current active subscription.
     */
    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->whereIn('status', ['active', 'trial'])
            ->where('current_period_end', '>', now())
            ->latest('current_period_end')
            ->first();
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    /**
     * Decks authored by this user.
     */
    public function decks(): HasMany
    {
        return $this->hasMany(Deck::class);
    }

    /**
     * Card progress records for this user.
     */
    public function cardProgress(): HasMany
    {
        return $this->hasMany(CardProgress::class);
    }

    /**
     * Study sessions for this user.
     */
    public function studySessions(): HasMany
    {
        return $this->hasMany(StudySession::class);
    }

    /**
     * Review logs for this user.
     */
    public function reviewLogs(): HasMany
    {
        return $this->hasMany(ReviewLog::class);
    }

    /**
     * Subscriptions for this user.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Folders created by this user.
     */
    public function folders(): HasMany
    {
        return $this->hasMany(Folder::class);
    }

    /**
     * Favorite decks.
     */
    public function favoriteDecks(): BelongsToMany
    {
        return $this->belongsToMany(Deck::class, 'deck_favorites')
            ->withPivot('created_at');
    }

    /**
     * Badges earned by this user.
     */
    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot('awarded_by', 'awarded_at');
    }

    /**
     * Admin permissions granted to this user.
     */
    public function adminPermissions(): HasMany
    {
        return $this->hasMany(AdminPermission::class);
    }

    /**
     * Messages sent by this user.
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Messages received by this user.
     */
    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    /**
     * Notifications for this user.
     */
    public function userNotifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Activity logs for this user.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Revision plans created by this user (as parent).
     */
    public function revisionPlansAsParent(): HasMany
    {
        return $this->hasMany(RevisionPlan::class, 'parent_id');
    }

    /**
     * Revision plans assigned to this user (as child).
     */
    public function revisionPlansAsChild(): HasMany
    {
        return $this->hasMany(RevisionPlan::class, 'child_id');
    }

    /**
     * Blog posts authored by this user.
     */
    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class, 'author_id');
    }

    /**
     * OCR jobs created by this user.
     */
    public function ocrJobs(): HasMany
    {
        return $this->hasMany(OcrJob::class);
    }

    // ──────────────────────────────────────────────
    // Parent-Child relationships
    // ──────────────────────────────────────────────

    /**
     * Children of this parent (via parent_child pivot).
     */
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_child', 'parent_id', 'child_id')
            ->withPivot('relationship', 'permissions', 'created_at');
    }

    /**
     * Parent of this child (via parent_child pivot).
     * Each child has exactly one parent.
     */
    public function parent(): ?self
    {
        return self::query()
            ->join('parent_child', 'users.id', '=', 'parent_child.parent_id')
            ->where('parent_child.child_id', $this->id)
            ->select('users.*')
            ->first();
    }

    /**
     * The parent-child link record (for accessing pivot data).
     */
    public function parentChildLink(): HasOne
    {
        return $this->hasOne(ParentChild::class, 'child_id');
    }

    /**
     * Deck ratings given by this user.
     */
    public function deckRatings(): HasMany
    {
        return $this->hasMany(DeckRating::class);
    }
}
