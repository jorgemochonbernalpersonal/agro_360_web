<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        :title="$pageTitle"
        :description="$pageDescription"
    />

    {{-- Tabs Abiertas / Bloqueadas --}}
    <x-agro.tabs
        :tabs="[
            'unlocked' => ['label' => 'Activas',   'count' => $stats['unlocked']],
            'locked'   => ['label' => 'Inactivas', 'count' => $stats['locked']],
        ]"
        :active="$currentTab"
        wireMethod="switchTab"
    />

    {{-- Toolbar --}}
    @php
        $filterCount = (int)(!empty($plotFilter))
                     + (int)(!empty($campaignFilter));
    @endphp

    <div class="flex items-center gap-3">

        <div class="flex-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Buscar por parcela o notas..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
            />
        </div>

        <button
            x-on:click="$dispatch('open-modal', 'activity-filters')"
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

        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        @can('create', \App\Models\AgriculturalActivity::class)
            <flux:button href="{{ $createRoute }}" variant="primary" icon="plus">
                Nuevo registro
            </flux:button>
        @endcan

    </div>

    {{-- Chips de filtros activos --}}
    @if($search || $plotFilter || $campaignFilter)
        <div class="flex flex-wrap items-center gap-2">
            @if($search)
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="magnifying-glass" class="size-3" />
                    "{{ $search }}"
                    <button wire:click="$set('search', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if($plotFilter)
                @php $plotName = $plots->firstWhere('id', $plotFilter)?->name ?? 'Parcela'; @endphp
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="map" class="size-3" />
                    {{ $plotName }}
                    <button wire:click="$set('plotFilter', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if($campaignFilter)
                @php $campaignName = $campaigns->firstWhere('id', $campaignFilter)?->name ?? 'Campaña'; @endphp
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="calendar" class="size-3" />
                    {{ $campaignName }}
                    <button wire:click="$set('campaignFilter', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">
                Limpiar todo
            </button>
        </div>
    @endif

    {{-- Card grid --}}
    @if($activities->count() > 0)
        <div
            class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="search, plotFilter, campaignFilter, clearFilters, switchTab"
        >
            @foreach($activities as $i => $activity)
                @php
                    $btnBase    = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors';
                    $btnDanger  = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors';
                    $btnDisabled = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-300 cursor-not-allowed';
                @endphp

                <x-agro.card
                    wire:key="activity-{{ $activity->id }}"
                    class="animate-fade-in-up hover:-translate-y-1 {{ $activity->is_locked ? 'opacity-75' : '' }}"
                    style="animation-delay: {{ min($i * 50, 400) }}ms"
                >
                    {{-- Header --}}
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 {{ $typeBg }} rounded-full flex items-center justify-center shrink-0">
                                <flux:icon icon="{{ $typeIcon }}" class="size-4 {{ $typeIconColor }}" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-zinc-900 text-sm truncate leading-tight" title="{{ $activity->plot->name }}">
                                    {{ $activity->plot->name }}
                                </p>
                                @if($activity->plotPlanting?->grapeVariety)
                                    <p class="text-xs text-zinc-400 leading-tight mt-0.5 truncate">
                                        {{ $activity->plotPlanting->grapeVariety->name }}
                                    </p>
                                @endif
                            </div>
                            @if($activity->is_locked)
                                <flux:badge color="yellow" size="sm" class="shrink-0">Bloqueada</flux:badge>
                            @elseif($typeBadgeColor)
                                <flux:badge color="{{ $typeBadgeColor }}" size="sm" class="shrink-0">Abierta</flux:badge>
                            @else
                                <flux:badge size="sm" class="shrink-0">Abierta</flux:badge>
                            @endif
                        </div>
                    </x-slot:header>

                    {{-- Fecha --}}
                    <div class="flex items-center gap-2 mb-3">
                        <flux:icon icon="calendar-days" class="size-3.5 text-zinc-400 shrink-0" />
                        <span class="text-xs font-semibold text-zinc-700">
                            {{ $activity->activity_date->format('d/m/Y') }}
                        </span>
                        @if($activity->campaign)
                            <span class="ml-auto text-xs text-zinc-400">{{ $activity->campaign->year }}</span>
                        @endif
                    </div>

                    {{-- Detalle específico por tipo --}}
                    <div class="bg-zinc-50 rounded-xl p-2.5 mb-3 space-y-1">

                        @if($activityType === 'phytosanitary' && $activity->phytosanitaryTreatment)
                            <p class="text-xs font-semibold text-zinc-700 truncate">
                                {{ $activity->phytosanitaryTreatment->product->name }}
                            </p>
                            @if($activity->phytosanitaryTreatment->area_treated)
                                <p class="text-xs text-zinc-500">
                                    Área: {{ number_format($activity->phytosanitaryTreatment->area_treated, 2) }} ha
                                </p>
                            @endif
                            @if($activity->phytosanitaryTreatment->pest)
                                <p class="text-xs text-zinc-500">
                                    Objetivo: {{ $activity->phytosanitaryTreatment->pest->name }}
                                </p>
                            @endif
                            @php
                                $safetyDays = $activity->phytosanitaryTreatment->product->withdrawal_period_days ?? 0;
                                $isPassed = false;
                                $daysLeft = 0;
                                if ($safetyDays > 0) {
                                    $safeDate = \Carbon\Carbon::parse($activity->activity_date)->addDays($safetyDays);
                                    $isPassed = \Carbon\Carbon::today() >= $safeDate;
                                    $daysLeft = \Carbon\Carbon::today()->diffInDays($safeDate, false);
                                }
                            @endphp
                            @if($safetyDays > 0)
                                <p class="text-xs {{ $isPassed ? 'text-green-600' : 'text-red-600' }} font-medium">
                                    {{ $isPassed
                                        ? '✓ Puede cosechar'
                                        : 'Esperar ' . abs($daysLeft) . 'd (hasta ' . $safeDate->format('d/m') . ')'
                                    }}
                                </p>
                            @endif

                        @elseif($activityType === 'fertilization' && $activity->fertilization)
                            <p class="text-xs font-semibold text-zinc-700">
                                {{ $activity->fertilization->fertilizer_name ?: 'Fertilización' }}
                            </p>
                            @if($activity->fertilization->quantity)
                                <p class="text-xs text-zinc-500">
                                    Cantidad: {{ number_format($activity->fertilization->quantity, 2) }} kg
                                </p>
                            @endif
                            @if($activity->fertilization->fertilizer_type)
                                <p class="text-xs text-zinc-500">{{ ucfirst($activity->fertilization->fertilizer_type) }}</p>
                            @endif

                        @elseif($activityType === 'irrigation' && $activity->irrigation)
                            <p class="text-xs font-semibold text-zinc-700">Riego</p>
                            @if($activity->irrigation->water_volume)
                                <p class="text-xs text-zinc-500">
                                    Volumen: {{ number_format($activity->irrigation->water_volume, 2) }} L
                                </p>
                            @endif
                            @if($activity->irrigation->irrigation_type)
                                <p class="text-xs text-zinc-500">{{ ucfirst($activity->irrigation->irrigation_type) }}</p>
                            @endif

                        @elseif($activityType === 'cultural' && $activity->culturalWork)
                            <p class="text-xs font-semibold text-zinc-700">
                                {{ $activity->culturalWork->work_type ?: 'Labor cultural' }}
                            </p>
                            @if($activity->culturalWork->hours_worked)
                                <p class="text-xs text-zinc-500">
                                    Duración: {{ number_format($activity->culturalWork->hours_worked, 1) }} h
                                </p>
                            @endif

                        @elseif($activityType === 'observation' && $activity->observation)
                            <p class="text-xs font-semibold text-zinc-700">
                                {{ $activity->observation->observation_type ?: 'Observación' }}
                            </p>
                            @if($activity->observation->severity)
                                <p class="text-xs text-zinc-500">
                                    Severidad: {{ ucfirst($activity->observation->severity) }}
                                </p>
                            @endif

                        @elseif($activityType === 'pruning' && $activity->culturalWork)
                            @php
                                $pruningLabels = [
                                    'guyot'       => 'Guyot',
                                    'doble_guyot' => 'Doble Guyot',
                                    'vaso'        => 'Vaso',
                                    'cordon'      => 'Cordón',
                                    'other'       => 'Otro',
                                ];
                            @endphp
                            <p class="text-xs font-semibold text-lime-700">
                                {{ $pruningLabels[$activity->culturalWork->pruning_type] ?? 'Poda' }}
                            </p>
                            @if($activity->culturalWork->productive_buds_per_hectare)
                                <p class="text-xs text-zinc-500">
                                    {{ number_format($activity->culturalWork->productive_buds_per_hectare) }} yemas/ha
                                </p>
                            @endif
                            @if($activity->culturalWork->hours_worked)
                                <p class="text-xs text-zinc-500">
                                    Duración: {{ number_format($activity->culturalWork->hours_worked, 1) }} h
                                </p>
                            @endif

                        @elseif($activityType === 'post_harvest' && $activity->postHarvestTreatment)
                            <p class="text-xs font-semibold text-indigo-700">
                                {{ $activity->postHarvestTreatment->application_type_label }}
                            </p>
                            @if($activity->postHarvestTreatment->treated_area_ha)
                                <p class="text-xs text-zinc-500">
                                    Superficie: {{ number_format($activity->postHarvestTreatment->treated_area_ha, 2) }} ha
                                </p>
                            @endif
                            @if($activity->postHarvestTreatment->product)
                                <p class="text-xs text-zinc-500">{{ $activity->postHarvestTreatment->product->name }}</p>
                            @endif

                        @else
                            <p class="text-xs text-zinc-400 italic">Sin detalles disponibles</p>
                        @endif

                    </div>

                    {{-- Equipo + Notas --}}
                    @if($activity->crew)
                        <div class="flex items-center gap-2 mb-2">
                            <flux:icon icon="users" class="size-3.5 text-zinc-400 shrink-0" />
                            <span class="text-xs text-zinc-600 truncate">{{ $activity->crew->name }}</span>
                        </div>
                    @endif
                    @if($activity->notes)
                        <p class="text-xs text-zinc-400 truncate">{{ Str::limit($activity->notes, 60) }}</p>
                    @endif

                    {{-- Footer --}}
                    <x-slot:footer>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1">
                                {{-- Parcela --}}
                                <a href="{{ route('plots.index') }}" class="{{ $btnBase }}" title="Ver parcelas">
                                    <flux:icon icon="map" class="size-4" />
                                </a>
                            </div>
                            <div class="flex items-center gap-1">
                                @if(!$activity->is_locked)
                                    @can('update', $activity)
                                        <a href="{{ route($editRouteName, $activity->id) }}" class="{{ $btnBase }}" title="Editar">
                                            <flux:icon icon="pencil-square" class="size-4" />
                                        </a>
                                    @endcan
                                    @can('delete', $activity)
                                        <button
                                            wire:click="delete({{ $activity->id }})"
                                            wire:confirm="¿Estás seguro de eliminar esta actividad? Esta acción no se puede deshacer."
                                            class="{{ $btnDanger }}"
                                            title="Eliminar"
                                        >
                                            <flux:icon icon="trash" class="size-4" />
                                        </button>
                                    @endcan
                                @else
                                    <span class="{{ $btnDisabled }}" title="Actividad bloqueada — cumplimiento PAC">
                                        <flux:icon icon="lock-closed" class="size-4" />
                                    </span>
                                @endif
                            </div>
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        @if($activities->hasPages())
            <div class="flex justify-center">{{ $activities->links() }}</div>
        @endif

    @else
        <x-agro.empty-state
            :icon="$typeIcon"
            :message="$currentTab === 'unlocked' ? 'No hay registros activos' : 'No hay registros inactivos'"
            :description="($search || $plotFilter)
                ? 'Ningún registro coincide con los filtros aplicados.'
                : ($currentTab === 'unlocked'
                    ? 'Empieza añadiendo tu primer registro de ' . strtolower($pageTitle) . '.'
                    : 'Los registros se bloquean automáticamente para cumplimiento PAC.')"
        >
            @if($search || $plotFilter)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                </x-slot:action>
            @elseif($currentTab === 'unlocked')
                <x-slot:action>
                    @can('create', \App\Models\AgriculturalActivity::class)
                        <flux:button href="{{ $createRoute }}" variant="primary" icon="plus">
                            Nuevo registro
                        </flux:button>
                    @endcan
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @endif

    {{-- Modal Filtros --}}
    <x-agro.modal name="activity-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'activity-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Campaña</label>
                <select wire:model.live="campaignFilter"
                        class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent">
                    <option value="">Todas las campañas</option>
                    @foreach($campaigns as $campaign)
                        <option value="{{ $campaign->id }}">
                            {{ $campaign->name }} ({{ $campaign->year }}){{ $campaign->active ? ' ★' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Parcela</label>
                <select wire:model.live="plotFilter"
                        class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent">
                    <option value="">Todas las parcelas</option>
                    @foreach($plots as $plot)
                        <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 flex items-center justify-between rounded-b-2xl">
            <button wire:click="clearFilters" x-on:click="$dispatch('close-modal', 'activity-filters')"
                    class="text-sm text-zinc-500 hover:text-zinc-700 transition-colors">
                Limpiar filtros
            </button>
            <flux:button x-on:click="$dispatch('close-modal', 'activity-filters')" variant="primary" size="sm">
                Aplicar
            </flux:button>
        </div>
    </x-agro.modal>

</div>
