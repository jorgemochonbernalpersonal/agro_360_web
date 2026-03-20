<div class="space-y-6 animate-fade-in">

    <x-agro.page-header title="Insights de Lotes" description="Análisis de ventas por lote de producto y período">
        <x-slot:actions>
            @if(!empty($pivot['lots']))
                <flux:button wire:click="export" icon="arrow-down-tray" variant="ghost">
                    Exportar Excel
                </flux:button>
            @endif
            <flux:button :href="roleRoute('product-lots.index')" icon="arrow-left" variant="ghost">
                Volver
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <x-agro.filter-input wire:model.live="dateFrom" type="date" label="Desde" />
        <x-agro.filter-input wire:model.live="dateTo"   type="date" label="Hasta" />

        <flux:field>
            <flux:label>Lote</flux:label>
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

    @if(empty($pivot['months']))
        <x-agro.empty-state
            icon="archive-box"
            title="Sin datos"
            description="No hay ventas de lotes en el período seleccionado con los filtros aplicados."
        />
    @else
        @php
            $months     = $pivot['months'];
            $lotRows    = $pivot['lots'];
            $colTotals  = $pivot['colTotals'];
            $grandTotal = $pivot['grandTotal'];

            $monthNames = ['01'=>'Ene','02'=>'Feb','03'=>'Mar','04'=>'Abr','05'=>'May','06'=>'Jun',
                           '07'=>'Jul','08'=>'Ago','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dic'];

            $isAmount = ($metric === 'amount');
            $fmt = fn($v) => $isAmount
                ? '€ ' . number_format($v, 2, ',', '.')
                : number_format($v, 0, ',', '.');
        @endphp

        {{-- KPIs --}}
        <div class="grid grid-cols-2 gap-4">
            <x-agro.stat-card
                label="Lotes con ventas"
                :value="count($lotRows)"
                icon="archive-box"
                color="violet"
            />
            <x-agro.stat-card
                label="Meses con ventas"
                :value="count($months)"
                icon="calendar"
                color="blue"
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

        {{-- Tabla pivot --}}
        <x-agro.card>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-zinc-50 border-b border-zinc-200">
                            <th class="text-left px-4 py-3 font-semibold text-zinc-700 min-w-[180px]">
                                Lote de producto
                            </th>
                            @foreach($months as $month)
                                @php [$y, $m] = explode('-', $month); @endphp
                                <th class="text-right px-3 py-3 font-semibold text-zinc-700 whitespace-nowrap">
                                    {{ ($monthNames[$m] ?? $m) . ' ' . $y }}
                                </th>
                            @endforeach
                            <th class="text-right px-4 py-3 font-bold text-zinc-900 bg-violet-50 border-l border-violet-200">
                                Total
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @foreach($lotRows as $lot)
                            <tr class="hover:bg-zinc-50 transition-colors">
                                <td class="px-4 py-3 font-medium text-zinc-900 truncate max-w-[220px]">
                                    {{ $lot['name'] }}
                                </td>
                                @foreach($months as $month)
                                    @php $val = $lot['months'][$month] ?? 0; @endphp
                                    <td class="px-3 py-3 text-right {{ $val > 0 ? 'text-zinc-800' : 'text-zinc-300' }}">
                                        {{ $val > 0 ? $fmt($val) : '—' }}
                                    </td>
                                @endforeach
                                <td class="px-4 py-3 text-right font-bold text-violet-700 bg-violet-50 border-l border-violet-200">
                                    {{ $fmt($lot['total']) }}
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
                            <td class="px-4 py-3 text-right text-violet-800 bg-violet-100 border-l border-violet-200">
                                {{ $fmt($grandTotal) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-agro.card>
    @endif

</div>
