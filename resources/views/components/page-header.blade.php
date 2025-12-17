@props([
    'icon' => '📋',
    'title',
    'description' => null,
    'iconColor' => 'from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]',
    'badgeIcon' => null,
    'badgeColor' => 'bg-[var(--color-agro-yellow)]',
])

<div class="glass-card rounded-2xl p-8 hover-lift">
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-6">
            <div class="relative">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br {{ $iconColor }} flex items-center justify-center shadow-lg animate-scale-in">
                    @if(str_starts_with($icon, '<svg'))
                        {!! $icon !!}
                    @else
                        <span class="text-4xl">{{ $icon }}</span>
                    @endif
                </div>
                @if($badgeIcon)
                    <div class="absolute -top-2 -right-2 w-8 h-8 rounded-full {{ $badgeColor }} flex items-center justify-center shadow-md">
                        @if(str_starts_with($badgeIcon, '<svg'))
                            {!! $badgeIcon !!}
                        @else
                            <span class="text-sm">{{ $badgeIcon }}</span>
                        @endif
                    </div>
                @endif
            </div>
            <div class="flex-1">
                <h1 class="text-4xl font-bold text-[var(--color-agro-green-dark)] mb-2">
                    {{ $title }}
                </h1>
                @if($description)
                    <p class="text-lg text-gray-600 flex items-center gap-2">
                        {{ $description }}
                    </p>
                @endif
            </div>
        </div>
        
        @isset($actionButton)
            <div class="flex-shrink-0">
                {{ $actionButton }}
            </div>
        @endisset
    </div>
</div>

