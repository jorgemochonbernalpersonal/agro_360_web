<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Aforos de Viticultores"
        description="Estimaciones de rendimiento declaradas por tus viticultores vinculados."
    />

    @php
        $filterCount = (int) !empty($viticulturistFilter) + (int) !empty($vintageFilter) + (int) !empty($statusFilter) + (int) !empty($roundFilter);
    @endphp

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">

        {{-- Filtros --}}
        <button x-on:click="$dispatch('open-modal', 'vitic-estimates-filters')"
            class="relative inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-50 shadow-sm transition-colors">
            <flux:icon icon="adjustments-horizontal" class="size-4 text-zinc-500" />
            Filtros
            @if($filterCount > 0)
                <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-agro-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">
                    {{ $filterCount }}
                </span>
            @endif
        </button>

        {{-- Separador --}}
        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        {{-- Navegación --}}
        <flux:button variant="ghost" icon="chart-bar" href="{{ route('winery.harvest-summary.index') }}" wire:navigate size="sm">
            Cuadro de mando
        </flux:button>
    </div>

    {{-- Chips de filtros activos --}}
    @if($filterCount > 0)
        <div class="flex flex-wrap items-center gap-2">
            @if($viticulturistFilter)
                @php $viticLabel = $linkedViticulturists->firstWhere('id', $viticulturistFilter)?->name ?? $viticulturistFilter; @endphp
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="user" class="size-3" />
                    {{ $viticLabel }}
                    <button wire:click="$set('viticulturistFilter', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if($vintageFilter)
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="calendar" class="size-3" />
                    Añada: {{ $vintageFilter }}
                    <button wire:click="$set('vintageFilter', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if($roundFilter)
                @php $roundLabel = $rounds[$roundFilter] ?? $roundFilter; @endphp
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="arrow-path" class="size-3" />
                    {{ $roundLabel }}
                    <button wire:click="$set('roundFilter', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if($statusFilter)
                @php $statusLabel = ['confirmed' => 'Confirmado', 'draft' => 'Borrador', 'archived' => 'Archivado'][$statusFilter] ?? $statusFilter; @endphp
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="funnel" class="size-3" />
                    {{ $statusLabel }}
                    <button wire:click="$set('statusFilter', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            <button wire:click="$set('viticulturistFilter', ''); $set('vintageFilter', ''); $set('roundFilter', ''); $set('statusFilter', '')"
                class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">
                Limpiar todo
            </button>
        </div>
    @endif

    {{-- Skeleton --}}
    <div wire:loading wire:target="viticulturistFilter, vintageFilter, roundFilter, statusFilter, gotoPage, previousPage, nextPage">
        <x-agro.card>
            <div class="space-y-3">
                @for($i = 0; $i < 8; $i++)
                    <div class="h-10 bg-zinc-100 rounded-lg animate-pulse"></div>
                @endfor
            </div>
        </x-agro.card>
    </div>

    {{-- Contenido --}}
    <div wire:loading.remove wire:target="viticulturistFilter, vintageFilter, roundFilter, statusFilter, gotoPage, previousPage, nextPage">
        @if($estimates->isEmpty())
            <x-agro.empty-state
                icon="calculator"
                title="Sin aforos para estos filtros"
                description="Tus viticultores aún no han registrado estimaciones de rendimiento confirmadas."
            />
        @else
            <x-agro.card>
                <x-agro.data-table :headers="['Viticultor', 'Variedad / Parcela', 'Añada', 'Ronda', 'Método', 'Kg/ha estimados', 'Total estimado', 'Alcohol', 'Sanidad', 'Estado']">
                    @foreach($estimates as $est)
                        <x-agro.table-row wire:key="est-{{ $est->id }}">

                            <x-agro.table-cell>
                                <span class="font-medium text-zinc-900">
                                    {{ $est->plotPlanting?->plot?->viticulturist?->name ?? '—' }}
                                </span>
                            </x-agro.table-cell>

                            <x-agro.table-cell>
                                <div class="font-medium text-zinc-900">
                                    {{ $est->plotPlanting?->grapeVariety?->name ?? $est->plotPlanting?->name ?? '—' }}
                                </div>
                                <div class="text-xs text-zinc-400">
                                    {{ $est->plotPlanting?->plot?->name ?? '—' }}
                                    @if($est->plotPlanting?->area_planted)
                                        · {{ number_format($est->plotPlanting->area_planted, 2) }} ha
                                    @endif
                                </div>
                            </x-agro.table-cell>

                            <x-agro.table-cell>
                                <span class="text-zinc-700">{{ $est->vintage ?? $est->campaign?->year ?? '—' }}</span>
                            </x-agro.table-cell>

                            <x-agro.table-cell>
                                <span class="text-violet-700 font-medium text-sm">{{ $est->round_label }}</span>
                                <div class="text-xs text-zinc-400">{{ $est->estimation_date?->format('d/m/Y') }}</div>
                            </x-agro.table-cell>

                            <x-agro.table-cell>
                                <span class="text-zinc-600 text-sm capitalize">
                                    {{ match($est->estimation_method) {
                                        'visual'     => 'Visual',
                                        'sampling'   => 'Muestreo',
                                        'historical' => 'Histórico',
                                        'satellite'  => 'Satélite',
                                        default      => $est->estimation_method ?? '—',
                                    } }}
                                </span>
                            </x-agro.table-cell>

                            <x-agro.table-cell>
                                @if($est->estimated_yield_per_hectare)
                                    <span class="text-zinc-900">{{ number_format($est->estimated_yield_per_hectare, 0) }} kg/ha</span>
                                @else
                                    <span class="text-zinc-300">—</span>
                                @endif
                            </x-agro.table-cell>

                            <x-agro.table-cell>
                                @if($est->estimated_total_yield)
                                    <span class="font-semibold text-violet-700">{{ number_format($est->estimated_total_yield, 0) }} kg</span>
                                    @if($est->auto_calculated_yield && abs($est->auto_calculated_yield - $est->estimated_total_yield) > 10)
                                        <div class="text-xs text-zinc-400">Auto: {{ number_format($est->auto_calculated_yield, 0) }} kg</div>
                                    @endif
                                @else
                                    <span class="text-zinc-300">—</span>
                                @endif
                            </x-agro.table-cell>

                            <x-agro.table-cell>
                                @if($est->potential_alcohol)
                                    <span class="text-zinc-700 text-sm font-medium">{{ number_format($est->potential_alcohol, 1) }}°</span>
                                @else
                                    <span class="text-zinc-300">—</span>
                                @endif
                            </x-agro.table-cell>

                            <x-agro.table-cell>
                                @if($est->health_status)
                                    @php
                                        $sanColors = [
                                            'excellent' => 'agro', 'good' => 'agro',
                                            'botrytis_light' => 'amber', 'oidium_light' => 'amber',
                                            'botrytis_moderate' => 'red', 'oidium_moderate' => 'red',
                                            'mixed' => 'amber', 'poor' => 'red',
                                        ];
                                        $sanColor = $sanColors[$est->health_status] ?? 'zinc';
                                        $sanLabel = \App\Models\EstimatedYield::HEALTH_STATUSES[$est->health_status] ?? $est->health_status;
                                    @endphp
                                    <x-agro.status-badge :color="$sanColor" :label="$sanLabel" />
                                @elseif($est->health_percentage !== null)
                                    <span class="text-zinc-500 text-xs">{{ number_format($est->health_percentage, 0) }}%</span>
                                @else
                                    <span class="text-zinc-300">—</span>
                                @endif
                                @if($est->other_wineries)
                                    <div class="text-xs text-amber-600 mt-0.5">Entrega múltiple</div>
                                @endif
                            </x-agro.table-cell>

                            <x-agro.table-cell>
                                @if($est->status === 'confirmed')
                                    <x-agro.status-badge color="agro" label="Confirmado" />
                                @elseif($est->status === 'draft')
                                    <x-agro.status-badge color="amber" label="Borrador" />
                                @else
                                    <x-agro.status-badge color="zinc" label="Archivado" />
                                @endif
                            </x-agro.table-cell>

                        </x-agro.table-row>
                    @endforeach
                </x-agro.data-table>
            </x-agro.card>

            <x-agro.pagination :paginator="$estimates" />
        @endif
    </div>

    {{-- Modal Filtros --}}
    <x-agro.modal name="vitic-estimates-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'vitic-estimates-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-5">
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Viticultor</label>
                <flux:select wire:model.live="viticulturistFilter">
                    <option value="">Todos</option>
                    @foreach($linkedViticulturists as $v)
                        <option value="{{ $v->id }}">{{ $v->name }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Añada</label>
                <flux:select wire:model.live="vintageFilter">
                    <option value="">Todas</option>
                    @foreach($vintages as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Ronda</label>
                <flux:select wire:model.live="roundFilter">
                    <option value="">Todas las rondas</option>
                    @foreach($rounds as $num => $label)
                        <option value="{{ $num }}">{{ $label }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Estado</label>
                <flux:select wire:model.live="statusFilter">
                    <option value="">Todos</option>
                    <option value="confirmed">Confirmado</option>
                    <option value="draft">Borrador</option>
                    <option value="archived">Archivado</option>
                </flux:select>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-zinc-200 flex items-center justify-between">
            @if($filterCount > 0)
                <button wire:click="$set('viticulturistFilter', ''); $set('vintageFilter', ''); $set('roundFilter', ''); $set('statusFilter', '')"
                    class="text-sm text-zinc-400 hover:text-zinc-600 transition-colors">
                    Limpiar filtros
                </button>
            @else
                <span></span>
            @endif
            <flux:button x-on:click="$dispatch('close-modal', 'vitic-estimates-filters')" variant="primary">
                Aplicar
            </flux:button>
        </div>
    </x-agro.modal>

</div>
