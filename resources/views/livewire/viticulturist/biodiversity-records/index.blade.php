<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        :title="__('Biodiversidad y Cubiertas')"
        :description="__('Registro de elementos de biodiversidad, cubiertas vegetales y estructuras ecológicas para eco-esquemas PAC')"
        icon="sparkles"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.biodiversity-records.create') }}" variant="primary" icon="plus">
                {{ __('Nuevo') }}
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats (colapsables) --}}
    <div x-data="{
        open: localStorage.getItem('biodiversity-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('biodiversity-stats-open', String(this.open));
        }
    }">
        <button @click="toggle()"
            class="flex items-center gap-1.5 text-[11px] font-semibold text-zinc-400 uppercase tracking-widest hover:text-zinc-600 transition-colors mb-3">
            <span>{{ __('Estadísticas') }}</span>
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
                    :label="__('Registros totales')"
                    :value="$stats['total']"
                    :description="$stats['total'] . ' ' . __('registros')"
                    icon="sparkles"
                    color="agro"
                />
                <x-agro.stat-card
                    :label="__('Superficie total')"
                    :value="number_format($stats['total_area_m2'], 2, ',', '.') . ' m²'"
                    :description="__('Superficie documentada')"
                    icon="map"
                    color="green"
                />
                <x-agro.stat-card
                    :label="__('Tipos utilizados')"
                    :value="$stats['types_count']"
                    :description="$stats['types_count'] . ' ' . __('tipos distintos')"
                    icon="tag"
                    color="blue"
                />
                <x-agro.stat-card
                    :label="__('Parcelas cubiertas')"
                    :value="$stats['plots_count']"
                    :description="$stats['plots_count'] . ' ' . __('parcelas')"
                    icon="map-pin"
                    color="amber"
                />
            </div>
        </div>
    </div>

    {{-- Toolbar --}}
    @php
        $filterCount = (int) !empty($filterRecordType) + (int) !empty($filterPlot) + (int) !empty($filterCampaign);
    @endphp
    <div class="flex items-center gap-3">
        <div class="flex-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                :placeholder="__('Buscar por descripción, especies, notas...')"
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
            />
        </div>
        <button
            x-on:click="$dispatch('open-modal', 'biodiversity-filters')"
            class="relative inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-50 shadow-sm transition-colors"
        >
            <flux:icon icon="adjustments-horizontal" class="size-4 text-zinc-500" />
            {{ __('Filtros') }}
            @if($filterCount > 0)
                <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-agro-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">{{ $filterCount }}</span>
            @endif
        </button>
        <div class="w-px h-6 bg-zinc-200"></div>
        <flux:button href="{{ roleRoute('viticulturist.biodiversity-records.create') }}" variant="primary" icon="plus">
            {{ __('Nuevo') }}
        </flux:button>
    </div>

    {{-- Chips filtros activos --}}
    @if($filterRecordType || $filterPlot || $filterCampaign)
        <div class="flex flex-wrap items-center gap-2">
            @if($filterRecordType)
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="sparkles" class="size-3" />
                    {{ $recordTypes[$filterRecordType] ?? $filterRecordType }}
                    <button wire:click="$set('filterRecordType', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if($filterPlot)
                @php $plotObj = $plots->firstWhere('id', $filterPlot); @endphp
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="map-pin" class="size-3" />
                    {{ $plotObj?->name ?? $filterPlot }}
                    <button wire:click="$set('filterPlot', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
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
            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">{{ __('Limpiar todo') }}</button>
        </div>
    @endif

    {{-- Skeleton carga --}}
    <div wire:loading wire:target="search, filterRecordType, filterPlot, filterCampaign, nextPage, previousPage, gotoPage">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @for($i = 0; $i < 8; $i++)
                <x-agro.skeleton-card />
            @endfor
        </div>
    </div>

    {{-- Grid de cards --}}
    <div wire:loading.remove wire:target="search, filterRecordType, filterPlot, filterCampaign, nextPage, previousPage, gotoPage">
        @if($entries->isEmpty())
            <x-agro.empty-state
                icon="sparkles"
                :title="__('Sin registros de biodiversidad')"
                :description="$search || $filterRecordType || $filterPlot || $filterCampaign ? __('Ningún registro coincide con los filtros aplicados.') : __('Documenta cubiertas vegetales, setos, fauna auxiliar y otros elementos de biodiversidad.')"
            >
                @if(!$search && !$filterRecordType && !$filterPlot && !$filterCampaign)
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('viticulturist.biodiversity-records.create') }}" variant="primary" icon="plus">
                            {{ __('Nuevo Registro') }}
                        </flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($entries as $entry)
                    @php
                        $delay = min($loop->index * 50, 300);
                        $color = $entry->record_type_color;
                        $colorClasses = match($color) {
                            'green'   => ['bg' => 'bg-green-100',   'icon' => 'text-green-600',   'badge' => 'bg-green-100 text-green-700'],
                            'amber'   => ['bg' => 'bg-amber-100',   'icon' => 'text-amber-600',   'badge' => 'bg-amber-100 text-amber-700'],
                            'emerald' => ['bg' => 'bg-emerald-100', 'icon' => 'text-emerald-600', 'badge' => 'bg-emerald-100 text-emerald-700'],
                            'blue'    => ['bg' => 'bg-blue-100',    'icon' => 'text-blue-600',    'badge' => 'bg-blue-100 text-blue-700'],
                            'violet'  => ['bg' => 'bg-violet-100',  'icon' => 'text-violet-600',  'badge' => 'bg-violet-100 text-violet-700'],
                            'orange'  => ['bg' => 'bg-orange-100',  'icon' => 'text-orange-600',  'badge' => 'bg-orange-100 text-orange-700'],
                            'cyan'    => ['bg' => 'bg-cyan-100',    'icon' => 'text-cyan-600',    'badge' => 'bg-cyan-100 text-cyan-700'],
                            default   => ['bg' => 'bg-zinc-100',    'icon' => 'text-zinc-400',    'badge' => 'bg-zinc-100 text-zinc-500'],
                        };
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="bio-{{ $entry->id }}"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 {{ $colorClasses['bg'] }} rounded-xl flex items-center justify-center shrink-0">
                                    <flux:icon icon="{{ $entry->record_type_icon }}" class="size-5 {{ $colorClasses['icon'] }}" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $colorClasses['badge'] }}">
                                        {{ $entry->record_type_label }}
                                    </span>
                                </div>
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-3">
                            {{-- Parcela + Fecha --}}
                            <div class="bg-zinc-50 rounded-xl p-3 space-y-1">
                                <div class="flex items-center gap-2 text-sm text-zinc-700">
                                    <flux:icon icon="map-pin" class="size-4 text-zinc-400 shrink-0" />
                                    <span class="truncate font-medium">{{ $entry->plot->name ?? '—' }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-zinc-400">
                                    <flux:icon icon="calendar-days" class="size-3.5 shrink-0" />
                                    <span class="text-xs">{{ $entry->record_date->format('d/m/Y') }}</span>
                                </div>
                            </div>

                            {{-- Superficie --}}
                            @if($entry->area_m2)
                                <div class="flex items-center gap-2 text-sm text-zinc-600">
                                    <flux:icon icon="map" class="size-4 text-zinc-400 shrink-0" />
                                    <span>{{ number_format($entry->area_m2, 2, ',', '.') }} m²</span>
                                </div>
                            @endif

                            {{-- Especies --}}
                            @if($entry->species)
                                <div class="flex items-start gap-2 text-sm text-zinc-600">
                                    <flux:icon icon="globe-europe-africa" class="size-4 text-zinc-400 shrink-0 mt-0.5" />
                                    <span class="line-clamp-2">{{ $entry->species }}</span>
                                </div>
                            @endif

                            {{-- Descripcion --}}
                            @if($entry->description)
                                <p class="text-xs text-zinc-500 line-clamp-2">{{ $entry->description }}</p>
                            @endif
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5">
                                <x-agro.action-button
                                    variant="edit"
                                    href="{{ roleRoute('viticulturist.biodiversity-records.edit', $entry) }}"
                                    :title="__('Editar')"
                                />
                                <x-agro.action-button
                                    variant="delete"
                                    wire:click="delete({{ $entry->id }})"
                                    wire:confirm="{{ __('¿Eliminar este registro permanentemente?') }}"
                                    :title="__('Eliminar')"
                                />
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <x-agro.pagination :paginator="$entries" />
        @endif
    </div>

    {{-- Modal: Filtros --}}
    <x-agro.filter-modal
        name="biodiversity-filters"
        :hasActiveFilters="(bool) ($filterRecordType || $filterPlot || $filterCampaign)"
        clearAction="clearFilters"
    >
        <div>
            <x-agro.field-label>{{ __('Tipo de registro') }}</x-agro.field-label>
            <flux:select wire:model.live="filterRecordType">
                <flux:select.option value="">{{ __('Todos los tipos') }}</flux:select.option>
                @foreach($recordTypes as $key => $label)
                    <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
        <div>
            <x-agro.field-label>{{ __('Parcela') }}</x-agro.field-label>
            <flux:select wire:model.live="filterPlot">
                <flux:select.option value="">{{ __('Todas las parcelas') }}</flux:select.option>
                @foreach($plots as $plot)
                    <flux:select.option value="{{ $plot->id }}">{{ $plot->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
        <div>
            <x-agro.field-label>{{ __('Campaña') }}</x-agro.field-label>
            <flux:select wire:model.live="filterCampaign">
                <flux:select.option value="">{{ __('Todas las campañas') }}</flux:select.option>
                @foreach($campaigns as $c)
                    <flux:select.option value="{{ $c->id }}">{{ $c->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
    </x-agro.filter-modal>

</div>
