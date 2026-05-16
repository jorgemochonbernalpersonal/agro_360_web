<div class="space-y-6 animate-fade-in">
    <x-agro.page-header title="Liquidaciones de Vendimia" description="Pagos a viticultores por la uva recibida">
        <x-slot:actions>
            <flux:button href="{{ roleRoute('invoices.grape-purchase.create') }}" wire:navigate variant="primary" icon="plus">
                Nueva Liquidación
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 gap-4">
        <x-agro.stat-card
            label="Total liquidaciones"
            :value="$stats['total']"
            icon="document-text"
            color="zinc"
        />
        <x-agro.stat-card
            label="Pagadas"
            :value="$stats['paid']"
            icon="check-circle"
            color="agro"
        />
        <x-agro.stat-card
            label="Pendientes"
            :value="$stats['pending']"
            icon="clock"
            color="amber"
        />
        <x-agro.stat-card
            label="Importe pendiente"
            :value="number_format($stats['pending_amount'], 2) . ' €'"
            icon="currency-euro"
            color="zinc"
        />
    </div>

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">
        <x-agro.search-input wire:model.live.debounce.300ms="search" placeholder="Buscar por nº, ref. o viticultor..." />
        <flux:select wire:model.live="viticulturistFilter" class="w-48">
            <flux:select.option value="">Todos los viticultores</flux:select.option>
            @foreach ($viticulturists as $v)
                <flux:select.option value="{{ $v->id }}">{{ $v->name }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="paymentFilter" class="w-40">
            <flux:select.option value="">Todos los pagos</flux:select.option>
            <flux:select.option value="unpaid">Pendiente</flux:select.option>
            <flux:select.option value="paid">Pagada</flux:select.option>
        </flux:select>
        @if ($search || $viticulturistFilter || $paymentFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                Limpiar
            </flux:button>
        @endif
    </div>

    {{-- Loading skeleton --}}
    <x-agro.loading-grid target="search, viticulturistFilter, paymentFilter, clearFilters, nextPage, previousPage" />

    <div wire:loading.remove wire:target="search, viticulturistFilter, paymentFilter, clearFilters, nextPage, previousPage">
    @if ($invoices->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach ($invoices as $invoice)
                @php $delay = min($loop->index * 50, 300); @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col {{ $invoice->status === 'cancelled' ? 'opacity-60' : '' }}"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="gp-invoice-{{ $invoice->id }}"
                >
                    <x-slot:header>
                        <x-agro.card-item-header
                            icon="document-text"
                            :title="$invoice->invoice_number ?? 'Sin código de factura'"
                            :subtitle="$invoice->delivery_note_code ?? \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y')"
                            iconBg="bg-agro-50"
                            iconColor="text-agro-600"
                            size="md"
                            radius="xl"
                        >
                            @if ($invoice->status === 'cancelled')
                                <flux:badge color="red" size="sm">Cancelada</flux:badge>
                            @elseif ($invoice->payment_status === 'paid')
                                <flux:badge color="green" size="sm">Pagada</flux:badge>
                            @else
                                <flux:badge color="orange" size="sm">Pendiente</flux:badge>
                            @endif
                        </x-agro.card-item-header>
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
                                <x-agro.action-button variant="edit" href="{{ roleRoute('invoices.grape-purchase.edit', $invoice->id) }}" wire:navigate title="Editar" />
                            @endif

                            <x-agro.action-button icon="document-text" variant="default" href="{{ roleRoute('invoices.grape-purchase.pdf', $invoice->id) }}" target="_blank" title="Descargar Liquidación" />

                            <x-agro.action-button icon="document-arrow-down" variant="default" href="{{ roleRoute('invoices.grape-purchase.delivery-note-pdf', $invoice->id) }}" target="_blank" title="Descargar Albarán" />

                            @if ($invoice->status !== 'cancelled' && $invoice->viticulturist?->email)
                                <x-agro.action-button icon="envelope" variant="default" wire:click="sendEmail({{ $invoice->id }})" wire:loading.attr="disabled" wire:target="sendEmail({{ $invoice->id }})" title="Enviar al viticultor" />
                            @endif

                            @if ($invoice->status !== 'cancelled' && $invoice->payment_status !== 'paid')
                                <x-agro.action-button variant="activate" wire:click="markPaid({{ $invoice->id }})" wire:confirm="¿Marcar esta liquidación como pagada?" title="Marcar como pagada" />
                                <x-agro.action-button icon="x-mark" variant="danger" wire:click="cancel({{ $invoice->id }})" wire:confirm="¿Cancelar esta liquidación? Las recepciones quedarán disponibles para una nueva liquidación." title="Cancelar" />
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
                <flux:button href="{{ roleRoute('invoices.grape-purchase.create') }}" wire:navigate variant="primary" icon="plus">
                    Nueva Liquidación
                </flux:button>
            </x-slot:action>
        </x-agro.empty-state>
    @endif
    </div>
</div>
