<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Albaranes / Facturas Mixtas"
        description="Gestiona tus albaranes con cosechas y lotes de vino"
    >
        <x-slot:actions>
            <flux:button href="{{ route('producer.invoices.mixed.create') }}" wire:navigate variant="primary" icon="plus">
                Nuevo albarán
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Barra de filtros --}}
    <div class="space-y-3">
        <div class="flex flex-wrap items-center gap-3">

            {{-- Buscador --}}
            <x-agro.search-input wire:model.live.debounce.300ms="search" placeholder="Buscar por albarán, nº factura o cliente..." />

            {{-- Filtro de estado de factura --}}
            <div class="min-w-[160px]">
                <select
                    wire:model.live="statusFilter"
                    class="w-full px-3 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm text-zinc-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent"
                >
                    <option value="">Estado: todos</option>
                    <option value="draft">Borrador</option>
                    <option value="sent">Emitida</option>
                    <option value="cancelled">Cancelada</option>
                </select>
            </div>

            {{-- Filtro de estado de entrega --}}
            <div class="min-w-[160px]">
                <select
                    wire:model.live="deliveryFilter"
                    class="w-full px-3 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm text-zinc-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent"
                >
                    <option value="">Entrega: todas</option>
                    <option value="pending">Pendiente</option>
                    <option value="delivered">Entregada</option>
                    <option value="cancelled">Cancelada</option>
                </select>
            </div>

            {{-- Limpiar filtros --}}
            @if($search || $statusFilter || $deliveryFilter)
                <button
                    wire:click="clearFilters"
                    class="inline-flex items-center gap-1.5 px-3 py-2.5 text-sm text-zinc-500 hover:text-zinc-700 border border-zinc-200 rounded-xl bg-white shadow-sm transition-colors hover:bg-zinc-50"
                >
                    <flux:icon icon="x-mark" class="size-4" />
                    Limpiar
                </button>
            @endif

        </div>

        {{-- Chips de filtros activos --}}
        @if($search || $statusFilter || $deliveryFilter)
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs text-zinc-400">Filtros activos:</span>

                @if($search)
                    <x-agro.filter-chip icon="magnifying-glass" :label="'&quot;' . $search . '&quot;'" wireRemove="$set('search', '')" />
                @endif

                @if($statusFilter)
                    @php $statusLabels = ['draft' => 'Borrador', 'sent' => 'Emitida', 'cancelled' => 'Cancelada']; @endphp
                    <x-agro.filter-chip :label="'Estado: ' . ($statusLabels[$statusFilter] ?? $statusFilter)" wireRemove="$set('statusFilter', '')" />
                @endif

                @if($deliveryFilter)
                    @php $deliveryLabels = ['pending' => 'Pendiente', 'delivered' => 'Entregada', 'cancelled' => 'Cancelada']; @endphp
                    <x-agro.filter-chip :label="'Entrega: ' . ($deliveryLabels[$deliveryFilter] ?? $deliveryFilter)" wireRemove="$set('deliveryFilter', '')" />
                @endif
            </div>
        @endif
    </div>

    {{-- Tabla --}}
    @if($invoices->count() > 0)
        <x-agro.card padding="none">
            <div class="overflow-x-auto" wire:loading.class="opacity-60 pointer-events-none" wire:target="search, statusFilter, deliveryFilter, clearFilters">
                <table class="min-w-full divide-y divide-zinc-200">
                    <thead class="bg-zinc-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Albarán</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Factura nº</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Cliente</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Entrega</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-zinc-500 uppercase tracking-wider">Importe</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-zinc-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-zinc-100">
                        @foreach($invoices as $invoice)
                            @php
                                [$statusLabel, $statusColor] = match($invoice->status) {
                                    'draft'     => ['Borrador', 'zinc'],
                                    'sent'      => ['Emitida',  'blue'],
                                    'cancelled' => ['Cancelada','red'],
                                    default     => [ucfirst($invoice->status ?? ''), 'zinc'],
                                };

                                [$deliveryLabel, $deliveryColor] = match($invoice->delivery_status) {
                                    'pending'   => ['Pendiente', 'yellow'],
                                    'delivered' => ['Entregada', 'green'],
                                    'cancelled' => ['Cancelada', 'red'],
                                    default     => [ucfirst($invoice->delivery_status ?? ''), 'zinc'],
                                };

                                $displayDate = $invoice->invoice_date ?? $invoice->delivery_note_date ?? $invoice->created_at;
                            @endphp
                            <tr wire:key="row-{{ $invoice->id }}" class="hover:bg-zinc-50 transition-colors">

                                {{-- Albarán --}}
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="font-mono text-sm font-semibold text-zinc-900">
                                        {{ $invoice->delivery_note_code ?? '—' }}
                                    </span>
                                </td>

                                {{-- Factura nº --}}
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($invoice->invoice_number)
                                        <span class="font-mono text-sm font-semibold text-zinc-900">{{ $invoice->invoice_number }}</span>
                                    @else
                                        <span class="text-sm text-zinc-400 italic">Sin emitir</span>
                                    @endif
                                </td>

                                {{-- Cliente --}}
                                <td class="px-4 py-3">
                                    <span class="text-sm font-medium text-zinc-800">
                                        {{ $invoice->client?->full_name ?? '—' }}
                                    </span>
                                </td>

                                {{-- Fecha --}}
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="text-sm text-zinc-600">
                                        {{ $displayDate ? $displayDate->format('d/m/Y') : '—' }}
                                    </span>
                                </td>

                                {{-- Estado --}}
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <flux:badge :color="$statusColor" size="sm">{{ $statusLabel }}</flux:badge>
                                </td>

                                {{-- Entrega --}}
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <flux:badge :color="$deliveryColor" size="sm">{{ $deliveryLabel }}</flux:badge>
                                </td>

                                {{-- Importe --}}
                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                    <span class="text-sm font-bold text-zinc-900">
                                        {{ number_format($invoice->total_amount ?? 0, 2, ',', '.') }} €
                                    </span>
                                </td>

                                {{-- Acciones --}}
                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                    @php $btnBase = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors'; @endphp
                                    <div class="flex items-center justify-end gap-1">

                                        {{-- Editar --}}
                                        <a
                                            href="{{ route('producer.invoices.mixed.edit', $invoice->id) }}"
                                            wire:navigate
                                            class="{{ $btnBase }}"
                                            title="Editar albarán"
                                        >
                                            <flux:icon icon="pencil-square" class="size-4" />
                                        </a>

                                        {{-- PDF Albarán --}}
                                        @if($invoice->delivery_note_code)
                                            <a
                                                href="{{ route('producer.invoices.mixed.delivery-note-pdf', $invoice->id) }}"
                                                class="{{ $btnBase }}"
                                                title="Descargar albarán PDF"
                                                target="_blank"
                                            >
                                                <flux:icon icon="document-arrow-down" class="size-4" />
                                            </a>
                                        @endif

                                        {{-- PDF Factura --}}
                                        @if($invoice->invoice_number)
                                            <a
                                                href="{{ route('producer.invoices.mixed.pdf', $invoice->id) }}"
                                                class="{{ $btnBase }} text-blue-400 hover:text-blue-600 hover:bg-blue-50"
                                                title="Descargar factura PDF"
                                                target="_blank"
                                            >
                                                <flux:icon icon="document-text" class="size-4" />
                                            </a>
                                        @endif

                                    </div>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-agro.card>

        @if($invoices->hasPages())
            <div class="flex justify-center">
                {{ $invoices->links() }}
            </div>
        @endif

    @else
        <x-agro.empty-state
            icon="document-text"
            message="No hay albaranes"
            :description="$search || $statusFilter || $deliveryFilter
                ? 'Ningún albarán coincide con los filtros aplicados.'
                : 'Crea tu primer albarán mixto para gestionar ventas de cosecha y vino juntos.'"
        >
            @if($search || $statusFilter || $deliveryFilter)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">
                        Limpiar filtros
                    </flux:button>
                </x-slot:action>
            @else
                <x-slot:action>
                    <flux:button href="{{ route('producer.invoices.mixed.create') }}" wire:navigate variant="primary" icon="plus">
                        Nuevo albarán
                    </flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @endif

</div>
