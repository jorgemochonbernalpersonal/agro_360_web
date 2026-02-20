@props(['label', 'value', 'description' => null, 'icon' => null, 'trend' => null, 'color' => 'agro'])

@php
$colorMap = [
    'agro'   => 'bg-agro-50 text-agro-700',
    'blue'   => 'bg-blue-50 text-blue-700',
    'red'    => 'bg-red-50 text-red-700',
    'yellow' => 'bg-yellow-50 text-yellow-700',
    'orange' => 'bg-orange-50 text-orange-700',
    'purple' => 'bg-purple-50 text-purple-700',
];
$iconColor = $colorMap[$color] ?? $colorMap['agro'];
$trendClasses = $trend > 0 ? 'text-green-600' : ($trend < 0 ? 'text-red-600' : 'text-zinc-500');
@endphp

<x-agro.card>
    <div class="flex items-center justify-between">
        <div class="flex-1 min-w-0">
            <flux:subheading>{{ $label }}</flux:subheading>
            <p class="mt-1 text-3xl font-semibold text-zinc-900">{{ $value }}</p>
            @if($description)
                <p class="mt-1 text-xs text-zinc-500">{{ $description }}</p>
            @endif
            @if($trend !== null)
                <p class="mt-1 flex items-center text-sm {{ $trendClasses }}">
                    @if($trend > 0)
                        <flux:icon icon="arrow-trending-up" variant="micro" class="mr-1" />
                    @elseif($trend < 0)
                        <flux:icon icon="arrow-trending-down" variant="micro" class="mr-1" />
                    @endif
                    <span>{{ abs($trend) }}%</span>
                </p>
            @endif
        </div>
        @if($icon)
            <div class="flex-shrink-0 p-3 rounded-lg {{ $iconColor }}">
                <flux:icon :$icon class="size-6" />
            </div>
        @endif
    </div>
</x-agro.card>
