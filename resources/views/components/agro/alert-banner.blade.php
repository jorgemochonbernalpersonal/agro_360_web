@props([
    'tone'        => 'info',       // danger | warning | info | success | brand
    'icon'        => null,
    'title'       => null,
    'message'     => null,
    'actionLabel' => null,
    'actionRoute' => null,
    'dismissible' => false,
])

@php
$toneMap = [
    'danger'  => ['bg' => 'bg-red-50',    'border' => 'border-red-500',    'title' => 'text-red-900',    'text' => 'text-red-700',    'icon' => 'text-red-500',    'btn' => 'text-red-700 hover:text-red-900'],
    'warning' => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-500', 'title' => 'text-yellow-900', 'text' => 'text-yellow-700', 'icon' => 'text-yellow-500', 'btn' => 'text-yellow-700 hover:text-yellow-900'],
    'info'    => ['bg' => 'bg-blue-50',   'border' => 'border-blue-500',   'title' => 'text-blue-900',   'text' => 'text-blue-700',   'icon' => 'text-blue-500',   'btn' => 'text-blue-700 hover:text-blue-900'],
    'success' => ['bg' => 'bg-green-50',  'border' => 'border-green-500',  'title' => 'text-green-900',  'text' => 'text-green-700',  'icon' => 'text-green-500',  'btn' => 'text-green-700 hover:text-green-900'],
    'brand'   => ['bg' => 'bg-agro-50',   'border' => 'border-agro-500',   'title' => 'text-agro-900',   'text' => 'text-agro-700',   'icon' => 'text-agro-500',   'btn' => 'text-agro-700 hover:text-agro-900'],
];

$defaultIcons = [
    'danger'  => 'exclamation-triangle',
    'warning' => 'exclamation-circle',
    'info'    => 'information-circle',
    'success' => 'check-circle',
    'brand'   => 'sparkles',
];

$t    = $toneMap[$tone] ?? $toneMap['info'];
$ic   = $icon ?? $defaultIcons[$tone] ?? 'information-circle';
@endphp

<div class="{{ $t['bg'] }} border-l-4 {{ $t['border'] }} rounded-lg p-4 flex items-start gap-3"
    @if($dismissible) x-data="{ show: true }" x-show="show" @endif>

    <flux:icon :icon="$ic" class="size-5 {{ $t['icon'] }} shrink-0 mt-0.5" />

    <div class="flex-1 min-w-0">
        @if($title)
            <p class="text-sm font-semibold {{ $t['title'] }}">{{ $title }}</p>
        @endif
        @if($message)
            <p class="text-sm {{ $t['text'] }} {{ $title ? 'mt-0.5' : '' }}">{{ $message }}</p>
        @endif
        @if($slot->isNotEmpty())
            <div class="mt-1 text-sm {{ $t['text'] }}">{{ $slot }}</div>
        @endif
        @if($actionLabel && $actionRoute)
            <a href="{{ $actionRoute }}" class="mt-2 inline-block text-sm font-medium underline {{ $t['btn'] }}">
                {{ $actionLabel }} →
            </a>
        @endif
    </div>

    @if($dismissible)
        <button @click="show = false" class="shrink-0 {{ $t['icon'] }} hover:opacity-70 transition-opacity">
            <flux:icon icon="x-mark" class="size-4" />
        </button>
    @endif
</div>
