<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        :title="__('Factura: :num', ['num' => $invoice->invoice_number])"
        :description="__('Detalles de la factura')"
    >
        <x-slot:actions>
            <flux:button
                href="{{ roleRoute('viticulturist.invoices.delivery-note-pdf', $invoice->id) }}"
                target="_blank"
                variant="ghost"
                icon="document"
            >
                {{ __('Albarán PDF') }}
            </flux:button>
            <flux:button
                href="{{ roleRoute('viticulturist.invoices.pdf', $invoice->id) }}"
                target="_blank"
                variant="ghost"
                icon="arrow-down-tray"
            >
                {{ __('Factura PDF') }}
            </flux:button>
            <flux:button href="{{ roleRoute('viticulturist.invoices.edit', $invoice->id) }}" variant="primary" icon="pencil-square">
                {{ __('Editar') }}
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-agro-50">
                    <flux:icon icon="document-text" class="size-4 text-agro-600" />
                </div>
                <span class="font-semibold text-zinc-900 text-sm">{{ __('Información de la Factura') }}</span>
            </div>
        </x-slot:header>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-zinc-500">{{ __('Cliente') }}</p>
                <p class="font-semibold">{{ $invoice->client->full_name }}</p>
            </div>
            <div>
                <p class="text-sm text-zinc-500">{{ __('Fecha') }}</p>
                <p class="font-semibold">{{ $invoice->invoice_date->format('d/m/Y') }}</p>
            </div>
            <div>
                <p class="text-sm text-zinc-500">{{ __('Total') }}</p>
                <p class="font-semibold text-lg">{{ number_format($invoice->total_amount, 2) }} €</p>
            </div>
            <div>
                <p class="text-sm text-zinc-500">{{ __('Estado') }}</p>
                <p class="font-semibold">{{ ucfirst($invoice->status) }}</p>
            </div>
        </div>

        <div class="mt-6">
            <h4 class="font-bold mb-2">{{ __('Items') }}</h4>
            <table class="min-w-full divide-y divide-zinc-200">
                <thead class="bg-zinc-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase">{{ __('Nombre') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase">{{ __('Cantidad') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase">{{ __('Precio') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase">{{ __('Total') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-zinc-200">
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
    </x-agro.card>
</div>
