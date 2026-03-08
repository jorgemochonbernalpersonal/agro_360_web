<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Previsiones de Vendimia"
        description="Gestiona los aforos de uva por viticultor y plantación antes de la vendimia."
    >
        <x-slot:actions>
            <flux:button variant="primary" icon="plus" href="{{ route('winery.harvest-forecasts.create') }}" wire:navigate>
                Nueva previsión
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Filtros --}}
    <x-agro.filter-bar :active-count="collect([$campaignFilter, $viticulturistFilter, $statusFilter, $search])->filter()->count()">
        <x-agro.filter-input wire:model.live="search" placeholder="Buscar viticultor, parcela, variedad..." />
        <x-agro.filter-select wire:model.live="campaignFilter" label="Campaña">
            <option value="">Todas las campañas</option>
            @foreach($campaigns as $campaign)
                <option value="{{ $campaign->id }}">{{ $campaign->year }}</option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="viticulturistFilter" label="Viticultor">
            <option value="">Todos los viticultores</option>
            @foreach($linkedViticulturists as $v)
                <option value="{{ $v->id }}">{{ $v->name }}</option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="statusFilter" label="Estado">
            <option value="">Todos</option>
            <option value="confirmed">Confirmadas</option>
            <option value="draft">Borradores</option>
        </x-agro.filter-select>
    </x-agro.filter-bar>

    {{-- Tabla --}}
    @if($forecasts->isEmpty())
        <x-agro.empty-state
            icon="clipboard-document-list"
            title="No hay previsiones"
            description="Crea el aforo previo a la vendimia para planificar las recepciones."
        >
            <flux:button variant="primary" icon="plus" href="{{ route('winery.harvest-forecasts.create') }}" wire:navigate>
                Nueva previsión
            </flux:button>
        </x-agro.empty-state>
    @else
        <x-agro.card>
            <x-agro.data-table :headers="['Viticultor', 'Plantación / Variedad', 'Parcela', 'Añada', 'Previsto', 'Recibido', 'Ejecución', 'Estado', '']">
                @foreach($forecasts as $forecast)
                    @php
                        $key        = $forecast->plot_planting_id . '_' . $forecast->campaign_id;
                        $batch      = $batchTotals->get($key);
                        $received   = $batch ? (float) $batch->total_weight_kg : 0;
                        $estimated  = (float) $forecast->estimated_kg;
                        $pct        = $estimated > 0 ? round(($received / $estimated) * 100, 1) : 0;
                        $exceeded   = $pct > 100;
                        $planting   = $forecast->plotPlanting;
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
                        <x-agro.table-cell>
                            {{ $planting?->plot?->name ?? '—' }}
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            {{ $forecast->vintage_year }}
                        </x-agro.table-cell>
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
                            <div class="flex items-center justify-end gap-2">
                                @if($forecast->status === 'draft')
                                    <flux:button size="sm" variant="ghost" icon="check" wire:click="confirm({{ $forecast->id }})" wire:confirm="¿Confirmar esta previsión? Pasará a ser el límite operativo de las recepciones.">
                                        Confirmar
                                    </flux:button>
                                @endif
                                <flux:button size="sm" variant="ghost" icon="pencil" href="{{ route('winery.harvest-forecasts.edit', $forecast) }}" wire:navigate />
                                <flux:button size="sm" variant="ghost" icon="trash" class="text-red-500" wire:click="delete({{ $forecast->id }})" wire:confirm="¿Eliminar esta previsión? Esta acción no se puede deshacer." />
                            </div>
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach
            </x-agro.data-table>
        </x-agro.card>

        <x-agro.pagination :paginator="$forecasts" />
    @endif

</div>
