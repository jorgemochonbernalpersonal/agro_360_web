@props([
    'icon',
    'title',
    'subtitle'   => null,
    'iconBg'     => 'bg-zinc-100',
    'iconColor'  => 'text-zinc-500',
    'size'       => 'sm',    // sm = w-9 h-9 / size-4 | md = w-10 h-10 / size-5
    'radius'     => 'full',  // full = rounded-full | xl = rounded-xl
])

@php
    $sizeMap = [
        'sm' => ['container' => 'w-9 h-9',   'icon' => 'size-4'],
        'md' => ['container' => 'w-10 h-10',  'icon' => 'size-5'],
    ];
    $s = $sizeMap[$size] ?? $sizeMap['sm'];
    $radiusClass = $radius === 'xl' ? 'rounded-xl' : 'rounded-full';
@endphp

<div class="flex items-center gap-3">
    <div class="{{ $s['container'] }} {{ $iconBg }} {{ $radiusClass }} flex items-center justify-center shrink-0">
        <flux:icon :$icon class="{{ $s['icon'] }} {{ $iconColor }}" />
    </div>
    <div class="flex-1 min-w-0">
        <p class="font-semibold text-zinc-900 text-sm truncate leading-tight">{{ $title }}</p>
        @if($subtitle)
            <p class="text-xs text-zinc-400 leading-tight mt-0.5 truncate">{{ $subtitle }}</p>
        @endif
    </div>
    @if($slot->isNotEmpty())
        <div class="shrink-0">{{ $slot }}</div>
    @endif
</div>
