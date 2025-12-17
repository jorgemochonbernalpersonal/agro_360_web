@props(['variant' => 'primary', 'size' => 'md', 'type' => 'button', 'loading' => false])

@php
    $baseClasses = 'inline-flex items-center justify-center font-semibold transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none';
    
    $variants = [
        'primary' => 'bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] shadow-md hover:shadow-lg focus:ring-[var(--color-agro-green-dark)]/50 active:scale-[0.98]',
        'secondary' => 'bg-white border-2 border-gray-300 text-gray-700 hover:bg-gray-50 hover:border-gray-400 focus:ring-gray-500/50 shadow-sm hover:shadow',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500/50 shadow-md hover:shadow-lg active:scale-[0.98]',
        'ghost' => 'text-gray-700 hover:bg-gray-100 focus:ring-gray-500/50',
        'success' => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500/50 shadow-md hover:shadow-lg',
    ];
    
    $sizes = [
        'sm' => 'px-4 py-2 text-sm rounded-lg gap-2',
        'md' => 'px-6 py-3 text-base rounded-xl gap-2',
        'lg' => 'px-8 py-4 text-lg rounded-xl gap-3',
    ];
    
    $classes = $baseClasses . ' ' . $variants[$variant] . ' ' . $sizes[$size];
    
    $href = $attributes->get('href');
    $tag = $href ? 'a' : 'button';
@endphp

@if($tag === 'a')
    <a href="{{ $href }}" wire:navigate {{ $attributes->except(['href', 'variant', 'size', 'type', 'loading'])->merge(['class' => $classes]) }}>
        @if($loading)
            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }} @if($loading) disabled @endif>
        @if($loading)
            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @endif
        {{ $slot }}
    </button>
@endif

