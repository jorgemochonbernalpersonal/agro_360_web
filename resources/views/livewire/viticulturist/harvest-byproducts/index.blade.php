<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        :title="__('Subproductos de Vendimia')"
        :description="__('Registro de salida de orujos, raspones y lías (Reglamento UE 2018/273)')"
        icon="beaker"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.harvest-byproducts.create') }}" variant="primary" icon="plus">
                {{ __('Registrar Subproducto') }}
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats (colapsables) --}}
    <x-agro.stats-section key="harvest-byproducts">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <x-agro.stat-card
                :label="__('Registros activos')"
                :value="$stats['active']"
                :description="$stats['active'] . ' ' . __('en total')"
                icon="beaker"
                color="agro"
            />
            <x-agro.stat-card
                :label="__('kg totales campaña')"
                :value="number_format($stats['total_kg'], 0, ',', '.') . ' kg'"
                :description="__('Campaña seleccionada')"
                icon="scale"
                color="green"
            />
            <x-agro.stat-card
                :label="__('Orujo + Hollejo')"
                :value="number_format($stats['pomace_kg'], 0, ',', '.') . ' kg'"
                :description="__('Campaña seleccionada')"
                icon="cube"
                color="amber"
            />
            <x-agro.stat-card
                :label="__('Archivados')"
                :value="$stats['archived']"
                :description="$stats['archived'] > 0 ? __('Fuera de activo') : __('Ninguno archivado')"
                icon="archive-box"
                color="zinc"
            />
        </div>
    </x-agro.stats-section>

    {{-- Tabs --}}
    <x-agro.tabs
        :tabs="[
            'active'   => ['label' => __('Activos'),    'count' => $stats['active']],
            'archived' => ['label' => __('Archivados'), 'count' => $stats['archived']],
        ]"
        :active="$currentTab"
        wireMethod="switchTab"
    />

    {{-- Toolbar --}}
    @php
        $filterCount = (int) !empty($filterCampaign) + (int) !empty($filterByproductType);
    @endphp
    <div class="flex items-center gap-3">
        <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar por destino, nº documento...')" />
        <x-agro.filter-button modal="harvest-byproducts-filters" :count="$filterCount" />
    </div>

    {{-- Chips filtros activos --}}
    @if($filterCampaign || $filterByproductType)
        <div class="flex flex-wrap items-center gap-2">
            @if($filterCampaign)
                @php $camp = $campaigns->firstWhere('id', $filterCampaign); @endphp
                <x-agro.filter-chip icon="calendar-days" :label="$camp?->name ?? $filterCampaign" wireRemove="$set('filterCampaign', '')" />
            @endif
            @if($filterByproductType)
                <x-agro.filter-chip icon="beaker" :label="$byproductTypes[$filterByproductType] ?? $filterByproductType" wireRemove="$set('filterByproductType', '')" />
            @endif
            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">{{ __('Limpiar todo') }}</button>
        </div>
    @endif

    {{-- Skeleton carga --}}
    <x-agro.loading-grid target="switchTab, search, filterCampaign, filterByproductType, nextPage, previousPage, gotoPage" />

    {{-- Grid de cards --}}
    <div wire:loading.remove wire:target="switchTab, search, filterCampaign, filterByproductType, nextPage, previousPage, gotoPage">
        @if($entries->isEmpty())
            <x-agro.empty-state
                icon="beaker"
                :title="$currentTab === 'active' ? __('Sin subproductos registrados') : __('Sin registros archivados')"
                :description="$search || $filterCampaign || $filterByproductType ? __('Ningún registro coincide con los filtros aplicados.') : __('Registra la salida de orujos, raspones y lías conforme al Reglamento UE 2018/273.')"
            >
                @if(!$search && !$filterCampaign && !$filterByproductType && $currentTab === 'active')
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('viticulturist.harvest-byproducts.create') }}" variant="primary" icon="plus">
                            {{ __('Registrar Subproducto') }}
                        </flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($entries as $entry)
                    @php
                        $delay = min($loop->index * 50, 300);
                        $typeColor = match($entry->byproduct_type) {
                            'pomace' => ['bg' => 'bg-amber-100',  'icon' => 'text-amber-600'],
                            'stem'   => ['bg' => 'bg-orange-100', 'icon' => 'text-orange-600'],
                            'lees'   => ['bg' => 'bg-yellow-100', 'icon' => 'text-yellow-600'],
                            default  => ['bg' => 'bg-zinc-100',   'icon' => 'text-zinc-400'],
                        };
                        $typeBadgeClass = match($entry->byproduct_type) {
                            'pomace' => 'bg-amber-100 text-amber-700',
                            'stem'   => 'bg-orange-100 text-orange-700',
                            'lees'   => 'bg-yellow-100 text-yellow-700',
                            default  => 'bg-zinc-100 text-zinc-500',
                        };
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1 {{ $currentTab === 'archived' ? 'opacity-60' : '' }}"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="hbp-{{ $entry->id }}"
                    >
                        <x-slot:header>
                            <x-agro.card-item-header
                                icon="beaker"
                                :title="$entry->destination_name"
                                :subtitle="$entry->date->format('d/m/Y')"
                                :iconBg="$typeColor['bg']"
                                :iconColor="$typeColor['icon']"
                                size="md"
                                radius="xl"
                            >
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $typeBadgeClass }}">
                                    {{ $entry->byproduct_type_label }}
                                </span>
                            </x-agro.card-item-header>
                        </x-slot:header>

                        <div class="flex-1 space-y-3">
                            {{-- Cantidad destacada --}}
                            <div class="bg-zinc-50 rounded-xl p-3">
                                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">{{ __('Cantidad') }}</p>
                                <p class="text-2xl font-bold text-zinc-700 leading-none">
                                    {{ number_format($entry->quantity_kg, 3, ',', '.') }}
                                    <span class="text-xs font-normal text-zinc-400 ml-0.5">kg</span>
                                </p>
                            </div>

                            {{-- Destino --}}
                            <div class="flex items-center gap-2 text-sm text-zinc-600">
                                <flux:icon icon="truck" class="size-4 text-zinc-400 shrink-0" />
                                <span class="truncate">{{ $entry->destination_type_label }}</span>
                            </div>

                            {{-- Documento referencia --}}
                            @if($entry->document_reference)
                                <div class="flex items-center gap-2 text-zinc-500">
                                    <flux:icon icon="document-text" class="size-4 text-zinc-400 shrink-0" />
                                    <span class="truncate font-mono text-xs">{{ $entry->document_reference }}</span>
                                </div>
                            @endif

                            {{-- Campaña --}}
                            <div class="flex items-center gap-2 text-zinc-400">
                                <flux:icon icon="calendar-days" class="size-3.5 shrink-0" />
                                <span class="text-xs truncate">{{ $entry->campaign->name ?? '—' }}</span>
                            </div>
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5">
                                @if($currentTab === 'active')
                                    <x-agro.action-button
                                        variant="edit"
                                        href="{{ roleRoute('viticulturist.harvest-byproducts.edit', $entry) }}"
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
                                    <x-agro.action-button
                                        variant="delete"
                                        wire:click="delete({{ $entry->id }})"
                                        wire:confirm="{{ __('¿Eliminar este registro permanentemente?') }}"
                                        :title="__('Eliminar')"
                                    />
                                @endif
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <x-agro-pagination :paginator="$entries" />
        @endif
    </div>

    {{-- Modal: Filtros --}}
    <x-agro.modal name="harvest-byproducts-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">{{ __('Filtros') }}</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'harvest-byproducts-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>
        <div class="px-6 py-5 space-y-5">
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">{{ __('Campaña') }}</label>
                <flux:select wire:model.live="filterCampaign">
                    <flux:select.option value="">{{ __('Todas las campañas') }}</flux:select.option>
                    @foreach($campaigns as $c)
                        <flux:select.option value="{{ $c->id }}">{{ $c->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">{{ __('Tipo de subproducto') }}</label>
                <flux:select wire:model.live="filterByproductType">
                    <flux:select.option value="">{{ __('Todos los tipos') }}</flux:select.option>
                    @foreach($byproductTypes as $key => $label)
                        <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-zinc-200 flex items-center justify-between">
            @if($filterCampaign || $filterByproductType)
                <button wire:click="clearFilters" class="text-sm text-zinc-400 hover:text-zinc-600 transition-colors">{{ __('Limpiar filtros') }}</button>
            @else
                <span></span>
            @endif
            <flux:button x-on:click="$dispatch('close-modal', 'harvest-byproducts-filters')" variant="primary">{{ __('Aplicar') }}</flux:button>
        </div>
    </x-agro.modal>

</div>
