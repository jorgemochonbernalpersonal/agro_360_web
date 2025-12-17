@props([
    'title' => null,
    'content' => null,
    'gradient' => 'from-[var(--color-agro-green)] via-[var(--color-agro-green-light)] to-[var(--color-agro-green)]',
    'icon' => null,
])

<div class="relative overflow-hidden rounded-2xl bg-gradient-to-r {{ $gradient }} p-8 shadow-xl animate-slide-in-right">
    <div class="absolute inset-0 opacity-10">
        <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
            <pattern id="grid-pattern-{{ uniqid() }}" width="10" height="10" patternUnits="userSpaceOnUse">
                <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
            </pattern>
            <rect width="100" height="100" fill="url(#grid-pattern-{{ uniqid() }})" />
        </svg>
    </div>
    
    <div class="relative z-10">
        @if($title || $icon)
            <div class="flex items-center gap-3 mb-4">
                @if($icon)
                    @if(str_starts_with($icon, '<svg'))
                        {!! $icon !!}
                    @else
                        <span class="text-2xl">{{ $icon }}</span>
                    @endif
                @endif
                @if($title)
                    <span class="text-white/90 text-lg font-medium">{{ $title }}</span>
                @endif
            </div>
        @endif
        
        <div class="text-white/90 text-lg">
            {{ $content ?? $slot }}
        </div>
    </div>
    
    <div class="absolute -bottom-6 -right-6 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
    <div class="absolute -top-6 -left-6 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
</div>

