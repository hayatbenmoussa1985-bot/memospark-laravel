<x-user-layout :title="'Chat with ' . $child->name">

    <div class="max-w-2xl mx-auto">

        {{-- Header --}}
        <div class="mb-4">
            <a href="{{ route('user.parent.messages') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Messages
            </a>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 flex flex-col" style="height: 70vh;">

            {{-- Chat header --}}
            <div class="px-6 py-4 border-b border-gray-200 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-sm font-bold text-emerald-700">
                    {{ strtoupper(substr($child->name, 0, 1)) }}
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">{{ $child->name }}</h3>
                    <p class="text-xs text-gray-500">{{ $child->school_level ?? 'Student' }}</p>
                </div>
            </div>

            {{-- Messages --}}
            <div class="flex-1 overflow-y-auto p-4 space-y-3" id="messages-container">
                @forelse($messages as $msg)
                    <div class="flex {{ $msg->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-2xl text-sm {{ $msg->sender_id === auth()->id() ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-900' }}">
                            <p>{{ $msg->content }}</p>
                            <p class="text-xs mt-1 {{ $msg->sender_id === auth()->id() ? 'text-emerald-200' : 'text-gray-400' }}">
                                {{ $msg->created_at->format('H:i') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-sm text-gray-400">Start a conversation!</div>
                @endforelse
            </div>

            {{-- Input --}}
            <form method="POST" action="{{ route('user.parent.messages.send', $child) }}" class="p-4 border-t border-gray-200">
                @csrf
                <div class="flex gap-2">
                    <input type="text" name="content" placeholder="Type a message..." required autofocus
                           class="flex-1 text-sm border-gray-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-xl hover:bg-emerald-700">
                        Send
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto-scroll to bottom
        document.getElementById('messages-container').scrollTop = document.getElementById('messages-container').scrollHeight;
    </script>

</x-user-layout>
