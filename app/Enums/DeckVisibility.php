<?php

namespace App\Enums;

enum DeckVisibility: string
{
    case Private = 'private';
    case Public = 'public';
    case Library = 'library';

    /**
     * Check if the deck is visible to everyone.
     */
    public function isPubliclyVisible(): bool
    {
        return in_array($this, [self::Public, self::Library]);
    }

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Private => 'Private',
            self::Public => 'Public',
            self::Library => 'Library',
        };
    }
}
