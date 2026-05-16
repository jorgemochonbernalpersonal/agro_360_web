<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        :title="$pageTitle"
        :description="$pageDescription"
    />

    {{-- Tabs Activas / Bloqueadas --}}
    <x-agro.tabs
        :tabs="[
            'unlocked' => ['label' => 'Activas',   'count' => $stats['unlocked']],
            'locked'   => ['label' => 'Bloqueadas', 'count' => $stats['locked']],
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

        <x-agro.search-input wire:model.live.debounce.300ms="search" placeholder="Buscar por parcela o notas..." />

        <x-agro.filter-button modal="activity-filters" :count="$filterCount" />

        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        @can('create', \App\Models\AgriculturalActivity::class)
            <flux:button href="{{ $createRoute }}" variant="primary" icon="plus">
                Nuevo
            </flux:button>
        @endcan

    </div>

    {{-- Chips de filtros activos --}}
    @if($search || $plotFilter || $campaignFilter)
        <div class="flex flex-wrap items-center gap-2">
            @if($search)
                <x-agro.filter-chip icon="magnifying-glass" :label="'\"' . $search . '\"'" wireRemove="$set('search', '')" />
            @endif
            @if($plotFilter)
                @php $plotName = $plots->firstWhere('id', $plotFilter)?->name ?? 'Parcela'; @endphp
                <x-agro.filter-chip icon="map" :label="$plotName" wireRemove="$set('plotFilter', '')" />
            @endif
            @if($campaignFilter)
                @php $campaignName = $campaigns->firstWhere('id', $campaignFilter)?->name ?? 'Campaña'; @endphp
                <x-agro.filter-chip icon="calendar" :label="$campaignName" wireRemove="$set('campaignFilter', '')" />
            @endif
            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">
                Limpiar todo
            </button>
        </div>
    @endif

    {{-- Skeleton durante carga --}}
    <x-agro.loading-grid target="search, plotFilter, campaignFilter, clearFilters, switchTab, nextPage, previousPage, gotoPage" cols="3" count="6" />

    {{-- Card grid --}}
    <div
        wire:loading.remove
        wire:target="search, plotFilter, campaignFilter, clearFilters, switchTab, nextPage, previousPage, gotoPage"
    >
        @if($activities->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($activities as $i => $activity)

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
                                    <x-agro.action-button
                                        variant="map"
                                        href="{{ roleRoute('viticulturist.plots.index') }}"
                                        title="Ver parcelas"
                                    />
                                </div>
                                <div class="flex items-center gap-1">
                                    @if(!$activity->is_locked)
                                        @can('update', $activity)
                                            <x-agro.action-button
                                                variant="edit"
                                                href="{{ route($editRouteName, $activity->id) }}"
                                                title="Editar"
                                            />
                                        @endcan
                                        @can('delete', $activity)
                                            <x-agro.action-button
                                                variant="delete"
                                                wire:click="delete({{ $activity->id }})"
                                                wire:confirm="¿Estás seguro de eliminar esta actividad? Esta acción no se puede deshacer."
                                                title="Eliminar"
                                            />
                                        @endcan
                                    @else
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-300 cursor-not-allowed" title="Actividad bloqueada — cumplimiento PAC">
                                            <flux:icon icon="lock-closed" class="size-4" />
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <div class="mt-6">
                <x-agro.pagination :paginator="$activities" />
            </div>

        @else
            <x-agro.empty-state
                :icon="$typeIcon"
                :title="$currentTab === 'unlocked' ? 'No hay registros activos' : 'No hay registros bloqueados'"
                :description="($search || $plotFilter || $campaignFilter)
                    ? 'Ningún registro coincide con los filtros aplicados.'
                    : ($currentTab === 'unlocked'
                        ? 'Empieza añadiendo tu primer registro de ' . strtolower($pageTitle) . '.'
                        : 'Los registros se bloquean automáticamente para cumplimiento PAC.')"
            >
                @if($search || $plotFilter || $campaignFilter)
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
    </div>

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

        <div class="px-6 py-5 space-y-5">
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Campaña</label>
                <flux:select wire:model.live="campaignFilter">
                    <option value="">Todas las campañas</option>
                    @foreach($campaigns as $campaign)
                        <option value="{{ $campaign->id }}">
                            {{ $campaign->name }} ({{ $campaign->year }}){{ $campaign->active ? ' ★' : '' }}
                        </option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Parcela</label>
                <flux:select wire:model.live="plotFilter">
                    <option value="">Todas las parcelas</option>
                    @foreach($plots as $plot)
                        <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-zinc-200 flex items-center justify-between">
            @if($plotFilter || $campaignFilter)
                <button
                    wire:click="clearFilters"
                    x-on:click="$dispatch('close-modal', 'activity-filters')"
                    class="text-sm text-zinc-400 hover:text-zinc-600 transition-colors"
                >
                    Limpiar filtros
                </button>
            @else
                <span></span>
            @endif
            <flux:button x-on:click="$dispatch('close-modal', 'activity-filters')" variant="primary" size="sm">
                Aplicar
            </flux:button>
        </div>
    </x-agro.modal>

</div>
