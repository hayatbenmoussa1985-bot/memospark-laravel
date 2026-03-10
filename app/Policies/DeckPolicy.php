<?php

namespace App\Policies;

use App\Models\Deck;
use App\Models\User;

class DeckPolicy
{
    /**
     * Anyone can view public/library decks. Owner can view private.
     */
    public function view(User $user, Deck $deck): bool
    {
        if ($deck->user_id === $user->id) {
            return true;
        }

        if ($user->isAdmin()) {
            return true;
        }

        // Parent can view child's decks
        if ($user->isParent() && $user->children()->where('users.id', $deck->user_id)->exists()) {
            return true;
        }

        return in_array($deck->visibility->value ?? $deck->visibility, ['public', 'library']);
    }

    /**
     * Only owner can update.
     */
    public function update(User $user, Deck $deck): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $deck->user_id === $user->id;
    }

    /**
     * Only owner can delete.
     */
    public function delete(User $user, Deck $deck): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $deck->user_id === $user->id;
    }
}
