<div class="space-y-6 animate-fade-in">
    <x-agro.page-header title="Facturación de Vendimia" description="Facturas emitidas por la venta de tu cosecha">
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.invoices.harvest-sale.create') }}" wire:navigate variant="primary" icon="plus">
                Nueva Factura
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar por nº, ref. o comprador..."
        />
        <flux:select wire:model.live="buyerFilter" size="sm" class="w-52">
            <flux:select.option value="">Todos los compradores</flux:select.option>
            @foreach ($buyers as $buyer)
                <flux:select.option value="{{ $buyer }}">{{ $buyer }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="paymentFilter" size="sm" class="w-44">
            <flux:select.option value="">Todos los pagos</flux:select.option>
            <flux:select.option value="unpaid">Pendiente</flux:select.option>
            <flux:select.option value="paid">Pagada</flux:select.option>
        </flux:select>
        @if ($search || $buyerFilter || $paymentFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                Limpiar
            </flux:button>
        @endif
    </x-agro.filter-bar>

    @if ($invoices->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach ($invoices as $invoice)
                @php $delay = min($loop->index * 50, 300); @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col hover:-translate-y-1 {{ $invoice->status === 'cancelled' ? 'opacity-60' : '' }}"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="hs-invoice-{{ $invoice->id }}"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-agro-50 flex items-center justify-center shrink-0">
                                <flux:icon icon="document-text" class="size-5 text-agro-600" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-mono font-bold text-zinc-900 truncate" title="{{ $invoice->invoice_number ?? 'Sin código de factura' }}">
                                    {{ $invoice->invoice_number ?? 'Sin código de factura' }}
                                </h3>
                                @if ($invoice->delivery_note_code)
                                    <p class="text-xs text-zinc-400 font-mono truncate" title="{{ $invoice->delivery_note_code }}">{{ $invoice->delivery_note_code }}</p>
                                @else
                                    <p class="text-xs text-zinc-400">
                                        {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}
                                    </p>
                                @endif
                            </div>
                            <div class="flex flex-col items-end gap-1 shrink-0">
                                @if ($invoice->status === 'cancelled')
                                    <flux:badge color="red" size="sm">Cancelada</flux:badge>
                                @elseif ($invoice->payment_status === 'paid')
                                    <flux:badge color="green" size="sm">Pagada</flux:badge>
                                @else
                                    <flux:badge color="orange" size="sm">Pendiente</flux:badge>
                                @endif
                                @if ($invoice->delivery_status === 'delivered')
                                    <flux:badge color="green" size="sm">Entregada</flux:badge>
                                @elseif ($invoice->delivery_status === 'cancelled')
                                    <flux:badge color="red" size="sm">Cancelada</flux:badge>
                                @else
                                    <flux:badge color="amber" size="sm">Pendiente entrega</flux:badge>
                                @endif
                            </div>
                        </div>
                    </x-slot:header>

                    <div class="flex-1 space-y-3 text-sm">
                        <div class="flex items-center gap-2 text-zinc-600">
                            <flux:icon icon="building-office" class="size-4 text-zinc-400 shrink-0" />
                            <span class="truncate font-medium" title="{{ $invoice->billing_company_name ?? '' }}">{{ $invoice->billing_company_name ?? '—' }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-zinc-400">Fecha</span>
                            <span class="text-zinc-700">
                                {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-zinc-400">Importe</span>
                            <span class="text-lg font-bold text-zinc-900">
                                {{ number_format($invoice->total_amount, 2) }} €
                            </span>
                        </div>
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-between">

                            {{-- Grupo izquierdo: navegar --}}
                            <div class="flex items-center gap-0.5">
                                @php
                                    $btnBase    = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors';
                                    $btnSuccess = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-green-600 hover:bg-green-50 transition-colors';
                                    $btnDanger  = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-colors';
                                @endphp

                                <a href="{{ roleRoute('viticulturist.invoices.harvest-sale.pdf', $invoice->id) }}"
                                    target="_blank" title="Descargar Factura"
                                    class="{{ $btnBase }}">
                                    <flux:icon icon="document-text" class="size-4" />
                                </a>

                                <a href="{{ roleRoute('viticulturist.invoices.harvest-sale.delivery-note-pdf', $invoice->id) }}"
                                    target="_blank" title="Descargar Albarán"
                                    class="{{ $btnBase }}">
                                    <flux:icon icon="document-arrow-down" class="size-4" />
                                </a>

                                @if ($invoice->status !== 'cancelled' && $invoice->billing_email)
                                    <button
                                        wire:click="sendEmail({{ $invoice->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="sendEmail({{ $invoice->id }})"
                                        title="Enviar al comprador"
                                        class="{{ $btnBase }}"
                                    >
                                        <flux:icon icon="envelope" class="size-4" />
                                    </button>
                                @endif
                            </div>

                            {{-- Separador vertical --}}
                            <div class="w-px h-5 bg-zinc-200 mx-1"></div>

                            {{-- Grupo derecho: gestionar --}}
                            <div class="flex items-center gap-0.5">
                                @if ($invoice->status !== 'cancelled' && $invoice->payment_status !== 'paid')
                                    <a href="{{ roleRoute('viticulturist.invoices.harvest-sale.edit', $invoice->id) }}"
                                        wire:navigate title="Editar"
                                        class="{{ $btnBase }}">
                                        <flux:icon icon="pencil" class="size-4" />
                                    </a>

                                    @if ($invoice->delivery_status !== 'delivered')
                                        <button
                                            wire:click="markDelivered({{ $invoice->id }})"
                                            wire:confirm="¿Marcar esta factura como entregada? El stock pasará a vendido."
                                            title="Marcar como entregada"
                                            class="{{ $btnSuccess }}"
                                        >
                                            <flux:icon icon="truck" class="size-4" />
                                        </button>
                                    @endif

                                    <button
                                        wire:click="markPaid({{ $invoice->id }})"
                                        wire:confirm="¿Marcar esta factura como pagada?"
                                        title="Marcar como pagada"
                                        class="{{ $btnSuccess }}"
                                    >
                                        <flux:icon icon="check-circle" class="size-4" />
                                    </button>

                                    <button
                                        wire:click="cancel({{ $invoice->id }})"
                                        wire:confirm="¿Cancelar esta factura? Los kg quedarán disponibles de nuevo."
                                        title="Cancelar"
                                        class="{{ $btnDanger }}"
                                    >
                                        <flux:icon icon="x-mark" class="size-4" />
                                    </button>
                                @endif
                            </div>

                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        <div class="mt-2">
            {{ $invoices->links() }}
        </div>
    @else
        <x-agro.empty-state
            icon="document-text"
            message="No hay facturas de vendimia registradas"
            description="Crea tu primera factura para cobrar por la uva entregada"
        >
            <x-slot:action>
                <flux:button href="{{ roleRoute('viticulturist.invoices.harvest-sale.create') }}" wire:navigate variant="primary" icon="plus">
                    Nueva Factura
                </flux:button>
            </x-slot:action>
        </x-agro.empty-state>
    @endif
</div>
