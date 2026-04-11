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
        <div class="grid grid-cols-3 sm:grid-cols-5 lg:grid-cols-9 gap-3">
            {{-- Total --}}
            <div class="col-span-3 sm:col-span-5 lg:col-span-1 flex lg:flex-col items-center lg:justify-center gap-3 lg:gap-1 bg-zinc-100 rounded-xl px-4 py-3">
                <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-zinc-200">
                    <flux:icon icon="calendar-days" class="size-5 text-zinc-600" />
                </div>
                <div class="lg:text-center">
                    <div class="text-2xl font-bold text-zinc-900 leading-none">{{ $stats['total'] }}</div>
                    <div class="text-xs text-zinc-500 mt-0.5">Total</div>
                </div>
            </div>

            @foreach([
                ['phytosanitary', 'Tratamientos',    'text-red-600',    'bg-red-50',     'bg-red-100',    'beaker'],
                ['fertilization', 'Fertilizaciones', 'text-blue-600',   'bg-blue-50',    'bg-blue-100',   'sparkles'],
                ['irrigation',    'Riegos',           'text-cyan-600',   'bg-cyan-50',    'bg-cyan-100',   'cloud'],
                ['cultural',      'Labores',          'text-yellow-700', 'bg-yellow-50',  'bg-yellow-100', 'wrench-screwdriver'],
                ['observation',   'Observaciones',    'text-zinc-600',   'bg-zinc-50',    'bg-zinc-200',   'eye'],
                ['pruning',       'Podas',            'text-lime-700',   'bg-lime-50',    'bg-lime-100',   'scissors'],
                ['harvest',       'Vendimias',        'text-amber-700',  'bg-amber-50',   'bg-amber-100',  'sun'],
                ['post_harvest',  'Post-vendimia',    'text-purple-600', 'bg-purple-50',  'bg-purple-100', 'check-badge'],
            ] as [$key, $label, $textColor, $bgCard, $bgIcon, $icon])
                <div class="flex lg:flex-col items-center lg:justify-center gap-3 lg:gap-1 {{ $bgCard }} rounded-xl px-3 py-3">
                    <div class="flex items-center justify-center w-9 h-9 rounded-lg {{ $bgIcon }} flex-shrink-0">
                        <flux:icon icon="{{ $icon }}" class="size-5 {{ $textColor }}" />
                    </div>
                    <div class="lg:text-center">
                        <div class="text-2xl font-bold {{ $textColor }} leading-none">{{ $stats[$key] }}</div>
                        <div class="text-xs text-zinc-500 mt-0.5 leading-tight">{{ $label }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-agro.card>

    {{-- Filtros rápidos --}}
    <div class="flex flex-wrap gap-2">
        <button
            wire:click="$set('activityType', '')"
            class="px-3 py-1.5 rounded-full text-sm font-medium border transition-all
                {{ $activityType === '' ? 'bg-zinc-800 text-white border-zinc-800' : 'bg-white text-zinc-600 border-zinc-300 hover:border-zinc-400' }}"
        >
            Todas
        </button>
        @foreach([
            ['phytosanitary', 'Tratamientos',    'border-red-300 text-red-700 bg-red-50',        'bg-red-600 text-white border-red-600'],
            ['fertilization', 'Fertilizaciones', 'border-blue-300 text-blue-700 bg-blue-50',     'bg-blue-600 text-white border-blue-600'],
            ['irrigation',    'Riegos',           'border-cyan-300 text-cyan-700 bg-cyan-50',     'bg-cyan-600 text-white border-cyan-600'],
            ['cultural',      'Labores',          'border-yellow-300 text-yellow-700 bg-yellow-50','bg-yellow-500 text-white border-yellow-500'],
            ['observation',   'Observaciones',    'border-zinc-300 text-zinc-600 bg-zinc-50',     'bg-zinc-600 text-white border-zinc-600'],
            ['pruning',       'Podas',            'border-lime-300 text-lime-700 bg-lime-50',     'bg-lime-600 text-white border-lime-600'],
            ['harvest',       'Vendimias',        'border-amber-300 text-amber-700 bg-amber-50',  'bg-amber-500 text-white border-amber-500'],
            ['post_harvest',  'Post-vendimia',    'border-purple-300 text-purple-700 bg-purple-50','bg-purple-600 text-white border-purple-600'],
        ] as [$value, $label, $inactiveClass, $activeClass])
            <button
                wire:click="$set('activityType', '{{ $value }}')"
                class="px-3 py-1.5 rounded-full text-sm font-medium border transition-all
                    {{ $activityType === $value ? $activeClass : $inactiveClass . ' hover:opacity-80' }}"
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

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

        {{-- Leyenda inline --}}
        <div class="flex flex-wrap gap-x-4 gap-y-1.5 mb-5 pb-4 border-b border-zinc-100">
            @foreach([
                ['bg-red-100 border-red-300',      'text-red-700',    'Tratamientos'],
                ['bg-blue-100 border-blue-300',    'text-blue-700',   'Fertilizaciones'],
                ['bg-cyan-100 border-cyan-300',    'text-cyan-700',   'Riegos'],
                ['bg-yellow-100 border-yellow-300','text-yellow-700', 'Labores'],
                ['bg-zinc-100 border-zinc-300',    'text-zinc-600',   'Observaciones'],
                ['bg-lime-100 border-lime-300',    'text-lime-700',   'Podas'],
                ['bg-amber-100 border-amber-300',  'text-amber-700',  'Vendimias'],
                ['bg-purple-100 border-purple-300','text-purple-700', 'Post-vendimia'],
            ] as [$bg, $text, $lbl])
                <div class="flex items-center gap-1.5">
                    <div class="w-3 h-3 rounded border {{ $bg }} flex-shrink-0"></div>
                    <span class="text-xs text-zinc-500">{{ $lbl }}</span>
                </div>
            @endforeach
            <div class="flex items-center gap-1.5 ml-2 pl-2 border-l border-zinc-200">
                <div class="w-3 h-3 rounded border border-orange-400 bg-orange-50 flex-shrink-0"></div>
                <span class="text-xs text-zinc-500">Alertas</span>
            </div>
            <div class="flex items-center gap-1.5">
                <div class="w-3 h-3 rounded border border-green-400 bg-green-50 flex-shrink-0"></div>
                <span class="text-xs text-zinc-500">Hitos campaña</span>
            </div>
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
                            <div class="text-xs px-1.5 py-0.5 rounded border {{ $this->getActivityTypeColor($activity->activity_type) }} leading-tight">
                                <div class="font-semibold truncate">{{ $this->getActivityTypeLabel($activity->activity_type) }}</div>
                                @if($activity->plot)
                                    <div class="truncate opacity-70">{{ $activity->plot->name }}</div>
                                @endif
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
