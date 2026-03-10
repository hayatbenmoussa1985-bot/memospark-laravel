@props(['href', 'active' => false])

<a href="{{ $href }}" {{ $attributes->merge([
    'class' => $active
        ? 'px-3 py-2 text-sm font-medium text-emerald-700 bg-emerald-50 rounded-lg'
        : 'px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition-colors'
]) }}>
    {{ $slot }}
</a>
