<x-user-layout title="Messages">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Messages</h1>
        <p class="text-sm text-gray-500">Conversations with your children</p>
    </div>

    <div class="space-y-2">
        @forelse($conversations as $child)
            <a href="{{ route('user.parent.messages.show', $child) }}"
               class="block bg-white rounded-xl border border-gray-200 p-4 hover:border-emerald-300 hover:shadow-sm transition-all">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-sm font-bold text-emerald-700 shrink-0">
                        {{ strtoupper(substr($child->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900">{{ $child->name }}</h3>
                            @if($child->last_message)
                                <span class="text-xs text-gray-400">{{ $child->last_message->created_at->diffForHumans() }}</span>
                            @endif
                        </div>
                        @if($child->last_message)
                            <p class="text-sm text-gray-500 truncate">
                                {{ $child->last_message->sender_id === auth()->id() ? 'You: ' : '' }}{{ Str::limit($child->last_message->content, 60) }}
                            </p>
                        @else
                            <p class="text-sm text-gray-400">No messages yet</p>
                        @endif
                    </div>
                    @if($child->unread_count > 0)
                        <span class="bg-emerald-500 text-white text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center shrink-0">
                            {{ $child->unread_count }}
                        </span>
                    @endif
                </div>
            </a>
        @empty
            <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                <p class="text-gray-500">No conversations yet.</p>
            </div>
        @endforelse
    </div>

</x-user-layout>
