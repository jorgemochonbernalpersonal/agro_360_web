<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="{{ __('Stream de actividad DO') }}"
        :description="__('Actividades del cuaderno de campo de los viticultores con acceso concedido.')"
    />

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-agro.stat-card
            :label="__('Viticultores con acceso')"
            :value="$totalWithAccess . ' / ' . $totalDo"
            icon="book-open"
            color="agro"
        />
        <x-agro.stat-card
            :label="__('Registros encontrados')"
            :value="$activities->total()"
            icon="clipboard-document-list"
            color="blue"
        />
        <div class="rounded-xl border {{ $activeAlerts > 0 ? 'border-red-200 bg-red-50' : 'border-zinc-100 bg-white' }} p-4">
            <p class="text-xs {{ $activeAlerts > 0 ? 'text-red-500' : 'text-zinc-400' }}">{{ __('Alertas activas') }}</p>
            <p class="text-2xl font-bold {{ $activeAlerts > 0 ? 'text-red-600' : 'text-zinc-300' }}">{{ $activeAlerts }}</p>
            <p class="text-xs {{ $activeAlerts > 0 ? 'text-red-400' : 'text-zinc-400' }}">{{ __('Umbral superado o seguimiento vencido') }}</p>
        </div>
    </div>

    @if($totalWithAccess === 0)
        <x-agro.empty-state
            icon="book-open"
            title="{{ __('Sin acceso al cuaderno') }}"
            :description="__('Ningún viticultor tiene acceso al cuaderno concedido.')"
        >
            <x-slot name="action">
                <a href="{{ route('supervisor.notebook.index') }}" wire:navigate>
                    <flux:button variant="primary" size="sm" icon="key">{{ __('Gestionar accesos') }}</flux:button>
                </a>
            </x-slot>
        </x-agro.empty-state>
    @else

        {{-- Filtros --}}
        <x-agro.filter-bar>
            <x-agro.filter-select wire:model.live="filterVit" :label="__('Viticultor')">
                <option value="">{{ __('Todos') }}</option>
                @foreach($viticulturists as $vit)
                    <option value="{{ $vit->id }}">{{ $vit->name }}</option>
                @endforeach
            </x-agro.filter-select>

            <x-agro.filter-select wire:model.live="filterType" :label="__('Tipo de actividad')">
                <option value="">{{ __('Todos los tipos') }}</option>
                @foreach($activityTypes as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </x-agro.filter-select>

            <div class="flex items-center gap-2">
                <input type="date" wire:model.live="filterFrom"
                    class="text-sm border border-zinc-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-agro-300" />
                <span class="text-xs text-zinc-400">—</span>
                <input type="date" wire:model.live="filterTo"
                    class="text-sm border border-zinc-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-agro-300" />
            </div>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" wire:model.live="onlyAlerts" class="rounded border-zinc-300 text-agro-600 focus:ring-agro-300" />
                <span class="text-sm text-zinc-600">{{ __('Solo alertas') }}</span>
            </label>

            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition px-2 py-1.5">{{ __('Limpiar') }}</button>
        </x-agro.filter-bar>

        {{-- Stream de actividades --}}
        <div class="space-y-1">
            @forelse($activities as $activity)
                @php
                    $isAlert = $activity->observation &&
                        ($activity->observation->threshold_exceeded ||
                         ($activity->observation->follow_up_date && $activity->observation->follow_up_date < now()));

                    $typeColors = [
                        'phytosanitary' => 'border-l-red-400 bg-red-50/30',
                        'fertilization' => 'border-l-yellow-400 bg-yellow-50/30',
                        'irrigation'    => 'border-l-blue-400 bg-blue-50/30',
                        'cultural'      => 'border-l-zinc-300 bg-zinc-50/30',
                        'observation'   => 'border-l-violet-400 bg-violet-50/30',
                        'harvest'       => 'border-l-emerald-400 bg-emerald-50/30',
                        'pruning'       => 'border-l-orange-400 bg-orange-50/30',
                        'post_harvest'  => 'border-l-teal-400 bg-teal-50/30',
                        'phenology'     => 'border-l-indigo-400 bg-indigo-50/30',
                    ];
                    $rowColor = $isAlert ? 'border-l-red-500 bg-red-50' : ($typeColors[$activity->activity_type] ?? 'border-l-zinc-200 bg-white');
                @endphp

                <div class="rounded-lg border border-zinc-100 border-l-4 {{ $rowColor }} px-4 py-3 flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="shrink-0 mt-0.5">
                            @php
                                $typeIcons = [
                                    'phytosanitary' => 'beaker',
                                    'fertilization' => 'sparkles',
                                    'irrigation'    => 'cloud',
                                    'cultural'      => 'wrench-screwdriver',
                                    'observation'   => 'eye',
                                    'harvest'       => 'scissors',
                                    'pruning'       => 'scissors',
                                    'post_harvest'  => 'shield-check',
                                    'phenology'     => 'magnifying-glass',
                                ];
                            @endphp
                            <flux:icon icon="{{ $typeIcons[$activity->activity_type] ?? 'document' }}" class="size-4 text-zinc-400" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-medium text-zinc-800">
                                    {{ $activityTypes[$activity->activity_type] ?? $activity->activity_type }}
                                </span>
                                @if($isAlert)
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs bg-red-100 text-red-700">
                                        <flux:icon icon="exclamation-triangle" class="size-3" />
                                        @if($activity->observation->threshold_exceeded)
                                            Umbral superado
                                        @else
                                            Seguimiento vencido
                                        @endif
                                    </span>
                                @endif
                            </div>
                            <div class="text-xs text-zinc-400 mt-0.5 flex items-center gap-2 flex-wrap">
                                <span>{{ $activity->viticulturist?->name ?? '—' }}</span>
                                @if($activity->plot)
                                    <span>· {{ $activity->plot->name }}</span>
                                @endif
                                @if($activity->notes)
                                    <span>· {{ Str::limit($activity->notes, 80) }}</span>
                                @endif
                            </div>
                            @if($isAlert && $activity->observation?->follow_up_date)
                                <div class="text-xs text-red-500 mt-0.5">
                                    Seguimiento: {{ $activity->observation->follow_up_date->format('d/m/Y') }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="text-xs text-zinc-400 shrink-0 mt-0.5">
                        {{ $activity->activity_date->format('d/m/Y') }}
                    </div>
                </div>
            @empty
                <x-agro.empty-state
                    icon="clipboard-document-list"
                    title="{{ __('Sin actividades') }}"
                    :description="__('No hay actividades registradas con los filtros actuales.')"
                />
            @endforelse
        </div>

        <x-agro.pagination :paginator="$activities" />

    @endif

</div>
