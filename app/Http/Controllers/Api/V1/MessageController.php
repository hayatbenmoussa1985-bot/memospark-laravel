<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * GET /api/v1/messages
     * List conversations (unique chat partners).
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        // Get unique conversation partners
        $partners = Message::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($m) => $m->sender_id === $userId ? $m->receiver_id : $m->sender_id)
            ->unique()
            ->values();

        $conversations = $partners->map(function ($partnerId) use ($userId) {
            $partner = User::find($partnerId);
            $lastMessage = Message::betweenUsers($userId, $partnerId)
                ->orderByDesc('created_at')
                ->first();
            $unreadCount = Message::where('sender_id', $partnerId)
                ->where('receiver_id', $userId)
                ->unread()
                ->count();

            return [
                'partner' => [
                    'id' => $partner->id,
                    'uuid' => $partner->uuid,
                    'name' => $partner->name,
                    'avatar_path' => $partner->avatar_path,
                ],
                'last_message' => $lastMessage ? [
                    'content' => $lastMessage->content,
                    'is_mine' => $lastMessage->sender_id === $userId,
                    'created_at' => $lastMessage->created_at->toIso8601String(),
                ] : null,
                'unread_count' => $unreadCount,
            ];
        });

        return response()->json(['conversations' => $conversations]);
    }

    /**
     * GET /api/v1/messages/{userId}
     * Get messages with a specific user.
     */
    public function show(Request $request, int $userId): JsonResponse
    {
        $messages = Message::betweenUsers($request->user()->id, $userId)
            ->orderByDesc('created_at')
            ->paginate(50);

        // Mark received messages as read
        Message::where('sender_id', $userId)
            ->where('receiver_id', $request->user()->id)
            ->unread()
            ->update(['is_read' => true]);

        return response()->json([
            'messages' => $messages->getCollection()->map(fn ($m) => [
                'id' => $m->id,
                'content' => $m->content,
                'is_mine' => $m->sender_id === $request->user()->id,
                'is_read' => $m->is_read,
                'created_at' => $m->created_at->toIso8601String(),
            ]),
        ]);
    }

    /**
     * POST /api/v1/messages
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'receiver_id' => 'required|integer|exists:users,id',
            'content' => 'required|string|max:5000',
        ]);

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $request->receiver_id,
            'content' => $request->content,
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => [
                'id' => $message->id,
                'content' => $message->content,
                'created_at' => $message->created_at->toIso8601String(),
            ],
        ], 201);
    }
}
