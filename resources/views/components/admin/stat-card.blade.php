@props(['title', 'value', 'icon' => null, 'trend' => null, 'trendUp' => true, 'color' => 'emerald', 'href' => null])

@php
$colorMap = [
    'emerald' => 'bg-emerald-50 text-emerald-600',
    'blue'    => 'bg-blue-50 text-blue-600',
    'purple'  => 'bg-purple-50 text-purple-600',
    'amber'   => 'bg-amber-50 text-amber-600',
    'red'     => 'bg-red-50 text-red-600',
    'indigo'  => 'bg-indigo-50 text-indigo-600',
];
$iconClasses = $colorMap[$color] ?? $colorMap['emerald'];
@endphp

<div class="bg-white rounded-xl border border-gray-200 p-6 {{ $href ? 'hover:border-emerald-300 transition-colors' : '' }}">
    @if($href)<a href="{{ $href }}" class="block">@endif

    <div class="flex items-start justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500">{{ $title }}</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $value }}</p>
            @if($trend)
                <p class="mt-1 text-sm {{ $trendUp ? 'text-emerald-600' : 'text-red-600' }}">
                    @if($trendUp)
                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    @else
                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    @endif
                    {{ $trend }}
                </p>
            @endif
        </div>
        @if($icon)
            <div class="p-3 rounded-lg {{ $iconClasses }}">
                {!! $icon !!}
            </div>
        @endif
    </div>

    @if($href)</a>@endif
</div>
