<div class="space-y-6 animate-fade-in">
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Factura: {{ $invoice->invoice_number }}"
        description="Detalles de la factura"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    >
        <x-slot:actionButton>
            <x-button href="{{ route('viticulturist.invoices.edit', $invoice->id) }}" variant="primary">
                Editar
            </x-button>
        </x-slot:actionButton>
    </x-page-header>

    <div class="glass-card rounded-xl p-6">
        <h3 class="text-lg font-bold mb-4">Información de la Factura</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500">Cliente</p>
                <p class="font-semibold">{{ $invoice->client->full_name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Fecha</p>
                <p class="font-semibold">{{ $invoice->invoice_date->format('d/m/Y') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total</p>
                <p class="font-semibold text-lg">{{ number_format($invoice->total_amount, 2) }} €</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Estado</p>
                <p class="font-semibold">{{ ucfirst($invoice->status) }}</p>
            </div>
        </div>

        <div class="mt-6">
            <h4 class="font-bold mb-2">Items</h4>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Precio</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($invoice->items as $item)
                        <tr>
                            <td class="px-6 py-4 text-sm">{{ $item->name }}</td>
                            <td class="px-6 py-4 text-sm">{{ $item->quantity }}</td>
                            <td class="px-6 py-4 text-sm">{{ number_format($item->unit_price, 4) }} €</td>
                            <td class="px-6 py-4 text-sm font-semibold">{{ number_format($item->total, 2) }} €</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
