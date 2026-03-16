<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Previsiones de Vendimia"
        description="Gestiona los aforos de uva por viticultor y plantación antes de la vendimia."
    />

    {{-- Nav vendimia --}}
    <x-agro.harvest-nav />

    @php
        $filterCount = (int) !empty($campaignFilter) + (int) !empty($viticulturistFilter) + (int) !empty($statusFilter) + (int) !empty($search);
    @endphp

    {{-- Toolbar + filtros inline --}}
    <div x-data="{ filtersOpen: {{ $filterCount > 0 ? 'true' : 'false' }} }" class="space-y-3">

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
            <button @click="filtersOpen = !filtersOpen"
                class="relative inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-50 shadow-sm transition-colors">
                <flux:icon icon="adjustments-horizontal" class="size-4 text-zinc-500" />
                Filtros
                @if($filterCount > 0)
                    <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-agro-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">
                        {{ $filterCount }}
                    </span>
                @endif
                <flux:icon icon="chevron-down" class="size-3.5 text-zinc-400 transition-transform duration-200" x-bind:class="filtersOpen ? 'rotate-180' : ''" />
            </button>

            {{-- Separador --}}
            <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

            {{-- Nueva --}}
            <flux:button variant="primary" icon="plus" href="{{ roleRoute('harvest-forecasts.create') }}" wire:navigate>
                Nueva previsión
            </flux:button>
        </div>

        {{-- Panel de filtros inline --}}
        <div x-show="filtersOpen" x-transition.duration.150ms
            class="bg-white border border-zinc-200 rounded-2xl p-4 shadow-sm">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
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
            @if($filterCount > 0)
                <div class="mt-3 pt-3 border-t border-zinc-100">
                    <button wire:click="$set('search', ''); $set('campaignFilter', ''); $set('viticulturistFilter', ''); $set('statusFilter', '')"
                        class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">
                        Limpiar filtros
                    </button>
                </div>
            @endif
        </div>
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
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @for($i = 0; $i < 6; $i++)
                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5 space-y-4 animate-pulse">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-zinc-100 rounded-xl"></div>
                            <div class="space-y-1.5">
                                <div class="h-4 w-28 bg-zinc-100 rounded"></div>
                                <div class="h-3 w-20 bg-zinc-100 rounded"></div>
                            </div>
                        </div>
                        <div class="h-5 w-16 bg-zinc-100 rounded-full"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="h-14 bg-zinc-50 rounded-lg"></div>
                        <div class="h-14 bg-zinc-50 rounded-lg"></div>
                    </div>
                    <div class="h-2 bg-zinc-100 rounded-full"></div>
                </div>
            @endfor
        </div>
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
                    <flux:button variant="primary" icon="plus" href="{{ roleRoute('harvest-forecasts.create') }}" wire:navigate>
                        Nueva previsión
                    </flux:button>
                </x-slot:action>
            </x-agro.empty-state>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($forecasts as $i => $forecast)
                    @php
                        $key       = $forecast->plot_planting_id . '_' . $forecast->campaign_id;
                        $batch     = $batchTotals->get($key);
                        $received  = $batch ? (float) $batch->total_weight_kg : 0;
                        $estimated = (float) $forecast->estimated_kg;
                        $pct       = $estimated > 0 ? round(($received / $estimated) * 100, 1) : 0;
                        $exceeded  = $pct > 100;
                        $planting  = $forecast->plotPlanting;
                        $isConfirmed = $forecast->status === 'confirmed';
                        $delay     = min($i * 50, 300);
                    @endphp

                    <x-agro.card wire:key="forecast-{{ $forecast->id }}"
                        class="hover:-translate-y-1 transition-transform duration-200 animate-fade-in-up"
                        style="animation-delay: {{ $delay }}ms">

                        <x-slot:header>
                            <div class="flex items-start justify-between gap-3 w-full">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                                        {{ $isConfirmed ? 'bg-agro-50' : 'bg-amber-50' }}">
                                        <flux:icon icon="clipboard-document-list"
                                            class="size-5 {{ $isConfirmed ? 'text-agro-600' : 'text-amber-500' }}" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-zinc-900 text-sm truncate">
                                            {{ $forecast->viticulturist?->name ?? '—' }}
                                        </p>
                                        <p class="text-xs text-zinc-400 truncate">
                                            Añada {{ $forecast->vintage_year }}
                                        </p>
                                    </div>
                                </div>
                                @if($isConfirmed)
                                    <x-agro.status-badge color="green" label="Confirmada" />
                                @else
                                    <x-agro.status-badge color="amber" label="Borrador" />
                                @endif
                            </div>
                        </x-slot:header>

                        <div class="space-y-4">

                            {{-- Variedad + Parcela --}}
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-zinc-900">
                                    {{ $planting?->grapeVariety?->name ?? $planting?->name ?? '—' }}
                                </p>
                                <p class="text-xs text-zinc-400 flex items-center gap-1">
                                    <flux:icon icon="map-pin" class="size-3.5 shrink-0" />
                                    {{ $planting?->plot?->name ?? '—' }}
                                    @if($planting?->area_planted)
                                        · {{ number_format($planting->area_planted, 2) }} ha
                                    @endif
                                </p>
                            </div>

                            {{-- Stats: Previsto / Recibido --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-agro-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-agro-600 uppercase tracking-wide mb-0.5">Previsto</p>
                                    <p class="text-sm font-bold text-agro-700">{{ number_format($estimated, 0) }} kg</p>
                                </div>
                                <div class="rounded-xl p-3 {{ $exceeded ? 'bg-red-50' : 'bg-zinc-50' }}">
                                    <p class="text-[10px] font-semibold uppercase tracking-wide mb-0.5 {{ $exceeded ? 'text-red-600' : 'text-zinc-500' }}">Recibido</p>
                                    <p class="text-sm font-bold {{ $exceeded ? 'text-red-700' : 'text-zinc-700' }}">
                                        {{ number_format($received, 0) }} kg
                                    </p>
                                </div>
                            </div>

                            {{-- Barra de ejecución --}}
                            @if($estimated > 0)
                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-zinc-500">Ejecución</span>
                                        <span class="text-xs font-semibold {{ $exceeded ? 'text-red-600' : 'text-zinc-700' }}">
                                            {{ $pct }}%@if($exceeded) ⚠@endif
                                        </span>
                                    </div>
                                    <x-agro.progress-bar
                                        :percentage="min($pct, 100)"
                                        :color="$exceeded ? 'red' : ($pct >= 80 ? 'amber' : 'agro')"
                                    />
                                </div>
                            @else
                                <p class="text-xs text-zinc-400 italic">Sin recepciones registradas</p>
                            @endif

                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1">
                                    @if(!$isConfirmed)
                                        <flux:button size="sm" variant="ghost" icon="check"
                                            wire:click="confirm({{ $forecast->id }})"
                                            wire:confirm="¿Confirmar esta previsión? Pasará a ser el límite operativo de las recepciones.">
                                            Confirmar
                                        </flux:button>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1">
                                    <flux:button size="sm" variant="ghost" icon="pencil"
                                        href="{{ roleRoute('harvest-forecasts.edit', $forecast) }}" wire:navigate />
                                    <flux:button size="sm" variant="ghost" icon="trash" class="text-red-500"
                                        wire:click="delete({{ $forecast->id }})"
                                        wire:loading.attr="disabled"
                                        wire:confirm="¿Eliminar esta previsión? Esta acción no se puede deshacer." />
                                </div>
                            </div>
                        </x-slot:footer>

                    </x-agro.card>
                @endforeach
            </div>

            <x-agro.pagination :paginator="$forecasts" />
        @endif
    </div>

</div>
