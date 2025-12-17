@props([
    'icon',
    'title',
    'description',
    'href' => null,
    'iconGradient' => 'from-[var(--color-agro-green-light)] to-[var(--color-agro-green)]',
    'hoverBorder' => 'hover:border-[var(--color-agro-green-light)]/50',
    'hoverText' => 'group-hover:text-[var(--color-agro-green)]',
])

@php
    $tag = $href ? 'a' : 'div';
    $classAttr = $href ? 'group' : 'group cursor-pointer';
    $hrefAttr = $href ? 'href="' . e($href) . '"' : '';
@endphp

@if($href)
    <a href="{{ $href }}" class="{{ $classAttr }}">
@else
    <div class="{{ $classAttr }}">
@endif
    <div class="glass-card rounded-xl p-6 hover-lift h-full border-2 border-transparent {{ $hoverBorder }} transition-all duration-300">
        <div class="flex items-start justify-between mb-4">
            <div class="w-14 h-14 rounded-xl bg-gradient-to-br {{ $iconGradient }} flex items-center justify-center shadow-md group-hover:scale-110 transition-transform duration-300">
                @if(str_starts_with($icon, '<svg'))
                    {!! $icon !!}
                @else
                    <span class="text-3xl">{{ $icon }}</span>
                @endif
            </div>
            @if($href)
                <div class="w-8 h-8 rounded-lg bg-[var(--color-agro-green-bg)] flex items-center justify-center group-hover:bg-[var(--color-agro-green-light)]/20 transition-colors duration-300">
                    <svg class="w-4 h-4 text-[var(--color-agro-green-dark)] group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            @endif
        </div>
        <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2 {{ $hoverText }} transition-colors duration-300">
            {{ $title }}
        </h3>
        <p class="text-sm text-gray-600 leading-relaxed">
            {{ $description }}
        </p>
    </div>
@if($href)
    </a>
@else
    </div>
@endif

