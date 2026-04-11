<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="Subproductos de Vendimia"
        description="Registro de salida de orujos, raspones y lías (Reglamento UE 2018/273)"
        icon="beaker"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.harvest-byproducts.create') }}" variant="primary" icon="plus">
                Registrar Subproducto
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats (colapsables) --}}
    <div x-data="{
        open: localStorage.getItem('harvest-byproducts-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('harvest-byproducts-stats-open', String(this.open));
        }
    }">
        <button @click="toggle()"
            class="flex items-center gap-1.5 text-[11px] font-semibold text-zinc-400 uppercase tracking-widest hover:text-zinc-600 transition-colors mb-3">
            <span>Estadísticas</span>
            <flux:icon icon="chevron-up" class="size-3.5 transition-transform duration-200" ::class="{ 'rotate-180': !open }" />
        </button>
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-1">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <x-agro.stat-card
                    label="Registros activos"
                    :value="$stats['active']"
                    :description="$stats['active'] . ' en total'"
                    icon="beaker"
                    color="agro"
                />
                <x-agro.stat-card
                    label="kg totales campaña"
                    :value="number_format($stats['total_kg'], 0, ',', '.') . ' kg'"
                    description="Campaña seleccionada"
                    icon="scale"
                    color="green"
                />
                <x-agro.stat-card
                    label="Orujo + Hollejo"
                    :value="number_format($stats['pomace_kg'], 0, ',', '.') . ' kg'"
                    description="Campaña seleccionada"
                    icon="cube"
                    color="amber"
                />
                <x-agro.stat-card
                    label="Archivados"
                    :value="$stats['archived']"
                    :description="$stats['archived'] > 0 ? 'Fuera de activo' : 'Ninguno archivado'"
                    icon="archive-box"
                    color="zinc"
                />
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <x-agro.tabs
        :tabs="[
            'active'   => ['label' => 'Activos',    'count' => $stats['active']],
            'archived' => ['label' => 'Archivados', 'count' => $stats['archived']],
        ]"
        :active="$currentTab"
        wireMethod="switchTab"
    />

    {{-- Toolbar --}}
    @php
        $filterCount = (int) !empty($filterCampaign) + (int) !empty($filterByproductType);
    @endphp
    <div class="flex items-center gap-3">
        <div class="flex-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Buscar por destino, nº documento..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
            />
        </div>
        <button
            x-on:click="$dispatch('open-modal', 'harvest-byproducts-filters')"
            class="relative inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-50 shadow-sm transition-colors"
        >
            <flux:icon icon="adjustments-horizontal" class="size-4 text-zinc-500" />
            Filtros
            @if($filterCount > 0)
                <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-agro-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">{{ $filterCount }}</span>
            @endif
        </button>
    </div>

    {{-- Chips filtros activos --}}
    @if($filterCampaign || $filterByproductType)
        <div class="flex flex-wrap items-center gap-2">
            @if($filterCampaign)
                @php $camp = $campaigns->firstWhere('id', $filterCampaign); @endphp
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="calendar-days" class="size-3" />
                    {{ $camp?->name ?? $filterCampaign }}
                    <button wire:click="$set('filterCampaign', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if($filterByproductType)
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="beaker" class="size-3" />
                    {{ $byproductTypes[$filterByproductType] ?? $filterByproductType }}
                    <button wire:click="$set('filterByproductType', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">Limpiar todo</button>
        </div>
    @endif

    {{-- Skeleton carga --}}
    <div wire:loading wire:target="switchTab, search, filterCampaign, filterByproductType, nextPage, previousPage, gotoPage">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @for($i = 0; $i < 8; $i++)
                <x-agro.skeleton-card />
            @endfor
        </div>
    </div>

    {{-- Grid de cards --}}
    <div wire:loading.remove wire:target="switchTab, search, filterCampaign, filterByproductType, nextPage, previousPage, gotoPage">
        @if($entries->isEmpty())
            <x-agro.empty-state
                icon="beaker"
                title="{{ $currentTab === 'active' ? 'Sin subproductos registrados' : 'Sin registros archivados' }}"
                description="{{ $search || $filterCampaign || $filterByproductType ? 'Ningún registro coincide con los filtros aplicados.' : 'Registra la salida de orujos, raspones y lías conforme al Reglamento UE 2018/273.' }}"
            >
                @if(!$search && !$filterCampaign && !$filterByproductType && $currentTab === 'active')
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('viticulturist.harvest-byproducts.create') }}" variant="primary" icon="plus">
                            Registrar Subproducto
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
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 {{ $typeColor['bg'] }} rounded-xl flex items-center justify-center shrink-0">
                                    <flux:icon icon="beaker" class="size-5 {{ $typeColor['icon'] }}" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">{{ $entry->destination_name }}</h3>
                                    <p class="text-xs text-zinc-500">{{ $entry->date->format('d/m/Y') }}</p>
                                </div>
                                <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $typeBadgeClass }}">
                                    {{ $entry->byproduct_type_label }}
                                </span>
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-3">
                            {{-- Cantidad destacada --}}
                            <div class="bg-zinc-50 rounded-xl p-3">
                                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">Cantidad</p>
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
                                    <a href="{{ roleRoute('viticulturist.harvest-byproducts.edit', $entry) }}"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors"
                                       title="Editar">
                                        <flux:icon icon="pencil-square" class="size-4" />
                                    </a>
                                    <button wire:click="archive({{ $entry->id }})"
                                            wire:confirm="¿Archivar este registro?"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-amber-600 hover:bg-amber-50 transition-colors"
                                            title="Archivar">
                                        <flux:icon icon="archive-box" class="size-4" />
                                    </button>
                                @else
                                    <button wire:click="unarchive({{ $entry->id }})"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-agro-600 hover:bg-agro-50 transition-colors"
                                            title="Restaurar">
                                        <flux:icon icon="arrow-uturn-left" class="size-4" />
                                    </button>
                                    <button wire:click="delete({{ $entry->id }})"
                                            wire:confirm="¿Eliminar este registro permanentemente?"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                            title="Eliminar">
                                        <flux:icon icon="trash" class="size-4" />
                                    </button>
                                @endif
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            @if($entries->hasPages())
                <div class="mt-6">{{ $entries->links() }}</div>
            @endif
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
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'harvest-byproducts-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>
        <div class="px-6 py-5 space-y-5">
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Campaña</label>
                <flux:select wire:model.live="filterCampaign">
                    <flux:select.option value="">Todas las campañas</flux:select.option>
                    @foreach($campaigns as $c)
                        <flux:select.option value="{{ $c->id }}">{{ $c->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Tipo de subproducto</label>
                <flux:select wire:model.live="filterByproductType">
                    <flux:select.option value="">Todos los tipos</flux:select.option>
                    @foreach($byproductTypes as $key => $label)
                        <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-zinc-200 flex items-center justify-between">
            @if($filterCampaign || $filterByproductType)
                <button wire:click="clearFilters" class="text-sm text-zinc-400 hover:text-zinc-600 transition-colors">Limpiar filtros</button>
            @else
                <span></span>
            @endif
            <flux:button x-on:click="$dispatch('close-modal', 'harvest-byproducts-filters')" variant="primary">Aplicar</flux:button>
        </div>
    </x-agro.modal>

</div>
