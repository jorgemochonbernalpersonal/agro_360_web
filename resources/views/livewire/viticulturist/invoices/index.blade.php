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

    <!-- Tabla -->
    <div class="glass-card rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Número</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($invoices as $invoice)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $invoice->invoice_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $invoice->client->full_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ number_format($invoice->total_amount, 2) }} €</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $invoice->payment_status === 'paid' ? 'bg-green-100 text-green-800' : ($invoice->payment_status === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ ucfirst($invoice->payment_status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('viticulturist.invoices.show', $invoice->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">Ver</a>
                                <a href="{{ route('viticulturist.invoices.edit', $invoice->id) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No se encontraron facturas</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">
            {{ $invoices->links() }}
        </div>
    </div>
</div>
