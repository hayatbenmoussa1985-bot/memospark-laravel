<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Notification management.
     */
    public function index()
    {
        $recentNotifications = Notification::with('user')
            ->latest('created_at')
            ->paginate(20);

        $totalSent = Notification::count();
        $unreadCount = Notification::unread()->count();

        return view('admin.notifications.index', compact(
            'recentNotifications',
            'totalSent',
            'unreadCount',
        ));
    }

    /**
     * Send notification form.
     */
    public function create()
    {
        $roles = ['all', 'learner', 'child', 'parent'];

        return view('admin.notifications.create', compact('roles'));
    }

    /**
     * Send notification to users.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'type' => ['required', 'string', 'max:50'],
            'target' => ['required', 'in:all,learner,child,parent,specific'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        // Determine target users
        $query = User::where('is_active', true);

        if ($validated['target'] === 'specific' && !empty($validated['user_ids'])) {
            $query->whereIn('id', $validated['user_ids']);
        } elseif ($validated['target'] !== 'all') {
            $query->where('role', $validated['target']);
        }

        $users = $query->get();
        $count = 0;

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'title' => $validated['title'],
                'message' => $validated['message'],
                'type' => $validated['type'],
                'data' => ['sent_by' => auth()->user()->name],
                'created_at' => now(),
            ]);
            $count++;
        }

        AuditLog::record(
            action: 'notification_sent',
            targetType: 'notification',
            targetId: 0,
            newValues: [
                'title' => $validated['title'],
                'target' => $validated['target'],
                'recipients_count' => $count,
            ],
        );

        return redirect()
            ->route('admin.notifications.index')
            ->with('success', "Notification sent to {$count} users.");
    }
}
