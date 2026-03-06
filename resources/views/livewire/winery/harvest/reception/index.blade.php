<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Recepción de Uva"
        description="Registro de entradas de uva por viticultor, parcela y variedad"
    >
        <x-slot:actions>
            <flux:button href="{{ route('winery.campaigns.index') }}" variant="ghost" icon="calendar">
                Campañas
            </flux:button>
            <flux:button href="{{ route('winery.grape-reception.create') }}" variant="primary" icon="plus">
                Nueva Recepción
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4">
        <x-agro.stat-card
            label="Kg recibidos"
            :value="number_format($stats['total_kg'], 0) . ' kg'"
            icon="scale"
        />
        <x-agro.stat-card
            label="Entradas"
            :value="$stats['total_entries']"
            icon="clipboard-document-list"
        />
        <x-agro.stat-card
            label="Baumé medio"
            :value="$stats['avg_baume'] > 0 ? $stats['avg_baume'] . ' °Bé' : '—'"
            icon="beaker"
        />
    </div>

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar viticultor, variedad, parcela o ticket..."
        />
        <flux:select wire:model.live="campaignFilter" size="sm" class="w-44">
            <flux:select.option value="">Todas las campañas</flux:select.option>
            @foreach($campaigns as $c)
                <flux:select.option value="{{ $c->id }}">
                    {{ $c->name }} {{ $c->active ? '(activa)' : '' }}
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

    {{-- Tabla --}}
    <x-agro.data-table
        :headers="['Fecha', 'Viticultor', 'Parcela / Variedad', 'Kg', 'Calidad', 'Ticket', 'Contenedor', 'Acciones']"
        empty-message="No hay recepciones registradas"
        empty-description="Registra la primera entrada de uva para esta campaña"
    >
        @if($receptions->count() > 0)
            @foreach($receptions as $reception)
                @php
                    $planting = $reception->plotPlanting;
                    $variety  = $planting?->grapeVariety?->name ?? $planting?->name ?? '—';
                    $plotName = $planting?->plot?->name ?? '—';
                    $vitic    = $reception->activity?->viticulturist;
                @endphp
                <x-agro.table-row>
                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-700">
                            {{ $reception->harvest_start_date?->format('d/m/Y') ?? '—' }}
                        </span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <p class="text-sm font-semibold text-zinc-900">{{ $vitic?->name ?? '—' }}</p>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <p class="text-sm font-semibold text-zinc-900">{{ $variety }}</p>
                        <p class="text-xs text-zinc-500">{{ $plotName }}</p>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm font-bold text-zinc-900">
                            {{ number_format($reception->total_weight, 0) }} kg
                        </span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @if($reception->baume_degree)
                            <span class="text-sm text-zinc-700">{{ $reception->baume_degree }} °Bé</span>
                        @endif
                        @if($reception->brix_degree)
                            <span class="text-xs text-zinc-500 ml-1">/ {{ $reception->brix_degree }} °Bx</span>
                        @endif
                        @if(!$reception->baume_degree && !$reception->brix_degree)
                            <span class="text-zinc-400">—</span>
                        @endif
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-xs text-zinc-600">{{ $reception->harvest_ticket_number ?? '—' }}</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @if($reception->container_id)
                            <flux:badge color="green" size="sm">{{ $reception->container?->name ?? '—' }}</flux:badge>
                        @else
                            <flux:badge color="zinc" size="sm">Sin asignar</flux:badge>
                        @endif
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <div class="flex items-center justify-end gap-1">
                            <flux:button
                                href="{{ route('winery.grape-reception.assign', $reception) }}"
                                variant="ghost"
                                size="xs"
                                icon="cube"
                                title="{{ $reception->container_id ? 'Reasignar contenedor' : 'Asignar a contenedor' }}"
                            />
                        </div>
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach

            <x-slot name="pagination">
                {{ $receptions->links() }}
            </x-slot>
        @endif
    </x-agro.data-table>
</div>
