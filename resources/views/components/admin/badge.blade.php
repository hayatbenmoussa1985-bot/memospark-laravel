@props(['color' => 'gray'])

@php
$colorMap = [
    'gray'    => 'bg-gray-100 text-gray-700',
    'emerald' => 'bg-emerald-100 text-emerald-700',
    'green'   => 'bg-green-100 text-green-700',
    'blue'    => 'bg-blue-100 text-blue-700',
    'purple'  => 'bg-purple-100 text-purple-700',
    'amber'   => 'bg-amber-100 text-amber-700',
    'yellow'  => 'bg-yellow-100 text-yellow-700',
    'red'     => 'bg-red-100 text-red-700',
    'indigo'  => 'bg-indigo-100 text-indigo-700',
    'pink'    => 'bg-pink-100 text-pink-700',
];
$classes = $colorMap[$color] ?? $colorMap['gray'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium $classes"]) }}>
    {{ $slot }}
</span>
