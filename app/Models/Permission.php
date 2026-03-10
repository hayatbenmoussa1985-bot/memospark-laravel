<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'slug',
        'name',
        'group',
        'description',
    ];

    /**
     * Default permissions to seed.
     */
    public const DEFAULTS = [
        ['slug' => 'manage_users', 'name' => 'Manage Users', 'group' => 'users', 'description' => 'Create, edit, deactivate users'],
        ['slug' => 'manage_decks', 'name' => 'Manage Decks', 'group' => 'content', 'description' => 'Manage all decks'],
        ['slug' => 'manage_library', 'name' => 'Manage Library', 'group' => 'content', 'description' => 'Manage public library decks'],
        ['slug' => 'manage_categories', 'name' => 'Manage Categories', 'group' => 'content', 'description' => 'Manage deck categories'],
        ['slug' => 'manage_subscriptions', 'name' => 'Manage Subscriptions', 'group' => 'billing', 'description' => 'Manage subscription plans and user subscriptions'],
        ['slug' => 'manage_reports', 'name' => 'Manage Reports', 'group' => 'moderation', 'description' => 'Review and resolve reported content'],
        ['slug' => 'manage_notifications', 'name' => 'Manage Notifications', 'group' => 'communication', 'description' => 'Send notifications to users'],
        ['slug' => 'manage_blog', 'name' => 'Manage Blog', 'group' => 'content', 'description' => 'Create and manage blog posts'],
        ['slug' => 'view_analytics', 'name' => 'View Analytics', 'group' => 'analytics', 'description' => 'Access analytics dashboard'],
        ['slug' => 'manage_settings', 'name' => 'Manage Settings', 'group' => 'system', 'description' => 'Manage application settings'],
        ['slug' => 'manage_ai', 'name' => 'Manage AI', 'group' => 'system', 'description' => 'Manage AI and OCR settings'],
    ];
}
