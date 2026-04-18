@props(['key', 'label' => 'Estadísticas', 'columns' => 2])

@php
$gridClass = match((int) $columns) {
    1 => 'grid-cols-1',
    3 => 'grid-cols-3',
    4 => 'grid-cols-4',
    5 => 'grid-cols-5',
    default => 'grid-cols-2',
};
@endphp

<div x-data="{
    open: localStorage.getItem('{{ $key }}-stats-open') !== 'false',
    toggle() {
        this.open = !this.open;
        localStorage.setItem('{{ $key }}-stats-open', String(this.open));
    }
}">
    <button
        @click="toggle()"
        class="flex items-center gap-1.5 text-[11px] font-semibold text-zinc-400 uppercase tracking-widest hover:text-zinc-600 transition-colors mb-3"
    >
        <span>{{ $label }}</span>
        <flux:icon icon="chevron-up" class="size-3.5 transition-transform duration-200" ::class="{ 'rotate-180': !open }" />
    </button>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
    >
        <div class="grid {{ $gridClass }} gap-4">
            {{ $slot }}
        </div>
    </div>
</div>
