<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Gestión de Plagas y Enfermedades"
        description="Base de conocimiento para identificación y tratamiento"
    />

    {{-- Alerta de riesgo --}}
    @if($pestsInRisk->count() > 0)
        <flux:callout variant="warning" icon="exclamation-triangle">
            <flux:callout.heading>Plagas/Enfermedades en período de riesgo — {{ now()->translatedFormat('F') }}</flux:callout.heading>
            <flux:callout.text>
                <ul class="list-disc list-inside space-y-0.5 mt-1">
                    @foreach($pestsInRisk as $riskPest)
                        <li>
                            <a href="{{ roleRoute('viticulturist.pest-management.show', $riskPest) }}" class="font-medium hover:underline">
                                {{ $riskPest->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </flux:callout.text>
        </flux:callout>
    @endif

    {{-- Toolbar --}}
    <div class="space-y-3">
        <div class="flex items-center gap-3">

            <x-agro.search-input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre o nombre científico..." data-cy="pest-search-input" />

            @php $filterCount = ($typeFilter !== 'all' ? 1 : 0) + ($showOnlyRisk ? 1 : 0); @endphp
            <x-agro.filter-button modal="pest-filters" :count="$filterCount" data-cy="pest-filter-btn" />

        </div>

        {{-- Active filter chips --}}
        @if($search || $typeFilter !== 'all' || $showOnlyRisk)
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs text-zinc-400">Filtros activos:</span>

                @if($search)
                    <x-agro.filter-chip icon="magnifying-glass" :label="'&quot;' . $search . '&quot;'" wireRemove="$set('search', '')" />
                @endif

                @if($typeFilter !== 'all')
                    <x-agro.filter-chip :label="$typeFilter === 'pest' ? 'Solo Plagas' : 'Solo Enfermedades'" wireRemove="$set('typeFilter', 'all')" />
                @endif

                @if($showOnlyRisk)
                    <x-agro.filter-chip :label="'En riesgo ahora'" wireRemove="$set('showOnlyRisk', false)" />
                @endif

                <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 underline">
                    Limpiar todo
                </button>
            </div>
        @endif
    </div>

    {{-- Card grid --}}
    @if($pests->count() > 0)
        <div
            class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="search, typeFilter, showOnlyRisk, clearFilters"
        >
            @foreach($pests as $i => $pest)
                @php
                    $isRisk = $pest->isInRiskPeriod();
                    $isPest = $pest->type === 'pest';
                @endphp

                <x-agro.card
                    wire:key="pest-{{ $pest->id }}"
                    data-cy="pest-card"
                    class="animate-fade-in-up hover:-translate-y-1"
                    style="animation-delay: {{ min($i * 50, 400) }}ms"
                >
                    <x-slot:header>
                        <x-agro.card-item-header
                            :icon="$isPest ? 'bug-ant' : 'shield-exclamation'"
                            :title="$pest->name"
                            :subtitle="$pest->scientific_name ?? null"
                            :iconBg="$isPest ? 'bg-red-50' : 'bg-blue-50'"
                            :iconColor="$isPest ? 'text-red-500' : 'text-blue-500'"
                        >
                            @if($isRisk)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">
                                    Riesgo
                                </span>
                            @endif
                        </x-agro.card-item-header>
                    </x-slot:header>

                    {{-- Tipo --}}
                    <div class="flex items-center gap-2 mb-3">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $isPest ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ $isPest ? 'Plaga' : 'Enfermedad' }}
                        </span>
                    </div>

                    {{-- Descripción --}}
                    @if($pest->description)
                        <p class="text-xs text-zinc-500 line-clamp-3 leading-relaxed">{{ $pest->description }}</p>
                    @endif

                    <x-slot:footer>
                        <div class="flex items-center justify-end">
                            <a href="{{ roleRoute('viticulturist.pest-management.show', $pest) }}"
                               class="inline-flex items-center gap-1.5 text-xs font-medium text-agro-600 hover:text-agro-700 transition-colors">
                                Ver detalles
                                <flux:icon icon="arrow-right" class="size-3.5" />
                            </a>
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        @if($pests->hasPages())
            <div class="flex justify-center">{{ $pests->links() }}</div>
        @endif

    @else
        <x-agro.empty-state
            icon="magnifying-glass"
            message="No se encontraron resultados"
            description="{{ $search || $typeFilter !== 'all' || $showOnlyRisk ? 'Ninguna plaga o enfermedad coincide con los filtros aplicados.' : 'No hay plagas ni enfermedades registradas.' }}"
        >
            @if($search || $typeFilter !== 'all' || $showOnlyRisk)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @endif

    {{-- Modal Filtros --}}
    <x-agro.modal name="pest-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'pest-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-5">
            <x-agro.filter-select label="Tipo" wire:model.live="typeFilter" data-cy="pest-type-filter">
                <flux:select.option value="all">Todos los tipos</flux:select.option>
                <flux:select.option value="pest">Solo Plagas</flux:select.option>
                <flux:select.option value="disease">Solo Enfermedades</flux:select.option>
            </x-agro.filter-select>
            <div class="flex items-center gap-3">
                <input
                    type="checkbox"
                    id="showOnlyRisk"
                    wire:model.live="showOnlyRisk"
                    data-cy="pest-risk-filter"
                    class="rounded border-zinc-300 text-agro-600 focus:ring-agro-500"
                />
                <label for="showOnlyRisk" class="text-sm font-medium text-zinc-700 cursor-pointer">
                    Solo en riesgo ahora
                    <span class="text-xs text-zinc-400 font-normal block">Mes actual: {{ now()->translatedFormat('F') }}</span>
                </label>
            </div>
        </div>

        <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 flex items-center justify-between rounded-b-2xl">
            <button wire:click="clearFilters" x-on:click="$dispatch('close-modal', 'pest-filters')"
                    class="text-sm text-zinc-500 hover:text-zinc-700 transition-colors">
                Limpiar filtros
            </button>
            <flux:button x-on:click="$dispatch('close-modal', 'pest-filters')" variant="primary" size="sm">
                Aplicar
            </flux:button>
        </div>
    </x-agro.modal>

</div>
