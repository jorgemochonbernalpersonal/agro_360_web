@props(['label', 'value', 'description' => null, 'icon' => null, 'trend' => null, 'color' => 'agro', 'link' => null, 'linkLabel' => null])

@php
$colorMap = [
    'agro'   => ['bg' => 'bg-agro-50',   'icon' => 'text-agro-600',   'value' => 'text-agro-700'],
    'blue'   => ['bg' => 'bg-blue-50',   'icon' => 'text-blue-600',   'value' => 'text-blue-700'],
    'red'    => ['bg' => 'bg-red-50',    'icon' => 'text-red-600',    'value' => 'text-red-700'],
    'yellow' => ['bg' => 'bg-yellow-50', 'icon' => 'text-yellow-600', 'value' => 'text-yellow-700'],
    'orange' => ['bg' => 'bg-orange-50', 'icon' => 'text-orange-600', 'value' => 'text-orange-700'],
    'purple' => ['bg' => 'bg-purple-50', 'icon' => 'text-purple-600', 'value' => 'text-purple-700'],
    'zinc'   => ['bg' => 'bg-zinc-100',  'icon' => 'text-zinc-500',   'value' => 'text-zinc-700'],
    'green'  => ['bg' => 'bg-green-50',  'icon' => 'text-green-600',  'value' => 'text-green-700'],
    'amber'  => ['bg' => 'bg-amber-50',  'icon' => 'text-amber-600',  'value' => 'text-amber-700'],
];
$c = $colorMap[$color] ?? $colorMap['agro'];
$trendClasses = $trend > 0 ? 'text-green-600' : ($trend < 0 ? 'text-red-600' : 'text-zinc-500');
@endphp

<div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-4 flex items-start gap-3 h-full">
    @if($icon)
        <div class="shrink-0 w-10 h-10 {{ $c['bg'] }} rounded-xl flex items-center justify-center mt-0.5">
            <flux:icon :$icon class="size-5 {{ $c['icon'] }}" />
        </div>
    @endif
    <div class="flex-1 min-w-0">
        <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest leading-none mb-2">{{ $label }}</p>
        <p class="text-2xl font-bold text-zinc-900 leading-none">{{ $value }}</p>
        @if($description)
            <p class="mt-1.5 text-xs text-zinc-500 leading-snug">{{ $description }}</p>
        @endif
        @if($trend !== null)
            <p class="mt-1.5 flex items-center gap-0.5 text-xs font-medium {{ $trendClasses }}">
                @if($trend > 0)
                    <flux:icon icon="arrow-trending-up" variant="micro" />
                @elseif($trend < 0)
                    <flux:icon icon="arrow-trending-down" variant="micro" />
                @endif
                {{ abs($trend) }}%
            </p>
        @endif
        @if($link)
            <a href="{{ $link }}" class="mt-2 inline-flex items-center gap-0.5 text-xs font-medium text-blue-600 hover:text-blue-800 transition-colors">
                {{ $linkLabel ?? __('Ver') }}
                <flux:icon icon="arrow-right" variant="micro" />
            </a>
        @endif
    </div>
</div>
