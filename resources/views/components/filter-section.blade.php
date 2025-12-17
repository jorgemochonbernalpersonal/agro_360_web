@props(['title' => 'Filtros de Búsqueda', 'color' => 'green'])

@php
    $colorClasses = [
        'green' => 'text-[var(--color-agro-green-dark)]',
        'brown' => 'text-[var(--color-agro-brown-dark)]',
        'blue' => 'text-[var(--color-agro-blue)]',
    ];
    $colorClass = $colorClasses[$color] ?? $colorClasses['green'];
@endphp

<div class="glass-card rounded-xl p-6">
    <div class="flex items-center gap-3 mb-4">
        <svg class="w-5 h-5 {{ $colorClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
        </svg>
        <h2 class="text-lg font-semibold {{ $colorClass }}">{{ $title }}</h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {{ $slot }}
    </div>
    @if(isset($actions))
        <div class="mt-4 flex justify-end">
            {{ $actions }}
        </div>
    @endif
</div>

