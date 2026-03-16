<div class="space-y-6 animate-fade-in">

    {{-- Cabecera --}}
    <x-agro.page-header title="Insights de Clientes" description="Análisis de ventas por cliente y período">
        <x-slot:actions>
            @if(!empty($pivot['clients']))
                <flux:button wire:click="export" icon="arrow-down-tray" variant="ghost">
                    Exportar Excel
                </flux:button>
            @endif
            <flux:button :href="roleRoute('clients.index')" icon="arrow-left" variant="ghost">
                Volver
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <x-agro.filter-input wire:model.live="dateFrom" type="date" label="Desde" />
        <x-agro.filter-input wire:model.live="dateTo" type="date" label="Hasta" />

        <flux:field>
            <flux:label>Cliente</flux:label>
            <flux:select wire:model.live="filterClientId">
                <flux:select.option value="">Todos los clientes</flux:select.option>
                @foreach($clients as $client)
                    <flux:select.option value="{{ $client->id }}">
                        {{ $client->company_name ?: trim($client->first_name . ' ' . $client->last_name) }}
                    </flux:select.option>
                @endforeach
            </flux:select>
        </flux:field>

        <flux:field>
            <flux:label>Lote de producto</flux:label>
            <flux:select wire:model.live="filterLotId">
                <flux:select.option value="">Todos los lotes</flux:select.option>
                @foreach($lots as $lot)
                    <flux:select.option value="{{ $lot->id }}">
                        {{ $lot->name }}{{ $lot->vintage ? ' (' . $lot->vintage . ')' : '' }}
                    </flux:select.option>
                @endforeach
            </flux:select>
        </flux:field>

        <flux:field>
            <flux:label>Métrica</flux:label>
            <flux:select wire:model.live="metric">
                <flux:select.option value="qty">Unidades</flux:select.option>
                <flux:select.option value="amount">Importe (€)</flux:select.option>
            </flux:select>
        </flux:field>

        <div class="flex items-end">
            <flux:button wire:click="clearFilters" variant="ghost" icon="x-mark" size="sm">
                Limpiar
            </flux:button>
        </div>
    </x-agro.filter-bar>

    {{-- Tabla pivot --}}
    @if(empty($pivot['months']))
        <x-agro.empty-state
            icon="chart-bar"
            title="Sin datos"
            description="No hay ventas en el período seleccionado con los filtros aplicados."
        />
    @else
        @php
            $months     = $pivot['months'];
            $clientRows = $pivot['clients'];
            $colTotals  = $pivot['colTotals'];
            $grandTotal = $pivot['grandTotal'];

            $monthNames = ['01'=>'Ene','02'=>'Feb','03'=>'Mar','04'=>'Abr','05'=>'May','06'=>'Jun',
                           '07'=>'Jul','08'=>'Ago','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dic'];

            $isAmount = ($metric === 'amount');

            $fmt = fn($v) => $isAmount
                ? '€ ' . number_format($v, 2, ',', '.')
                : number_format($v, 0, ',', '.');
        @endphp

        {{-- Resumen KPIs --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <x-agro.stat-card
                label="Clientes"
                :value="count($clientRows)"
                icon="users"
                color="blue"
            />
            <x-agro.stat-card
                label="Meses con ventas"
                :value="count($months)"
                icon="calendar"
                color="green"
            />
            <x-agro.stat-card
                label="{{ $isAmount ? 'Importe total' : 'Unidades totales' }}"
                :value="$isAmount ? '€ ' . number_format($grandTotal, 2, ',', '.') : number_format($grandTotal, 0, ',', '.')"
                icon="{{ $isAmount ? 'banknotes' : 'archive-box' }}"
                color="teal"
            />
            <x-agro.stat-card
                label="{{ $isAmount ? 'Media/mes' : 'Media uds/mes' }}"
                :value="count($months) > 0
                    ? ($isAmount ? '€ ' . number_format($grandTotal / count($months), 2, ',', '.') : number_format($grandTotal / count($months), 1, ',', '.'))
                    : '—'"
                icon="calculator"
                color="orange"
            />
        </div>

        {{-- Tabla --}}
        <x-agro.card>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-zinc-50 border-b border-zinc-200">
                            <th class="text-left px-4 py-3 font-semibold text-zinc-700 min-w-[160px]">
                                Cliente
                            </th>
                            @foreach($months as $month)
                                @php [$y,$m] = explode('-', $month); @endphp
                                <th class="text-right px-3 py-3 font-semibold text-zinc-700 whitespace-nowrap">
                                    {{ ($monthNames[$m] ?? $m) . ' ' . $y }}
                                </th>
                            @endforeach
                            <th class="text-right px-4 py-3 font-bold text-zinc-900 bg-green-50 border-l border-green-200">
                                Total
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @foreach($clientRows as $client)
                            <tr class="hover:bg-zinc-50 transition-colors">
                                <td class="px-4 py-3 font-medium text-zinc-900 truncate max-w-[200px]">
                                    {{ $client['name'] }}
                                </td>
                                @foreach($months as $month)
                                    @php $val = $client['months'][$month] ?? 0; @endphp
                                    <td class="px-3 py-3 text-right {{ $val > 0 ? 'text-zinc-800' : 'text-zinc-300' }}">
                                        {{ $val > 0 ? $fmt($val) : '—' }}
                                    </td>
                                @endforeach
                                <td class="px-4 py-3 text-right font-bold text-green-700 bg-green-50 border-l border-green-200">
                                    {{ $fmt($client['total']) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-zinc-100 border-t-2 border-zinc-300 font-bold">
                            <td class="px-4 py-3 text-zinc-900">TOTAL</td>
                            @foreach($months as $month)
                                <td class="px-3 py-3 text-right text-zinc-800">
                                    {{ $fmt($colTotals[$month] ?? 0) }}
                                </td>
                            @endforeach
                            <td class="px-4 py-3 text-right text-green-800 bg-green-100 border-l border-green-200">
                                {{ $fmt($grandTotal) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-agro.card>
    @endif

</div>
