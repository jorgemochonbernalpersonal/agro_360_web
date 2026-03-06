<div>
    <x-agro.page-header title="Facturas de Vino" description="Ventas de vino a clientes externos">
        <x-slot name="actions">
            <flux:button href="{{ route('winery.invoices.wine-sale.create') }}" wire:navigate variant="primary" icon="plus">
                Nueva Factura
            </flux:button>
        </x-slot>
    </x-agro.page-header>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <x-agro.stat-card label="Total facturas"     :value="$stats['total']"                             icon="document-text" />
        <x-agro.stat-card label="Pendientes de cobro" :value="$stats['pending']"                          icon="clock"         color="orange" />
        <x-agro.stat-card label="Cobradas"            :value="$stats['paid']"                             icon="check-circle"  color="green" />
        <x-agro.stat-card label="Entregadas"          :value="$stats['delivered']"                        icon="truck"         color="blue" />
        <x-agro.stat-card label="Importe total"       :value="number_format($stats['amount'], 2) . ' €'"  icon="banknotes"     color="purple" />
    </div>

    <!-- Filters -->
    <x-agro.filter-bar>
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por nº, albarán o cliente..." icon="magnifying-glass" clearable />

        <flux:select wire:model.live="statusFilter" class="w-44">
            <option value="">Todos los estados</option>
            <option value="draft">Borrador</option>
            <option value="sent">Enviada</option>
            <option value="cancelled">Cancelada</option>
        </flux:select>

        <flux:select wire:model.live="paymentFilter" class="w-44">
            <option value="">Todos los cobros</option>
            <option value="unpaid">Pendiente</option>
            <option value="partial">Parcial</option>
            <option value="paid">Cobrada</option>
        </flux:select>

        <flux:select wire:model.live="deliveryFilter" class="w-44">
            <option value="">Todas las entregas</option>
            <option value="pending">Pendiente</option>
            <option value="delivered">Entregada</option>
            <option value="cancelled">Cancelada</option>
        </flux:select>

        @if ($search || $statusFilter || $paymentFilter || $deliveryFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm">Limpiar</flux:button>
        @endif
    </x-agro.filter-bar>

    <!-- Table -->
    <x-agro.table>
        <x-slot name="head">
            <x-agro.th>Nº / Albarán</x-agro.th>
            <x-agro.th>Fecha</x-agro.th>
            <x-agro.th>Cliente</x-agro.th>
            <x-agro.th>Total</x-agro.th>
            <x-agro.th>Cobro</x-agro.th>
            <x-agro.th>Entrega</x-agro.th>
            <x-agro.th class="text-right">Acciones</x-agro.th>
        </x-slot>

        @forelse ($invoices as $invoice)
            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 {{ $invoice->status === 'cancelled' ? 'opacity-60' : '' }}">
                <!-- Nº factura + albarán -->
                <td class="px-4 py-3">
                    <div class="flex items-center gap-1.5">
                        <span class="font-mono font-medium text-zinc-900 dark:text-zinc-100">{{ $invoice->invoice_number }}</span>
                        @if ($invoice->corrective)
                            <span class="text-[10px] font-bold text-orange-600 bg-orange-50 px-1.5 py-0.5 rounded" title="Factura rectificativa">R/</span>
                        @endif
                        @if ($invoice->correctives_count > 0)
                            <span class="text-[10px] font-medium text-orange-600 bg-orange-50 px-1.5 py-0.5 rounded" title="Tiene rectificativa">RECT</span>
                        @endif
                    </div>
                    @if ($invoice->delivery_note_code)
                        <div class="text-xs text-zinc-400 font-mono">{{ $invoice->delivery_note_code }}</div>
                    @endif
                </td>

                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400 text-sm">
                    {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}
                </td>

                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">
                    {{ $invoice->client?->full_name ?? '—' }}
                </td>

                <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">
                    {{ number_format($invoice->total_amount, 2) }} €
                </td>

                <!-- Estado de cobro -->
                <td class="px-4 py-3">
                    @switch($invoice->payment_status)
                        @case('unpaid')  <flux:badge color="orange" size="sm">Pendiente</flux:badge> @break
                        @case('partial') <flux:badge color="yellow" size="sm">Parcial</flux:badge> @break
                        @case('paid')    <flux:badge color="green"  size="sm">Cobrada</flux:badge> @break
                        @default         <flux:badge size="sm">—</flux:badge>
                    @endswitch
                </td>

                <!-- Estado de entrega -->
                <td class="px-4 py-3">
                    @switch($invoice->delivery_status)
                        @case('pending')    <flux:badge color="zinc"   size="sm">Pendiente</flux:badge> @break
                        @case('in_transit') <flux:badge color="blue"   size="sm">En tránsito</flux:badge> @break
                        @case('delivered')  <flux:badge color="green"  size="sm">Entregada</flux:badge> @break
                        @case('cancelled')  <flux:badge color="red"    size="sm">Cancelada</flux:badge> @break
                        @default            <flux:badge size="sm">—</flux:badge>
                    @endswitch
                </td>

                <!-- Acciones -->
                <td class="px-4 py-3 text-right">
                    <div class="flex justify-end gap-2 flex-wrap">
                        {{-- Editar (solo si no entregada ni cancelada) --}}
                        @if ($invoice->status !== 'cancelled' && $invoice->delivery_status !== 'delivered')
                            <flux:button href="{{ route('winery.invoices.wine-sale.edit', $invoice->id) }}"
                                wire:navigate size="sm" variant="ghost" icon="pencil" title="Editar" />
                        @endif

                        {{-- PDF Albarán --}}
                        @if ($invoice->delivery_note_code)
                            <a href="{{ route('winery.invoices.wine-sale.delivery-note-pdf', $invoice->id) }}"
                               target="_blank" title="Descargar albarán PDF">
                                <flux:button size="sm" variant="ghost" icon="document-arrow-down" />
                            </a>
                        @endif

                        {{-- PDF Albarán Valorado --}}
                        @if ($invoice->invoice_number || $invoice->delivery_note_code)
                            <a href="{{ route('winery.invoices.wine-sale.valorado-pdf', $invoice->id) }}"
                               target="_blank" title="Descargar albarán valorado (con precios)">
                                <flux:button size="sm" variant="ghost" icon="currency-euro" />
                            </a>
                        @endif

                        {{-- PDF Factura --}}
                        @if ($invoice->invoice_number)
                            <a href="{{ route('winery.invoices.wine-sale.pdf', $invoice->id) }}"
                               target="_blank" title="Descargar factura PDF">
                                <flux:button size="sm" variant="ghost" icon="document-text" />
                            </a>
                        @endif

                        {{-- Rectificativa (solo facturas emitidas sin rectificativa previa) --}}
                        @if ($invoice->status === 'sent' && !$invoice->corrective && $invoice->correctives_count === 0)
                            <flux:button wire:click="openCorrectiveModal({{ $invoice->id }})"
                                size="sm" variant="ghost" icon="arrow-uturn-left"
                                class="text-orange-400 hover:text-orange-600"
                                title="Crear factura rectificativa" />
                        @endif

                        {{-- Enviar por email --}}
                        @if ($invoice->status !== 'cancelled' && ($invoice->billing_email ?: $invoice->client?->email))
                            <flux:button wire:click="sendEmail({{ $invoice->id }})"
                                wire:loading.attr="disabled"
                                wire:target="sendEmail({{ $invoice->id }})"
                                size="sm" variant="ghost" icon="envelope" title="Enviar por email" />
                        @endif

                        {{-- Marcar como cobrada --}}
                        @if ($invoice->payment_status !== 'paid' && $invoice->status !== 'cancelled')
                            <flux:button wire:click="markPaid({{ $invoice->id }})"
                                wire:confirm="¿Marcar esta factura como cobrada?"
                                size="sm" variant="ghost" icon="banknotes" title="Marcar como cobrada" />
                        @endif

                        {{-- Marcar como entregada (reserved → sold) --}}
                        @if (in_array($invoice->delivery_status, ['pending', 'in_transit']) && $invoice->status !== 'cancelled')
                            <flux:button wire:click="markDelivered({{ $invoice->id }})"
                                wire:confirm="¿Marcar como entregada? El stock pasará a 'vendido' de forma definitiva."
                                size="sm" variant="ghost" icon="truck" title="Marcar como entregada" />
                        @endif

                        {{-- Cancelar --}}
                        @if ($invoice->status !== 'cancelled' && $invoice->payment_status !== 'paid')
                            <flux:button wire:click="cancel({{ $invoice->id }})"
                                wire:confirm="¿Cancelar esta factura? El stock será restaurado automáticamente."
                                size="sm" variant="ghost" icon="x-mark" class="text-red-500" title="Cancelar" />
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <x-agro.empty-row colspan="7" message="No hay facturas de vino registradas." />
        @endforelse
    </x-agro.table>

    <div class="mt-4">{{ $invoices->links() }}</div>

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
                    <flux:button wire:click="closeCorrectiveModal" variant="ghost" size="sm" icon="x-mark" />
                </div>

                <div class="px-6 py-5 space-y-4">
                    <p class="text-sm text-zinc-500">
                        Se creará una factura rectificativa con importes negativos. El stock de vino quedará restaurado automáticamente.
                    </p>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">
                            Fecha de la rectificativa <span class="text-red-500">*</span>
                        </label>
                        <input
                            wire:model="correctiveDate"
                            type="date"
                            class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent"
                        />
                        @error('correctiveDate')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">
                            Motivo <span class="text-zinc-400 font-normal">(opcional)</span>
                        </label>
                        <textarea
                            wire:model="correctiveReason"
                            rows="2"
                            placeholder="Error en precio, devolución, etc."
                            class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent resize-none"
                        ></textarea>
                        @error('correctiveReason')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
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
                        class="bg-orange-600 hover:bg-orange-700 focus:ring-orange-500"
                    >
                        <span wire:loading.remove wire:target="confirmCorrective">Emitir Rectificativa</span>
                        <span wire:loading wire:target="confirmCorrective">Generando...</span>
                    </flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
