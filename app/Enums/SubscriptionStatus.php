<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case Trial = 'trial';

    /**
     * Check if the subscription grants access to premium features.
     */
    public function grantsAccess(): bool
    {
        return in_array($this, [self::Active, self::Trial]);
    }

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Expired => 'Expired',
            self::Cancelled => 'Cancelled',
            self::Trial => 'Trial',
        };
    }

    /**
     * Get CSS badge color class.
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::Active => 'green',
            self::Trial => 'blue',
            self::Expired => 'gray',
            self::Cancelled => 'red',
        };
    }
}
