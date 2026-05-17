@props([
    'variant' => 'default',
    'icon'    => null,
])

@php
// Semantic variant → icon + hover color
$semanticMap = [
    'view'       => ['icon' => 'eye',                'color' => 'default'],
    'edit'       => ['icon' => 'pencil-square',      'color' => 'default'],
    'delete'     => ['icon' => 'trash',              'color' => 'danger'],
    'archive'    => ['icon' => 'archive-box',        'color' => 'warning'],
    'restore'    => ['icon' => 'arrow-path',         'color' => 'primary'],
    'activate'   => ['icon' => 'check-circle',       'color' => 'success'],
    'deactivate' => ['icon' => 'no-symbol',          'color' => 'danger'],
    'map'        => ['icon' => 'map',                'color' => 'default'],
    'info'       => ['icon' => 'information-circle', 'color' => 'default'],
    'generate'   => ['icon' => 'sparkles',           'color' => 'primary'],
    'history'    => ['icon' => 'clock',              'color' => 'default'],
    'planting'   => ['icon' => 'book-open',          'color' => 'default'],
    'send'       => ['icon' => 'paper-airplane',     'color' => 'primary'],
    'download'   => ['icon' => 'arrow-down-tray',    'color' => 'default'],
];

$colorClasses = [
    'default' => 'text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100',
    'danger'  => 'text-zinc-400 hover:text-red-500 hover:bg-red-50',
    'warning' => 'text-zinc-400 hover:text-amber-600 hover:bg-amber-50',
    'primary' => 'text-zinc-400 hover:text-agro-600 hover:bg-agro-50',
    'success' => 'text-zinc-400 hover:text-green-600 hover:bg-green-50',
];

if (isset($semanticMap[$variant])) {
    $resolvedIcon = $icon ?? $semanticMap[$variant]['icon'];
    $colorKey     = $semanticMap[$variant]['color'];
} else {
    $resolvedIcon = $icon ?? 'ellipsis-horizontal';
    $colorKey     = $variant;
}

$base = 'inline-flex items-center justify-center w-8 h-8 rounded-lg transition-colors '
    . ($colorClasses[$colorKey] ?? $colorClasses['default']);
@endphp

@if($attributes->has('href'))
    <a {{ $attributes->merge(['class' => $base]) }}>
        <flux:icon :icon="$resolvedIcon" variant="outline" class="size-4" />
    </a>
@else
    <button {{ $attributes->merge(['class' => $base]) }}>
        <flux:icon :icon="$resolvedIcon" variant="outline" class="size-4" />
    </button>
@endif
