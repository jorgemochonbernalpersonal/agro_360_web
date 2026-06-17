@props(['label', 'value', 'icon', 'href', 'color' => 'zinc', 'active' => null])

@php
// active=null → always colored (default); active=true → colored + activeBg; active=false → gray
$colorMap = [
    'indigo' => ['icon' => 'text-indigo-400', 'iconHover' => 'group-hover:text-indigo-500', 'value' => 'text-indigo-600', 'hover' => 'hover:border-indigo-300', 'activeBg' => 'border-indigo-300 bg-indigo-50/30'],
    'emerald'=> ['icon' => 'text-emerald-400','iconHover' => 'group-hover:text-emerald-500','value' => 'text-emerald-600','hover' => 'hover:border-emerald-300','activeBg' => 'border-emerald-300 bg-emerald-50/30'],
    'amber'  => ['icon' => 'text-amber-400',  'iconHover' => 'group-hover:text-amber-500',  'value' => 'text-amber-600',  'hover' => 'hover:border-amber-300',  'activeBg' => 'border-amber-300 bg-amber-50/30'],
    'rose'   => ['icon' => 'text-rose-400',   'iconHover' => 'group-hover:text-rose-500',   'value' => 'text-rose-600',   'hover' => 'hover:border-rose-300',   'activeBg' => 'border-rose-300 bg-rose-50/30'],
    'blue'   => ['icon' => 'text-blue-400',   'iconHover' => 'group-hover:text-blue-500',   'value' => 'text-blue-600',   'hover' => 'hover:border-blue-300',   'activeBg' => 'border-blue-300 bg-blue-50/30'],
    'red'    => ['icon' => 'text-red-400',    'iconHover' => 'group-hover:text-red-500',    'value' => 'text-red-600',    'hover' => 'hover:border-red-300',    'activeBg' => 'border-red-300 bg-red-50/30'],
    'zinc'   => ['icon' => 'text-zinc-400',   'iconHover' => 'group-hover:text-zinc-500',   'value' => 'text-zinc-600',   'hover' => 'hover:border-zinc-300',   'activeBg' => 'border-zinc-300 bg-zinc-50/30'],
];
$c = $colorMap[$color] ?? $colorMap['zinc'];
$colored = $active === null || $active === true;
$iconClass  = $colored ? "size-5 {$c['icon']} {$c['iconHover']} mb-2" : 'size-5 text-zinc-300 mb-2';
$valueClass = 'text-2xl font-bold leading-none ' . ($colored ? $c['value'] : 'text-zinc-400');
$cardExtra  = $active === true ? $c['activeBg'] : '';
@endphp

<a href="{{ $href }}" wire:navigate {{ $attributes->class(['block bg-white border border-zinc-200 rounded-2xl p-4 shadow-sm transition-colors group', $c['hover'], $cardExtra]) }}>
    <flux:icon :icon="$icon" class="{{ $iconClass }}" />
    <p class="{{ $valueClass }}">{{ $value }}</p>
    <p class="text-[11px] text-zinc-400 mt-1">{{ $label }}</p>
    @if($slot->isNotEmpty()){{ $slot }}@endif
</a>
