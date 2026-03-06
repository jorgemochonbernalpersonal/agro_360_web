<div class="space-y-6 animate-fade-in">
    <x-agro.page-header title="Facturas de Vino" description="Ventas de vino a clientes externos">
        <x-slot:actions>
            <flux:button href="{{ route('winery.invoices.wine-sale.create') }}" wire:navigate variant="primary" icon="plus">
                Nueva Factura
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar por nº, albarán o cliente..."
        />
        <flux:select wire:model.live="statusFilter" size="sm" class="w-44">
            <flux:select.option value="">Todos los estados</flux:select.option>
            <flux:select.option value="draft">Borrador</flux:select.option>
            <flux:select.option value="sent">Enviada</flux:select.option>
            <flux:select.option value="cancelled">Cancelada</flux:select.option>
        </flux:select>
        <flux:select wire:model.live="paymentFilter" size="sm" class="w-44">
            <flux:select.option value="">Todos los cobros</flux:select.option>
            <flux:select.option value="unpaid">Pendiente</flux:select.option>
            <flux:select.option value="partial">Parcial</flux:select.option>
            <flux:select.option value="paid">Cobrada</flux:select.option>
        </flux:select>
        <flux:select wire:model.live="deliveryFilter" size="sm" class="w-44">
            <flux:select.option value="">Todas las entregas</flux:select.option>
            <flux:select.option value="pending">Pendiente</flux:select.option>
            <flux:select.option value="delivered">Entregada</flux:select.option>
            <flux:select.option value="cancelled">Cancelada</flux:select.option>
        </flux:select>
        @if ($search || $statusFilter || $paymentFilter || $deliveryFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                Limpiar
            </flux:button>
        @endif
    </x-agro.filter-bar>

    <x-agro.data-table
        :headers="['Nº / Albarán', 'Fecha', 'Cliente', 'Total', 'Cobro', 'Entrega', 'Acciones']"
        empty-message="No hay facturas de vino registradas"
        empty-description="Crea la primera factura para vender tu vino"
    >
        @if ($invoices->count() > 0)
            @foreach ($invoices as $invoice)
                <x-agro.table-row class="{{ $invoice->status === 'cancelled' ? 'opacity-60' : '' }}">
                    <x-agro.table-cell>
                        <div class="flex items-center gap-1.5">
                            <span class="font-mono font-medium text-zinc-900">{{ $invoice->invoice_number }}</span>
                            @if ($invoice->corrective)
                                <span class="text-[10px] font-bold text-orange-600 bg-orange-50 px-1.5 py-0.5 rounded" title="Factura rectificativa">R/</span>
                            @endif
                            @if ($invoice->correctives_count > 0)
                                <span class="text-[10px] font-medium text-orange-600 bg-orange-50 px-1.5 py-0.5 rounded" title="Tiene rectificativa">RECT</span>
                            @endif
                        </div>
                        @if ($invoice->delivery_note_code)
                            <p class="text-xs text-zinc-400 font-mono">{{ $invoice->delivery_note_code }}</p>
                        @endif
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-600">
                            {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}
                        </span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-700">{{ $invoice->client?->full_name ?? '—' }}</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm font-medium text-zinc-900">{{ number_format($invoice->total_amount, 2) }} €</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @switch($invoice->payment_status)
                            @case('unpaid')  <flux:badge color="orange" size="sm">Pendiente</flux:badge> @break
                            @case('partial') <flux:badge color="yellow" size="sm">Parcial</flux:badge> @break
                            @case('paid')    <flux:badge color="green"  size="sm">Cobrada</flux:badge> @break
                            @default         <flux:badge size="sm">—</flux:badge>
                        @endswitch
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @switch($invoice->delivery_status)
                            @case('pending')    <flux:badge color="zinc"  size="sm">Pendiente</flux:badge> @break
                            @case('in_transit') <flux:badge color="blue"  size="sm">En tránsito</flux:badge> @break
                            @case('delivered')  <flux:badge color="green" size="sm">Entregada</flux:badge> @break
                            @case('cancelled')  <flux:badge color="red"   size="sm">Cancelada</flux:badge> @break
                            @default            <flux:badge size="sm">—</flux:badge>
                        @endswitch
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <div class="flex items-center justify-end gap-1 flex-wrap">
                            @if ($invoice->status !== 'cancelled' && $invoice->delivery_status !== 'delivered')
                                <a href="{{ route('winery.invoices.wine-sale.edit', $invoice->id) }}" wire:navigate title="Editar">
                                    <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                        <flux:icon icon="pencil" class="size-4" />
                                    </button>
                                </a>
                            @endif

                            @if ($invoice->delivery_note_code)
                                <a href="{{ route('winery.invoices.wine-sale.delivery-note-pdf', $invoice->id) }}" target="_blank" title="Descargar albarán PDF">
                                    <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                        <flux:icon icon="document-arrow-down" class="size-4" />
                                    </button>
                                </a>
                            @endif

                            @if ($invoice->invoice_number || $invoice->delivery_note_code)
                                <a href="{{ route('winery.invoices.wine-sale.valorado-pdf', $invoice->id) }}" target="_blank" title="Descargar albarán valorado">
                                    <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                        <flux:icon icon="currency-euro" class="size-4" />
                                    </button>
                                </a>
                            @endif

                            @if ($invoice->invoice_number)
                                <a href="{{ route('winery.invoices.wine-sale.pdf', $invoice->id) }}" target="_blank" title="Descargar factura PDF">
                                    <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                        <flux:icon icon="document-text" class="size-4" />
                                    </button>
                                </a>
                            @endif

                            @if ($invoice->status === 'sent' && !$invoice->corrective && $invoice->correctives_count === 0)
                                <button
                                    wire:click="openCorrectiveModal({{ $invoice->id }})"
                                    title="Crear factura rectificativa"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-orange-600 hover:bg-orange-50 transition-colors"
                                >
                                    <flux:icon icon="arrow-uturn-left" class="size-4" />
                                </button>
                            @endif

                            @if ($invoice->status !== 'cancelled' && ($invoice->billing_email ?: $invoice->client?->email))
                                <button
                                    wire:click="sendEmail({{ $invoice->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="sendEmail({{ $invoice->id }})"
                                    title="Enviar por email"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors"
                                >
                                    <flux:icon icon="envelope" class="size-4" />
                                </button>
                            @endif

                            @if ($invoice->payment_status !== 'paid' && $invoice->status !== 'cancelled')
                                <button
                                    wire:click="markPaid({{ $invoice->id }})"
                                    wire:confirm="¿Marcar esta factura como cobrada?"
                                    title="Marcar como cobrada"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors"
                                >
                                    <flux:icon icon="banknotes" class="size-4" />
                                </button>
                            @endif

                            @if (in_array($invoice->delivery_status, ['pending', 'in_transit']) && $invoice->status !== 'cancelled')
                                <button
                                    wire:click="markDelivered({{ $invoice->id }})"
                                    wire:confirm="¿Marcar como entregada? El stock pasará a 'vendido' de forma definitiva."
                                    title="Marcar como entregada"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors"
                                >
                                    <flux:icon icon="truck" class="size-4" />
                                </button>
                            @endif

                            @if ($invoice->status !== 'cancelled' && $invoice->payment_status !== 'paid')
                                <button
                                    wire:click="cancel({{ $invoice->id }})"
                                    wire:confirm="¿Cancelar esta factura? El stock será restaurado automáticamente."
                                    title="Cancelar"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                >
                                    <flux:icon icon="x-mark" class="size-4" />
                                </button>
                            @endif
                        </div>
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach

            <x-slot name="pagination">
                {{ $invoices->links() }}
            </x-slot>
        @endif
    </x-agro.data-table>

    {{-- Modal Rectificativa --}}
    @if($correctiveModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             x-data x-on:keydown.escape.window="$wire.closeCorrectiveModal()">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm" @click.stop>
                <div class="px-6 py-4 border-b border-zinc-200 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                            <flux:icon icon="arrow-uturn-left" class="size-4 text-orange-600" />
                        </div>
                        <h3 class="text-base font-semibold text-zinc-900">Crear Rectificativa</h3>
                    </div>
                    <button wire:click="closeCorrectiveModal" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                        <flux:icon icon="x-mark" class="size-4" />
                    </button>
                </div>

                <div class="px-6 py-5 space-y-4">
                    <p class="text-sm text-zinc-500">
                        Se creará una factura rectificativa con importes negativos. El stock de vino quedará restaurado automáticamente.
                    </p>

                    <flux:field>
                        <flux:label>Fecha de la rectificativa <span class="text-red-500">*</span></flux:label>
                        <flux:input wire:model="correctiveDate" type="date" />
                        <flux:error name="correctiveDate" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Motivo <span class="text-zinc-400 font-normal">(opcional)</span></flux:label>
                        <flux:textarea wire:model="correctiveReason" rows="2" placeholder="Error en precio, devolución, etc." />
                        <flux:error name="correctiveReason" />
                    </flux:field>
                </div>

                <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 rounded-b-2xl flex items-center justify-end gap-3">
                    <flux:button wire:click="closeCorrectiveModal" variant="ghost" size="sm">
                        Cancelar
                    </flux:button>
                    <flux:button
                        wire:click="confirmCorrective"
                        wire:loading.attr="disabled"
                        variant="primary"
                        size="sm"
                        icon="arrow-uturn-left"
                    >
                        <span wire:loading.remove wire:target="confirmCorrective">Emitir Rectificativa</span>
                        <span wire:loading wire:target="confirmCorrective">Generando...</span>
                    </flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
