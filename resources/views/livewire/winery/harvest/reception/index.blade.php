<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Recepción de Uva"
        description="Registro de entradas de uva por viticultor, parcela y variedad"
    >
        <x-slot:actions>
            <a href="{{ route('winery.grape-reception.export-pdf', array_filter(['campaign' => $campaignFilter, 'viticulturist' => $viticulturistFilter, 'disqualified' => $disqualifiedFilter])) }}"
               target="_blank">
                <flux:button variant="ghost" icon="document-arrow-down" size="sm">PDF</flux:button>
            </a>
            <a href="{{ route('winery.grape-reception.export-excel', array_filter(['campaign' => $campaignFilter, 'viticulturist' => $viticulturistFilter, 'disqualified' => $disqualifiedFilter])) }}">
                <flux:button variant="ghost" icon="table-cells" size="sm">Excel</flux:button>
            </a>
            <flux:button href="{{ route('winery.grape-reception.create') }}" variant="primary" icon="plus">
                Nueva Recepción
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <x-agro.stat-card
            label="Total recibido"
            :value="number_format($stats['total_kg'], 0) . ' kg'"
            icon="scale"
            color="agro"
        />
        <x-agro.stat-card
            label="Recepciones"
            :value="$stats['total_count']"
            icon="clipboard-document-list"
            color="blue"
        />
        <x-agro.stat-card
            label="Viticultores"
            :value="$stats['viticulturists']"
            icon="users"
            color="purple"
        />
        <x-agro.stat-card
            label="Kg descartados"
            :value="number_format($stats['disqualified_kg'], 0) . ' kg'"
            icon="x-circle"
            color="red"
        />
    </div>

    {{-- Resumen por viticultor y variedad --}}
    @if($stats['total_count'] > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <flux:icon icon="users" class="size-4 text-agro-600" />
                        <span class="font-semibold text-zinc-900 text-sm">Por viticultor</span>
                    </div>
                </x-slot:header>
                <div class="space-y-2">
                    @foreach($byViticulturist as $name => $kg)
                        @php $pct = $stats['total_kg'] > 0 ? round(($kg / $stats['total_kg']) * 100) : 0; @endphp
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-zinc-700 truncate max-w-[70%]">{{ $name }}</span>
                                <span class="font-medium text-zinc-900">{{ number_format($kg, 0) }} kg ({{ $pct }}%)</span>
                            </div>
                            <div class="w-full bg-zinc-100 rounded-full h-1.5">
                                <div class="bg-agro-500 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-agro.card>

            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <flux:icon icon="sparkles" class="size-4 text-purple-600" />
                        <span class="font-semibold text-zinc-900 text-sm">Por variedad</span>
                    </div>
                </x-slot:header>
                <div class="space-y-2">
                    @foreach($byVariety as $variety => $kg)
                        @php $pct = $stats['total_kg'] > 0 ? round(($kg / $stats['total_kg']) * 100) : 0; @endphp
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-zinc-700 truncate max-w-[70%]">{{ $variety }}</span>
                                <span class="font-medium text-zinc-900">{{ number_format($kg, 0) }} kg ({{ $pct }}%)</span>
                            </div>
                            <div class="w-full bg-zinc-100 rounded-full h-1.5">
                                <div class="bg-purple-400 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-agro.card>
        </div>
    @endif

    {{-- Estado de límites por plantación (solo con campaña seleccionada) --}}
    @if($limitStatus->isNotEmpty())
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <flux:icon icon="chart-bar" class="size-4 text-amber-600" />
                    <span class="font-semibold text-zinc-900 text-sm">Estado de límites por plantación</span>
                    <span class="text-xs text-zinc-400 ml-1">· añada seleccionada</span>
                </div>
            </x-slot:header>
            <div class="overflow-x-auto -mx-4 sm:mx-0">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="border-b border-zinc-100">
                            <th class="text-left py-2 px-3 font-medium text-zinc-500">Viticultor</th>
                            <th class="text-left py-2 px-3 font-medium text-zinc-500">Plantación / Variedad</th>
                            <th class="text-right py-2 px-3 font-medium text-zinc-500">Límite</th>
                            <th class="text-right py-2 px-3 font-medium text-zinc-500">Recibido</th>
                            <th class="text-left py-2 px-3 font-medium text-zinc-500 w-36">Uso</th>
                            <th class="text-center py-2 px-3 font-medium text-zinc-500">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($limitStatus as $row)
                            @php
                                $pct    = $row['pct'] ?? 0;
                                $barPct = min($pct, 100);
                                $barColor = $pct > 100 ? 'bg-red-500' : ($pct > 85 ? 'bg-amber-400' : 'bg-agro-500');
                            @endphp
                            <tr class="border-b border-zinc-50 hover:bg-zinc-50/50">
                                <td class="py-2 px-3 text-zinc-700">{{ $row['viticulturist'] }}</td>
                                <td class="py-2 px-3">
                                    <span class="text-zinc-800">{{ $row['planting'] }}</span><br>
                                    <span class="text-zinc-400">{{ $row['variety'] }}</span>
                                </td>
                                <td class="py-2 px-3 text-right text-zinc-600 font-mono">{{ number_format($row['limit'], 0) }} kg</td>
                                <td class="py-2 px-3 text-right font-bold {{ $row['exceeded'] ? 'text-red-600' : 'text-zinc-900' }} font-mono">
                                    {{ number_format($row['received'], 0) }} kg
                                </td>
                                <td class="py-2 px-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-zinc-100 rounded-full h-1.5">
                                            <div class="{{ $barColor }} h-1.5 rounded-full transition-all" style="width: {{ $barPct }}%"></div>
                                        </div>
                                        <span class="text-zinc-500 font-mono w-10 text-right">{{ $pct }}%</span>
                                    </div>
                                </td>
                                <td class="py-2 px-3 text-center">
                                    @if($row['exceeded'])
                                        <flux:badge color="red" size="sm">Excedido</flux:badge>
                                    @elseif($pct > 85)
                                        <flux:badge color="yellow" size="sm">Cerca</flux:badge>
                                    @else
                                        <flux:badge color="green" size="sm">Dentro</flux:badge>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-agro.card>
    @endif

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar viticultor, variedad, parcela o ticket..."
        />
        <flux:select wire:model.live="campaignFilter" size="sm" class="w-36">
            <flux:select.option value="">Todas las añadas</flux:select.option>
            @foreach($campaigns as $c)
                <flux:select.option value="{{ $c->id }}">
                    {{ $c->year }}{{ $c->active ? ' (activa)' : '' }}
                </flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="viticulturistFilter" size="sm" class="w-44">
            <flux:select.option value="">Todos los viticultores</flux:select.option>
            @foreach($linkedViticulturists as $v)
                <flux:select.option value="{{ $v->id }}">{{ $v->name }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="disqualifiedFilter" size="sm" class="w-40">
            <flux:select.option value="">Todas</flux:select.option>
            <flux:select.option value="0">Solo válidas</flux:select.option>
            <flux:select.option value="1">Solo descartadas</flux:select.option>
        </flux:select>
        @if($search || $campaignFilter || $viticulturistFilter || $disqualifiedFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                Limpiar
            </flux:button>
        @endif
    </x-agro.filter-bar>

    @if($receptions->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($receptions as $reception)
                @php
                    $planting    = $reception->plotPlanting;
                    $variety     = $planting?->grapeVariety?->name ?? $planting?->name ?? '—';
                    $plotName    = $planting?->plot?->name ?? '—';
                    $vitic       = $reception->activity?->viticulturist;
                    $year        = $reception->activity?->campaign?->year;
                    $delay       = min($loop->index * 50, 300);
                    $isCancelled = $reception->status === 'cancelled';
                @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col {{ $isCancelled ? 'opacity-60' : '' }}"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="reception-card-{{ $reception->id }}"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl {{ $isCancelled ? 'bg-zinc-100' : ($reception->disqualified ? 'bg-red-50' : 'bg-agro-50') }} flex items-center justify-center shrink-0">
                                <flux:icon icon="scale" class="size-5 {{ $isCancelled ? 'text-zinc-400' : ($reception->disqualified ? 'text-red-500' : 'text-agro-600') }}" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-zinc-900 truncate">{{ $variety }}</h3>
                                <p class="text-xs text-zinc-500 truncate">{{ $plotName }}</p>
                            </div>
                            <div class="flex flex-col items-end gap-1 shrink-0">
                                @if($year)
                                    <span class="text-sm font-bold text-agro-700">{{ $year }}</span>
                                @endif
                                @if($isCancelled)
                                    <flux:badge color="zinc" size="sm">Anulada</flux:badge>
                                @elseif($reception->disqualified)
                                    <flux:badge color="red" size="sm">Descartada</flux:badge>
                                @endif
                            </div>
                        </div>
                    </x-slot:header>

                    <div class="flex-1 space-y-3 text-sm">
                        {{-- Viticultor --}}
                        <div class="flex items-center gap-2 text-zinc-600">
                            <flux:icon icon="user" class="size-4 text-zinc-400 shrink-0" />
                            <span class="truncate font-medium">{{ $vitic?->name ?? '—' }}</span>
                        </div>

                        {{-- Fecha --}}
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-400">Fecha</span>
                            <span class="text-zinc-700">
                                {{ $reception->harvest_start_date?->format('d/m/Y') ?? '—' }}
                                @if($reception->harvest_time)
                                    <span class="text-zinc-400 text-xs">{{ $reception->harvest_time }}</span>
                                @endif
                            </span>
                        </div>

                        {{-- Kg --}}
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-400">Peso total</span>
                            <span class="font-bold text-zinc-900">
                                {{ number_format($reception->total_weight, 0) }} kg
                            </span>
                        </div>

                        {{-- Rendimiento real kg/ha --}}
                        @if($reception->yield_per_hectare)
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-400">Rendimiento</span>
                                <span class="text-zinc-700">{{ number_format($reception->yield_per_hectare, 0) }} kg/ha</span>
                            </div>
                        @endif

                        {{-- Calidad --}}
                        @if($reception->baume_degree || $reception->brix_degree || $reception->potential_alcohol)
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-400">Calidad</span>
                                <span class="text-zinc-700 text-xs">
                                    @if($reception->potential_alcohol)
                                        {{ $reception->potential_alcohol }}%
                                    @endif
                                    @if($reception->baume_degree)
                                        <span class="{{ $reception->potential_alcohol ? 'text-zinc-400' : '' }}">{{ $reception->baume_degree }}°Bé</span>
                                    @endif
                                    @if($reception->brix_degree)
                                        <span class="text-zinc-400">/ {{ $reception->brix_degree }}°Bx</span>
                                    @endif
                                </span>
                            </div>
                        @endif

                        {{-- Ticket --}}
                        @if($reception->harvest_ticket_number)
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-400">Ticket</span>
                                <span class="font-mono text-xs text-zinc-600">{{ $reception->harvest_ticket_number }}</span>
                            </div>
                        @endif

                        {{-- Contenedor --}}
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-400">Contenedor</span>
                            @if($reception->container_id)
                                <flux:badge color="green" size="sm">{{ $reception->container?->name ?? '—' }}</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">Sin asignar</flux:badge>
                            @endif
                        </div>
                    </div>

                    <x-slot:footer>
                        <div class="flex justify-between items-center">
                            <div>
                                @if(!$isCancelled)
                                    <button
                                        wire:click="cancelReception({{ $reception->id }})"
                                        wire:confirm="¿Anular esta recepción? Esta acción no se puede deshacer."
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                        title="Anular recepción"
                                    >
                                        <flux:icon icon="x-circle" class="size-4" />
                                    </button>
                                @endif
                            </div>
                            <div class="flex gap-1">
                                <a href="{{ route('winery.grape-reception.show', $reception) }}"
                                   title="Ver detalle">
                                    <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                        <flux:icon icon="eye" class="size-4" />
                                    </button>
                                </a>
                                @if(!$isCancelled)
                                    <a href="{{ route('winery.grape-reception.edit', $reception) }}"
                                       title="Editar recepción">
                                        <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                            <flux:icon icon="pencil-square" class="size-4" />
                                        </button>
                                    </a>
                                    <a href="{{ route('winery.grape-reception.assign', $reception) }}"
                                       title="{{ $reception->container_id ? 'Reasignar contenedor' : 'Asignar a contenedor' }}">
                                        <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                            <flux:icon icon="cube" class="size-4" />
                                        </button>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        <div class="mt-2">
            {{ $receptions->links() }}
        </div>
    @else
        <x-agro.empty-state
            icon="scale"
            title="No hay recepciones registradas"
            description="Registra la primera entrada de uva para esta campaña"
        >
            <x-slot:action>
                <flux:button href="{{ route('winery.grape-reception.create') }}" variant="primary" icon="plus">
                    Nueva Recepción
                </flux:button>
            </x-slot:action>
        </x-agro.empty-state>
    @endif
</div>
