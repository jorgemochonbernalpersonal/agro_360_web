<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Facturas / Pedidos"
        description="Gestiona tus facturas y pedidos"
    >
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.invoices.create') }}" variant="primary" icon="plus">
                Nueva Factura
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <!-- Estadísticas -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <x-agro.stat-card label="Total"      :value="$stats['total']"  icon="document-text"  color="blue"   />
        <x-agro.stat-card label="Borradores" :value="$stats['draft']"  icon="pencil-square"                 />
        <x-agro.stat-card label="Enviadas"   :value="$stats['sent']"   icon="paper-airplane" color="blue"   />
        <x-agro.stat-card label="Pagadas"    :value="$stats['paid']"   icon="check-circle"   color="agro"   />
        <x-agro.stat-card label="Pendientes" :value="$stats['unpaid']" icon="clock"          color="yellow" />
        <x-agro.stat-card label="Vencidas"   :value="$stats['overdue']" icon="exclamation-triangle" color="red" />
    </div>

    <!-- Filtros -->
    <x-agro.filter-bar>
        <x-agro.filter-input wire:model.live="search" placeholder="Buscar facturas..." />
        <x-agro.filter-select wire:model.live="filterStatus">
            <option value="">Todos los estados</option>
            <option value="draft">Borrador</option>
            <option value="sent">Enviada</option>
            <option value="paid">Pagada</option>
            <option value="cancelled">Cancelada</option>
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="filterPaymentStatus">
            <option value="">Todos los pagos</option>
            <option value="unpaid">Pendiente</option>
            <option value="partial">Parcial</option>
            <option value="paid">Pagado</option>
            <option value="overdue">Vencido</option>
        </x-agro.filter-select>
    </x-agro.filter-bar>


    @php
        $headers = ['Código Factura', 'Código Albarán', 'Cliente', 'Fechas', 'Total / Kilos', 'Estado Entrega', 'Estado Pago', 'Acciones'];
    @endphp

    <x-agro.data-table
        :headers="$headers"
        empty-message="No hay facturas registradas"
        empty-description="Comienza creando tu primera factura"
    >
        @if($invoices->count() > 0)
            @foreach($invoices as $invoice)
                <x-agro.table-row>
                    <x-agro.table-cell>
                        <span class="text-sm font-bold text-zinc-900">{{ $invoice->invoice_number ?? '-' }}</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm font-medium text-zinc-700">{{ $invoice->delivery_note_code ?? '-' }}</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-700">{{ $invoice->client->full_name }}</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <div class="flex flex-col gap-1 text-sm text-zinc-700">
                            <div>
                                <span class="text-xs text-zinc-500">Pedido:</span>
                                <span class="ml-1">
                                    @if($invoice->order_date)
                                        {{ $invoice->order_date->format('d/m/Y') }}
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                            <div>
                                <span class="text-xs text-zinc-500">Entrega:</span>
                                <span class="ml-1">
                                    @if($invoice->delivery_status === 'delivered' || $invoice->delivery_status === 'cancelled')
                                        @if($invoice->delivery_note_date)
                                            {{ $invoice->delivery_note_date->format('d/m/Y') }}
                                        @else
                                            -
                                        @endif
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                            <div>
                                <span class="text-xs text-zinc-500">Pago:</span>
                                <span class="ml-1">
                                    @if($invoice->payment_status === 'paid' && $invoice->payment_date)
                                        {{ $invoice->payment_date->format('d/m/Y') }}
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                        </div>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-semibold text-zinc-900">{{ number_format($invoice->total_amount, 2) }} €</span>
                            @php
                                $totalKilos = $invoice->items->sum('quantity');
                            @endphp
                            @if($totalKilos > 0)
                                <span class="text-xs text-zinc-600">{{ number_format($totalKilos, 2) }} kg</span>
                            @endif
                        </div>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @php
                            [$deliveryLabel, $deliveryColor] = match($invoice->delivery_status) {
                                'pending'    => ['Pendiente',   'yellow'],
                                'in_transit' => ['En Tránsito', 'blue'],
                                'delivered'  => ['Entregado',   'green'],
                                'cancelled'  => ['Cancelado',   'red'],
                                default      => [ucfirst($invoice->delivery_status), null],
                            };
                        @endphp
                        <flux:badge :color="$deliveryColor" size="sm">{{ $deliveryLabel }}</flux:badge>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @php
                            [$paymentLabel, $paymentColor] = match($invoice->payment_status) {
                                'paid'    => ['Pagado',    'green'],
                                'overdue' => ['Vencido',   'red'],
                                'partial' => ['Parcial',   'blue'],
                                'unpaid'  => ['Pendiente', 'yellow'],
                                default   => [ucfirst($invoice->payment_status), null],
                            };
                        @endphp
                        <flux:badge :color="$paymentColor" size="sm">{{ $paymentLabel }}</flux:badge>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <div class="flex items-center justify-end gap-2">
                            <x-agro.action-button
                                variant="view"
                                href="{{ route('viticulturist.invoices.show', $invoice->id) }}"
                            />
                            <x-agro.action-button
                                variant="edit"
                                href="{{ route('viticulturist.invoices.edit', $invoice->id) }}"
                            />
                        </div>
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach

            @if($invoices->hasPages())
                <x-slot name="pagination">
                    {{ $invoices->links() }}
                </x-slot>
            @endif
        @endif
    </x-agro.data-table>
</div>
