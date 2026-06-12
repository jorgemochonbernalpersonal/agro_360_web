@props([
    'name',
    'title' => null,
    'icon' => 'adjustments-horizontal',
    'maxWidth' => 'sm',
    'hasActiveFilters' => false,
    'clearAction' => null,
    'applyLabel' => null,
])

{{--
    Modal de filtros reutilizable.

    Encapsula el patrón header (icono + título + cerrar) / cuerpo / footer
    (limpiar + aplicar) que antes se reescribía inline en cada vista de listado.

    Uso:
        <x-agro.filter-modal
            name="plot-filters"
            :hasActiveFilters="$filterAutonomousCommunity || $filterProvince"
            clearAction="$set('filterAutonomousCommunity', ''); $set('filterProvince', '')"
        >
            <x-agro.field-label>{{ __('Comunidad Autónoma') }}</x-agro.field-label>
            <flux:select wire:model.live="filterAutonomousCommunity"> ... </flux:select>
        </x-agro.filter-modal>
--}}

<x-agro.modal :name="$name" :maxWidth="$maxWidth">
    {{-- Header --}}
    <div class="px-6 py-4 border-b border-zinc-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-agro-50 rounded-lg flex items-center justify-center">
                    <flux:icon :icon="$icon" class="size-4 text-agro-700" />
                </div>
                <h3 class="text-base font-semibold text-zinc-900">{{ $title ?? __('Filtros') }}</h3>
            </div>
            <flux:button x-on:click="$dispatch('close-modal', '{{ $name }}')" variant="ghost" size="sm" icon="x-mark" />
        </div>
    </div>

    {{-- Cuerpo: campos de filtro --}}
    <div class="px-6 py-5 space-y-5">
        {{ $slot }}
    </div>

    {{-- Footer --}}
    <div class="px-6 py-4 border-t border-zinc-200 flex items-center justify-between">
        @if($hasActiveFilters && $clearAction)
            <button
                wire:click="{{ $clearAction }}"
                class="text-sm text-zinc-400 hover:text-zinc-600 transition-colors"
            >
                {{ __('Limpiar filtros') }}
            </button>
        @else
            <span></span>
        @endif
        <flux:button x-on:click="$dispatch('close-modal', '{{ $name }}')" variant="primary">
            {{ $applyLabel ?? __('Aplicar') }}
        </flux:button>
    </div>
</x-agro.modal>
