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
                                <div class="flex items-center gap-1.5">
                                    <p class="font-semibold text-zinc-900 text-sm truncate leading-tight">
                                        {{ $invoice->invoice_number ?? 'Sin número' }}
                                    </p>
                                    @if($invoice->corrective)
                                        <span class="text-[10px] font-bold text-orange-600 bg-orange-50 px-1.5 py-0.5 rounded shrink-0" title="Factura rectificativa">R/</span>
                                    @endif
                                </div>
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
                        <div class="flex items-center justify-between gap-2">
                            <flux:badge :color="$deliveryColor" size="sm" class="shrink-0">{{ $deliveryLabel }}</flux:badge>
                            <div class="flex items-center gap-1 flex-wrap justify-end">

                                {{-- Ver --}}
                                <a href="{{ route('viticulturist.invoices.show', $invoice->id) }}"
                                   class="{{ $btnBase }}" title="Ver factura">
                                    <flux:icon icon="eye" class="size-4" />
                                </a>

                                {{-- Editar (no canceladas ni entregadas) --}}
                                @if($invoice->isEditable())
                                    <a href="{{ route('viticulturist.invoices.edit', $invoice->id) }}"
                                       class="{{ $btnBase }}" title="Editar">
                                        <flux:icon icon="pencil-square" class="size-4" />
                                    </a>
                                @endif

                                {{-- PDF Albarán --}}
                                @if($invoice->delivery_note_code)
                                    <a href="{{ route('viticulturist.invoices.delivery-note-pdf', $invoice->id) }}"
                                       class="{{ $btnBase }}" title="Descargar albarán PDF" target="_blank">
                                        <flux:icon icon="document-arrow-down" class="size-4" />
                                    </a>
                                @endif

                                {{-- PDF Factura --}}
                                @if($invoice->invoice_number)
                                    <a href="{{ route('viticulturist.invoices.pdf', $invoice->id) }}"
                                       class="{{ $btnBase }}" title="Descargar factura PDF" target="_blank">
                                        <flux:icon icon="document-text" class="size-4" />
                                    </a>
                                @endif

                                {{-- Emitir (solo draft) --}}
                                @if($invoice->status === 'draft')
                                    <button wire:click="openEmitirModal({{ $invoice->id }})"
                                            class="{{ $btnBase }} text-agro-500 hover:text-agro-700 hover:bg-agro-50"
                                            title="Emitir factura">
                                        <flux:icon icon="paper-airplane" class="size-4" />
                                    </button>
                                @endif

                                {{-- Marcar pagada --}}
                                @if($invoice->status !== 'cancelled' && $invoice->payment_status !== 'paid')
                                    <button wire:click="markPaid({{ $invoice->id }})"
                                            wire:confirm="¿Marcar esta factura como pagada?"
                                            class="{{ $btnBase }} text-green-500 hover:text-green-700 hover:bg-green-50"
                                            title="Marcar como pagada">
                                        <flux:icon icon="banknotes" class="size-4" />
                                    </button>
                                @endif

                                {{-- Enviar por email (emitidas con email) --}}
                                @if($invoice->status === 'sent' && ($invoice->billing_email ?: $invoice->client?->email))
                                    <button wire:click="sendEmail({{ $invoice->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="sendEmail({{ $invoice->id }})"
                                            class="{{ $btnBase }} text-blue-500 hover:text-blue-700 hover:bg-blue-50"
                                            title="Enviar por email">
                                        <flux:icon icon="envelope" class="size-4" />
                                    </button>
                                @endif

                                {{-- Rectificativa (facturas emitidas sin rectificativa previa) --}}
                                @if($invoice->status === 'sent' && !$invoice->corrective && $invoice->correctives_count === 0)
                                    <button wire:click="openCorrectiveModal({{ $invoice->id }})"
                                            class="{{ $btnBase }} text-orange-400 hover:text-orange-600 hover:bg-orange-50"
                                            title="Crear factura rectificativa">
                                        <flux:icon icon="arrow-uturn-left" class="size-4" />
                                    </button>
                                @endif

                                {{-- Badge rectificativa (si ya tiene una) --}}
                                @if($invoice->correctives_count > 0)
                                    <span class="text-[10px] font-medium text-orange-600 bg-orange-50 px-1.5 py-0.5 rounded" title="Tiene rectificativa">
                                        RECT
                                    </span>
                                @endif

                                {{-- Cancelar (solo draft) --}}
                                @if($invoice->status === 'draft')
                                    <button wire:click="cancel({{ $invoice->id }})"
                                            wire:confirm="¿Cancelar esta factura? El stock quedará liberado."
                                            class="{{ $btnBase }} text-red-400 hover:text-red-600 hover:bg-red-50"
                                            title="Cancelar factura">
                                        <flux:icon icon="x-circle" class="size-4" />
                                    </button>
                                @endif

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

    {{-- Modal Emitir Factura --}}
    @if($emitirModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             x-data x-on:keydown.escape.window="$wire.closeEmitirModal()">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm" @click.stop>
                <div class="px-6 py-4 border-b border-zinc-200 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                            <flux:icon icon="paper-airplane" class="size-4 text-agro-600" />
                        </div>
                        <h3 class="text-base font-semibold text-zinc-900">Emitir Factura</h3>
                    </div>
                    <flux:button wire:click="closeEmitirModal" variant="ghost" size="sm" icon="x-mark" />
                </div>

                <div class="px-6 py-5 space-y-4">
                    <p class="text-sm text-zinc-500">
                        Se generará un número de factura secuencial y la factura quedará emitida formalmente.
                    </p>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">
                            Fecha de factura <span class="text-red-500">*</span>
                        </label>
                        <input
                            wire:model="emitirDate"
                            type="date"
                            class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent"
                        />
                        @error('emitirDate')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 rounded-b-2xl flex items-center justify-end gap-3">
                    <flux:button wire:click="closeEmitirModal" variant="ghost" size="sm">
                        Cancelar
                    </flux:button>
                    <flux:button
                        wire:click="confirmEmitir"
                        wire:loading.attr="disabled"
                        variant="primary"
                        size="sm"
                        icon="paper-airplane"
                    >
                        <span wire:loading.remove wire:target="confirmEmitir">Emitir Factura</span>
                        <span wire:loading wire:target="confirmEmitir">Emitiendo...</span>
                    </flux:button>
                </div>
            </div>
        </div>
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
                    <flux:button wire:click="closeCorrectiveModal" variant="ghost" size="sm" icon="x-mark" />
                </div>

                <div class="px-6 py-5 space-y-4">
                    <p class="text-sm text-zinc-500">
                        Se creará una factura rectificativa con importes negativos. El stock de cosecha quedará restaurado como disponible.
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
