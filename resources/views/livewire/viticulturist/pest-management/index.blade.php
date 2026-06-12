<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        :title="__('Gestión de Plagas y Enfermedades')"
        :description="__('Base de conocimiento para identificación y tratamiento')"
    />

    {{-- Alerta de riesgo --}}
    @if($pestsInRisk->count() > 0)
        <flux:callout variant="warning" icon="exclamation-triangle">
            <flux:callout.heading>{{ __('Plagas/Enfermedades en período de riesgo') }} — {{ now()->translatedFormat('F') }}</flux:callout.heading>
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

            <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar por nombre o nombre científico...')" data-cy="pest-search-input" />

            @php $filterCount = ($typeFilter !== 'all' ? 1 : 0) + ($showOnlyRisk ? 1 : 0); @endphp
            <x-agro.filter-button modal="pest-filters" :count="$filterCount" data-cy="pest-filter-btn" />

        </div>

        {{-- Active filter chips --}}
        @if($search || $typeFilter !== 'all' || $showOnlyRisk)
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs text-zinc-400">{{ __('Filtros activos:') }}</span>

                @if($search)
                    <x-agro.filter-chip icon="magnifying-glass" :label="'&quot;' . $search . '&quot;'" wireRemove="$set('search', '')" />
                @endif

                @if($typeFilter !== 'all')
                    <x-agro.filter-chip :label="$typeFilter === 'pest' ? __('Solo Plagas') : __('Solo Enfermedades')" wireRemove="$set('typeFilter', 'all')" />
                @endif

                @if($showOnlyRisk)
                    <x-agro.filter-chip :label="__('En riesgo ahora')" wireRemove="$set('showOnlyRisk', false)" />
                @endif

                <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 underline">
                    {{ __('Limpiar todo') }}
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
                                    {{ __('Riesgo') }}
                                </span>
                            @endif
                        </x-agro.card-item-header>
                    </x-slot:header>

                    {{-- Tipo --}}
                    <div class="flex items-center gap-2 mb-3">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $isPest ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ $isPest ? __('Plaga') : __('Enfermedad') }}
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
                                {{ __('Ver detalles') }}
                                <flux:icon icon="arrow-right" class="size-3.5" />
                            </a>
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        <x-agro.pagination :paginator="$pests" />

    @else
        <x-agro.empty-state
            icon="magnifying-glass"
            :message="__('No se encontraron resultados')"
            :description="$search || $typeFilter !== 'all' || $showOnlyRisk ? __('Ninguna plaga o enfermedad coincide con los filtros aplicados.') : __('No hay plagas ni enfermedades registradas.')"
        >
            @if($search || $typeFilter !== 'all' || $showOnlyRisk)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">{{ __('Limpiar filtros') }}</flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @endif

    {{-- Modal Filtros --}}
    <x-agro.filter-modal
        name="pest-filters"
        :hasActiveFilters="$typeFilter !== 'all' || $showOnlyRisk"
        clearAction="clearFilters"
    >
        <x-agro.filter-select :label="__('Tipo')" wire:model.live="typeFilter" data-cy="pest-type-filter">
            <flux:select.option value="all">{{ __('Todos los tipos') }}</flux:select.option>
            <flux:select.option value="pest">{{ __('Solo Plagas') }}</flux:select.option>
            <flux:select.option value="disease">{{ __('Solo Enfermedades') }}</flux:select.option>
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
                {{ __('Solo en riesgo ahora') }}
                <span class="text-xs text-zinc-400 font-normal block">{{ __('Mes actual:') }} {{ now()->translatedFormat('F') }}</span>
            </label>
        </div>
    </x-agro.filter-modal>

</div>
