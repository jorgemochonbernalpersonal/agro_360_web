<div>
    <x-agro.page-header title="Liquidaciones de Vendimia" description="Pagos a viticultores por la uva recibida">
        <x-slot name="actions">
            <flux:button href="{{ route('winery.invoices.grape-purchase.create') }}" wire:navigate variant="primary" icon="plus">
                Nueva Liquidación
            </flux:button>
        </x-slot>
    </x-agro.page-header>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <x-agro.stat-card label="Total liquidaciones" :value="$stats['total']"                             icon="document-text" />
        <x-agro.stat-card label="Pendientes de pago"  :value="$stats['pending']"                          icon="clock"         color="orange" />
        <x-agro.stat-card label="Pagadas"              :value="$stats['paid']"                             icon="check-circle"  color="green" />
        <x-agro.stat-card label="Importe total"        :value="number_format($stats['amount'], 2) . ' €'"  icon="banknotes"     color="blue" />
    </div>

    <!-- Filters -->
    <x-agro.filter-bar>
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por nº, ref. o viticultor..." icon="magnifying-glass" clearable />

        <flux:select wire:model.live="viticulturistFilter" class="w-48">
            <option value="">Todos los viticultores</option>
            @foreach ($viticulturists as $v)
                <option value="{{ $v->id }}">{{ $v->name }}</option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="paymentFilter" class="w-44">
            <option value="">Todos los pagos</option>
            <option value="unpaid">Pendiente</option>
            <option value="paid">Pagada</option>
        </flux:select>

        @if ($search || $viticulturistFilter || $paymentFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm">Limpiar</flux:button>
        @endif
    </x-agro.filter-bar>

    <!-- Table -->
    <x-agro.table>
        <x-slot name="head">
            <x-agro.th>Nº / Ref.</x-agro.th>
            <x-agro.th>Fecha</x-agro.th>
            <x-agro.th>Viticultor</x-agro.th>
            <x-agro.th>Total</x-agro.th>
            <x-agro.th>Estado</x-agro.th>
            <x-agro.th class="text-right">Acciones</x-agro.th>
        </x-slot>

        @forelse ($invoices as $invoice)
            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 {{ $invoice->status === 'cancelled' ? 'opacity-60' : '' }}">
                <td class="px-4 py-3">
                    <div class="font-mono font-medium text-zinc-900 dark:text-zinc-100">{{ $invoice->invoice_number }}</div>
                    @if ($invoice->delivery_note_code)
                        <div class="text-xs text-zinc-400 font-mono">{{ $invoice->delivery_note_code }}</div>
                    @endif
                </td>

                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400 text-sm">
                    {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}
                </td>

                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">
                    {{ $invoice->viticulturist?->name ?? '—' }}
                </td>

                <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">
                    {{ number_format($invoice->total_amount, 2) }} €
                </td>

                <td class="px-4 py-3">
                    @if ($invoice->status === 'cancelled')
                        <flux:badge color="red" size="sm">Cancelada</flux:badge>
                    @elseif ($invoice->payment_status === 'paid')
                        <flux:badge color="green" size="sm">Pagada</flux:badge>
                    @else
                        <flux:badge color="orange" size="sm">Pendiente</flux:badge>
                    @endif
                </td>

                <td class="px-4 py-3 text-right">
                    <div class="flex justify-end gap-2 flex-wrap">
                        {{-- Editar (solo si no cancelada ni pagada) --}}
                        @if ($invoice->status !== 'cancelled' && $invoice->payment_status !== 'paid')
                            <flux:button href="{{ route('winery.invoices.grape-purchase.edit', $invoice->id) }}"
                                wire:navigate size="sm" variant="ghost" icon="pencil" title="Editar" />
                        @endif

                        {{-- PDF Liquidación --}}
                        @if ($invoice->delivery_note_code)
                            <a href="{{ route('winery.invoices.grape-purchase.delivery-note-pdf', $invoice->id) }}"
                               target="_blank" title="Descargar liquidación PDF">
                                <flux:button size="sm" variant="ghost" icon="document-arrow-down" />
                            </a>
                        @endif

                        {{-- PDF Factura --}}
                        @if ($invoice->invoice_number)
                            <a href="{{ route('winery.invoices.grape-purchase.pdf', $invoice->id) }}"
                               target="_blank" title="Descargar factura PDF">
                                <flux:button size="sm" variant="ghost" icon="document-text" />
                            </a>
                        @endif

                        {{-- Enviar por email al viticultor --}}
                        @if ($invoice->status !== 'cancelled' && $invoice->viticulturist?->email)
                            <flux:button wire:click="sendEmail({{ $invoice->id }})"
                                wire:loading.attr="disabled"
                                wire:target="sendEmail({{ $invoice->id }})"
                                size="sm" variant="ghost" icon="envelope" title="Enviar al viticultor" />
                        @endif

                        @if ($invoice->status !== 'cancelled' && $invoice->payment_status !== 'paid')
                            <flux:button wire:click="markPaid({{ $invoice->id }})"
                                wire:confirm="¿Marcar esta liquidación como pagada?"
                                size="sm" variant="ghost" icon="check" title="Marcar como pagada" />

                            <flux:button wire:click="cancel({{ $invoice->id }})"
                                wire:confirm="¿Cancelar esta liquidación? Las recepciones quedarán disponibles para una nueva liquidación."
                                size="sm" variant="ghost" icon="x-mark" class="text-red-500" title="Cancelar" />
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <x-agro.empty-row colspan="6" message="No hay liquidaciones de vendimia registradas." />
        @endforelse
    </x-agro.table>

    <div class="mt-4">{{ $invoices->links() }}</div>
</div>
