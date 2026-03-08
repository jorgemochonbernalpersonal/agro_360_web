<div class="space-y-6 animate-fade-in">
    <x-agro.page-header title="Venta de Productos" description="Facturas de venta de productos a clientes externos">
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

    @if ($invoices->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($invoices as $invoice)
                @php $delay = min($loop->index * 50, 300); @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col {{ $invoice->status === 'cancelled' ? 'opacity-60' : '' }}"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="ws-invoice-{{ $invoice->id }}"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                                <flux:icon icon="document-text" class="size-5 text-blue-600" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <h3 class="font-mono font-bold text-zinc-900 truncate">
                                        {{ $invoice->invoice_number ?? '—' }}
                                    </h3>
                                    @if ($invoice->corrective)
                                        <span class="text-[10px] font-bold text-orange-600 bg-orange-50 px-1.5 py-0.5 rounded shrink-0" title="Factura rectificativa">R/</span>
                                    @endif
                                    @if ($invoice->correctives_count > 0)
                                        <span class="text-[10px] font-medium text-orange-600 bg-orange-50 px-1.5 py-0.5 rounded shrink-0" title="Tiene rectificativa">RECT</span>
                                    @endif
                                </div>
                                @if ($invoice->delivery_note_code)
                                    <p class="text-xs text-zinc-400 font-mono truncate">{{ $invoice->delivery_note_code }}</p>
                                @else
                                    <p class="text-xs text-zinc-400">
                                        {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}
                                    </p>
                                @endif
                            </div>
                            @switch($invoice->payment_status)
                                @case('unpaid')  <flux:badge color="orange" size="sm" class="shrink-0">Pendiente</flux:badge> @break
                                @case('partial') <flux:badge color="yellow" size="sm" class="shrink-0">Parcial</flux:badge> @break
                                @case('paid')    <flux:badge color="green"  size="sm" class="shrink-0">Cobrada</flux:badge> @break
                                @default         <flux:badge size="sm" class="shrink-0">—</flux:badge>
                            @endswitch
                        </div>
                    </x-slot:header>

                    <div class="flex-1 space-y-3 text-sm">
                        <div class="flex items-center gap-2 text-zinc-600">
                            <flux:icon icon="user" class="size-4 text-zinc-400 shrink-0" />
                            <span class="truncate font-medium">{{ $invoice->client?->full_name ?? '—' }}</span>
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

                        <div class="flex items-center justify-between">
                            <span class="text-zinc-400">Entrega</span>
                            @switch($invoice->delivery_status)
                                @case('pending')    <flux:badge color="zinc"  size="sm">Pendiente</flux:badge> @break
                                @case('in_transit') <flux:badge color="blue"  size="sm">En tránsito</flux:badge> @break
                                @case('delivered')  <flux:badge color="green" size="sm">Entregada</flux:badge> @break
                                @case('cancelled')  <flux:badge color="red"   size="sm">Cancelada</flux:badge> @break
                                @default            <flux:badge size="sm">—</flux:badge>
                            @endswitch
                        </div>
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-end gap-1 flex-wrap">
                            @if ($invoice->status !== 'cancelled' && $invoice->delivery_status !== 'delivered')
                                <a href="{{ route('winery.invoices.wine-sale.edit', $invoice->id) }}" wire:navigate title="Editar">
                                    <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                        <flux:icon icon="pencil" class="size-4" />
                                    </button>
                                </a>
                            @endif

                            @if ($invoice->delivery_note_code)
                                <a href="{{ route('winery.invoices.wine-sale.delivery-note-pdf', $invoice->id) }}" target="_blank" title="Albarán PDF">
                                    <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                        <flux:icon icon="document-arrow-down" class="size-4" />
                                    </button>
                                </a>
                            @endif

                            @if ($invoice->invoice_number || $invoice->delivery_note_code)
                                <a href="{{ route('winery.invoices.wine-sale.valorado-pdf', $invoice->id) }}" target="_blank" title="Albarán valorado">
                                    <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                        <flux:icon icon="currency-euro" class="size-4" />
                                    </button>
                                </a>
                            @endif

                            @if ($invoice->invoice_number)
                                <a href="{{ route('winery.invoices.wine-sale.pdf', $invoice->id) }}" target="_blank" title="Factura PDF">
                                    <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                        <flux:icon icon="document-text" class="size-4" />
                                    </button>
                                </a>
                            @endif

                            @if ($invoice->status === 'sent' && !$invoice->corrective && $invoice->correctives_count === 0)
                                <button
                                    wire:click="openCorrectiveModal({{ $invoice->id }})"
                                    title="Crear rectificativa"
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
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-green-600 hover:bg-green-50 transition-colors"
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
            title="No hay facturas de productos registradas"
            description="Crea la primera factura para vender tus productos"
        >
            <x-slot:action>
                <flux:button href="{{ route('winery.invoices.wine-sale.create') }}" wire:navigate variant="primary" icon="plus">
                    Nueva Factura
                </flux:button>
            </x-slot:action>
        </x-agro.empty-state>
    @endif

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
                    <flux:button wire:click="closeCorrectiveModal" variant="ghost" size="sm">Cancelar</flux:button>
                    <flux:button wire:click="confirmCorrective" wire:loading.attr="disabled" variant="primary" size="sm" icon="arrow-uturn-left">
                        <span wire:loading.remove wire:target="confirmCorrective">Emitir Rectificativa</span>
                        <span wire:loading wire:target="confirmCorrective">Generando...</span>
                    </flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
