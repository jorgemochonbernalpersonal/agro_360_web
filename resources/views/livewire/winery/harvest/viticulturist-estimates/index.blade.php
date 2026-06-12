<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="{{ __('Aforos de Viticultores') }}"
        :description="__('Estimaciones de rendimiento declaradas por tus viticultores vinculados.')"
    />

    @php
        $filterCount = (int) !empty($search) + (int) !empty($viticulturistFilter) + (int) !empty($vintageFilter) + (int) !empty($statusFilter) + (int) !empty($roundFilter);
    @endphp

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">

        {{-- Search --}}
        <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar viticultor, variedad, parcela...')" />

        {{-- Filtros --}}
        <x-agro.filter-button modal="vitic-estimates-filters" :count="$filterCount" />

        {{-- Separador --}}
        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        {{-- Navegación --}}
        <flux:button variant="ghost" icon="chart-bar" href="{{ roleRoute('harvest-summary.index') }}" wire:navigate size="sm">
            Cuadro de mando
        </flux:button>
    </div>

    {{-- Chips de filtros activos --}}
    @if($filterCount > 0)
        <div class="flex flex-wrap items-center gap-2">
            @if($search)
                <x-agro.filter-chip icon="magnifying-glass" :label="$search" wireRemove="$set('search', '')" />
            @endif
            @if($viticulturistFilter)
                @php $viticLabel = $linkedViticulturists->firstWhere('id', $viticulturistFilter)?->name ?? $viticulturistFilter; @endphp
                <x-agro.filter-chip icon="user" :label="$viticLabel" wireRemove="$set('viticulturistFilter', '')" />
            @endif
            @if($vintageFilter)
                <x-agro.filter-chip icon="calendar" :label="'Añada: ' . $vintageFilter" wireRemove="$set('vintageFilter', '')" />
            @endif
            @if($roundFilter)
                @php $roundLabel = $rounds[$roundFilter] ?? $roundFilter; @endphp
                <x-agro.filter-chip icon="arrow-path" :label="$roundLabel" wireRemove="$set('roundFilter', '')" />
            @endif
            @if($statusFilter)
                @php $statusLabel = ['confirmed' => 'Confirmado', 'draft' => 'Borrador', 'archived' => 'Archivado'][$statusFilter] ?? $statusFilter; @endphp
                <x-agro.filter-chip icon="funnel" :label="$statusLabel" wireRemove="$set('statusFilter', '')" />
            @endif
            <button wire:click="$set('search', ''); $set('viticulturistFilter', ''); $set('vintageFilter', ''); $set('roundFilter', ''); $set('statusFilter', '')"
                class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">{{ __('Limpiar todo') }}</button>
        </div>
    @endif

    {{-- Skeleton --}}
    <x-agro.loading-grid target="search, viticulturistFilter, vintageFilter, roundFilter, statusFilter, gotoPage, previousPage, nextPage" :count="6" />

    {{-- Grid de cards --}}
    <div wire:loading.remove wire:target="search, viticulturistFilter, vintageFilter, roundFilter, statusFilter, gotoPage, previousPage, nextPage">
        @if($estimates->isEmpty())
            <x-agro.empty-state
                icon="calculator"
                title="{{ __('Sin aforos para estos filtros') }}"
                :description="__('Tus viticultores aún no han registrado estimaciones de rendimiento confirmadas.')"
            />
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($estimates as $est)
                    @php
                        $delay      = min($loop->index * 50, 300);
                        $vitName    = $est->plotPlanting?->plot?->viticulturist?->name ?? '—';
                        $initials   = strtoupper(substr($vitName, 0, 1));
                        $variety    = $est->plotPlanting?->grapeVariety?->name ?? $est->plotPlanting?->name ?? '—';
                        $plotName   = $est->plotPlanting?->plot?->name ?? '—';
                        $area       = $est->plotPlanting?->area_planted;

                        $statusColor = match($est->status) {
                            'confirmed' => 'agro',
                            'draft'     => 'amber',
                            default     => 'zinc',
                        };
                        $statusLabel = match($est->status) {
                            'confirmed' => 'Confirmado',
                            'draft'     => 'Borrador',
                            default     => 'Archivado',
                        };

                        $sanColors = [
                            'excellent' => 'agro', 'good' => 'agro',
                            'botrytis_light' => 'amber', 'oidium_light' => 'amber',
                            'botrytis_moderate' => 'red', 'oidium_moderate' => 'red',
                            'mixed' => 'amber', 'poor' => 'red',
                        ];
                        $sanColor = $sanColors[$est->health_status] ?? 'zinc';
                        $sanLabel = \App\Models\EstimatedYield::HEALTH_STATUSES[$est->health_status] ?? null;
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="est-card-{{ $est->id }}"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center shrink-0">
                                    <span class="text-sm font-bold text-violet-700">{{ $initials }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">{{ $vitName }}</h3>
                                    <p class="text-xs text-zinc-500 truncate">{{ $variety }}</p>
                                </div>
                                <x-agro.status-badge :color="$statusColor" :label="$statusLabel" class="shrink-0" />
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">

                            {{-- Parcela + área --}}
                            <div class="flex items-center gap-2 text-sm text-zinc-600">
                                <flux:icon icon="map" class="size-4 text-zinc-400 shrink-0" />
                                <span class="truncate">{{ $plotName }}</span>
                                @if($area)
                                    <span class="text-zinc-400 shrink-0">· {{ number_format($area, 2) }} ha</span>
                                @endif
                            </div>

                            {{-- Mini stats: Total estimado + Kg/ha --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-violet-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-violet-400 uppercase tracking-widest mb-1">{{ __('Total estimado') }}</p>
                                    @if($est->estimated_total_yield)
                                        <p class="text-xl font-bold text-violet-700 leading-none">
                                            {{ number_format($est->estimated_total_yield, 0) }}
                                            <span class="text-xs font-medium text-violet-400 ml-0.5">kg</span>
                                        </p>
                                    @else
                                        <p class="text-base font-medium text-zinc-300 italic leading-none">—</p>
                                    @endif
                                </div>
                                <div class="bg-zinc-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-1">{{ __('Kg / ha') }}</p>
                                    @if($est->estimated_yield_per_hectare)
                                        <p class="text-xl font-bold text-zinc-700 leading-none">
                                            {{ number_format($est->estimated_yield_per_hectare, 0) }}
                                            <span class="text-xs font-medium text-zinc-400 ml-0.5">{{ __('kg/ha') }}</span>
                                        </p>
                                    @else
                                        <p class="text-base font-medium text-zinc-300 italic leading-none">—</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Detalles --}}
                            <div class="space-y-1.5 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">{{ __('Añada · Ronda') }}</span>
                                    <span class="text-zinc-700 font-medium">
                                        {{ $est->vintage ?? $est->campaign?->year ?? '—' }}
                                        · <span class="text-violet-700">{{ $est->round_label }}</span>
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">{{ __('Método') }}</span>
                                    <span class="text-zinc-600 capitalize">{{ match($est->estimation_method) {
                                        'visual'     => 'Visual',
                                        'sampling'   => 'Muestreo',
                                        'historical' => 'Histórico',
                                        'satellite'  => 'Satélite',
                                        default      => $est->estimation_method ?? '—',
                                    } }}</span>
                                </div>
                                @if($est->estimation_date)
                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-400">{{ __('Fecha') }}</span>
                                        <span class="text-zinc-600">{{ $est->estimation_date->format('d/m/Y') }}</span>
                                    </div>
                                @endif
                                @if($est->potential_alcohol)
                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-400">{{ __('Alcohol potencial') }}</span>
                                        <span class="text-zinc-700 font-medium">{{ number_format($est->potential_alcohol, 1) }}°</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Sanidad --}}
                            @if($est->health_status || $est->health_percentage !== null)
                                <div class="border-t border-zinc-100 pt-3 flex items-center justify-between">
                                    <span class="text-xs text-zinc-400">{{ __('Sanidad') }}</span>
                                    @if($est->health_status && $sanLabel)
                                        <x-agro.status-badge :color="$sanColor" :label="$sanLabel" />
                                    @elseif($est->health_percentage !== null)
                                        <span class="text-sm text-zinc-600">{{ number_format($est->health_percentage, 0) }}%</span>
                                    @endif
                                </div>
                            @endif

                            {{-- Entrega múltiple --}}
                            @if($est->other_wineries)
                                <div class="flex items-center gap-1.5 text-xs text-amber-600 bg-amber-50 rounded-lg px-3 py-1.5">
                                    <flux:icon icon="exclamation-triangle" class="size-3 shrink-0" />
                                    Entrega a múltiples bodegas
                                </div>
                            @endif

                        </div>
                    </x-agro.card>
                @endforeach
            </div>

            <x-agro.pagination :paginator="$estimates" />
        @endif
    </div>

    {{-- Modal Filtros --}}
    <x-agro.filter-modal
        name="vitic-estimates-filters"
        :hasActiveFilters="$filterCount > 0"
        clearAction="$set('search', ''); $set('viticulturistFilter', ''); $set('vintageFilter', ''); $set('roundFilter', ''); $set('statusFilter', '')"
    >
        <div>
            <x-agro.field-label>{{ __('Viticultor') }}</x-agro.field-label>
            <flux:select wire:model.live="viticulturistFilter">
                <option value="">{{ __('Todos') }}</option>
                @foreach($linkedViticulturists as $v)
                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                @endforeach
            </flux:select>
        </div>
        <div>
            <x-agro.field-label>{{ __('Añada') }}</x-agro.field-label>
            <flux:select wire:model.live="vintageFilter">
                <option value="">{{ __('Todas') }}</option>
                @foreach($vintages as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </flux:select>
        </div>
        <div>
            <x-agro.field-label>{{ __('Ronda') }}</x-agro.field-label>
            <flux:select wire:model.live="roundFilter">
                <option value="">{{ __('Todas las rondas') }}</option>
                @foreach($rounds as $num => $label)
                    <option value="{{ $num }}">{{ $label }}</option>
                @endforeach
            </flux:select>
        </div>
        <div>
            <x-agro.field-label>{{ __('Estado') }}</x-agro.field-label>
            <flux:select wire:model.live="statusFilter">
                <option value="">{{ __('Todos') }}</option>
                <option value="confirmed">{{ __('Confirmado') }}</option>
                <option value="draft">{{ __('Borrador') }}</option>
                <option value="archived">{{ __('Archivado') }}</option>
            </flux:select>
        </div>
    </x-agro.filter-modal>

</div>
