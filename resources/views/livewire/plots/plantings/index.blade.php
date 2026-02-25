<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Plantaciones"
        description="Gestiona las plantaciones de variedades en tus parcelas"
    />

    {{-- Tabs --}}
    <x-agro.tabs
        :tabs="[
            'active'   => ['label' => 'Activas',   'count' => $stats['active']],
            'inactive' => ['label' => 'Inactivas',  'count' => $stats['inactive']],
        ]"
        :active="$currentTab"
        wireMethod="switchTab"
    />

    {{-- Toolbar --}}
    <div class="space-y-3">
        <div class="flex items-center gap-3">

            {{-- Search --}}
            <div class="flex-1 relative">
                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                    <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
                </div>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Buscar por nombre, parcela o variedad..."
                    class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
                />
            </div>

            {{-- Filtros button --}}
            @php $filterCount = ($status !== '' ? 1 : 0) + ($year !== '' ? 1 : 0); @endphp
            <button
                x-on:click="$dispatch('open-modal', 'planting-filters')"
                class="relative inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-50 shadow-sm transition-colors"
            >
                <flux:icon icon="adjustments-horizontal" class="size-4 text-zinc-500" />
                Filtros
                @if($filterCount > 0)
                    <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-agro-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">
                        {{ $filterCount }}
                    </span>
                @endif
            </button>

            {{-- Separador --}}
            <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

            {{-- Nueva Plantación --}}
            @can('create', App\Models\PlotPlanting::class)
                <flux:button href="{{ route('plots.index') }}" variant="primary" icon="plus">
                    Nueva
                </flux:button>
            @endcan

            {{-- Ver Parcelas --}}
            <flux:button href="{{ route('plots.index') }}" variant="outline" icon="map">
                Parcelas
            </flux:button>

        </div>

        {{-- Active filter chips --}}
        @if($search || $status !== '' || $year !== '')
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs text-zinc-400">Filtros activos:</span>

                @if($search)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                        <flux:icon icon="magnifying-glass" class="size-3" />
                        "{{ $search }}"
                        <button wire:click="$set('search', '')" class="hover:text-agro-900 ml-0.5">
                            <flux:icon icon="x-mark" class="size-3" />
                        </button>
                    </span>
                @endif

                @if($status !== '')
                    @php
                        $statusLabels = [
                            'active'       => 'Activa',
                            'removed'      => 'Arrancada',
                            'experimental' => 'Experimental',
                            'replanting'   => 'Replantación',
                        ];
                    @endphp
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                        Estado: {{ $statusLabels[$status] ?? $status }}
                        <button wire:click="$set('status', '')" class="hover:text-agro-900 ml-0.5">
                            <flux:icon icon="x-mark" class="size-3" />
                        </button>
                    </span>
                @endif

                @if($year !== '')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                        Año: {{ $year }}
                        <button wire:click="$set('year', '')" class="hover:text-agro-900 ml-0.5">
                            <flux:icon icon="x-mark" class="size-3" />
                        </button>
                    </span>
                @endif

                <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 underline">
                    Limpiar todo
                </button>
            </div>
        @endif
    </div>

    {{-- Cards grid --}}
    @if($plantings->count() > 0)
        <div
            class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="switchTab, search, status, year, clearFilters"
        >
            @php
                $btnBase    = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors';
                $btnDanger  = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors';
                $btnSuccess = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-agro-600 hover:bg-agro-50 transition-colors';
            @endphp
            @foreach($plantings as $i => $planting)

                <x-agro.card
                    wire:key="planting-{{ $planting->id }}"
                    class="animate-fade-in-up hover:-translate-y-1 {{ !$planting->active ? 'opacity-60' : '' }}"
                    style="animation-delay: {{ min($i * 50, 400) }}ms"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            {{-- Icono variedad --}}
                            <div class="w-9 h-9 bg-zinc-100 rounded-full flex items-center justify-center shrink-0">
                                <flux:icon icon="scissors" class="size-4 text-zinc-500" />
                            </div>

                            {{-- Nombre + parcela --}}
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-zinc-900 text-sm truncate leading-tight">
                                    {{ $planting->name ?: 'Sin nombre' }}
                                </p>
                                <a href="{{ route('plots.show', $planting->plot) }}"
                                   class="text-xs text-agro-600 hover:underline truncate block leading-tight mt-0.5">
                                    {{ $planting->plot->name }}
                                </a>
                            </div>

                            {{-- Status dot --}}
                            <x-agro.status-badge :status="$planting->active" />
                        </div>
                    </x-slot:header>

                    {{-- Variedad --}}
                    @if($planting->grapeVariety)
                        @php
                            $dotColor = match($planting->grapeVariety->color ?? '') {
                                'red'   => 'bg-red-500',
                                'white' => 'bg-amber-400',
                                'rose'  => 'bg-pink-400',
                                default => 'bg-zinc-300',
                            };
                            $dotLabel = match($planting->grapeVariety->color ?? '') {
                                'red'   => 'Tinto',
                                'white' => 'Blanco',
                                'rose'  => 'Rosado',
                                default => null,
                            };
                        @endphp
                        <div class="flex items-center gap-2 mb-3">
                            <flux:icon icon="tag" class="size-3.5 text-zinc-400 shrink-0" />
                            <span class="text-xs text-zinc-600 truncate">{{ $planting->grapeVariety->name }}</span>
                            @if($dotLabel)
                                <span class="inline-flex items-center gap-1 text-xs text-zinc-500 shrink-0">
                                    <span class="w-2 h-2 rounded-full {{ $dotColor }}"></span>
                                    {{ $dotLabel }}
                                </span>
                            @endif
                        </div>
                    @else
                        <div class="flex items-center gap-2 mb-3">
                            <flux:icon icon="tag" class="size-3.5 text-zinc-300 shrink-0" />
                            <span class="text-xs text-zinc-400 italic">Sin variedad asignada</span>
                        </div>
                    @endif

                    {{-- Metric tiles --}}
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <div class="bg-agro-50 rounded-xl p-2.5">
                            <p class="text-[10px] text-agro-600 font-medium uppercase tracking-wide mb-0.5">Superficie</p>
                            <p class="text-sm font-bold text-agro-700">{{ number_format($planting->area_planted, 3) }} ha</p>
                        </div>
                        <div class="bg-zinc-50 rounded-xl p-2.5">
                            <p class="text-[10px] text-zinc-500 font-medium uppercase tracking-wide mb-0.5">Año plantación</p>
                            <p class="text-sm font-bold text-zinc-700">{{ $planting->planting_year ?? '—' }}</p>
                        </div>
                    </div>

                    {{-- Riego --}}
                    @if($planting->irrigated)
                        <span class="inline-flex items-center gap-1 text-xs text-blue-600">
                            <flux:icon icon="cloud" class="size-3.5" />
                            Con riego
                        </span>
                    @endif

                    <x-slot:footer>
                        <div class="flex items-center justify-between">
                            {{-- Left: navegar --}}
                            <div class="flex items-center gap-1">
                                <a href="{{ route('plots.show', $planting->plot) }}"
                                   class="{{ $btnBase }}"
                                   title="Ver parcela">
                                    <flux:icon icon="map" class="size-4" />
                                </a>
                                @can('update', $planting->plot)
                                    <a href="{{ route('plots.plantings.edit', $planting) }}"
                                       class="{{ $btnBase }}"
                                       title="Editar plantación">
                                        <flux:icon icon="pencil-square" class="size-4" />
                                    </a>
                                @endcan
                                <a href="{{ route('viticulturist.phenology.index', ['filter_planting_id' => $planting->id]) }}"
                                   class="{{ $btnBase }}"
                                   title="Ver fenología">
                                    <flux:icon icon="sun" class="size-4" />
                                </a>
                                <a href="{{ route('viticulturist.phenology.create', ['planting_id' => $planting->id]) }}"
                                   class="{{ $btnBase }}"
                                   title="Registrar estadio fenológico">
                                    <flux:icon icon="plus-circle" class="size-4" />
                                </a>
                            </div>

                            {{-- Right: toggle activo --}}
                            @can('update', $planting->plot)
                                <button
                                    wire:click="toggleActive({{ $planting->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="toggleActive({{ $planting->id }})"
                                    class="{{ $planting->active ? $btnDanger : $btnSuccess }}"
                                    title="{{ $planting->active ? 'Desactivar plantación' : 'Activar plantación' }}"
                                >
                                    <span wire:loading.remove wire:target="toggleActive({{ $planting->id }})">
                                        <flux:icon icon="{{ $planting->active ? 'no-symbol' : 'check-circle' }}" class="size-4" />
                                    </span>
                                    <span wire:loading wire:target="toggleActive({{ $planting->id }})">
                                        <svg class="animate-spin size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                </button>
                            @endcan
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($plantings->hasPages())
            <div class="flex justify-center">
                {{ $plantings->links() }}
            </div>
        @endif

    @else
        <x-agro.empty-state
            icon="scissors"
            title="{{ $currentTab === 'active' ? 'No hay plantaciones activas' : 'No hay plantaciones inactivas' }}"
            description="{{ $search || $status !== '' || $year !== '' ? 'Ninguna plantación coincide con los filtros aplicados.' : 'Las plantaciones se crean desde la página de detalle de cada parcela.' }}"
        >
            @if($search || $status !== '' || $year !== '')
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">
                        Limpiar filtros
                    </flux:button>
                </x-slot:action>
            @else
                @can('create', App\Models\PlotPlanting::class)
                    <x-slot:action>
                        <flux:button href="{{ route('plots.index') }}" variant="primary" icon="map">
                            Ir a Parcelas
                        </flux:button>
                    </x-slot:action>
                @endcan
            @endif
        </x-agro.empty-state>
    @endif

    {{-- Modal Filtros --}}
    <x-agro.modal name="planting-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'planting-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-5">

            {{-- Estado operativo --}}
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Estado operativo</label>
                <select wire:model.live="status"
                        class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent">
                    <option value="">Todos los estados</option>
                    <option value="active">Activa</option>
                    <option value="removed">Arrancada</option>
                    <option value="experimental">Experimental</option>
                    <option value="replanting">Replantación</option>
                </select>
            </div>

            {{-- Año de plantación --}}
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Año de plantación</label>
                <select wire:model.live="year"
                        class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent">
                    <option value="">Todos los años</option>
                    @foreach($years as $yearOption)
                        <option value="{{ $yearOption }}">{{ $yearOption }}</option>
                    @endforeach
                </select>
            </div>

        </div>

        <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 flex items-center justify-between rounded-b-2xl">
            <button wire:click="clearFilters" x-on:click="$dispatch('close-modal', 'planting-filters')"
                    class="text-sm text-zinc-500 hover:text-zinc-700 transition-colors">
                Limpiar filtros
            </button>
            <flux:button x-on:click="$dispatch('close-modal', 'planting-filters')" variant="primary" size="sm">
                Aplicar
            </flux:button>
        </div>
    </x-agro.modal>

</div>
