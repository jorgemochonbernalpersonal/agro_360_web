<div class="space-y-6 animate-fade-in">
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Facturas / Pedidos"
        description="Gestiona tus facturas y pedidos"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    >
        <x-slot:actionButton>
            <x-button href="{{ route('viticulturist.invoices.create') }}" variant="primary">
                Nueva Factura
            </x-button>
        </x-slot:actionButton>
    </x-page-header>

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
        <div class="glass-card rounded-xl p-6">
            <p class="text-sm font-medium text-gray-600">Total</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="glass-card rounded-xl p-6">
            <p class="text-sm font-medium text-gray-600">Borradores</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['draft'] }}</p>
        </div>
        <div class="glass-card rounded-xl p-6">
            <p class="text-sm font-medium text-gray-600">Enviadas</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['sent'] }}</p>
        </div>
        <div class="glass-card rounded-xl p-6">
            <p class="text-sm font-medium text-gray-600">Pagadas</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['paid'] }}</p>
        </div>
        <div class="glass-card rounded-xl p-6">
            <p class="text-sm font-medium text-gray-600">Pendientes</p>
            <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $stats['unpaid'] }}</p>
        </div>
        <div class="glass-card rounded-xl p-6">
            <p class="text-sm font-medium text-gray-600">Vencidas</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ $stats['overdue'] }}</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="glass-card rounded-xl p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-input wire:model.live="search" type="text" placeholder="Buscar facturas..." />
            <x-select wire:model.live="filterStatus">
                <option value="">Todos los estados</option>
                <option value="draft">Borrador</option>
                <option value="sent">Enviada</option>
                <option value="paid">Pagada</option>
                <option value="cancelled">Cancelada</option>
            </x-select>
            <x-select wire:model.live="filterPaymentStatus">
                <option value="">Todos los pagos</option>
                <option value="unpaid">Pendiente</option>
                <option value="partial">Parcial</option>
                <option value="paid">Pagado</option>
                <option value="overdue">Vencido</option>
            </x-select>
        </div>
    </div>


    @php
        $headers = [
            ['label' => 'Número', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>'],
            ['label' => 'Cliente', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'],
            ['label' => 'Fecha', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'],
            ['label' => 'Total', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'],
            ['label' => 'Estado', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'],
            'Acciones',
        ];
    @endphp

    <x-data-table 
        :headers="$headers" 
        empty-message="No hay facturas registradas" 
        empty-description="Comienza creando tu primera factura"
        color="green"
    >
        @if($invoices->count() > 0)
            @foreach($invoices as $invoice)
                <x-table-row>
                    <x-table-cell>
                        <span class="text-sm font-bold text-gray-900">{{ $invoice->invoice_number }}</span>
                    </x-table-cell>
                    
                    <x-table-cell>
                        <span class="text-sm text-gray-700">{{ $invoice->client->full_name }}</span>
                    </x-table-cell>
                    
                    <x-table-cell>
                        <span class="text-sm text-gray-700">{{ $invoice->invoice_date->format('d/m/Y') }}</span>
                    </x-table-cell>
                    
                    <x-table-cell>
                        <span class="text-sm font-semibold text-gray-900">{{ number_format($invoice->total_amount, 2) }} €</span>
                    </x-table-cell>
                    
                    <x-table-cell>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $invoice->payment_status === 'paid' ? 'bg-green-100 text-green-800' : ($invoice->payment_status === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ ucfirst($invoice->payment_status) }}
                        </span>
                    </x-table-cell>
                    
                    <x-table-cell>
                        <x-table-actions align="right">
                            <x-action-button 
                                variant="view" 
                                href="{{ route('viticulturist.invoices.show', $invoice->id) }}"
                            />
                            <x-action-button 
                                variant="edit" 
                                href="{{ route('viticulturist.invoices.edit', $invoice->id) }}"
                            />
                        </x-table-actions>
                    </x-table-cell>
                </x-table-row>
            @endforeach

            <x-slot name="pagination">
                {{ $invoices->links() }}
            </x-slot>
        @endif
    </x-data-table>
</div>
