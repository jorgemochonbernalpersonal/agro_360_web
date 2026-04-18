@props(['modal', 'count' => 0])

<button
    x-on:click="$dispatch('open-modal', '{{ $modal }}')"
    {{ $attributes->merge(['class' => 'relative inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-50 shadow-sm transition-colors']) }}
>
    <flux:icon icon="adjustments-horizontal" class="size-4 text-zinc-500" />
    Filtros
    @if($count > 0)
        <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-agro-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">
            {{ $count }}
        </span>
    @endif
</button>
