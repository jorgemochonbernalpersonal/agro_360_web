<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        :title="__('Ventas — :name', ['name' => $lot->name . ($lot->vintage ? ' (' . $lot->vintage . ')' : '')])"
        :description="__('Historial de albaranes y facturas de este lote')"
    >
        <x-slot:actions>
            <flux:button :href="roleRoute('product-lots.index')" icon="arrow-left" variant="ghost">{{ __('Volver a lotes') }}</flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs del lote --}}
    <div class="grid grid-cols-2 gap-4">
        <x-agro.stat-card
            :label="__('Unidades totales')"
            :value="number_format((float) $lot->quantity, 0, ',', '.')"
            icon="archive-box"
            color="blue"
        />
        <x-agro.stat-card
            :label="__('Disponibles')"
            :value="number_format((float) $lot->available_quantity, 0, ',', '.')"
            icon="check-circle"
            color="green"
        />
        <x-agro.stat-card
            :label="__('Vendidas')"
            :value="number_format((float) $lot->sold_quantity, 0, ',', '.')"
            icon="shopping-cart"
            color="violet"
        />
        <x-agro.stat-card
            :label="__('Importe vendido')"
            :value="'€ ' . number_format((float) $totals->total_amount, 2, ',', '.')"
            icon="banknotes"
            color="teal"
        />
    </div>

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <div class="flex-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="{{ __('Buscar por cliente, factura o albarán...') }}"
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
            />
        </div>

        <x-agro.filter-input wire:model.live="dateFrom" type="date" :label="__('Desde')" />
        <x-agro.filter-input wire:model.live="dateTo"   type="date" :label="__('Hasta')" />

        @if($search || $dateFrom || $dateTo)
            <div class="flex items-end">
                <flux:button wire:click="clearFilters" variant="ghost" icon="x-mark" size="sm">{{ __('Limpiar') }}</flux:button>
            </div>
        @endif
    </x-agro.filter-bar>

    {{-- Tabla --}}
    @if($items->isEmpty())
        <x-agro.empty-state
            icon="shopping-cart"
            title="{{ __('Sin ventas') }}"
            :description="__('Este lote no tiene albaranes ni facturas con los filtros aplicados.')"
        />
    @else
        <x-agro.card>
            <x-agro.data-table>
                <x-slot:head>
                    <x-agro.table-cell header>{{ __('Fecha') }}</x-agro.table-cell>
                    <x-agro.table-cell header>{{ __('Albarán / Factura') }}</x-agro.table-cell>
                    <x-agro.table-cell header>{{ __('Cliente') }}</x-agro.table-cell>
                    <x-agro.table-cell header align="right">{{ __('Uds.') }}</x-agro.table-cell>
                    <x-agro.table-cell header align="right">{{ __('Precio/ud') }}</x-agro.table-cell>
                    <x-agro.table-cell header align="right">{{ __('Dto.') }}</x-agro.table-cell>
                    <x-agro.table-cell header align="right">{{ __('Total') }}</x-agro.table-cell>
                    <x-agro.table-cell header align="center">{{ __('Cobro') }}</x-agro.table-cell>
                </x-slot:head>

                @foreach($items as $item)
                    @php
                        $payBadge = match($item->payment_status) {
                            'paid'    => ['text' => 'Cobrada',  'class' => 'bg-green-100 text-green-700'],
                            'partial' => ['text' => 'Parcial',  'class' => 'bg-orange-100 text-orange-700'],
                            default   => ['text' => 'Pendiente','class' => 'bg-zinc-100 text-zinc-500'],
                        };
                    @endphp
                    <x-agro.table-row>
                        <x-agro.table-cell>
                            {{ $item->invoice_date ? \Carbon\Carbon::parse($item->invoice_date)->format('d/m/Y') : '—' }}
                            @if($item->gift)
                                <flux:badge color="pink" size="sm" class="ml-1">{{ __('Regalo') }}</flux:badge>
                            @endif
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            <div class="flex flex-col gap-0.5">
                                @if($item->delivery_note_code)
                                    <span class="text-xs font-mono text-zinc-500">{{ $item->delivery_note_code }}</span>
                                @endif
                                @if($item->invoice_number)
                                    <span class="text-xs font-mono text-zinc-800">{{ $item->invoice_number }}</span>
                                @endif
                                @if(!$item->delivery_note_code && !$item->invoice_number)
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </div>
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            {{ trim($item->client_name) ?: '—' }}
                        </x-agro.table-cell>

                        <x-agro.table-cell align="right">
                            {{ number_format((float) $item->quantity, 0, ',', '.') }}
                        </x-agro.table-cell>

                        <x-agro.table-cell align="right">
                            {{ number_format((float) $item->unit_price, 2, ',', '.') }} €
                        </x-agro.table-cell>

                        <x-agro.table-cell align="right">
                            {{ $item->discount_percentage > 0 ? number_format((float) $item->discount_percentage, 1) . '%' : '—' }}
                        </x-agro.table-cell>

                        <x-agro.table-cell align="right">
                            <span class="font-semibold text-zinc-800">
                                {{ number_format((float) $item->total, 2, ',', '.') }} €
                            </span>
                        </x-agro.table-cell>

                        <x-agro.table-cell align="center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $payBadge['class'] }}">
                                {{ $payBadge['text'] }}
                            </span>
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach

                {{-- Fila de totales filtrados --}}
                <x-agro.table-row class="bg-zinc-50 font-bold border-t-2 border-zinc-300">
                    <x-agro.table-cell colspan="3">
                        Total ({{ $items->total() }} {{ $items->total() === 1 ? 'línea' : 'líneas' }})
                    </x-agro.table-cell>
                    <x-agro.table-cell align="right">
                        {{ number_format($items->sum('quantity'), 0, ',', '.') }}
                    </x-agro.table-cell>
                    <x-agro.table-cell></x-agro.table-cell>
                    <x-agro.table-cell></x-agro.table-cell>
                    <x-agro.table-cell align="right">
                        {{ number_format($items->sum('total'), 2, ',', '.') }} €
                    </x-agro.table-cell>
                    <x-agro.table-cell></x-agro.table-cell>
                </x-agro.table-row>
            </x-agro.data-table>
        </x-agro.card>

        <x-agro.pagination :paginator="$items" />
    @endif

</div>
