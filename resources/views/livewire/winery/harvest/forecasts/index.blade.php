<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Previsiones de Vendimia"
        description="Gestiona los aforos de uva por viticultor y plantación antes de la vendimia."
    />

    @php
        $filterCount = (int) !empty($campaignFilter) + (int) !empty($viticulturistFilter) + (int) !empty($statusFilter) + (int) !empty($search);
    @endphp

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">

        {{-- Search --}}
        <div class="flex-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
            </div>
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Buscar viticultor, parcela, variedad..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition" />
        </div>

        {{-- Filtros --}}
        <button x-on:click="$dispatch('open-modal', 'forecasts-filters')"
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

        {{-- Nueva --}}
        <flux:button variant="primary" icon="plus" href="{{ route('winery.harvest-forecasts.create') }}" wire:navigate>
            Nueva previsión
        </flux:button>
    </div>

    {{-- Chips de filtros activos --}}
    @if($filterCount > 0)
        <div class="flex flex-wrap items-center gap-2">
            @if($campaignFilter)
                @php $campLabel = $campaigns->firstWhere('id', $campaignFilter)?->year ?? $campaignFilter; @endphp
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="calendar" class="size-3" />
                    Campaña: {{ $campLabel }}
                    <button wire:click="$set('campaignFilter', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
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
            @if($statusFilter)
                @php $statusLabel = ['confirmed' => 'Confirmadas', 'draft' => 'Borradores'][$statusFilter] ?? $statusFilter; @endphp
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="funnel" class="size-3" />
                    {{ $statusLabel }}
                    <button wire:click="$set('statusFilter', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            <button wire:click="$set('search', ''); $set('campaignFilter', ''); $set('viticulturistFilter', ''); $set('statusFilter', '')"
                class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">
                Limpiar todo
            </button>
        </div>
    @endif

    {{-- Skeleton --}}
    <div wire:loading wire:target="search, campaignFilter, viticulturistFilter, statusFilter, gotoPage, previousPage, nextPage">
        <x-agro.card>
            <div class="space-y-3">
                @for($i = 0; $i < 8; $i++)
                    <div class="h-10 bg-zinc-100 rounded-lg animate-pulse"></div>
                @endfor
            </div>
        </x-agro.card>
    </div>

    {{-- Contenido --}}
    <div wire:loading.remove wire:target="search, campaignFilter, viticulturistFilter, statusFilter, gotoPage, previousPage, nextPage">
        @if($forecasts->isEmpty())
            <x-agro.empty-state
                icon="clipboard-document-list"
                title="No hay previsiones"
                description="Crea el aforo previo a la vendimia para planificar las recepciones."
            >
                <x-slot:action>
                    <flux:button variant="primary" icon="plus" href="{{ route('winery.harvest-forecasts.create') }}" wire:navigate>
                        Nueva previsión
                    </flux:button>
                </x-slot:action>
            </x-agro.empty-state>
        @else
            <x-agro.card>
                <x-agro.data-table :headers="['Viticultor', 'Plantación / Variedad', 'Parcela', 'Añada', 'Previsto', 'Recibido', 'Ejecución', 'Estado', '']">
                    @foreach($forecasts as $forecast)
                        @php
                            $key       = $forecast->plot_planting_id . '_' . $forecast->campaign_id;
                            $batch     = $batchTotals->get($key);
                            $received  = $batch ? (float) $batch->total_weight_kg : 0;
                            $estimated = (float) $forecast->estimated_kg;
                            $pct       = $estimated > 0 ? round(($received / $estimated) * 100, 1) : 0;
                            $exceeded  = $pct > 100;
                            $planting  = $forecast->plotPlanting;
                        @endphp
                        <x-agro.table-row wire:key="forecast-{{ $forecast->id }}">
                            <x-agro.table-cell>
                                <span class="font-medium text-zinc-900">{{ $forecast->viticulturist?->name ?? '—' }}</span>
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                <div class="font-medium text-zinc-900">{{ $planting?->grapeVariety?->name ?? $planting?->name ?? '—' }}</div>
                                @if($planting?->area_planted)
                                    <div class="text-xs text-zinc-400">{{ number_format($planting->area_planted, 2) }} ha</div>
                                @endif
                            </x-agro.table-cell>
                            <x-agro.table-cell>{{ $planting?->plot?->name ?? '—' }}</x-agro.table-cell>
                            <x-agro.table-cell>{{ $forecast->vintage_year }}</x-agro.table-cell>
                            <x-agro.table-cell>
                                <span class="font-medium">{{ number_format($estimated, 0) }} kg</span>
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                <span class="{{ $exceeded ? 'text-red-600 font-semibold' : 'text-zinc-900' }}">
                                    {{ number_format($received, 0) }} kg
                                </span>
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                @if($estimated > 0)
                                    <div class="flex items-center gap-2">
                                        <x-agro.progress-bar
                                            :percentage="min($pct, 100)"
                                            :color="$exceeded ? 'red' : ($pct >= 80 ? 'amber' : 'agro')"
                                            class="w-20"
                                        />
                                        <span class="text-xs {{ $exceeded ? 'text-red-600 font-semibold' : 'text-zinc-600' }}">
                                            {{ $pct }}%
                                            @if($exceeded) ⚠ @endif
                                        </span>
                                    </div>
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                @if($forecast->status === 'confirmed')
                                    <x-agro.status-badge color="green" label="Confirmada" />
                                @else
                                    <x-agro.status-badge color="amber" label="Borrador" />
                                @endif
                            </x-agro.table-cell>
                            <x-agro.table-cell align="right">
                                <div class="flex items-center justify-end gap-1">
                                    @if($forecast->status === 'draft')
                                        <flux:button size="sm" variant="ghost" icon="check"
                                            wire:click="confirm({{ $forecast->id }})"
                                            wire:confirm="¿Confirmar esta previsión? Pasará a ser el límite operativo de las recepciones.">
                                            Confirmar
                                        </flux:button>
                                    @endif
                                    <flux:button size="sm" variant="ghost" icon="pencil"
                                        href="{{ route('winery.harvest-forecasts.edit', $forecast) }}" wire:navigate />
                                    <flux:button size="sm" variant="ghost" icon="trash" class="text-red-500"
                                        wire:click="delete({{ $forecast->id }})"
                                        wire:confirm="¿Eliminar esta previsión? Esta acción no se puede deshacer." />
                                </div>
                            </x-agro.table-cell>
                        </x-agro.table-row>
                    @endforeach
                </x-agro.data-table>
            </x-agro.card>

            <x-agro.pagination :paginator="$forecasts" />
        @endif
    </div>

    {{-- Modal Filtros --}}
    <x-agro.modal name="forecasts-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'forecasts-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-5">
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Campaña</label>
                <flux:select wire:model.live="campaignFilter">
                    <option value="">Todas las campañas</option>
                    @foreach($campaigns as $campaign)
                        <option value="{{ $campaign->id }}">{{ $campaign->year }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Viticultor</label>
                <flux:select wire:model.live="viticulturistFilter">
                    <option value="">Todos los viticultores</option>
                    @foreach($linkedViticulturists as $v)
                        <option value="{{ $v->id }}">{{ $v->name }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Estado</label>
                <flux:select wire:model.live="statusFilter">
                    <option value="">Todos</option>
                    <option value="confirmed">Confirmadas</option>
                    <option value="draft">Borradores</option>
                </flux:select>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-zinc-200 flex items-center justify-between">
            @if($filterCount > 0)
                <button wire:click="$set('search', ''); $set('campaignFilter', ''); $set('viticulturistFilter', ''); $set('statusFilter', '')"
                    class="text-sm text-zinc-400 hover:text-zinc-600 transition-colors">
                    Limpiar filtros
                </button>
            @else
                <span></span>
            @endif
            <flux:button x-on:click="$dispatch('close-modal', 'forecasts-filters')" variant="primary">
                Aplicar
            </flux:button>
        </div>
    </x-agro.modal>

</div>
