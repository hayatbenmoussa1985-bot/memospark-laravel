@props(['placeholder' => 'Search...', 'name' => 'search', 'value' => ''])

<form method="GET" class="relative">
    {{-- Preserve existing query params except search and page --}}
    @foreach(request()->except([$name, 'page']) as $key => $val)
        @if(is_string($val))
            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
        @endif
    @endforeach

    <div class="relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text"
               name="{{ $name }}"
               value="{{ $value ?: request($name) }}"
               placeholder="{{ $placeholder }}"
               class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
    </div>
</form>
