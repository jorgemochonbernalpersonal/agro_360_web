<div class="space-y-6 animate-fade-in">
    <!-- Mensajes Flash -->
    @if(session('message'))
        <flux:callout variant="success" icon="check-circle">
            {{ session('message') }}
        </flux:callout>
    @endif

    @if(session('error'))
        <flux:callout variant="danger" icon="exclamation-circle">
            {{ session('error') }}
        </flux:callout>
    @endif

    <!-- Header -->
    <x-agro.page-header
        title="Cuaderno Digital"
        :description="$currentCampaign ? 'Campaña ' . $currentCampaign->name . ' - ' . $currentCampaign->year : 'Registro completo de todas tus actividades agrícolas'"
    >
        <x-slot:actions>
            @if($currentCampaign)
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-zinc-700">Campaña:</span>
                    <flux:select wire:model.live="selectedCampaign">
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign->id }}">
                                {{ $campaign->name }} ({{ $campaign->year }})
                                @if($campaign->active) [Activa] @endif
                            </option>
                        @endforeach
                    </flux:select>
                </div>
            @endif
        </x-slot:actions>
    </x-agro.page-header>

    @if($currentCampaign)
        <!-- Estadísticas de la Campaña -->
        <x-agro.card>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-agro-700">{{ $stats['total'] }}</div>
                    <div class="text-sm text-zinc-600">Total Actividades</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">{{ $stats['phytosanitary'] }}</div>
                    <div class="text-sm text-zinc-600">Tratamientos</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $stats['fertilization'] }}</div>
                    <div class="text-sm text-zinc-600">Fertilizaciones</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-cyan-600">{{ $stats['irrigation'] }}</div>
                    <div class="text-sm text-zinc-600">Riegos</div>
                </div>
            </div>

            <!-- Botones de acción rápida -->
            @can('create', \App\Models\AgriculturalActivity::class)
                <div class="mt-6 pt-6 border-t border-zinc-200" data-cy="quick-actions">
                    <div class="flex flex-wrap gap-3 justify-center">
                        <flux:button href="{{ route('viticulturist.digital-notebook.treatment.create') }}" data-cy="create-treatment-button" variant="danger" size="sm">
                            + Tratamiento
                        </flux:button>
                        <flux:button href="{{ route('viticulturist.digital-notebook.fertilization.create') }}" data-cy="create-fertilization-button" variant="primary" size="sm" class="!bg-blue-600 hover:!bg-blue-700">
                            + Fertilización
                        </flux:button>
                        <flux:button href="{{ route('viticulturist.digital-notebook.irrigation.create') }}" data-cy="create-irrigation-button" variant="primary" size="sm" class="!bg-cyan-600 hover:!bg-cyan-700">
                            + Riego
                        </flux:button>
                        <flux:button href="{{ route('viticulturist.digital-notebook.cultural.create') }}" data-cy="create-cultural-button" variant="primary" size="sm" class="!bg-purple-600 hover:!bg-purple-700">
                            + Labor
                        </flux:button>
                        <flux:button href="{{ route('viticulturist.digital-notebook.observation.create') }}" data-cy="create-observation-button" variant="primary" size="sm" class="!bg-amber-600 hover:!bg-amber-700">
                            + Observación
                        </flux:button>
                        <flux:button href="{{ route('viticulturist.digital-notebook.harvest.create') }}" data-cy="create-harvest-button" variant="primary" size="sm" class="!bg-purple-600 hover:!bg-purple-700">
                            Cosecha
                        </flux:button>
                    </div>
                </div>
            @endcan
        </x-agro.card>
    @endif

    <!-- Filtros -->
    <x-agro.filter-bar>
        <x-agro.filter-select wire:model.live="selectedPlot" data-cy="plot-filter">
            <option value="">Todas las parcelas</option>
            @foreach($plots as $plot)
                <option value="{{ $plot->id }}">{{ $plot->name }}</option>
            @endforeach
        </x-agro.filter-select>

        <x-agro.filter-select wire:model.live="activityType" data-cy="activity-type-filter">
            <option value="">Todas las actividades</option>
            <option value="phytosanitary">Tratamientos Fitosanitarios</option>
            <option value="fertilization">Fertilizaciones</option>
            <option value="irrigation">Riegos</option>
            <option value="cultural">Labores Culturales</option>
            <option value="observation">Observaciones</option>
            <option value="harvest">Cosechas / Vendimias</option>
        </x-agro.filter-select>

        @if($activityType === 'phytosanitary' && $products->count() > 0)
            <x-agro.filter-select wire:model.live="productFilter">
                <option value="">Todos los productos</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </x-agro.filter-select>
        @endif

        <x-agro.filter-input
            wire:model.live="dateFrom"
            type="date"
            placeholder="Fecha desde..."
        />

        <x-agro.filter-input
            wire:model.live="dateTo"
            type="date"
            placeholder="Fecha hasta..."
        />

        <x-agro.filter-input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar en notas, parcelas, productos..."
            data-cy="activity-search-input"
        />

        @if($selectedPlot || $activityType || $search || $dateFrom || $dateTo || $productFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm">
                Limpiar Filtros
            </flux:button>
        @endif
    </x-agro.filter-bar>

    <!-- Tabla de Actividades -->
    @php
        $headers = ['Fecha', 'Parcela', 'Tipo', 'Detalle', 'Equipo', 'Notas', 'Acciones'];
    @endphp

    <x-agro.data-table
        :headers="$headers"
        empty-message="No hay actividades registradas"
        empty-description="{{ ($selectedPlot || $activityType || $search || $dateFrom || $dateTo || $productFilter) ? 'No se encontraron actividades con los filtros seleccionados' : 'Comienza registrando tu primera actividad agrícola' }}"
    >
        @if($activities->count() > 0)
            @foreach($activities as $activity)
                <x-agro.table-row>
                    <x-agro.table-cell>
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-blue-100 text-blue-700 text-sm font-semibold">
                            <flux:icon icon="calendar-days" class="size-4" />
                            {{ $activity->activity_date->format('d/m/Y') }}
                        </span>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <div class="flex items-center gap-2">
                            <flux:icon icon="map" class="size-4 text-agro-500 flex-shrink-0" />
                            <div>
                                <span class="text-sm font-medium text-zinc-900">{{ $activity->plot->name }}</span>
                                @if($activity->plotPlanting)
                                    <br><span class="text-xs text-zinc-600">
                                        {{ $activity->plotPlanting->name }}
                                        @if($activity->plotPlanting->grapeVariety)
                                            - {{ $activity->plotPlanting->grapeVariety->name }}
                                        @endif
                                    </span>
                                @endif
                            </div>
                        </div>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        @php
                            $typeMap = [
                                'phytosanitary' => ['label' => 'Tratamiento',   'color' => 'red'],
                                'fertilization' => ['label' => 'Fertilización', 'color' => 'blue'],
                                'irrigation'    => ['label' => 'Riego',         'color' => 'cyan'],
                                'cultural'      => ['label' => 'Labor',         'color' => 'yellow'],
                                'observation'   => ['label' => 'Observación',   'color' => null],
                                'harvest'       => ['label' => 'Cosecha',       'color' => 'purple'],
                            ];
                            $typeInfo = $typeMap[$activity->activity_type] ?? ['label' => ucfirst($activity->activity_type), 'color' => null];
                        @endphp
                        <flux:badge :color="$typeInfo['color']" size="sm">{{ $typeInfo['label'] }}</flux:badge>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        @if($activity->phytosanitaryTreatment)
                            <div class="text-sm">
                                <span class="font-semibold text-zinc-900">{{ $activity->phytosanitaryTreatment->product->name }}</span>
                                @if($activity->phytosanitaryTreatment->area_treated)
                                    <span class="text-zinc-600"> - {{ number_format($activity->phytosanitaryTreatment->area_treated, 3) }} ha</span>
                                @endif
                                @if($activity->phytosanitaryTreatment->pest)
                                    <div class="text-xs text-zinc-500 mt-1">Objetivo: {{ $activity->phytosanitaryTreatment->pest->name }}</div>
                                @endif

                                {{-- Safety Interval Badge --}}
                                @php
                                    $product = $activity->phytosanitaryTreatment->product;
                                    $safetyDays = $product->withdrawal_period_days ?? 0;

                                    if ($safetyDays > 0) {
                                        $treatmentDate = \Carbon\Carbon::parse($activity->activity_date);
                                        $safeDate = $treatmentDate->copy()->addDays($safetyDays);
                                        $isPassed = \Carbon\Carbon::today() >= $safeDate;
                                        $daysRemaining = \Carbon\Carbon::today()->diffInDays($safeDate, false);
                                    }
                                @endphp

                                @if($safetyDays > 0)
                                    <div class="mt-2">
                                        @if($isPassed)
                                            <x-agro.status-badge
                                                :label="'Puede cosechar (desde ' . $safeDate->format('d/m/Y') . ')'"
                                                type="success"
                                            />
                                        @else
                                            <x-agro.status-badge
                                                :label="'Esperar ' . abs($daysRemaining) . ' día' . (abs($daysRemaining) != 1 ? 's' : '') . ' (hasta ' . $safeDate->format('d/m/Y') . ')'"
                                                type="warning"
                                            />
                                        @endif
                                    </div>
                                @elseif($safetyDays === 0)
                                    <div class="mt-2">
                                        <flux:badge size="sm">Sin plazo definido</flux:badge>
                                    </div>
                                @endif
                            </div>
                        @elseif($activity->fertilization)
                            <div class="text-sm">
                                <span class="font-semibold text-zinc-900">{{ $activity->fertilization->fertilizer_name ?: 'Fertilización' }}</span>
                                @if($activity->fertilization->quantity)
                                    <span class="text-zinc-600"> - {{ number_format($activity->fertilization->quantity, 2) }} kg</span>
                                @endif
                            </div>
                        @elseif($activity->irrigation)
                            <div class="text-sm">
                                <span class="font-semibold text-zinc-900">Riego</span>
                                @if($activity->irrigation->water_volume)
                                    <span class="text-zinc-600"> - {{ number_format($activity->irrigation->water_volume, 2) }} L</span>
                                @endif
                            </div>
                        @elseif($activity->culturalWork)
                            <div class="text-sm">
                                <span class="font-semibold text-zinc-900">{{ $activity->culturalWork->work_type ?: 'Labor cultural' }}</span>
                                @if($activity->culturalWork->hours_worked)
                                    <span class="text-zinc-600"> - {{ number_format($activity->culturalWork->hours_worked, 2) }} h</span>
                                @endif
                            </div>
                        @elseif($activity->observation)
                            <div class="text-sm">
                                <span class="font-semibold text-zinc-900">{{ $activity->observation->observation_type ?: 'Observación' }}</span>
                                @if($activity->observation->severity)
                                    <span class="text-zinc-600"> - {{ ucfirst($activity->observation->severity) }}</span>
                                @endif
                            </div>
                        @elseif($activity->harvest)
                            <div class="text-sm">
                                <span class="font-semibold text-purple-900">Vendimia</span>
                                @if($activity->harvest->plotPlanting && $activity->harvest->plotPlanting->grapeVariety)
                                    <span class="text-purple-700"> - {{ $activity->harvest->plotPlanting->grapeVariety->name }}</span>
                                @endif
                                <div class="flex gap-3 mt-1">
                                    <span class="text-xs font-semibold text-zinc-700">{{ number_format($activity->harvest->total_weight, 0) }} kg</span>
                                    @if($activity->harvest->yield_per_hectare)
                                        <span class="text-xs text-zinc-600">({{ number_format($activity->harvest->yield_per_hectare, 0) }} kg/ha)</span>
                                    @endif
                                    @if($activity->harvest->total_value)
                                        <span class="text-xs font-semibold text-green-700">{{ number_format($activity->harvest->total_value, 2) }}€</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        @if($activity->crew)
                            <span class="text-sm text-zinc-700">
                                Cuadrilla: {{ $activity->crew->name }}
                            </span>
                        @elseif($activity->crewMember && $activity->crewMember->viticulturist)
                            <span class="text-sm text-zinc-700">
                                Trabajador: {{ $activity->crewMember->viticulturist->name }}
                            </span>
                        @else
                            <span class="text-sm text-zinc-400">-</span>
                        @endif
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        @if($activity->notes)
                            <span class="text-sm text-zinc-600">{{ Str::limit($activity->notes, 50) }}</span>
                        @else
                            <span class="text-sm text-zinc-400">-</span>
                        @endif
                    </x-agro.table-cell>
                    <x-agro.table-cell align="right">
                        <div class="flex items-center gap-1 justify-end">
                            {{-- Botón de Historial de Auditoría --}}
                            <flux:button
                                wire:click="openAuditHistory({{ $activity->id }})"
                                variant="ghost"
                                size="sm"
                                icon="clock"
                                tooltip="Ver historial de cambios"
                            />

                            {{-- Badge de actividad bloqueada --}}
                            @if($activity->is_locked)
                                <x-activity-locked-badge :activity="$activity" />
                            @endif

                            @if($activity->harvest)
                                {{-- Para cosechas, mostrar botones de ver y editar con iconos --}}
                                <x-agro.action-button variant="view" href="{{ route('viticulturist.digital-notebook.harvest.show', $activity->harvest->id) }}" />
                                @can('update', $activity)
                                    @if(!$activity->is_locked)
                                        <x-agro.action-button variant="edit" href="{{ route('viticulturist.digital-notebook.harvest.edit', $activity->harvest->id) }}" />
                                    @endif
                                @endcan
                            @else
                                {{-- Edit buttons for all activity types (same pattern as harvest) --}}
                                @can('update', $activity)
                                    @if(!$activity->is_locked)
                                        @if($activity->activity_type === 'phytosanitary')
                                            <x-agro.action-button variant="edit" href="{{ route('viticulturist.digital-notebook.treatment.edit', $activity->id) }}" />
                                        @elseif($activity->activity_type === 'fertilization')
                                            <x-agro.action-button variant="edit" href="{{ route('viticulturist.digital-notebook.fertilization.edit', $activity->id) }}" />
                                        @elseif($activity->activity_type === 'irrigation')
                                            <x-agro.action-button variant="edit" href="{{ route('viticulturist.digital-notebook.irrigation.edit', $activity->id) }}" />
                                        @elseif($activity->activity_type === 'cultural')
                                            <x-agro.action-button variant="edit" href="{{ route('viticulturist.digital-notebook.cultural.edit', $activity->id) }}" />
                                        @elseif($activity->activity_type === 'observation')
                                            <x-agro.action-button variant="edit" href="{{ route('viticulturist.digital-notebook.observation.edit', $activity->id) }}" />
                                        @endif
                                    @endif
                                @endcan
                                @can('delete', $activity)
                                    @if(!$activity->is_locked)
                                        <x-agro.action-button
                                            variant="delete"
                                            wireClick="deleteActivity({{ $activity->id }})"
                                            wireConfirm="¿Estás seguro de eliminar esta actividad?"
                                        />
                                    @endif
                                @endcan
                            @endif
                        </div>
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach
            <x-slot name="pagination">
                {{ $activities->links() }}
            </x-slot>
        @endif
    </x-agro.data-table>

    {{-- Modal de Historial de Auditoría --}}
    @if($showAuditHistory && $selectedActivityId)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col">
                {{-- Header --}}
                <div class="bg-zinc-50 px-6 py-4 border-b border-zinc-200 flex items-center justify-between">
                    <flux:heading size="sm">Historial de Auditoría</flux:heading>
                    <flux:button
                        wire:click="closeAuditHistory"
                        variant="ghost"
                        size="sm"
                        icon="x-mark"
                    />
                </div>

                {{-- Content --}}
                <div class="px-6 py-4 overflow-y-auto flex-1">
                    @livewire('viticulturist.digital-notebook.activity-audit-history', ['activity' => \App\Models\AgriculturalActivity::find($selectedActivityId)], 'audit-'.$selectedActivityId)
                </div>

                {{-- Footer --}}
                <div class="bg-zinc-50 px-6 py-3 border-t border-zinc-200 flex justify-end">
                    <flux:button
                        wire:click="closeAuditHistory"
                        variant="outline"
                    >
                        Cerrar
                    </flux:button>
                </div>
            </div>
        </div>
    @endif


</div>
