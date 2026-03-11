<div class="space-y-6 animate-fade-in">
    {{-- Flash messages --}}
    @if(session('message'))
        <flux:callout variant="success" icon="check-circle">
            <flux:callout.text>{{ session('message') }}</flux:callout.text>
        </flux:callout>
    @endif
    @if(session('error'))
        <flux:callout variant="danger" icon="exclamation-circle">
            <flux:callout.text>{{ session('error') }}</flux:callout.text>
        </flux:callout>
    @endif

    {{-- Header --}}
    <x-agro.page-header
        title="Calendario"
        :description="$currentCampaign ? 'Campaña ' . $currentCampaign->name . ' · ' . $currentCampaign->year : 'Actividades, alertas y hitos de tu explotación'"
    >
        <x-slot:actions>
            @if($currentCampaign)
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-zinc-700">Campaña:</span>
                    <flux:select wire:model.live="selectedCampaign" class="min-w-[180px]">
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

    {{-- Estadísticas del Mes --}}
    <x-agro.card>
        <div class="grid grid-cols-3 md:grid-cols-6 gap-4 text-center">
            <div>
                <div class="text-2xl font-bold text-zinc-900">{{ $stats['total'] }}</div>
                <div class="text-xs text-zinc-500">Total</div>
            </div>
            <div>
                <div class="text-2xl font-bold text-red-600">{{ $stats['phytosanitary'] }}</div>
                <div class="text-xs text-zinc-500">Tratamientos</div>
            </div>
            <div>
                <div class="text-2xl font-bold text-blue-600">{{ $stats['fertilization'] }}</div>
                <div class="text-xs text-zinc-500">Fertilizaciones</div>
            </div>
            <div>
                <div class="text-2xl font-bold text-cyan-600">{{ $stats['irrigation'] }}</div>
                <div class="text-xs text-zinc-500">Riegos</div>
            </div>
            <div>
                <div class="text-2xl font-bold text-yellow-600">{{ $stats['cultural'] }}</div>
                <div class="text-xs text-zinc-500">Labores</div>
            </div>
            <div>
                <div class="text-2xl font-bold text-zinc-600">{{ $stats['observation'] }}</div>
                <div class="text-xs text-zinc-500">Observaciones</div>
            </div>
        </div>
    </x-agro.card>

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <x-agro.filter-select wire:model.live="activityType">
            <option value="">Todas las actividades</option>
            <option value="phytosanitary">Tratamientos Fitosanitarios</option>
            <option value="fertilization">Fertilizaciones</option>
            <option value="irrigation">Riegos</option>
            <option value="cultural">Labores Culturales</option>
            <option value="observation">Observaciones</option>
        </x-agro.filter-select>
    </x-agro.filter-bar>

    {{-- Calendario --}}
    <x-agro.card>
        {{-- Navegación --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <flux:button wire:click="previousMonth" variant="ghost" icon="chevron-left" title="Mes anterior" />
                <h2 class="text-2xl font-bold text-zinc-900">{{ $monthName }} {{ $currentYear }}</h2>
                <flux:button wire:click="nextMonth" variant="ghost" icon="chevron-right" title="Mes siguiente" />
            </div>
            <flux:button wire:click="goToToday" variant="primary">Hoy</flux:button>
        </div>

        {{-- Días de la semana --}}
        <div class="grid grid-cols-7 gap-2 mb-2">
            @foreach(['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $day)
                <div class="text-center text-sm font-bold text-zinc-700 py-2">{{ $day }}</div>
            @endforeach
        </div>

        {{-- Días --}}
        <div class="grid grid-cols-7 gap-2">
            @foreach($calendarDays as $day)
                <div
                    class="min-h-[110px] border-2 rounded-lg p-2 transition-all cursor-pointer hover:shadow-md
                        {{ $day['isCurrentMonth'] ? 'bg-white border-zinc-200' : 'bg-zinc-50 border-zinc-100 opacity-60' }}
                        {{ $day['isToday'] ? 'ring-2 ring-agro-500 border-agro-500' : '' }}
                        {{ $day['hasAlerts'] ? 'border-l-4 border-l-orange-400' : '' }}"
                    wire:click="selectDate('{{ $day['dateKey'] }}')"
                >
                    {{-- Número del día + badges --}}
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-semibold {{ $day['isToday'] ? 'text-agro-700' : ($day['isCurrentMonth'] ? 'text-zinc-900' : 'text-zinc-400') }}">
                            {{ $day['day'] }}
                        </span>
                        <div class="flex items-center gap-0.5">
                            @if($day['activityCount'] > 0)
                                <span class="text-xs font-bold text-zinc-600 bg-zinc-200 px-1.5 py-0.5 rounded-full">
                                    {{ $day['activityCount'] }}
                                </span>
                            @endif
                            @if($day['eventCount'] > 0)
                                <span class="text-xs font-bold text-orange-700 bg-orange-100 px-1.5 py-0.5 rounded-full">
                                    {{ $day['eventCount'] }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Actividades (hasta 2) --}}
                    <div class="space-y-0.5">
                        @foreach($day['activities']->take(2) as $activity)
                            <div class="text-xs px-1.5 py-0.5 rounded border {{ $this->getActivityTypeColor($activity->activity_type) }} truncate">
                                {{ $this->getActivityTypeLabel($activity->activity_type) }}
                            </div>
                        @endforeach

                        {{-- Eventos/alertas (hasta 2) --}}
                        @foreach($day['events']->take(2) as $event)
                            <div class="text-xs px-1.5 py-0.5 rounded border {{ $this->getEventColor($event['type'], $event['urgency']) }} truncate">
                                {{ $event['label'] }}
                            </div>
                        @endforeach

                        {{-- "+N más" si hay overflow --}}
                        @php $total = $day['activityCount'] + $day['eventCount']; $shown = min(2, $day['activityCount']) + min(2, $day['eventCount']); @endphp
                        @if($total > $shown)
                            <div class="text-xs text-zinc-500 font-semibold text-center">+{{ $total - $shown }} más</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </x-agro.card>

    {{-- Leyenda --}}
    <x-agro.card>
        <h3 class="text-sm font-semibold text-zinc-700 mb-4">Leyenda</h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            {{-- Actividades --}}
            <div class="col-span-2 md:col-span-5">
                <p class="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">Actividades de campo</p>
            </div>
            @foreach([
                ['bg-red-100 border-red-300',    'Tratamientos'],
                ['bg-blue-100 border-blue-300',   'Fertilizaciones'],
                ['bg-cyan-100 border-cyan-300',   'Riegos'],
                ['bg-yellow-100 border-yellow-300','Labores'],
                ['bg-zinc-100 border-zinc-300',   'Observaciones'],
            ] as [$cls, $lbl])
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded border {{ $cls }}"></div>
                    <span class="text-xs text-zinc-700">{{ $lbl }}</span>
                </div>
            @endforeach

            {{-- Alertas y eventos --}}
            <div class="col-span-2 md:col-span-5 mt-3">
                <p class="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">Alertas y eventos</p>
            </div>
            @foreach([
                ['bg-red-100 border-red-400',       'Vencido (ROPO/ITB/Autor.)'],
                ['bg-orange-100 border-orange-400', 'Vence pronto'],
                ['bg-purple-100 border-purple-300', 'Análisis residuos'],
                ['bg-slate-100 border-slate-300',   'Gestión residuos'],
                ['bg-amber-100 border-amber-300',   'Energía / PAC'],
                ['bg-teal-100 border-teal-300',     'Pago PAC'],
                ['bg-green-100 border-green-400',   'Hito campaña'],
            ] as [$cls, $lbl])
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded border {{ $cls }}"></div>
                    <span class="text-xs text-zinc-700">{{ $lbl }}</span>
                </div>
            @endforeach
        </div>
    </x-agro.card>

    {{-- Modal: Detalle del Día --}}
    @if($showActivityModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" wire:click="closeModal">
            <div class="bg-white rounded-xl shadow-xl p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto" wire:click.stop>
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-xl font-bold text-zinc-900">
                        {{ $this->getFormattedSelectedDate() }}
                    </h3>
                    <flux:button wire:click="closeModal" variant="ghost" icon="x-mark" />
                </div>

                {{-- Actividades --}}
                @if($selectedActivity && $selectedActivity->count() > 0)
                    <p class="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-3">Actividades de campo</p>
                    <div class="space-y-3 mb-5">
                        @foreach($selectedActivity as $activity)
                            <div class="border-2 rounded-lg p-4 {{ $this->getActivityTypeColor($activity->activity_type) }}">
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <span class="font-bold">{{ $this->getActivityTypeLabel($activity->activity_type) }}</span>
                                        <span class="text-sm text-zinc-600 ml-2">{{ $activity->plot->name }}</span>
                                    </div>
                                </div>

                                @if($activity->phytosanitaryTreatment)
                                    <div class="text-sm mt-1 space-y-0.5">
                                        <p><strong>Producto:</strong> {{ $activity->phytosanitaryTreatment->product->name }}</p>
                                        @if($activity->phytosanitaryTreatment->area_treated)
                                            <p><strong>Área:</strong> {{ number_format($activity->phytosanitaryTreatment->area_treated, 3) }} ha</p>
                                        @endif
                                        @if($activity->phytosanitaryTreatment->pest)
                                            <p><strong>Objetivo:</strong> {{ $activity->phytosanitaryTreatment->pest->name }}</p>
                                        @endif
                                    </div>
                                @elseif($activity->fertilization)
                                    <div class="text-sm mt-1 space-y-0.5">
                                        <p><strong>Fertilizante:</strong> {{ $activity->fertilization->fertilizer_name ?: 'N/A' }}</p>
                                        @if($activity->fertilization->quantity)
                                            <p><strong>Cantidad:</strong> {{ number_format($activity->fertilization->quantity, 2) }} kg</p>
                                        @endif
                                    </div>
                                @elseif($activity->irrigation)
                                    <div class="text-sm mt-1">
                                        @if($activity->irrigation->water_volume)
                                            <p><strong>Volumen:</strong> {{ number_format($activity->irrigation->water_volume, 2) }} L</p>
                                        @endif
                                    </div>
                                @elseif($activity->culturalWork)
                                    <div class="text-sm mt-1 space-y-0.5">
                                        <p><strong>Labor:</strong> {{ $activity->culturalWork->work_type ?: 'N/A' }}</p>
                                        @if($activity->culturalWork->hours_worked)
                                            <p><strong>Horas:</strong> {{ number_format($activity->culturalWork->hours_worked, 2) }} h</p>
                                        @endif
                                    </div>
                                @elseif($activity->observation)
                                    <div class="text-sm mt-1 space-y-0.5">
                                        <p><strong>Tipo:</strong> {{ $activity->observation->observation_type ?: 'N/A' }}</p>
                                        @if($activity->observation->severity)
                                            <p><strong>Severidad:</strong> {{ ucfirst($activity->observation->severity) }}</p>
                                        @endif
                                    </div>
                                @endif

                                @if($activity->crew)
                                    <p class="text-sm mt-1"><strong>Cuadrilla:</strong> {{ $activity->crew->name }}</p>
                                @endif
                                @if($activity->machinery)
                                    <p class="text-sm mt-1"><strong>Maquinaria:</strong> {{ $activity->machinery->name }}</p>
                                @endif
                                @if($activity->notes)
                                    <p class="text-sm mt-1"><strong>Notas:</strong> {{ $activity->notes }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Eventos / alertas --}}
                @if($selectedEvents && $selectedEvents->count() > 0)
                    <p class="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-3">Alertas y eventos</p>
                    <div class="space-y-2">
                        @foreach($selectedEvents as $event)
                            <div class="border-2 rounded-lg p-3 flex items-start gap-3 {{ $this->getEventColor($event['type'], $event['urgency']) }}">
                                <div class="flex-1">
                                    <p class="font-semibold text-sm">{{ $event['label'] }}</p>
                                    @if($event['description'])
                                        <p class="text-xs mt-0.5 opacity-80">{{ $event['description'] }}</p>
                                    @endif
                                </div>
                                @if(in_array($event['type'], ['alert_ropo', 'alert_itb', 'alert_authorization']) && $event['urgency'] === 'danger')
                                    <span class="text-xs font-bold bg-red-600 text-white px-2 py-0.5 rounded-full">Vencido</span>
                                @elseif(in_array($event['type'], ['alert_ropo', 'alert_itb', 'alert_authorization']) && $event['urgency'] === 'warning')
                                    <span class="text-xs font-bold bg-orange-500 text-white px-2 py-0.5 rounded-full">Próximo</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Empty state si no hay nada --}}
                @if((!$selectedActivity || $selectedActivity->count() === 0) && (!$selectedEvents || $selectedEvents->count() === 0))
                    <x-agro.empty-state message="No hay actividades ni eventos registrados para esta fecha" />
                @endif
            </div>
        </div>
    @endif
</div>
