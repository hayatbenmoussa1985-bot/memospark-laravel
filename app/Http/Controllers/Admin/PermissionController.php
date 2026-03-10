<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AdminPermission;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * List admins and their permissions.
     */
    public function index()
    {
        $admins = User::where('role', UserRole::Admin)
            ->with('adminPermissions.permission')
            ->get();

        $permissions = Permission::all();

        return view('admin.permissions.index', compact('admins', 'permissions'));
    }

    /**
     * Edit admin permissions.
     */
    public function edit(User $user)
    {
        if (!$user->isAdmin() || $user->isSuperAdmin()) {
            return redirect()
                ->route('admin.permissions.index')
                ->with('error', 'Can only manage permissions for admin users.');
        }

        $permissions = Permission::all();
        $userPermissionSlugs = $user->adminPermissions->pluck('permission_slug')->toArray();

        return view('admin.permissions.edit', compact('user', 'permissions', 'userPermissionSlugs'));
    }

    /**
     * Update admin permissions.
     */
    public function update(Request $request, User $user)
    {
        if (!$user->isAdmin() || $user->isSuperAdmin()) {
            return redirect()
                ->route('admin.permissions.index')
                ->with('error', 'Can only manage permissions for admin users.');
        }

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,slug'],
        ]);

        $newPermissions = $validated['permissions'] ?? [];
        $oldPermissions = $user->adminPermissions->pluck('permission_slug')->toArray();

        // Remove old permissions
        $user->adminPermissions()->delete();

        // Add new permissions
        foreach ($newPermissions as $slug) {
            AdminPermission::create([
                'user_id' => $user->id,
                'permission_slug' => $slug,
                'granted_by' => auth()->id(),
                'granted_at' => now(),
            ]);
        }

        AuditLog::record(
            action: 'permissions_updated',
            targetType: 'user',
            targetId: $user->id,
            oldValues: ['permissions' => $oldPermissions],
            newValues: ['permissions' => $newPermissions],
        );

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', "Permissions updated for {$user->name}.");
    }

    /**
     * Promote a user to admin role.
     */
    public function promote(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $user = User::findOrFail($validated['user_id']);

        if ($user->isAdmin()) {
            return back()->with('error', 'User is already an admin.');
        }

        $oldRole = $user->role->value;
        $user->update(['role' => UserRole::Admin]);

        AuditLog::record(
            action: 'user_promoted_to_admin',
            targetType: 'user',
            targetId: $user->id,
            oldValues: ['role' => $oldRole],
            newValues: ['role' => 'admin'],
        );

        return redirect()
            ->route('admin.permissions.edit', $user)
            ->with('success', "{$user->name} has been promoted to admin. Assign permissions below.");
    }

    /**
     * Demote an admin back to learner.
     */
    public function demote(User $user)
    {
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Cannot demote a super admin.');
        }

        if (!$user->isAdmin()) {
            return back()->with('error', 'User is not an admin.');
        }

        // Remove all permissions
        $user->adminPermissions()->delete();

        // Change role back to learner
        $user->update(['role' => UserRole::Learner]);

        AuditLog::record(
            action: 'admin_demoted',
            targetType: 'user',
            targetId: $user->id,
            oldValues: ['role' => 'admin'],
            newValues: ['role' => 'learner'],
        );

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', "{$user->name} has been demoted to learner.");
    }
}
