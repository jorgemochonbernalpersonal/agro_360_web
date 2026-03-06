<div class="space-y-6 animate-fade-in">
    <x-agro.page-header title="Liquidaciones de Vendimia" description="Pagos a viticultores por la uva recibida">
        <x-slot:actions>
            <flux:button href="{{ route('winery.invoices.grape-purchase.create') }}" wire:navigate variant="primary" icon="plus">
                Nueva Liquidación
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <x-agro.stat-card label="Total liquidaciones" :value="$stats['total']"                             icon="document-text" />
        <x-agro.stat-card label="Pendientes de pago"  :value="$stats['pending']"                          icon="clock"         color="orange" />
        <x-agro.stat-card label="Pagadas"              :value="$stats['paid']"                             icon="check-circle"  color="green" />
        <x-agro.stat-card label="Importe total"        :value="number_format($stats['amount'], 2) . ' €'"  icon="banknotes"     color="blue" />
    </div>

    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar por nº, ref. o viticultor..."
        />
        <flux:select wire:model.live="viticulturistFilter" size="sm" class="w-48">
            <flux:select.option value="">Todos los viticultores</flux:select.option>
            @foreach ($viticulturists as $v)
                <flux:select.option value="{{ $v->id }}">{{ $v->name }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="paymentFilter" size="sm" class="w-44">
            <flux:select.option value="">Todos los pagos</flux:select.option>
            <flux:select.option value="unpaid">Pendiente</flux:select.option>
            <flux:select.option value="paid">Pagada</flux:select.option>
        </flux:select>
        @if ($search || $viticulturistFilter || $paymentFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                Limpiar
            </flux:button>
        @endif
    </x-agro.filter-bar>

    <x-agro.data-table
        :headers="['Nº / Ref.', 'Fecha', 'Viticultor', 'Total', 'Estado', 'Acciones']"
        empty-message="No hay liquidaciones de vendimia registradas"
        empty-description="Crea la primera liquidación para pagar a los viticultores"
    >
        @if ($invoices->count() > 0)
            @foreach ($invoices as $invoice)
                <x-agro.table-row class="{{ $invoice->status === 'cancelled' ? 'opacity-60' : '' }}">
                    <x-agro.table-cell>
                        <p class="font-mono font-medium text-zinc-900">{{ $invoice->invoice_number }}</p>
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
                        <span class="text-sm text-zinc-700">{{ $invoice->viticulturist?->name ?? '—' }}</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm font-medium text-zinc-900">{{ number_format($invoice->total_amount, 2) }} €</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @if ($invoice->status === 'cancelled')
                            <flux:badge color="red" size="sm">Cancelada</flux:badge>
                        @elseif ($invoice->payment_status === 'paid')
                            <flux:badge color="green" size="sm">Pagada</flux:badge>
                        @else
                            <flux:badge color="orange" size="sm">Pendiente</flux:badge>
                        @endif
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <div class="flex items-center justify-end gap-1 flex-wrap">
                            @if ($invoice->status !== 'cancelled' && $invoice->payment_status !== 'paid')
                                <a href="{{ route('winery.invoices.grape-purchase.edit', $invoice->id) }}" wire:navigate title="Editar">
                                    <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                        <flux:icon icon="pencil" class="size-4" />
                                    </button>
                                </a>
                            @endif

                            @if ($invoice->delivery_note_code)
                                <a href="{{ route('winery.invoices.grape-purchase.delivery-note-pdf', $invoice->id) }}" target="_blank" title="Descargar liquidación PDF">
                                    <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                        <flux:icon icon="document-arrow-down" class="size-4" />
                                    </button>
                                </a>
                            @endif

                            @if ($invoice->invoice_number)
                                <a href="{{ route('winery.invoices.grape-purchase.pdf', $invoice->id) }}" target="_blank" title="Descargar factura PDF">
                                    <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                        <flux:icon icon="document-text" class="size-4" />
                                    </button>
                                </a>
                            @endif

                            @if ($invoice->status !== 'cancelled' && $invoice->viticulturist?->email)
                                <button
                                    wire:click="sendEmail({{ $invoice->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="sendEmail({{ $invoice->id }})"
                                    title="Enviar al viticultor"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors"
                                >
                                    <flux:icon icon="envelope" class="size-4" />
                                </button>
                            @endif

                            @if ($invoice->status !== 'cancelled' && $invoice->payment_status !== 'paid')
                                <button
                                    wire:click="markPaid({{ $invoice->id }})"
                                    wire:confirm="¿Marcar esta liquidación como pagada?"
                                    title="Marcar como pagada"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors"
                                >
                                    <flux:icon icon="check" class="size-4" />
                                </button>
                                <button
                                    wire:click="cancel({{ $invoice->id }})"
                                    wire:confirm="¿Cancelar esta liquidación? Las recepciones quedarán disponibles para una nueva liquidación."
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
</div>
