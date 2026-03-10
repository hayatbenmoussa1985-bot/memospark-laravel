@props(['title' => 'No data found', 'message' => null, 'icon' => 'inbox'])

<div class="flex flex-col items-center justify-center py-12 text-center">
    <div class="p-4 rounded-full bg-gray-100 mb-4">
        @if($icon === 'inbox')
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
        @elseif($icon === 'search')
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        @endif
    </div>
    <h3 class="text-sm font-medium text-gray-900">{{ $title }}</h3>
    @if($message)
        <p class="mt-1 text-sm text-gray-500">{{ $message }}</p>
    @endif
    @if(isset($action))
        <div class="mt-4">{{ $action }}</div>
    @endif
</div>
