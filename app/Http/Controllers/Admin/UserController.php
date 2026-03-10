<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * List all users with search and filter.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search by name or email
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        // Filter by status
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show user detail.
     */
    public function show(User $user)
    {
        $user->load([
            'decks' => fn ($q) => $q->withCount('cards')->latest()->take(10),
            'studySessions' => fn ($q) => $q->with('deck')->latest()->take(10),
            'subscriptions' => fn ($q) => $q->with('plan')->latest()->take(5),
            'badges',
        ]);

        $stats = [
            'total_decks' => $user->decks()->count(),
            'total_cards' => $user->decks()->withCount('cards')->get()->sum('cards_count'),
            'total_sessions' => $user->studySessions()->count(),
            'total_reviews' => $user->cardProgress()->sum('total_reviews'),
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    /**
     * Edit user form.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(array_column(UserRole::cases(), 'value'))],
            'is_active' => ['boolean'],
            'school_level' => ['nullable', 'string', 'max:100'],
            'locale' => ['nullable', 'string', 'max:5'],
        ]);

        $oldValues = $user->only(['name', 'email', 'role', 'is_active']);

        $user->update($validated);

        AuditLog::record(
            action: 'user_updated',
            targetType: 'user',
            targetId: $user->id,
            oldValues: $oldValues,
            newValues: $validated,
        );

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', "User {$user->name} updated successfully.");
    }

    /**
     * Toggle user active status.
     */
    public function toggleActive(User $user)
    {
        // Prevent deactivating yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        // Prevent deactivating super_admin (unless you are super_admin)
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            return back()->with('error', 'Only a super admin can deactivate another super admin.');
        }

        $user->update(['is_active' => !$user->is_active]);

        AuditLog::record(
            action: $user->is_active ? 'user_activated' : 'user_deactivated',
            targetType: 'user',
            targetId: $user->id,
        );

        $status = $user->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "User {$user->name} has been {$status}.");
    }

    /**
     * Reset user password.
     */
    public function resetPassword(User $user)
    {
        $newPassword = 'MemoSpark2024!';
        $user->update(['password' => Hash::make($newPassword)]);

        AuditLog::record(
            action: 'password_reset',
            targetType: 'user',
            targetId: $user->id,
        );

        return back()->with('success', "Password for {$user->name} has been reset to default.");
    }
}
