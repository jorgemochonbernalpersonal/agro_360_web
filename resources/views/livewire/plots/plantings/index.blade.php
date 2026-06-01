<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        :title="__('Plantaciones')"
        :description="__('Gestiona las plantaciones de variedades en tus parcelas')"
    />

    {{-- Stats --}}
    <div x-data="{
        open: localStorage.getItem('plantings-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('plantings-stats-open', String(this.open));
        }
    }">
        <button
            @click="toggle()"
            class="flex items-center gap-1.5 text-[11px] font-semibold text-zinc-400 uppercase tracking-widest hover:text-zinc-600 transition-colors mb-3"
        >
            <span>{{ __('Estadísticas') }}</span>
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
            <div class="grid grid-cols-2 gap-4">
                <x-agro.stat-card
                    :label="__('Total plantaciones')"
                    :value="$stats['total']"
                    :description="$stats['active'] . ' ' . __('activas') . ' · ' . $stats['inactive'] . ' ' . __('inactivas')"
                    icon="scissors"
                    color="agro"
                />
                <x-agro.stat-card
                    :label="__('Superficie total')"
                    :value="number_format($stats['total_area'], 2) . ' ha'"
                    :description="__('Área plantada declarada')"
                    icon="square-2-stack"
                    color="blue"
                />
                <x-agro.stat-card
                    :label="__('Activas')"
                    :value="$stats['active']"
                    :description="__('En producción')"
                    icon="check-circle"
                    color="agro"
                />
                <x-agro.stat-card
                    :label="__('Inactivas')"
                    :value="$stats['inactive']"
                    :description="$stats['inactive'] > 0 ? __('Archivadas') : __('Todas activas')"
                    icon="archive-box"
                    color="zinc"
                />
            </div>
        </div>
    </div>
    {{-- Tabs --}}
    <x-agro.tabs
        :tabs="[
            'active'   => ['label' => __('Activas'),   'count' => $stats['active']],
            'inactive' => ['label' => __('Inactivas'),  'count' => $stats['inactive']],
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
                    :placeholder="__('Buscar por nombre, parcela o variedad...')"
                    placeholder="{{ __('Buscar por nombre, parcela o variedad...') }}"
                    class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
                />
            </div>

            {{-- Filtros button --}}
            @php $filterCount = ($status !== '' ? 1 : 0) + ($year !== '' ? 1 : 0) + (!$wineryOnly && $cropType !== '' ? 1 : 0); @endphp
            <button
                x-on:click="$dispatch('open-modal', 'planting-filters')"
                class="relative inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-50 shadow-sm transition-colors"
            >
                <flux:icon icon="adjustments-horizontal" class="size-4 text-zinc-500" />
                {{ __('Filtros') }}
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
                <flux:button
                    x-on:click="$dispatch('open-modal', 'pick-plot-for-planting')"
                    variant="primary"
                    icon="plus"
                >
                    {{ __('Nueva') }}
                </flux:button>
            @endcan

        </div>

        {{-- Active filter chips --}}
        @if($search || $status !== '' || $year !== '' || (!$wineryOnly && $cropType !== ''))
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs text-zinc-400">{{ __('Filtros activos') }}:</span>

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
                            'active'       => __('Activa'),
                            'removed'      => __('Arrancada'),
                            'experimental' => __('Experimental'),
                            'replanting'   => __('Replantación'),
                        ];
                    @endphp
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                        {{ __('Estado') }}: {{ $statusLabels[$status] ?? $status }}
                        <button wire:click="$set('status', '')" class="hover:text-agro-900 ml-0.5">
                            <flux:icon icon="x-mark" class="size-3" />
                        </button>
                    </span>
                @endif

                @if($year !== '')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                        {{ __('Año') }}: {{ $year }}
                        <button wire:click="$set('year', '')" class="hover:text-agro-900 ml-0.5">
                            <flux:icon icon="x-mark" class="size-3" />
                        </button>
                    </span>
                @endif

                @if(!$wineryOnly && $cropType !== '')
                    @php $cropLabels = \App\Models\GrapeVariety::CROP_TYPES; @endphp
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                        {{ __('Cultivo') }}: {{ $cropLabels[$cropType] ?? $cropType }}
                        <button wire:click="$set('cropType', '')" class="hover:text-agro-900 ml-0.5">
                            <flux:icon icon="x-mark" class="size-3" />
                        </button>
                    </span>
                @endif

                <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 underline">
                    {{ __('Limpiar todo') }}
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
            @foreach($plantings as $i => $planting)

                <x-agro.card
                    wire:key="planting-{{ $planting->id }}"
                    class="animate-fade-in-up hover:-translate-y-1 {{ !$planting->active ? 'opacity-60' : '' }}"
                    style="animation-delay: {{ min($i * 50, 400) }}ms"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            {{-- Icono variedad (depende del tipo de cultivo) --}}
                            @php
                                $cropIcon = $planting->grapeVariety
                                    ? \App\Models\GrapeVariety::CROP_TYPE_ICONS[$planting->grapeVariety->crop_type] ?? 'sparkles'
                                    : 'scissors';
                                $cropBg = match($planting->grapeVariety->crop_type ?? 'wine') {
                                    'olive' => 'bg-amber-100',
                                    'other' => 'bg-emerald-100',
                                    default => 'bg-zinc-100',
                                };
                                $cropIconColor = match($planting->grapeVariety->crop_type ?? 'wine') {
                                    'olive' => 'text-amber-600',
                                    'other' => 'text-emerald-600',
                                    default => 'text-zinc-500',
                                };
                            @endphp
                            <div class="w-9 h-9 {{ $cropBg }} rounded-full flex items-center justify-center shrink-0">
                                <flux:icon icon="{{ $cropIcon }}" class="size-4 {{ $cropIconColor }}" />
                            </div>

                            {{-- Nombre + parcela --}}
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-zinc-900 text-sm truncate leading-tight">
                                    {{ $planting->name ?: __('Sin nombre') }}
                                </p>
                                <a href="{{ route('plots.show', $planting->plot) }}"
                                   class="text-xs text-agro-600 hover:underline truncate block leading-tight mt-0.5">
                                    {{ $planting->plot->name }}
                                </a>
                            </div>

                            {{-- Status badge --}}
                            <span class="text-[10px] font-semibold uppercase tracking-wide px-2 py-0.5 rounded-full shrink-0
                                {{ match($planting->status) {
                                    'active' => 'bg-agro-50 text-agro-700',
                                    'removed' => 'bg-red-50 text-red-600',
                                    'experimental' => 'bg-amber-50 text-amber-700',
                                    'replanting' => 'bg-blue-50 text-blue-600',
                                    default => 'bg-zinc-100 text-zinc-500',
                                } }}">
                                {{ match($planting->status) {
                                    'active' => __('Activa'),
                                    'removed' => __('Arrancada'),
                                    'experimental' => __('Experimental'),
                                    'replanting' => __('Replantación'),
                                    default => $planting->status,
                                } }}
                            </span>
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
                                'red'   => __('Tinto'),
                                'white' => __('Blanco'),
                                'rose'  => __('Rosado'),
                                default => null,
                            };
                            $cropType = $planting->grapeVariety->crop_type ?? 'wine';
                        @endphp
                        <div class="flex items-center gap-2 mb-3">
                            <flux:icon icon="tag" class="size-3.5 text-zinc-400 shrink-0" />
                            <span class="text-xs text-zinc-600 truncate">{{ $planting->grapeVariety->name }}</span>
                            @if($cropType !== 'wine')
                                @php
                                    $cropBadge = match($cropType) {
                                        'olive' => ['bg-amber-100 text-amber-700', \App\Models\GrapeVariety::CROP_TYPES['olive']],
                                        default => ['bg-emerald-100 text-emerald-700', \App\Models\GrapeVariety::CROP_TYPES['other'] ?? 'Otro'],
                                    };
                                @endphp
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium {{ $cropBadge[0] }} shrink-0">
                                    {{ $cropBadge[1] }}
                                </span>
                            @elseif($dotLabel)
                                <span class="inline-flex items-center gap-1 text-xs text-zinc-500 shrink-0">
                                    <span class="w-2 h-2 rounded-full {{ $dotColor }}"></span>
                                    {{ $dotLabel }}
                                </span>
                            @endif
                        </div>
                    @else
                        <div class="flex items-center gap-2 mb-3">
                            <flux:icon icon="tag" class="size-3.5 text-zinc-300 shrink-0" />
                            <span class="text-xs text-zinc-400 italic">{{ __('Sin variedad asignada') }}</span>
                        </div>
                    @endif

                    {{-- Metric tiles --}}
                    <div class="grid grid-cols-3 gap-2 mb-3">
                        <div class="bg-agro-50 rounded-xl p-2.5">
                            <p class="text-[10px] text-agro-600 font-medium uppercase tracking-wide mb-0.5">{{ __('Superficie') }}</p>
                            <p class="text-sm font-bold text-agro-700">{{ number_format($planting->area_planted, 3) }} ha</p>
                        </div>
                        <div class="bg-zinc-50 rounded-xl p-2.5">
                            <p class="text-[10px] text-zinc-500 font-medium uppercase tracking-wide mb-0.5">{{ __('Plantación') }}</p>
                            <p class="text-sm font-bold text-zinc-700">{{ $planting->planting_year ?? '—' }}</p>
                        </div>
                        <div class="bg-amber-50 rounded-xl p-2.5">
                            <p class="text-[10px] text-amber-600 font-medium uppercase tracking-wide mb-0.5">{{ __('Límite') }}</p>
                            <p class="text-sm font-bold text-amber-700">{{ $planting->harvest_limit_kg ? number_format($planting->harvest_limit_kg, 0, ',', '.') . ' kg' : '—' }}</p>
                        </div>
                    </div>

                    {{-- Detalles extra --}}
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-zinc-500 mb-1">
                        @if($planting->vine_count)
                            <span class="inline-flex items-center gap-1">
                                <flux:icon icon="sparkles" class="size-3.5 text-zinc-400" />
                                {{ number_format($planting->vine_count, 0, ',', '.') }} {{ __('cepas') }}
                            </span>
                        @endif
                        @if($planting->trainingSystem)
                            <span class="inline-flex items-center gap-1">
                                <flux:icon icon="adjustments-vertical" class="size-3.5 text-zinc-400" />
                                {{ $planting->trainingSystem->name }}
                            </span>
                        @endif
                        @if($planting->irrigated)
                            <span class="inline-flex items-center gap-1 text-blue-600">
                                <flux:icon icon="cloud" class="size-3.5" />
                                {{ __('Con riego') }}
                            </span>
                        @endif
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-between">
                            {{-- Left: navegar --}}
                            <div class="flex items-center gap-1">
                                <x-agro.action-button variant="map" href="{{ route('plots.show', $planting->plot) }}" :title="__('Ver parcela')" />
                                @can('update', $planting->plot)
                                    <x-agro.action-button variant="edit" href="{{ route('plots.plantings.edit', $planting) }}" :title="__('Editar plantación')" />
                                @endcan
                                @if(auth()->user()->hasViticulturistAccess())
                                <x-agro.action-button icon="sun" variant="default" href="{{ roleRoute('phenology.index', ['filter_planting_id' => $planting->id]) }}" :title="__('Ver fenología')" />
                                <x-agro.action-button icon="plus-circle" variant="default" href="{{ roleRoute('phenology.create', ['planting_id' => $planting->id]) }}" :title="__('Registrar estadio fenológico')" />
                                @endif
                            </div>

                            {{-- Right: toggle activo --}}
                            @can('update', $planting->plot)
                                @if($planting->active)
                                    <x-agro.action-button variant="deactivate" wire:click="toggleActive({{ $planting->id }})" wire:loading.attr="disabled" wire:target="toggleActive({{ $planting->id }})" :title="__('Desactivar plantación')" />
                                @else
                                    <x-agro.action-button variant="activate" wire:click="toggleActive({{ $planting->id }})" wire:loading.attr="disabled" wire:target="toggleActive({{ $planting->id }})" :title="__('Activar plantación')" />
                                @endif
                            @endcan
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        {{-- Pagination --}}
        <x-agro.pagination :paginator="$plantings" />

    @else
        <x-agro.empty-state
            icon="scissors"
            :title="$currentTab === 'active' ? __('No hay plantaciones activas') : __('No hay plantaciones inactivas')"
            :description="$search || $status !== '' || $year !== '' ? __('Ninguna plantación coincide con los filtros aplicados.') : __('Las plantaciones se crean desde la página de detalle de cada parcela.')"
        >
            @if($search || $status !== '' || $year !== '')
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">
                        {{ __('Limpiar filtros') }}
                    </flux:button>
                </x-slot:action>
            @else
                @can('create', App\Models\PlotPlanting::class)
                    <x-slot:action>
                        <flux:button href="{{ route('plots.index') }}" variant="primary" icon="map">
                            {{ __('Ir a Parcelas') }}
                        </flux:button>
                    </x-slot:action>
                @endcan
            @endif
        </x-agro.empty-state>
    @endif

    {{-- Modal: Elegir parcela para nueva plantación --}}
    <x-agro.modal name="pick-plot-for-planting" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="map" class="size-4 text-agro-600" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-zinc-900">{{ __('Nueva plantación') }}</h3>
                        <p class="text-xs text-zinc-500">{{ __('Elige la parcela donde añadir la plantación') }}</p>
                    </div>
                </div>
                <flux:button
                    x-on:click="$dispatch('close-modal', 'pick-plot-for-planting')"
                    variant="ghost"
                    size="sm"
                    icon="x-mark"
                />
            </div>
        </div>

        <div class="px-6 py-4 space-y-3">
            <input
                wire:model.live.debounce.200ms="plotSearch"
                type="text"
                placeholder="{{ __('Buscar parcela...') }}"
                class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent"
            />

            <div class="max-h-64 overflow-y-auto space-y-1">
                @forelse($selectablePlots as $p)
                    <a
                        href="{{ route('plots.plantings.create', $p->id) }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-agro-50 transition-colors group"
                    >
                        <div class="w-7 h-7 bg-zinc-100 rounded-lg flex items-center justify-center shrink-0 group-hover:bg-agro-100">
                            <flux:icon icon="map" class="size-3.5 text-zinc-400 group-hover:text-agro-600" />
                        </div>
                        <span class="text-sm text-zinc-700 group-hover:text-agro-700 font-medium truncate">{{ $p->name }}</span>
                        <flux:icon icon="chevron-right" class="size-3.5 text-zinc-300 group-hover:text-agro-500 ml-auto shrink-0" />
                    </a>
                @empty
                    <p class="text-sm text-zinc-400 text-center py-4">
                        {{ $plotSearch ? __('No se encontraron parcelas') : __('No hay parcelas activas') }}
                    </p>
                @endforelse
            </div>
        </div>
    </x-agro.modal>

    {{-- Modal Filtros --}}
    <x-agro.modal name="planting-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">{{ __('Filtros') }}</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'planting-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-5">

            {{-- Tipo de cultivo (solo viticultor/producer) --}}
            @if(!$wineryOnly)
            <x-agro.filter-select :label="__('Tipo de cultivo')" wire:model.live="cropType" :placeholder="__('Todos los cultivos')">
                @foreach(\App\Models\GrapeVariety::CROP_TYPES as $key => $label)
                    <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                @endforeach
            </x-agro.filter-select>
            @endif

            {{-- Estado operativo --}}
            <x-agro.filter-select :label="__('Estado operativo')" wire:model.live="status" :placeholder="__('Todos los estados')">
                <flux:select.option value="active">{{ __('Activa') }}</flux:select.option>
                <flux:select.option value="removed">{{ __('Arrancada') }}</flux:select.option>
                <flux:select.option value="experimental">{{ __('Experimental') }}</flux:select.option>
                <flux:select.option value="replanting">{{ __('Replantación') }}</flux:select.option>
            </x-agro.filter-select>

            {{-- Año de plantación --}}
            <x-agro.filter-select :label="__('Año de plantación')" wire:model.live="year" :placeholder="__('Todos los años')">
                @foreach($years as $yearOption)
                    <flux:select.option value="{{ $yearOption }}">{{ $yearOption }}</flux:select.option>
                @endforeach
            </x-agro.filter-select>

        </div>

        <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 flex items-center justify-between rounded-b-2xl">
            <button wire:click="clearFilters" x-on:click="$dispatch('close-modal', 'planting-filters')"
                    class="text-sm text-zinc-500 hover:text-zinc-700 transition-colors">
                {{ __('Limpiar filtros') }}
            </button>
            <flux:button x-on:click="$dispatch('close-modal', 'planting-filters')" variant="primary" size="sm">
                {{ __('Aplicar') }}
            </flux:button>
        </div>
    </x-agro.modal>

</div>
