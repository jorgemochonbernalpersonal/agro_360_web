@props(['href', 'icon', 'color' => 'agro', 'label', 'description' => null, 'locked' => false])

@php
$colorMap = [
    'agro'   => ['bg' => 'bg-agro-50',   'hover_bg' => 'group-hover:bg-agro-100',   'hover_border' => 'hover:border-agro-300',   'text' => 'text-agro-600'],
    'purple' => ['bg' => 'bg-purple-50',  'hover_bg' => 'group-hover:bg-purple-100',  'hover_border' => 'hover:border-purple-300',  'text' => 'text-purple-600'],
    'orange' => ['bg' => 'bg-orange-50',  'hover_bg' => 'group-hover:bg-orange-100',  'hover_border' => 'hover:border-orange-300',  'text' => 'text-orange-500'],
    'violet' => ['bg' => 'bg-violet-50',  'hover_bg' => 'group-hover:bg-violet-100',  'hover_border' => 'hover:border-violet-300',  'text' => 'text-violet-600'],
    'amber'  => ['bg' => 'bg-amber-50',   'hover_bg' => 'group-hover:bg-amber-100',   'hover_border' => 'hover:border-amber-300',   'text' => 'text-amber-600'],
    'blue'   => ['bg' => 'bg-blue-50',    'hover_bg' => 'group-hover:bg-blue-100',    'hover_border' => 'hover:border-blue-300',    'text' => 'text-blue-600'],
    'indigo' => ['bg' => 'bg-indigo-50',  'hover_bg' => 'group-hover:bg-indigo-100',  'hover_border' => 'hover:border-indigo-300',  'text' => 'text-indigo-600'],
    'zinc'   => ['bg' => 'bg-zinc-50',    'hover_bg' => 'group-hover:bg-zinc-100',    'hover_border' => 'hover:border-zinc-300',    'text' => 'text-zinc-600'],
];
$c = $colorMap[$color] ?? $colorMap['agro'];
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => "flex items-center gap-3 p-4 bg-white border border-zinc-200 rounded-2xl hover:shadow-md {$c['hover_border']} transition-all group"]) }}>
    <div class="w-10 h-10 rounded-xl {{ $c['bg'] }} flex items-center justify-center {{ $c['hover_bg'] }} transition-colors shrink-0">
        <flux:icon icon="{{ $icon }}" class="size-5 {{ $c['text'] }}" />
    </div>
    <div @class(['flex-1 min-w-0' => $locked])>
        <p class="text-sm font-semibold text-zinc-900">{{ $label }}</p>
        @if($description)
            <p class="text-xs text-zinc-400">{{ $description }}</p>
        @endif
    </div>
    @if($locked)
        <flux:icon icon="lock-closed" variant="micro" class="shrink-0 text-amber-400" />
    @endif
</a>
