<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Recepción de Uva"
        description="Registro de entradas de uva por viticultor, parcela y variedad"
    >
        <x-slot:actions>
            <flux:button href="{{ route('winery.grape-reception.create') }}" variant="primary" icon="plus">
                Nueva Recepción
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

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
        @if($search || $campaignFilter || $viticulturistFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                Limpiar
            </flux:button>
        @endif
    </x-agro.filter-bar>

    @if($receptions->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($receptions as $reception)
                @php
                    $planting = $reception->plotPlanting;
                    $variety  = $planting?->grapeVariety?->name ?? $planting?->name ?? '—';
                    $plotName = $planting?->plot?->name ?? '—';
                    $vitic    = $reception->activity?->viticulturist;
                    $year     = $reception->activity?->campaign?->year;
                    $delay    = min($loop->index * 50, 300);
                @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="reception-card-{{ $reception->id }}"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-agro-50 flex items-center justify-center shrink-0">
                                <flux:icon icon="scale" class="size-5 text-agro-600" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-zinc-900 truncate">{{ $variety }}</h3>
                                <p class="text-xs text-zinc-500 truncate">{{ $plotName }}</p>
                            </div>
                            @if($year)
                                <span class="text-sm font-bold text-agro-700 shrink-0">{{ $year }}</span>
                            @endif
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
                            </span>
                        </div>

                        {{-- Kg --}}
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-400">Peso total</span>
                            <span class="font-bold text-zinc-900">
                                {{ number_format($reception->total_weight, 0) }} kg
                            </span>
                        </div>

                        {{-- Calidad --}}
                        @if($reception->baume_degree || $reception->brix_degree)
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-400">Calidad</span>
                                <span class="text-zinc-700">
                                    @if($reception->baume_degree)
                                        {{ $reception->baume_degree }} °Bé
                                    @endif
                                    @if($reception->brix_degree)
                                        <span class="text-zinc-400">/ {{ $reception->brix_degree }} °Bx</span>
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
                        <div class="flex justify-end">
                            <a href="{{ route('winery.grape-reception.assign', $reception) }}"
                               title="{{ $reception->container_id ? 'Reasignar contenedor' : 'Asignar a contenedor' }}">
                                <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                    <flux:icon icon="cube" class="size-4" />
                                </button>
                            </a>
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
