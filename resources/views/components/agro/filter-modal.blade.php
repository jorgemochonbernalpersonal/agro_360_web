@props([
    'name',
    'title'    => 'Filtros',
    'icon'     => 'adjustments-horizontal',
    'maxWidth' => 'sm',
    'wireReset' => null,   // Livewire method name to clear filters, e.g. 'resetFilters'
])

<x-agro.modal :$name :$maxWidth>
    {{-- Header --}}
    <div class="flex items-center gap-3 px-6 py-4 border-b border-zinc-200">
        <div class="w-8 h-8 bg-agro-50 rounded-lg flex items-center justify-center shrink-0">
            <flux:icon :icon="$icon" class="size-4 text-agro-600" />
        </div>
        <h3 class="text-base font-semibold text-zinc-900 flex-1">{{ $title }}</h3>
        <button
            type="button"
            x-on:click="$dispatch('close-modal', '{{ $name }}')"
            class="text-zinc-400 hover:text-zinc-600 transition-colors"
        >
            <flux:icon icon="x-mark" class="size-5" />
        </button>
    </div>

    {{-- Body --}}
    <div class="px-6 py-5 space-y-5">
        {{ $slot }}
    </div>

    {{-- Footer --}}
    <div class="flex items-center justify-between px-6 py-4 border-t border-zinc-200 bg-zinc-50 rounded-b-xl">
        @if($wireReset)
            <flux:button variant="ghost" wire:click="{{ $wireReset }}">
                Limpiar filtros
            </flux:button>
        @else
            <div></div>
        @endif
        <flux:button variant="primary" x-on:click="$dispatch('close-modal', '{{ $name }}')">
            Aplicar
        </flux:button>
    </div>
</x-agro.modal>
