<div class="space-y-6 animate-fade-in">
    <x-agro.page-header title="Liquidaciones de Vendimia" description="Pagos a viticultores por la uva recibida">
        <x-slot:actions>
            <flux:button href="{{ route('winery.invoices.grape-purchase.create') }}" wire:navigate variant="primary" icon="plus">
                Nueva Liquidación
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

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

    @if ($invoices->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($invoices as $invoice)
                @php $delay = min($loop->index * 50, 300); @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col {{ $invoice->status === 'cancelled' ? 'opacity-60' : '' }}"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="gp-invoice-{{ $invoice->id }}"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-agro-50 flex items-center justify-center shrink-0">
                                <flux:icon icon="document-text" class="size-5 text-agro-600" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-mono font-bold text-zinc-900 truncate">
                                    {{ $invoice->invoice_number ?? 'Sin código de factura' }}
                                </h3>
                                @if ($invoice->delivery_note_code)
                                    <p class="text-xs text-zinc-400 font-mono truncate">{{ $invoice->delivery_note_code }}</p>
                                @else
                                    <p class="text-xs text-zinc-400">
                                        {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}
                                    </p>
                                @endif
                            </div>
                            @if ($invoice->status === 'cancelled')
                                <flux:badge color="red" size="sm" class="shrink-0">Cancelada</flux:badge>
                            @elseif ($invoice->payment_status === 'paid')
                                <flux:badge color="green" size="sm" class="shrink-0">Pagada</flux:badge>
                            @else
                                <flux:badge color="orange" size="sm" class="shrink-0">Pendiente</flux:badge>
                            @endif
                        </div>
                    </x-slot:header>

                    <div class="flex-1 space-y-3 text-sm">
                        <div class="flex items-center gap-2 text-zinc-600">
                            <flux:icon icon="user" class="size-4 text-zinc-400 shrink-0" />
                            <span class="truncate font-medium">{{ $invoice->viticulturist?->name ?? '—' }}</span>
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
                        <div class="flex items-center justify-end gap-1 flex-wrap">
                            @if ($invoice->status !== 'cancelled' && $invoice->payment_status !== 'paid')
                                <a href="{{ route('winery.invoices.grape-purchase.edit', $invoice->id) }}" wire:navigate title="Editar">
                                    <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                        <flux:icon icon="pencil" class="size-4" />
                                    </button>
                                </a>
                            @endif

                            <a href="{{ route('winery.invoices.grape-purchase.pdf', $invoice->id) }}" target="_blank" title="Descargar Liquidación">
                                <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                    <flux:icon icon="document-text" class="size-4" />
                                </button>
                            </a>

                            <a href="{{ route('winery.invoices.grape-purchase.delivery-note-pdf', $invoice->id) }}" target="_blank" title="Descargar Albarán">
                                <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                    <flux:icon icon="document-arrow-down" class="size-4" />
                                </button>
                            </a>

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
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-green-600 hover:bg-green-50 transition-colors"
                                >
                                    <flux:icon icon="check-circle" class="size-4" />
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
            title="No hay liquidaciones de vendimia registradas"
            description="Crea la primera liquidación para pagar a los viticultores"
        >
            <x-slot:action>
                <flux:button href="{{ route('winery.invoices.grape-purchase.create') }}" wire:navigate variant="primary" icon="plus">
                    Nueva Liquidación
                </flux:button>
            </x-slot:action>
        </x-agro.empty-state>
    @endif
</div>
