@props(['href', 'icon', 'color' => 'agro', 'label', 'description' => null])

@php
$colorMap = [
    'agro'   => ['bg' => 'bg-agro-50',   'hover_bg' => 'group-hover:bg-agro-100',   'text' => 'text-agro-600',   'hover_text' => 'group-hover:text-agro-600',   'hover_chevron' => 'group-hover:text-agro-400'],
    'orange' => ['bg' => 'bg-orange-50',  'hover_bg' => 'group-hover:bg-orange-100',  'text' => 'text-orange-600',  'hover_text' => 'group-hover:text-orange-600',  'hover_chevron' => 'group-hover:text-orange-400'],
    'blue'   => ['bg' => 'bg-blue-50',    'hover_bg' => 'group-hover:bg-blue-100',    'text' => 'text-blue-600',    'hover_text' => 'group-hover:text-blue-600',    'hover_chevron' => 'group-hover:text-blue-400'],
    'purple' => ['bg' => 'bg-purple-50',  'hover_bg' => 'group-hover:bg-purple-100',  'text' => 'text-purple-600',  'hover_text' => 'group-hover:text-purple-600',  'hover_chevron' => 'group-hover:text-purple-400'],
    'red'    => ['bg' => 'bg-red-50',     'hover_bg' => 'group-hover:bg-red-100',     'text' => 'text-red-600',     'hover_text' => 'group-hover:text-red-600',     'hover_chevron' => 'group-hover:text-red-400'],
    'teal'   => ['bg' => 'bg-teal-50',    'hover_bg' => 'group-hover:bg-teal-100',    'text' => 'text-teal-600',    'hover_text' => 'group-hover:text-teal-600',    'hover_chevron' => 'group-hover:text-teal-400'],
    'amber'  => ['bg' => 'bg-amber-50',   'hover_bg' => 'group-hover:bg-amber-100',   'text' => 'text-amber-600',   'hover_text' => 'group-hover:text-amber-600',   'hover_chevron' => 'group-hover:text-amber-400'],
    'violet' => ['bg' => 'bg-violet-50',  'hover_bg' => 'group-hover:bg-violet-100',  'text' => 'text-violet-600',  'hover_text' => 'group-hover:text-violet-600',  'hover_chevron' => 'group-hover:text-violet-400'],
    'indigo' => ['bg' => 'bg-indigo-50',  'hover_bg' => 'group-hover:bg-indigo-100',  'text' => 'text-indigo-600',  'hover_text' => 'group-hover:text-indigo-600',  'hover_chevron' => 'group-hover:text-indigo-400'],
];
$c = $colorMap[$color] ?? $colorMap['agro'];
@endphp

<x-agro.card class="hover-lift transition-all duration-200">
    <a href="{{ $href }}" class="flex items-center gap-4 group">
        <div class="w-10 h-10 rounded-lg {{ $c['bg'] }} flex items-center justify-center flex-shrink-0 {{ $c['hover_bg'] }} transition-colors">
            <flux:icon icon="{{ $icon }}" class="size-5 {{ $c['text'] }}" />
        </div>
        <div class="min-w-0">
            <p class="font-semibold text-zinc-900 {{ $c['hover_text'] }} transition-colors text-sm">{{ $label }}</p>
            @if($description)
                <p class="text-xs text-zinc-500 truncate">{{ $description }}</p>
            @endif
        </div>
        <flux:icon icon="chevron-right" class="size-4 text-zinc-300 ml-auto {{ $c['hover_chevron'] }} transition-colors" />
    </a>
</x-agro.card>
