<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Facturas / Pedidos"
        description="Gestiona tus facturas y pedidos"
    />

    {{-- Toolbar --}}
    <div class="space-y-3">
        <div class="flex items-center gap-3">

            <div class="flex-1 relative">
                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                    <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
                </div>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Buscar por nº factura, albarán o cliente..."
                    class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
                />
            </div>

            @php $filterCount = ($filterStatus ? 1 : 0) + ($filterPaymentStatus ? 1 : 0); @endphp
            <button
                x-on:click="$dispatch('open-modal', 'invoice-filters')"
                class="relative inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-50 shadow-sm transition-colors"
            >
                <flux:icon icon="adjustments-horizontal" class="size-4 text-zinc-500" />
                Filtros
                @if($filterCount > 0)
                    <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-agro-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">
                        {{ $filterCount }}
                    </span>
                @endif
            </button>

            <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

            <flux:button href="{{ route('viticulturist.invoices.harvest.index') }}" variant="outline" icon="archive-box">
                Por Cosecha
            </flux:button>

            <flux:button href="{{ route('viticulturist.invoices.create') }}" variant="primary" icon="plus">
                Nueva Factura
            </flux:button>

        </div>

        {{-- Active filter chips --}}
        @if($search || $filterStatus || $filterPaymentStatus)
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs text-zinc-400">Filtros activos:</span>

                @if($search)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                        <flux:icon icon="magnifying-glass" class="size-3" />
                        "{{ $search }}"
                        <button wire:click="$set('search', '')" class="hover:text-agro-900 ml-0.5">
                            <flux:icon icon="x-mark" class="size-3" />
                        </button>
                    </span>
                @endif

                @if($filterStatus)
                    @php $statusLabels = ['draft' => 'Borrador', 'sent' => 'Enviada', 'paid' => 'Pagada', 'cancelled' => 'Cancelada']; @endphp
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                        Estado: {{ $statusLabels[$filterStatus] ?? $filterStatus }}
                        <button wire:click="$set('filterStatus', '')" class="hover:text-agro-900 ml-0.5">
                            <flux:icon icon="x-mark" class="size-3" />
                        </button>
                    </span>
                @endif

                @if($filterPaymentStatus)
                    @php $payLabels = ['unpaid' => 'Pendiente', 'partial' => 'Parcial', 'paid' => 'Pagado', 'overdue' => 'Vencido']; @endphp
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                        Pago: {{ $payLabels[$filterPaymentStatus] ?? $filterPaymentStatus }}
                        <button wire:click="$set('filterPaymentStatus', '')" class="hover:text-agro-900 ml-0.5">
                            <flux:icon icon="x-mark" class="size-3" />
                        </button>
                    </span>
                @endif

                <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 underline">
                    Limpiar todo
                </button>
            </div>
        @endif
    </div>

    {{-- Card grid --}}
    @if($invoices->count() > 0)
        <div
            class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="search, filterStatus, filterPaymentStatus, clearFilters"
        >
            @foreach($invoices as $i => $invoice)
                @php
                    $btnBase = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors';

                    [$deliveryLabel, $deliveryColor] = match($invoice->delivery_status) {
                        'pending'    => ['Pendiente',   'yellow'],
                        'in_transit' => ['En Tránsito', 'blue'],
                        'delivered'  => ['Entregado',   'green'],
                        'cancelled'  => ['Cancelado',   'red'],
                        default      => [ucfirst($invoice->delivery_status ?? ''), null],
                    };

                    [$paymentLabel, $paymentColor] = match($invoice->payment_status) {
                        'paid'    => ['Pagado',    'green'],
                        'overdue' => ['Vencido',   'red'],
                        'partial' => ['Parcial',   'blue'],
                        'unpaid'  => ['Pendiente', 'yellow'],
                        default   => [ucfirst($invoice->payment_status ?? ''), null],
                    };

                    $totalKilos = $invoice->items->sum('quantity');
                @endphp

                <x-agro.card
                    wire:key="invoice-{{ $invoice->id }}"
                    class="animate-fade-in-up hover:-translate-y-1"
                    style="animation-delay: {{ min($i * 50, 400) }}ms"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-zinc-100 rounded-full flex items-center justify-center shrink-0">
                                <flux:icon icon="document-text" class="size-4 text-zinc-500" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-zinc-900 text-sm truncate leading-tight">
                                    {{ $invoice->invoice_number ?? 'Sin número' }}
                                </p>
                                @if($invoice->delivery_note_code)
                                    <p class="text-xs text-zinc-400 leading-tight mt-0.5">Albarán: {{ $invoice->delivery_note_code }}</p>
                                @endif
                            </div>
                            <flux:badge :color="$paymentColor" size="sm" class="shrink-0">{{ $paymentLabel }}</flux:badge>
                        </div>
                    </x-slot:header>

                    {{-- Cliente --}}
                    <div class="flex items-center gap-2 mb-3">
                        <flux:icon icon="user" class="size-3.5 text-zinc-400 shrink-0" />
                        <span class="text-xs text-zinc-600 truncate">{{ $invoice->client->full_name }}</span>
                    </div>

                    {{-- Total + Kilos --}}
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <div class="bg-agro-50 rounded-xl p-2.5">
                            <p class="text-[10px] text-agro-600 font-medium uppercase tracking-wide mb-0.5">Total</p>
                            <p class="text-sm font-bold text-agro-700">{{ number_format($invoice->total_amount, 2) }} €</p>
                        </div>
                        <div class="bg-zinc-50 rounded-xl p-2.5">
                            <p class="text-[10px] text-zinc-500 font-medium uppercase tracking-wide mb-0.5">Kilos</p>
                            <p class="text-sm font-bold text-zinc-700">
                                {{ $totalKilos > 0 ? number_format($totalKilos, 2) . ' kg' : '—' }}
                            </p>
                        </div>
                    </div>

                    {{-- Fechas --}}
                    <div class="flex items-center gap-3 text-xs text-zinc-500">
                        @if($invoice->order_date)
                            <span>Pedido: {{ $invoice->order_date->format('d/m/Y') }}</span>
                        @endif
                        @if($invoice->payment_status === 'paid' && $invoice->payment_date)
                            <span>· Pago: {{ $invoice->payment_date->format('d/m/Y') }}</span>
                        @endif
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-between">
                            <flux:badge :color="$deliveryColor" size="sm">{{ $deliveryLabel }}</flux:badge>
                            <div class="flex items-center gap-1">
                                <a href="{{ route('viticulturist.invoices.show', $invoice->id) }}" class="{{ $btnBase }}" title="Ver factura">
                                    <flux:icon icon="eye" class="size-4" />
                                </a>
                                <a href="{{ route('viticulturist.invoices.edit', $invoice->id) }}" class="{{ $btnBase }}" title="Editar">
                                    <flux:icon icon="pencil-square" class="size-4" />
                                </a>
                            </div>
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        @if($invoices->hasPages())
            <div class="flex justify-center">{{ $invoices->links() }}</div>
        @endif

    @else
        <x-agro.empty-state
            icon="document-text"
            message="No hay facturas"
            description="{{ $search || $filterStatus || $filterPaymentStatus ? 'Ninguna factura coincide con los filtros aplicados.' : 'Crea tu primera factura para empezar a gestionar tu facturación.' }}"
        >
            @if($search || $filterStatus || $filterPaymentStatus)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                </x-slot:action>
            @else
                <x-slot:action>
                    <flux:button href="{{ route('viticulturist.invoices.create') }}" variant="primary" icon="plus">
                        Nueva Factura
                    </flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @endif

    {{-- Modal Filtros --}}
    <x-agro.modal name="invoice-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'invoice-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-5">
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Estado de entrega</label>
                <select wire:model.live="filterStatus"
                        class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent">
                    <option value="">Todos</option>
                    <option value="draft">Borrador</option>
                    <option value="sent">Enviada</option>
                    <option value="paid">Pagada</option>
                    <option value="cancelled">Cancelada</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Estado de pago</label>
                <select wire:model.live="filterPaymentStatus"
                        class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent">
                    <option value="">Todos</option>
                    <option value="unpaid">Pendiente</option>
                    <option value="partial">Parcial</option>
                    <option value="paid">Pagado</option>
                    <option value="overdue">Vencido</option>
                </select>
            </div>
        </div>

        <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 flex items-center justify-between rounded-b-2xl">
            <button wire:click="clearFilters" x-on:click="$dispatch('close-modal', 'invoice-filters')"
                    class="text-sm text-zinc-500 hover:text-zinc-700 transition-colors">
                Limpiar filtros
            </button>
            <flux:button x-on:click="$dispatch('close-modal', 'invoice-filters')" variant="primary" size="sm">
                Aplicar
            </flux:button>
        </div>
    </x-agro.modal>

</div>
