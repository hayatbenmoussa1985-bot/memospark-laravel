@props(['percentage' => 0, 'size' => 80, 'strokeWidth' => 6, 'color' => 'emerald'])

@php
    $radius = ($size - $strokeWidth) / 2;
    $circumference = 2 * pi() * $radius;
    $offset = $circumference - ($percentage / 100) * $circumference;
    $colorMap = [
        'emerald' => '#10b981',
        'blue' => '#3b82f6',
        'amber' => '#f59e0b',
        'red' => '#ef4444',
        'purple' => '#8b5cf6',
    ];
    $strokeColor = $colorMap[$color] ?? $colorMap['emerald'];
@endphp

<div class="relative inline-flex items-center justify-center" style="width: {{ $size }}px; height: {{ $size }}px;">
    <svg class="transform -rotate-90" width="{{ $size }}" height="{{ $size }}">
        <circle cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $radius }}"
                stroke="#e5e7eb" stroke-width="{{ $strokeWidth }}" fill="transparent" />
        <circle cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $radius }}"
                stroke="{{ $strokeColor }}" stroke-width="{{ $strokeWidth }}" fill="transparent"
                stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}"
                stroke-linecap="round" class="transition-all duration-500" />
    </svg>
    <div class="absolute inset-0 flex items-center justify-center">
        <span class="text-sm font-bold text-gray-900">{{ round($percentage) }}%</span>
    </div>
</div>
