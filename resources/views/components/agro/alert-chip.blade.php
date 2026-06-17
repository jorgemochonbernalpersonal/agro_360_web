@props(['color' => 'amber', 'icon' => null, 'href' => null])

@php
$colorMap = [
    'red'    => 'bg-red-50 border-red-200 text-red-700 hover:bg-red-100',
    'rose'   => 'bg-rose-50 border-rose-200 text-rose-700 hover:bg-rose-100',
    'amber'  => 'bg-amber-50 border-amber-200 text-amber-700 hover:bg-amber-100',
    'orange' => 'bg-orange-50 border-orange-200 text-orange-700 hover:bg-orange-100',
    'blue'   => 'bg-blue-50 border-blue-200 text-blue-700 hover:bg-blue-100',
    'green'  => 'bg-green-50 border-green-200 text-green-700 hover:bg-green-100',
    'agro'   => 'bg-agro-50 border-agro-200 text-agro-700 hover:bg-agro-100',
];
$classes = 'inline-flex items-center gap-2 px-3 py-2 rounded-lg border text-sm transition ' . ($colorMap[$color] ?? $colorMap['amber']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->class([$classes]) }}>
        @if($icon)<flux:icon :icon="$icon" class="size-4 shrink-0" />@endif
        {{ $slot }}
    </a>
@else
    <div {{ $attributes->class([$classes]) }}>
        @if($icon)<flux:icon :icon="$icon" class="size-4 shrink-0" />@endif
        {{ $slot }}
    </div>
@endif
