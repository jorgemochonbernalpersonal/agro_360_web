{{--
    x-agro.help-tip — Inline contextual help icon with popover
    Props:
      - text  : string — the help text to show (required)
      - title : string — optional heading inside popover
      - size  : 'xs'|'sm' (default xs)
--}}
@props([
    'text',
    'title' => null,
    'size'  => 'xs',
])

<span
    x-data="{ open: false }"
    @click.outside="open = false"
    class="relative inline-flex items-center"
>
    <button
        type="button"
        @click.stop="open = !open"
        class="inline-flex items-center justify-center rounded-full bg-zinc-100 hover:bg-blue-100 text-zinc-400 hover:text-blue-600 transition-colors focus:outline-none
               {{ $size === 'xs' ? 'w-4 h-4 text-[10px]' : 'w-5 h-5 text-xs' }}"
        title="Más información"
        aria-label="Ayuda"
    >
        ?
    </button>

    <span
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-1 w-64 rounded-xl bg-white border border-zinc-200 shadow-lg p-3 text-left"
        style="display: none;"
    >
        @if($title)
            <p class="text-xs font-semibold text-zinc-700 mb-1">{{ $title }}</p>
        @endif
        <p class="text-xs text-zinc-600 leading-relaxed">{{ $text }}</p>
    </span>
</span>
