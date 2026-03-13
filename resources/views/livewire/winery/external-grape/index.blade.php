<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Uva / Mosto / Vino Externo"
        description="Partidas de uva, mosto o vino a granel de proveedores externos."
    />

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        <x-agro.stat-card
            label="Partidas disponibles"
            :value="$stats['partidas']"
            icon="archive-box"
            color="agro"
        />
        <x-agro.stat-card
            label="Kg disponibles"
            :value="$stats['available_kg'] ? number_format($stats['available_kg'], 0) . ' kg' : '—'"
            icon="scale"
            color="agro"
        />
        <x-agro.stat-card
            label="Kg total en stock"
            :value="$stats['total_kg'] ? number_format($stats['total_kg'], 0) . ' kg' : '—'"
            icon="archive-box-arrow-down"
            color="zinc"
        />
    </div>

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">
        <div class="flex-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
            </div>
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Buscar por proveedor..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition" />
        </div>

        @php $filterCount = ($typeFilter ? 1 : 0) + ($statusFilter ? 1 : 0) + ($vintageFilter ? 1 : 0) + ($colorFilter ? 1 : 0); @endphp
        <button x-on:click="$dispatch('open-modal', 'external-grape-filters')"
            class="relative inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-50 shadow-sm transition-colors">
            <flux:icon icon="adjustments-horizontal" class="size-4 text-zinc-500" />
            Filtros
            @if($filterCount > 0)
                <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-agro-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">
                    {{ $filterCount }}
                </span>
            @endif
        </button>

        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        <flux:button href="{{ route('winery.external-grape.create') }}" wire:navigate variant="primary" icon="plus">
            Nueva
        </flux:button>
    </div>

    {{-- Filter chips --}}
    @if($typeFilter || $statusFilter || $vintageFilter || $colorFilter)
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs text-zinc-400">Filtros activos:</span>

            @if($typeFilter)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    {{ $types[$typeFilter] ?? $typeFilter }}
                    <button wire:click="$set('typeFilter', '')" class="hover:text-agro-900 ml-0.5"><flux:icon icon="x-mark" class="size-3" /></button>
                </span>
            @endif

            @if($colorFilter)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    {{ $colors[$colorFilter] ?? $colorFilter }}
                    <button wire:click="$set('colorFilter', '')" class="hover:text-agro-900 ml-0.5"><flux:icon icon="x-mark" class="size-3" /></button>
                </span>
            @endif

            @if($vintageFilter)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    Añada {{ $vintageFilter }}
                    <button wire:click="$set('vintageFilter', '')" class="hover:text-agro-900 ml-0.5"><flux:icon icon="x-mark" class="size-3" /></button>
                </span>
            @endif

            @if($statusFilter)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    {{ $statuses[$statusFilter] ?? $statusFilter }}
                    <button wire:click="$set('statusFilter', '')" class="hover:text-agro-900 ml-0.5"><flux:icon icon="x-mark" class="size-3" /></button>
                </span>
            @endif

            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">Limpiar todo</button>
        </div>
    @endif

    {{-- Skeleton durante carga --}}
    <div wire:loading wire:target="search, typeFilter, statusFilter, vintageFilter, colorFilter, nextPage, previousPage, gotoPage">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @for($i = 0; $i < 8; $i++)
                <x-agro.skeleton-card />
            @endfor
        </div>
    </div>

    {{-- Grid real --}}
    <div wire:loading.remove wire:target="search, typeFilter, statusFilter, vintageFilter, colorFilter, nextPage, previousPage, gotoPage">
        @if($grapes->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($grapes as $grape)
                    @php
                        $delay = min($loop->index * 50, 300);

                        $typeColors = [
                            'grapes'    => ['bg' => 'bg-agro-100',   'icon' => 'text-agro-600',   'ico' => 'archive-box'],
                            'must'      => ['bg' => 'bg-amber-100',  'icon' => 'text-amber-600',  'ico' => 'beaker'],
                            'bulk_wine' => ['bg' => 'bg-violet-100', 'icon' => 'text-violet-600', 'ico' => 'beaker'],
                        ];
                        $tc = $typeColors[$grape->grape_type] ?? ['bg' => 'bg-zinc-100', 'icon' => 'text-zinc-500', 'ico' => 'archive-box'];

                        $statusColor = match($grape->status) {
                            'available' => 'green',
                            'used'      => 'zinc',
                            'archived'  => 'zinc',
                            default     => 'zinc',
                        };

                        $avail = $grape->getAvailableWeightKg();
                        $isExpired = $grape->expiration_date && $grape->expiration_date->isPast() && $grape->status === 'available';

                        $btnBase   = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors';
                        $btnDanger = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-amber-600 hover:bg-amber-50 transition-colors';
                    @endphp

                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="grape-card-{{ $grape->id }}"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl {{ $tc['bg'] }} flex items-center justify-center shrink-0">
                                    <flux:icon icon="{{ $tc['ico'] }}" class="size-5 {{ $tc['icon'] }}" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">{{ $grape->supplier_name }}</h3>
                                    <p class="text-xs text-zinc-500 truncate">
                                        {{ $grape->getTypeLabel() }}{{ $grape->vintage_year ? ' · ' . $grape->vintage_year : '' }}{{ $grape->color ? ' · ' . $grape->getColorLabel() : '' }}
                                    </p>
                                </div>
                                <x-agro.status-badge :label="$isExpired ? 'Caducado' : (\App\Models\ExternalGrape::STATUSES[$grape->status] ?? $grape->status)" :color="$isExpired ? 'red' : $statusColor" class="shrink-0" />
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">
                            <div class="grid grid-cols-2 gap-2 text-center">
                                <div class="bg-agro-50 rounded-xl p-2">
                                    <p class="text-[10px] text-agro-400 uppercase tracking-wide mb-0.5">Disponible</p>
                                    <p class="text-sm font-bold {{ $avail > 0 ? 'text-agro-700' : 'text-zinc-400' }}">
                                        {{ number_format($avail, 0) }} kg
                                    </p>
                                </div>
                                <div class="bg-zinc-50 rounded-xl p-2">
                                    <p class="text-[10px] text-zinc-400 uppercase tracking-wide mb-0.5">Total</p>
                                    <p class="text-sm font-bold text-zinc-700">{{ number_format($grape->total_weight_kg, 0) }} kg</p>
                                </div>
                            </div>

                            <div class="space-y-1.5 text-sm">
                                @if($grape->grapeVariety)
                                    <div class="flex items-center gap-2 text-zinc-600">
                                        <flux:icon icon="tag" class="size-3.5 text-zinc-400 shrink-0" />
                                        <span class="truncate text-xs">{{ $grape->grapeVariety->name }}</span>
                                    </div>
                                @endif
                                @if($grape->geographic_origin)
                                    <div class="flex items-center gap-2 text-zinc-600">
                                        <flux:icon icon="map-pin" class="size-3.5 text-zinc-400 shrink-0" />
                                        <span class="truncate text-xs">{{ $grape->geographic_origin }}</span>
                                    </div>
                                @endif
                                @if($grape->protection_level)
                                    <div class="flex items-center gap-2">
                                        <flux:icon icon="shield-check" class="size-3.5 text-zinc-400 shrink-0" />
                                        <span class="text-xs text-blue-600 truncate">{{ $grape->protection_level }}</span>
                                    </div>
                                @endif
                                @if($grape->alcohol_pct)
                                    <div class="flex items-center gap-2 text-zinc-600">
                                        <flux:icon icon="beaker" class="size-3.5 text-zinc-400 shrink-0" />
                                        <span class="text-xs">{{ number_format($grape->alcohol_pct, 1) }}° alcohol</span>
                                    </div>
                                @endif
                                <div class="flex items-center gap-2 text-zinc-400">
                                    <flux:icon icon="calendar" class="size-3.5 shrink-0" />
                                    <span class="text-xs">Entrada {{ $grape->entry_date->format('d/m/Y') }}</span>
                                </div>
                            </div>
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-0.5">
                                    <a href="{{ route('winery.external-grape.edit', $grape) }}" wire:navigate
                                        class="{{ $btnBase }}" title="Editar">
                                        <flux:icon icon="pencil-square" class="size-4" />
                                    </a>
                                </div>

                                @if($grape->status === 'available')
                                    <div class="w-px h-5 bg-zinc-200 mx-1"></div>
                                    <div class="flex items-center gap-0.5">
                                        <button wire:click="archive({{ $grape->id }})"
                                            wire:loading.attr="disabled"
                                            wire:confirm="¿Archivar esta partida?"
                                            class="{{ $btnDanger }}" title="Archivar">
                                            <flux:icon icon="archive-box-x-mark" class="size-4" />
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $grapes->links() }}
            </div>
        @else
            <x-agro.empty-state
                icon="archive-box"
                title="Sin partidas registradas"
                :description="$search || $typeFilter || $statusFilter || $vintageFilter || $colorFilter
                    ? 'Ninguna partida coincide con los filtros aplicados.'
                    : 'Registra uva, mosto o vino a granel de proveedores externos para controlar tu stock.'"
            >
                @if(!$search && !$typeFilter && !$statusFilter && !$vintageFilter && !$colorFilter)
                    <x-slot:action>
                        <flux:button href="{{ route('winery.external-grape.create') }}" wire:navigate variant="primary" icon="plus">
                            Nueva partida
                        </flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @endif
    </div>

    {{-- Modal Filtros --}}
    <x-agro.modal name="external-grape-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'external-grape-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-5">
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Tipo de partida</label>
                <flux:select wire:model.live="typeFilter">
                    <flux:select.option value="">Todos los tipos</flux:select.option>
                    @foreach($types as $key => $label)
                        <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Color</label>
                <flux:select wire:model.live="colorFilter">
                    <flux:select.option value="">Todos los colores</flux:select.option>
                    @foreach($colors as $key => $label)
                        <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Añada</label>
                <flux:select wire:model.live="vintageFilter">
                    <flux:select.option value="">Todas las añadas</flux:select.option>
                    @foreach($vintages as $v)
                        <flux:select.option value="{{ $v }}">{{ $v }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Estado</label>
                <flux:select wire:model.live="statusFilter">
                    <flux:select.option value="">Todos los estados</flux:select.option>
                    @foreach($statuses as $key => $label)
                        <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-zinc-200 flex items-center justify-between">
            @if($typeFilter || $statusFilter || $vintageFilter || $colorFilter)
                <flux:button wire:click="clearFilters" x-on:click="$dispatch('close-modal', 'external-grape-filters')" variant="ghost" size="sm">
                    Limpiar filtros
                </flux:button>
            @else
                <span></span>
            @endif
            <flux:button x-on:click="$dispatch('close-modal', 'external-grape-filters')" variant="primary" size="sm">
                Aplicar
            </flux:button>
        </div>
    </x-agro.modal>

</div>
