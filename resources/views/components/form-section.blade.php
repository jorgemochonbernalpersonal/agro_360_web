@props([
    'title',
    'icon' => null,
    'color' => 'green',
])

@php
    $colorMap = [
        'green' => 'text-[var(--color-agro-green-dark)]',
        'blue' => 'text-[var(--color-agro-blue)]',
        'brown' => 'text-[var(--color-agro-brown-dark)]',
        'purple' => 'text-purple-700',
        'gray' => 'text-gray-700',
    ];
    $textColor = $colorMap[$color] ?? $colorMap['green'];
@endphp

<div class="border-b border-gray-200 pb-6 {{ $attributes->get('class') }}">
    <h3 class="text-lg font-bold {{ $textColor }} mb-4 flex items-center gap-2">
        @if($icon)
            {!! $icon !!}
        @else
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        @endif
        {{ $title }}
    </h3>
    {{ $slot }}
</div>

