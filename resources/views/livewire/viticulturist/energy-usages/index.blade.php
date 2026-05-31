<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        :title="__('Consumo Energético')"
        :description="__('Huella de carbono y costes energéticos de la explotación')"
        icon="bolt"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.energy-usages.create') }}" variant="primary" icon="plus">
                {{ __('Registrar Consumo') }}
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Tabs --}}
    <x-agro.tabs
        :tabs="[
            'active'   => ['label' => __('Activos'),    'count' => $stats['active']],
            'archived' => ['label' => __('Archivados'),  'count' => $stats['archived']],
        ]"
        :active="$currentTab"
        wireMethod="switchTab"
    />

    {{-- Filtros --}}
    <div class="flex items-center gap-3 flex-wrap">
        <flux:select wire:model.live="filterCampaign" class="w-48">
            <flux:select.option value="">{{ __('Todas las campañas') }}</flux:select.option>
            @foreach($campaigns as $c)
                <flux:select.option value="{{ $c->id }}">{{ $c->name }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="filterEnergyType" class="w-44">
            <flux:select.option value="">{{ __('Todos los tipos') }}</flux:select.option>
            @foreach($energyTypes as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
        @if($filterCampaign || $filterEnergyType)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">{{ __('Limpiar') }}</flux:button>
        @endif
    </div>

    {{-- Chips filtros activos --}}
    @if($filterCampaign || $filterEnergyType)
        <div class="flex flex-wrap items-center gap-2">
            @if($filterCampaign)
                @php $camp = $campaigns->firstWhere('id', $filterCampaign); @endphp
                <x-agro.filter-chip icon="calendar-days" :label="$camp?->name ?? $filterCampaign" wireRemove="$set('filterCampaign', '')" />
            @endif
            @if($filterEnergyType)
                <x-agro.filter-chip icon="bolt" :label="$energyTypes[$filterEnergyType] ?? $filterEnergyType" wireRemove="$set('filterEnergyType', '')" />
            @endif
        </div>
    @endif

    {{-- KPI CO₂ --}}
    @if($co2Total > 0)
        <x-agro.card>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                    <flux:icon icon="globe-europe-africa" class="w-6 h-6 text-emerald-600" />
                </div>
                <div>
                    <p class="text-sm text-zinc-500">{{ __('CO₂ total campaña seleccionada') }}</p>
                    <p class="text-2xl font-bold text-zinc-900">{{ number_format($co2Total, 1, ',', '.') }} kg CO₂e</p>
                    <p class="text-xs text-zinc-400">{{ number_format($co2Total / 1000, 3, ',', '.') }} {{ __('toneladas CO₂ equivalente') }}</p>
                </div>
            </div>
        </x-agro.card>
    @endif

    {{-- Grid de cards --}}
    @if($entries->isEmpty())
        <x-agro.empty-state
            icon="bolt"
            :title="$currentTab === 'active' ? __('Sin registros energéticos') : __('Sin registros archivados')"
            :description="$filterCampaign || $filterEnergyType ? __('Ningún registro coincide con los filtros aplicados.') : __('Registra el consumo de gasóleo, electricidad y otros combustibles para calcular tu huella de carbono.')"
        >
            @if($filterCampaign || $filterEnergyType)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">{{ __('Limpiar filtros') }}</flux:button>
                </x-slot:action>
            @elseif($currentTab === 'active')
                <x-slot:action>
                    <flux:button href="{{ roleRoute('viticulturist.energy-usages.create') }}" variant="primary" icon="plus">
                        {{ __('Registrar Consumo') }}
                    </flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($entries as $entry)
                @php $delay = min($loop->index * 50, 300); @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="energy-{{ $entry->id }}"
                >
                    <x-slot:header>
                        <x-agro.card-item-header
                            icon="bolt"
                            :title="$entry->energy_type_label"
                            :subtitle="$entry->date->format('d/m/Y')"
                            iconBg="bg-emerald-100"
                            iconColor="text-emerald-600"
                            size="md"
                            radius="xl"
                        />
                    </x-slot:header>

                    <div class="flex-1 space-y-3">
                        @if($entry->usage_description)
                            <p class="text-xs text-zinc-500 line-clamp-2">{{ $entry->usage_description }}</p>
                        @endif

                        <div class="grid grid-cols-2 gap-2">
                            <div class="bg-zinc-50 rounded-xl p-3">
                                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">{{ __('Cantidad') }}</p>
                                <p class="text-base font-bold text-zinc-700 leading-none">
                                    {{ number_format($entry->quantity, 2, ',', '.') }}<span class="text-xs font-normal text-zinc-400 ml-0.5">{{ $entry->unit_label }}</span>
                                </p>
                            </div>
                            @if($entry->co2_kg_equivalent)
                                <div class="bg-emerald-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">{{ __('CO₂') }}</p>
                                    <p class="text-base font-bold text-emerald-700 leading-none">
                                        {{ number_format($entry->co2_kg_equivalent, 1, ',', '.') }}<span class="text-xs font-normal text-zinc-400 ml-0.5">kg</span>
                                    </p>
                                </div>
                            @endif
                        </div>

                        @if($entry->total_cost)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-zinc-400">{{ __('Coste') }}</span>
                                <span class="font-semibold text-zinc-700">{{ number_format($entry->total_cost, 2, ',', '.') }} €</span>
                            </div>
                        @endif
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-end gap-0.5">
                            @if($currentTab === 'active')
                                <x-agro.action-button
                                    variant="edit"
                                    href="{{ roleRoute('viticulturist.energy-usages.edit', $entry) }}"
                                    :title="__('Editar')"
                                />
                                <x-agro.action-button
                                    variant="archive"
                                    wire:click="archive({{ $entry->id }})"
                                    wire:confirm="{{ __('¿Archivar este registro?') }}"
                                    :title="__('Archivar')"
                                />
                            @else
                                <x-agro.action-button
                                    variant="restore"
                                    icon="arrow-uturn-left"
                                    wire:click="unarchive({{ $entry->id }})"
                                    :title="__('Restaurar')"
                                />
                            @endif
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        <x-agro.pagination :paginator="$entries" />
    @endif

</div>
