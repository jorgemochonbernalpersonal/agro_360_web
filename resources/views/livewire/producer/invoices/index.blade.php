<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        :title="__('Albaranes / Facturas Mixtas')"
        :description="__('Gestiona tus albaranes con cosechas y lotes de vino')"
    >
        <x-slot:actions>
            <flux:button href="{{ route('producer.invoices.mixed.create') }}" wire:navigate variant="primary" icon="plus">
                {{ __('Nuevo albarán') }}
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Barra de filtros --}}
    <div class="space-y-3">
        <div class="flex flex-wrap items-center gap-3">

            {{-- Buscador --}}
            <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar por albarán, nº factura o cliente...')" />

            {{-- Filtro de estado de factura --}}
            <flux:select wire:model.live="statusFilter" class="min-w-[160px]">
                <flux:select.option value="">{{ __('Estado: todos') }}</flux:select.option>
                <flux:select.option value="draft">{{ __('Borrador') }}</flux:select.option>
                <flux:select.option value="sent">{{ __('Emitida') }}</flux:select.option>
                <flux:select.option value="cancelled">{{ __('Cancelada') }}</flux:select.option>
            </flux:select>

            {{-- Filtro de estado de entrega --}}
            <flux:select wire:model.live="deliveryFilter" class="min-w-[160px]">
                <flux:select.option value="">{{ __('Entrega: todas') }}</flux:select.option>
                <flux:select.option value="pending">{{ __('Pendiente') }}</flux:select.option>
                <flux:select.option value="delivered">{{ __('Entregada') }}</flux:select.option>
                <flux:select.option value="cancelled">{{ __('Cancelada') }}</flux:select.option>
            </flux:select>

            {{-- Limpiar filtros --}}
            @if($search || $statusFilter || $deliveryFilter)
                <button
                    wire:click="clearFilters"
                    class="inline-flex items-center gap-1.5 px-3 py-2.5 text-sm text-zinc-500 hover:text-zinc-700 border border-zinc-200 rounded-xl bg-white shadow-sm transition-colors hover:bg-zinc-50"
                >
                    <flux:icon icon="x-mark" class="size-4" />
                    {{ __('Limpiar') }}
                </button>
            @endif

        </div>

        {{-- Chips de filtros activos --}}
        @if($search || $statusFilter || $deliveryFilter)
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs text-zinc-400">{{ __('Filtros activos:') }}</span>

                @if($search)
                    <x-agro.filter-chip icon="magnifying-glass" :label="'&quot;' . $search . '&quot;'" wireRemove="$set('search', '')" />
                @endif

                @if($statusFilter)
                    @php $statusLabels = ['draft' => __('Borrador'), 'sent' => __('Emitida'), 'cancelled' => __('Cancelada')]; @endphp
                    <x-agro.filter-chip :label="__('Estado:') . ' ' . ($statusLabels[$statusFilter] ?? $statusFilter)" wireRemove="$set('statusFilter', '')" />
                @endif

                @if($deliveryFilter)
                    @php $deliveryLabels = ['pending' => __('Pendiente'), 'delivered' => __('Entregada'), 'cancelled' => __('Cancelada')]; @endphp
                    <x-agro.filter-chip :label="__('Entrega:') . ' ' . ($deliveryLabels[$deliveryFilter] ?? $deliveryFilter)" wireRemove="$set('deliveryFilter', '')" />
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
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Albarán') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Factura nº') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Cliente') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Fecha') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Estado') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Entrega') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Importe') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Acciones') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-zinc-100">
                        @foreach($invoices as $invoice)
                            @php
                                [$statusLabel, $statusColor] = match($invoice->status) {
                                    'draft'     => [__('Borrador'), 'zinc'],
                                    'sent'      => [__('Emitida'),  'blue'],
                                    'cancelled' => [__('Cancelada'),'red'],
                                    default     => [ucfirst($invoice->status ?? ''), 'zinc'],
                                };

                                [$deliveryLabel, $deliveryColor] = match($invoice->delivery_status) {
                                    'pending'   => [__('Pendiente'), 'yellow'],
                                    'delivered' => [__('Entregada'), 'green'],
                                    'cancelled' => [__('Cancelada'), 'red'],
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
                                        <span class="text-sm text-zinc-400 italic">{{ __('Sin emitir') }}</span>
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
                                    <div class="flex items-center justify-end gap-1">

                                        {{-- Editar --}}
                                        <x-agro.action-button variant="edit" href="{{ route('producer.invoices.mixed.edit', $invoice->id) }}" wire:navigate :title="__('Editar albarán')" />

                                        {{-- PDF Albarán --}}
                                        @if($invoice->delivery_note_code)
                                            <x-agro.action-button icon="document-arrow-down" variant="default" href="{{ route('producer.invoices.mixed.delivery-note-pdf', $invoice->id) }}" target="_blank" :title="__('Descargar albarán PDF')" />
                                        @endif

                                        {{-- PDF Factura --}}
                                        @if($invoice->invoice_number)
                                            <x-agro.action-button icon="document-text" variant="success" href="{{ route('producer.invoices.mixed.pdf', $invoice->id) }}" target="_blank" :title="__('Descargar factura PDF')" />
                                        @endif

                                    </div>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-agro.card>

        <x-agro-pagination :paginator="$invoices" />

    @else
        <x-agro.empty-state
            icon="document-text"
            :message="__('No hay albaranes')"
            :description="$search || $statusFilter || $deliveryFilter
                ? __('Ningún albarán coincide con los filtros aplicados.')
                : __('Crea tu primer albarán mixto para gestionar ventas de cosecha y vino juntos.')"
        >
            @if($search || $statusFilter || $deliveryFilter)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">
                        {{ __('Limpiar filtros') }}
                    </flux:button>
                </x-slot:action>
            @else
                <x-slot:action>
                    <flux:button href="{{ route('producer.invoices.mixed.create') }}" wire:navigate variant="primary" icon="plus">
                        {{ __('Nuevo albarán') }}
                    </flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @endif

</div>
