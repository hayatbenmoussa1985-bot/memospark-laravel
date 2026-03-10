<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Parent = 'parent';
    case Child = 'child';
    case Learner = 'learner';

    /**
     * Check if the role has admin-level access.
     */
    public function isAdmin(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin]);
    }

    /**
     * Check if the role is super admin (bypasses all permission checks).
     */
    public function isSuperAdmin(): bool
    {
        return $this === self::SuperAdmin;
    }

    /**
     * Check if the role is a parent.
     */
    public function isParent(): bool
    {
        return $this === self::Parent;
    }

    /**
     * Check if the role represents a studying user.
     */
    public function isStudyUser(): bool
    {
        return in_array($this, [self::Child, self::Learner]);
    }

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin => 'Admin',
            self::Parent => 'Parent',
            self::Child => 'Child',
            self::Learner => 'Learner',
        };
    }
}
