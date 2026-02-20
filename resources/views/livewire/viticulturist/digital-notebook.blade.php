<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Cuaderno Digital"
        :description="$currentCampaign ? 'Campaña ' . $currentCampaign->name . ' · ' . $currentCampaign->year : 'Registro completo de todas tus actividades agrícolas'"
    />

    @if($currentCampaign)
        {{-- Stats rápidas --}}
        <x-agro.card>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-agro-700">{{ $stats['total'] }}</div>
                    <div class="text-sm text-zinc-500">Total</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">{{ $stats['phytosanitary'] }}</div>
                    <div class="text-sm text-zinc-500">Tratamientos</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $stats['fertilization'] }}</div>
                    <div class="text-sm text-zinc-500">Fertilizaciones</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-cyan-600">{{ $stats['irrigation'] }}</div>
                    <div class="text-sm text-zinc-500">Riegos</div>
                </div>
            </div>

            @can('create', \App\Models\AgriculturalActivity::class)
                <div class="mt-5 pt-5 border-t border-zinc-100">
                    <div class="flex flex-wrap gap-2 justify-center">
                        <flux:button href="{{ route('viticulturist.digital-notebook.treatment.create') }}" variant="danger" size="sm">+ Tratamiento</flux:button>
                        <flux:button href="{{ route('viticulturist.digital-notebook.fertilization.create') }}" variant="primary" size="sm" class="!bg-blue-600 hover:!bg-blue-700">+ Fertilización</flux:button>
                        <flux:button href="{{ route('viticulturist.digital-notebook.irrigation.create') }}" variant="primary" size="sm" class="!bg-cyan-600 hover:!bg-cyan-700">+ Riego</flux:button>
                        <flux:button href="{{ route('viticulturist.digital-notebook.cultural.create') }}" variant="primary" size="sm" class="!bg-purple-600 hover:!bg-purple-700">+ Labor</flux:button>
                        <flux:button href="{{ route('viticulturist.digital-notebook.observation.create') }}" variant="primary" size="sm" class="!bg-amber-600 hover:!bg-amber-700">+ Observación</flux:button>
                        <flux:button href="{{ route('viticulturist.digital-notebook.harvest.create') }}" variant="primary" size="sm" class="!bg-purple-600 hover:!bg-purple-700">Cosecha</flux:button>
                    </div>
                </div>
            @endcan
        </x-agro.card>
    @endif

    {{-- Toolbar --}}
    @php
        $filterCount = (int)(!empty($selectedPlot))
                     + (int)(!empty($activityType))
                     + (int)(!empty($dateFrom))
                     + (int)(!empty($dateTo))
                     + (int)(!empty($productFilter));
    @endphp

    <div class="flex items-center gap-3">

        <div class="flex-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Buscar en notas, parcelas, productos..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
            />
        </div>

        <button
            x-on:click="$dispatch('open-modal', 'notebook-filters')"
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

        {{-- Selector de campaña --}}
        @if($campaigns->count() > 0)
            <select
                wire:model.live="selectedCampaign"
                class="px-3 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm text-zinc-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
            >
                @foreach($campaigns as $campaign)
                    <option value="{{ $campaign->id }}">
                        {{ $campaign->name }} ({{ $campaign->year }}){{ $campaign->active ? ' ★' : '' }}
                    </option>
                @endforeach
            </select>
        @endif

    </div>

    {{-- Chips de filtros activos --}}
    @if($search || $selectedPlot || $activityType || $dateFrom || $dateTo || $productFilter)
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
            @if($activityType)
                @php $typeLabels = ['phytosanitary' => 'Tratamiento', 'fertilization' => 'Fertilización', 'irrigation' => 'Riego', 'cultural' => 'Labor', 'observation' => 'Observación', 'harvest' => 'Cosecha']; @endphp
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    {{ $typeLabels[$activityType] ?? $activityType }}
                    <button wire:click="$set('activityType', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if($dateFrom || $dateTo)
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="calendar-days" class="size-3" />
                    {{ $dateFrom ?? '…' }} — {{ $dateTo ?? '…' }}
                    <button wire:click="$set('dateFrom', ''); $set('dateTo', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
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
            wire:target="search, selectedPlot, activityType, dateFrom, dateTo, productFilter, clearFilters, selectedCampaign"
        >
            @foreach($activities as $i => $activity)
                @php
                    $typeConfig = [
                        'phytosanitary' => ['label' => 'Tratamiento',   'color' => 'red',    'bg' => 'bg-red-50',    'icon_color' => 'text-red-600',    'badge' => 'red',    'icon' => 'shield-exclamation'],
                        'fertilization' => ['label' => 'Fertilización', 'color' => 'blue',   'bg' => 'bg-blue-50',   'icon_color' => 'text-blue-600',   'badge' => 'blue',   'icon' => 'beaker'],
                        'irrigation'    => ['label' => 'Riego',         'color' => 'cyan',   'bg' => 'bg-cyan-50',   'icon_color' => 'text-cyan-600',   'badge' => 'cyan',   'icon' => 'cloud'],
                        'cultural'      => ['label' => 'Labor',         'color' => 'yellow', 'bg' => 'bg-amber-50',  'icon_color' => 'text-amber-600',  'badge' => 'yellow', 'icon' => 'wrench-screwdriver'],
                        'observation'   => ['label' => 'Observación',   'color' => null,     'bg' => 'bg-zinc-100',  'icon_color' => 'text-zinc-500',   'badge' => null,     'icon' => 'eye'],
                        'harvest'       => ['label' => 'Cosecha',       'color' => 'purple', 'bg' => 'bg-purple-50', 'icon_color' => 'text-purple-600', 'badge' => 'purple', 'icon' => 'scissors'],
                    ];
                    $tc = $typeConfig[$activity->activity_type] ?? ['label' => ucfirst($activity->activity_type), 'color' => null, 'bg' => 'bg-zinc-100', 'icon_color' => 'text-zinc-500', 'badge' => null, 'icon' => 'document'];

                    $btnBase    = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors';
                    $btnDanger  = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors';
                @endphp

                <x-agro.card
                    wire:key="activity-{{ $activity->id }}"
                    class="animate-fade-in-up hover:-translate-y-1"
                    style="animation-delay: {{ min($i * 50, 400) }}ms"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 {{ $tc['bg'] }} rounded-full flex items-center justify-center shrink-0">
                                <flux:icon icon="{{ $tc['icon'] }}" class="size-4 {{ $tc['icon_color'] }}" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-zinc-900 text-sm truncate leading-tight">{{ $activity->plot->name }}</p>
                                @if($activity->plotPlanting?->grapeVariety)
                                    <p class="text-xs text-zinc-400 leading-tight mt-0.5 truncate">{{ $activity->plotPlanting->grapeVariety->name }}</p>
                                @endif
                            </div>
                            <flux:badge :color="$tc['badge']" size="sm" class="shrink-0">{{ $tc['label'] }}</flux:badge>
                        </div>
                    </x-slot:header>

                    {{-- Fecha --}}
                    <div class="flex items-center gap-2 mb-3">
                        <flux:icon icon="calendar-days" class="size-3.5 text-zinc-400 shrink-0" />
                        <span class="text-xs font-semibold text-zinc-700">{{ $activity->activity_date->format('d/m/Y') }}</span>
                        @if($activity->is_locked)
                            <flux:badge color="yellow" size="sm" class="ml-auto">Bloqueada</flux:badge>
                        @endif
                    </div>

                    {{-- Detalle según tipo --}}
                    <div class="bg-zinc-50 rounded-xl p-2.5 mb-3 space-y-1">
                        @if($activity->phytosanitaryTreatment)
                            <p class="text-xs font-semibold text-zinc-700 truncate">{{ $activity->phytosanitaryTreatment->product->name }}</p>
                            @if($activity->phytosanitaryTreatment->area_treated)
                                <p class="text-xs text-zinc-500">Área: {{ number_format($activity->phytosanitaryTreatment->area_treated, 2) }} ha</p>
                            @endif
                            @if($activity->phytosanitaryTreatment->pest)
                                <p class="text-xs text-zinc-500">Objetivo: {{ $activity->phytosanitaryTreatment->pest->name }}</p>
                            @endif
                            @php
                                $safetyDays = $activity->phytosanitaryTreatment->product->withdrawal_period_days ?? 0;
                                if ($safetyDays > 0) {
                                    $safeDate = \Carbon\Carbon::parse($activity->activity_date)->addDays($safetyDays);
                                    $isPassed = \Carbon\Carbon::today() >= $safeDate;
                                    $daysLeft = \Carbon\Carbon::today()->diffInDays($safeDate, false);
                                }
                            @endphp
                            @if($safetyDays > 0)
                                <p class="text-xs {{ $isPassed ? 'text-green-600' : 'text-red-600' }} font-medium">
                                    {{ $isPassed ? '✓ Puede cosechar' : 'Esperar ' . abs($daysLeft) . 'd (hasta ' . $safeDate->format('d/m') . ')' }}
                                </p>
                            @endif
                        @elseif($activity->fertilization)
                            <p class="text-xs font-semibold text-zinc-700">{{ $activity->fertilization->fertilizer_name ?: 'Fertilización' }}</p>
                            @if($activity->fertilization->quantity)
                                <p class="text-xs text-zinc-500">Cantidad: {{ number_format($activity->fertilization->quantity, 2) }} kg</p>
                            @endif
                        @elseif($activity->irrigation)
                            <p class="text-xs font-semibold text-zinc-700">Riego</p>
                            @if($activity->irrigation->water_volume)
                                <p class="text-xs text-zinc-500">Volumen: {{ number_format($activity->irrigation->water_volume, 2) }} L</p>
                            @endif
                        @elseif($activity->culturalWork)
                            <p class="text-xs font-semibold text-zinc-700">{{ $activity->culturalWork->work_type ?: 'Labor cultural' }}</p>
                            @if($activity->culturalWork->hours_worked)
                                <p class="text-xs text-zinc-500">Duración: {{ number_format($activity->culturalWork->hours_worked, 1) }} h</p>
                            @endif
                        @elseif($activity->observation)
                            <p class="text-xs font-semibold text-zinc-700">{{ $activity->observation->observation_type ?: 'Observación' }}</p>
                            @if($activity->observation->severity)
                                <p class="text-xs text-zinc-500">Severidad: {{ ucfirst($activity->observation->severity) }}</p>
                            @endif
                        @elseif($activity->harvest)
                            <p class="text-xs font-semibold text-purple-700">Vendimia</p>
                            <p class="text-xs text-zinc-500">{{ number_format($activity->harvest->total_weight, 0) }} kg
                                @if($activity->harvest->total_value) · {{ number_format($activity->harvest->total_value, 2) }}€ @endif
                            </p>
                        @endif
                    </div>

                    {{-- Equipo + Notas --}}
                    @if($activity->crew || ($activity->crewMember && $activity->crewMember->viticulturist))
                        <div class="flex items-center gap-2 mb-2">
                            <flux:icon icon="users" class="size-3.5 text-zinc-400 shrink-0" />
                            <span class="text-xs text-zinc-600 truncate">
                                {{ $activity->crew?->name ?? $activity->crewMember?->viticulturist?->name }}
                            </span>
                        </div>
                    @endif
                    @if($activity->notes)
                        <p class="text-xs text-zinc-400 truncate">{{ Str::limit($activity->notes, 60) }}</p>
                    @endif

                    <x-slot:footer>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1">
                                <button wire:click="openAuditHistory({{ $activity->id }})" class="{{ $btnBase }}" title="Historial de cambios">
                                    <flux:icon icon="clock" class="size-4" />
                                </button>
                                @if($activity->harvest)
                                    <a href="{{ route('viticulturist.digital-notebook.harvest.show', $activity->harvest->id) }}" class="{{ $btnBase }}" title="Ver cosecha">
                                        <flux:icon icon="eye" class="size-4" />
                                    </a>
                                @endif
                            </div>
                            <div class="flex items-center gap-1">
                                @if(!$activity->is_locked)
                                    @can('update', $activity)
                                        @if($activity->harvest)
                                            <a href="{{ route('viticulturist.digital-notebook.harvest.edit', $activity->harvest->id) }}" class="{{ $btnBase }}" title="Editar">
                                                <flux:icon icon="pencil-square" class="size-4" />
                                            </a>
                                        @elseif($activity->activity_type === 'phytosanitary')
                                            <a href="{{ route('viticulturist.digital-notebook.treatment.edit', $activity->id) }}" class="{{ $btnBase }}" title="Editar">
                                                <flux:icon icon="pencil-square" class="size-4" />
                                            </a>
                                        @elseif($activity->activity_type === 'fertilization')
                                            <a href="{{ route('viticulturist.digital-notebook.fertilization.edit', $activity->id) }}" class="{{ $btnBase }}" title="Editar">
                                                <flux:icon icon="pencil-square" class="size-4" />
                                            </a>
                                        @elseif($activity->activity_type === 'irrigation')
                                            <a href="{{ route('viticulturist.digital-notebook.irrigation.edit', $activity->id) }}" class="{{ $btnBase }}" title="Editar">
                                                <flux:icon icon="pencil-square" class="size-4" />
                                            </a>
                                        @elseif($activity->activity_type === 'cultural')
                                            <a href="{{ route('viticulturist.digital-notebook.cultural.edit', $activity->id) }}" class="{{ $btnBase }}" title="Editar">
                                                <flux:icon icon="pencil-square" class="size-4" />
                                            </a>
                                        @elseif($activity->activity_type === 'observation')
                                            <a href="{{ route('viticulturist.digital-notebook.observation.edit', $activity->id) }}" class="{{ $btnBase }}" title="Editar">
                                                <flux:icon icon="pencil-square" class="size-4" />
                                            </a>
                                        @endif
                                    @endcan
                                    @can('delete', $activity)
                                        <button
                                            wire:click="deleteActivity({{ $activity->id }})"
                                            wire:confirm="¿Estás seguro de eliminar esta actividad?"
                                            class="{{ $btnDanger }}"
                                            title="Eliminar"
                                        >
                                            <flux:icon icon="trash" class="size-4" />
                                        </button>
                                    @endcan
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
            icon="book-open"
            message="No hay actividades registradas"
            description="{{ ($selectedPlot || $activityType || $search || $dateFrom || $dateTo || $productFilter) ? 'Ninguna actividad coincide con los filtros aplicados.' : 'Comienza registrando tu primera actividad agrícola.' }}"
        >
            @if($selectedPlot || $activityType || $search || $dateFrom || $dateTo || $productFilter)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @endif

    {{-- Modal Filtros --}}
    <x-agro.modal name="notebook-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'notebook-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Parcela</label>
                <select wire:model.live="selectedPlot"
                        class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent">
                    <option value="">Todas las parcelas</option>
                    @foreach($plots as $plot)
                        <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Tipo de actividad</label>
                <select wire:model.live="activityType"
                        class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent">
                    <option value="">Todas las actividades</option>
                    <option value="phytosanitary">Tratamientos Fitosanitarios</option>
                    <option value="fertilization">Fertilizaciones</option>
                    <option value="irrigation">Riegos</option>
                    <option value="cultural">Labores Culturales</option>
                    <option value="observation">Observaciones</option>
                    <option value="harvest">Cosechas / Vendimias</option>
                </select>
            </div>
            @if($activityType === 'phytosanitary' && $products->count() > 0)
                <div>
                    <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Producto</label>
                    <select wire:model.live="productFilter"
                            class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent">
                        <option value="">Todos los productos</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Fecha desde</label>
                    <input wire:model.live="dateFrom" type="date"
                           class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Fecha hasta</label>
                    <input wire:model.live="dateTo" type="date"
                           class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent" />
                </div>
            </div>
        </div>

        <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 flex items-center justify-between rounded-b-2xl">
            <button wire:click="clearFilters" x-on:click="$dispatch('close-modal', 'notebook-filters')"
                    class="text-sm text-zinc-500 hover:text-zinc-700 transition-colors">
                Limpiar filtros
            </button>
            <flux:button x-on:click="$dispatch('close-modal', 'notebook-filters')" variant="primary" size="sm">
                Aplicar
            </flux:button>
        </div>
    </x-agro.modal>

    {{-- Modal Auditoría --}}
    @if($showAuditHistory && $selectedActivityId)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col">
                <div class="bg-zinc-50 px-6 py-4 border-b border-zinc-200 flex items-center justify-between">
                    <flux:heading size="sm">Historial de Auditoría</flux:heading>
                    <flux:button wire:click="closeAuditHistory" variant="ghost" size="sm" icon="x-mark" />
                </div>
                <div class="px-6 py-4 overflow-y-auto flex-1">
                    @livewire('viticulturist.digital-notebook.activity-audit-history', ['activity' => \App\Models\AgriculturalActivity::find($selectedActivityId)], 'audit-'.$selectedActivityId)
                </div>
                <div class="bg-zinc-50 px-6 py-3 border-t border-zinc-200 flex justify-end">
                    <flux:button wire:click="closeAuditHistory" variant="outline">Cerrar</flux:button>
                </div>
            </div>
        </div>
    @endif

</div>
