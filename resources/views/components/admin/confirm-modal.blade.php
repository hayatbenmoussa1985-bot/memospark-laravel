@props(['id', 'title' => 'Confirm Action', 'message' => 'Are you sure?', 'confirmText' => 'Confirm', 'cancelText' => 'Cancel', 'danger' => false])

<div x-data="{ open: false }"
     x-on:open-modal-{{ $id }}.window="open = true"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center">

    {{-- Backdrop --}}
    <div x-show="open"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="absolute inset-0 bg-gray-900/50"
         @click="open = false">
    </div>

    {{-- Modal --}}
    <div x-show="open"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="relative bg-white rounded-xl shadow-xl max-w-md w-full mx-4 p-6">

        <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
        <p class="mt-2 text-sm text-gray-600">{{ $message }}</p>

        {{-- Optional extra content --}}
        @if(isset($body))
            <div class="mt-4">{{ $body }}</div>
        @endif

        <div class="mt-6 flex justify-end gap-3">
            <button @click="open = false" type="button"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                {{ $cancelText }}
            </button>
            {{ $slot }}
        </div>
    </div>
</div>
