@props(['streak' => 0])

@php
    $bgColor = match(true) {
        $streak >= 30 => 'bg-amber-100 text-amber-800 border-amber-200',
        $streak >= 7  => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        $streak >= 1  => 'bg-blue-100 text-blue-800 border-blue-200',
        default       => 'bg-gray-100 text-gray-600 border-gray-200',
    };
@endphp

<div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border text-sm font-medium {{ $bgColor }}">
    🔥 {{ $streak }} day{{ $streak !== 1 ? 's' : '' }} streak
</div>
