<div>
<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        :title="__('Facturas / Pedidos')"
        :description="__('Gestiona tus facturas y pedidos')"
    />

    {{-- Stats --}}
    <x-agro.stats-section key="invoices">
        <x-agro.stat-card
            :label="__('Total facturas')"
            :value="$stats['total']"
            :description="__('Historial completo')"
            icon="document-text"
            color="agro"
        />
        <x-agro.stat-card
            :label="__('Emitidas')"
            :value="$stats['issued']"
            :description="__('Facturas en firme')"
            icon="check-circle"
            color="agro"
        />
        <x-agro.stat-card
            :label="__('Borradores')"
            :value="$stats['draft']"
            :description="__('Pendientes de emitir')"
            icon="pencil-square"
            color="orange"
        />
        <x-agro.stat-card
            :label="__('Pendiente cobro')"
            :value="number_format($stats['pending_amount'], 2) . ' €'"
            :description="__('Importe sin cobrar')"
            icon="banknotes"
            color="red"
        />
    </x-agro.stats-section>
    {{-- Toolbar --}}
    <div class="space-y-3">
        <div class="flex items-center gap-3">

            <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar por nº factura, albarán o cliente...')" />

            @php $filterCount = ($filterStatus ? 1 : 0) + ($filterPaymentStatus ? 1 : 0); @endphp
            <x-agro.filter-button modal="invoice-filters" :count="$filterCount" />

            <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

            <flux:button href="{{ roleRoute('viticulturist.invoices.harvest.index') }}" variant="outline" icon="archive-box">
                {{ __('Por Cosecha') }}
            </flux:button>

            <flux:button href="{{ roleRoute('viticulturist.invoices.create') }}" variant="primary" icon="plus">
                {{ __('Nueva Factura') }}
            </flux:button>

        </div>

        {{-- Active filter chips --}}
        @if($search || $filterStatus || $filterPaymentStatus)
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs text-zinc-400">{{ __('Filtros activos:') }}</span>

                @if($search)
                    <x-agro.filter-chip icon="magnifying-glass" :label="'&quot;' . $search . '&quot;'" wireRemove="$set('search', '')" />
                @endif

                @if($filterStatus)
                    @php $statusLabels = ['draft' => __('Borrador'), 'sent' => __('Enviada'), 'paid' => __('Pagada'), 'cancelled' => __('Cancelada')]; @endphp
                    <x-agro.filter-chip :label="__('Estado:') . ' ' . ($statusLabels[$filterStatus] ?? $filterStatus)" wireRemove="$set('filterStatus', '')" />
                @endif

                @if($filterPaymentStatus)
                    @php $payLabels = ['unpaid' => __('Pendiente'), 'partial' => __('Parcial'), 'paid' => __('Pagado'), 'overdue' => __('Vencido')]; @endphp
                    <x-agro.filter-chip :label="__('Pago:') . ' ' . ($payLabels[$filterPaymentStatus] ?? $filterPaymentStatus)" wireRemove="$set('filterPaymentStatus', '')" />
                @endif

                <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 underline">
                    {{ __('Limpiar todo') }}
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
                    [$deliveryLabel, $deliveryColor, $deliveryIcon] = match($invoice->delivery_status) {
                        'pending'    => [__('Pendiente'),   'yellow', 'clock'],
                        'in_transit' => [__('En tránsito'), 'blue',   'truck'],
                        'delivered'  => [__('Entregado'),   'green',  'check-circle'],
                        'cancelled'  => [__('Cancelado'),   'red',    'x-circle'],
                        default      => [ucfirst($invoice->delivery_status ?? ''), null, 'question-mark-circle'],
                    };

                    [$paymentLabel, $paymentColor, $paymentIcon] = match($invoice->payment_status) {
                        'paid'    => [__('Cobrado'),   'green',  'banknotes'],
                        'overdue' => [__('Vencido'),   'red',    'exclamation-circle'],
                        'partial' => [__('Parcial'),   'blue',   'adjustments-horizontal'],
                        'unpaid'  => [__('Sin cobrar'),'yellow', 'clock'],
                        default   => [ucfirst($invoice->payment_status ?? ''), null, 'question-mark-circle'],
                    };

                    $totalUnits = $invoice->items->sum('quantity');

                    // Identificador principal: factura > albarán > ID
                    $primaryId   = $invoice->invoice_number ?? $invoice->delivery_note_code ?? '#' . $invoice->id;
                    $isInvoice   = (bool) $invoice->invoice_number;
                    $hasAlbaran  = (bool) $invoice->delivery_note_code;
                    $docType     = $isInvoice ? __('Factura') : ($hasAlbaran ? __('Albarán') : __('Pedido'));
                    $docIcon     = $isInvoice ? 'document-text' : ($hasAlbaran ? 'document' : 'shopping-cart');
                    $docColor    = $isInvoice ? 'text-agro-600 bg-agro-100' : ($hasAlbaran ? 'text-blue-600 bg-blue-100' : 'text-zinc-500 bg-zinc-100');

                    // Fecha más relevante para mostrar
                    $primaryDate = $invoice->invoice_date ?? $invoice->order_date;
                @endphp

                <x-agro.card
                    wire:key="invoice-{{ $invoice->id }}"
                    class="animate-fade-in-up hover:-translate-y-1"
                    style="animation-delay: {{ min($i * 50, 400) }}ms"
                >
                    <x-slot:header>
                        <div class="flex items-start gap-3">
                            {{-- Icono tipo documento --}}
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 {{ $docColor }}">
                                <flux:icon icon="{{ $docIcon }}" class="size-4" />
                            </div>

                            {{-- Título: tipo + número --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">{{ $docType }}</span>
                                    @if($invoice->corrective)
                                        <span class="text-[10px] font-bold text-orange-600 bg-orange-50 px-1.5 py-0.5 rounded" :title="__('Factura rectificativa')">RECT</span>
                                    @endif
                                </div>
                                <p class="font-bold text-zinc-900 text-sm leading-tight truncate">{{ $primaryId }}</p>
                                {{-- Si hay factura Y albarán, mostrar el albarán como secundario --}}
                                @if($isInvoice && $hasAlbaran)
                                    <p class="text-xs text-zinc-400 leading-tight">{{ __('Alb.') }} {{ $invoice->delivery_note_code }}</p>
                                @elseif(!$isInvoice && !$hasAlbaran)
                                    <p class="text-xs text-zinc-400 leading-tight">{{ __('Sin número asignado') }}</p>
                                @endif
                            </div>
                        </div>

                        {{-- Badges de estado en fila --}}
                        <div class="flex items-center gap-1.5 mt-2.5 flex-wrap">
                            <flux:badge :color="$paymentColor" size="sm">{{ $paymentLabel }}</flux:badge>
                            <flux:badge :color="$deliveryColor" size="sm">{{ $deliveryLabel }}</flux:badge>
                            @if($invoice->correctives_count > 0)
                                <flux:badge color="orange" size="sm">{{ __('Rectificada') }}</flux:badge>
                            @endif
                        </div>
                    </x-slot:header>

                    {{-- Cliente --}}
                    <div class="flex items-center gap-2 mb-3">
                        <flux:icon icon="building-office" class="size-3.5 text-zinc-400 shrink-0" />
                        <span class="text-sm font-medium text-zinc-800 truncate">{{ $invoice->client->full_name }}</span>
                    </div>

                    {{-- Total + Unidades --}}
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <div class="bg-agro-50 rounded-xl p-3">
                            <p class="text-[10px] text-agro-600 font-semibold uppercase tracking-wide mb-1">{{ __('Importe') }}</p>
                            <p class="text-base font-bold text-agro-700 leading-tight">{{ number_format($invoice->total_amount, 2, ',', '.') }} €</p>
                        </div>
                        <div class="bg-zinc-50 rounded-xl p-3">
                            <p class="text-[10px] text-zinc-500 font-semibold uppercase tracking-wide mb-1">{{ __('Unidades') }}</p>
                            <p class="text-base font-bold text-zinc-700 leading-tight">
                                {{ $totalUnits > 0 ? number_format($totalUnits, 0, ',', '.') : '—' }}
                            </p>
                        </div>
                    </div>

                    {{-- Fechas --}}
                    <div class="flex flex-col gap-0.5 text-xs text-zinc-400">
                        @if($primaryDate)
                            <span>
                                <span class="font-medium text-zinc-500">{{ $isInvoice ? __('Fecha factura') : __('Fecha pedido') }}:</span>
                                {{ $primaryDate->format('d/m/Y') }}
                            </span>
                        @endif
                        @if($invoice->payment_status === 'paid' && $invoice->payment_date)
                            <span>
                                <span class="font-medium text-green-600">{{ __('Cobrado:') }}</span>
                                {{ $invoice->payment_date->format('d/m/Y') }}
                            </span>
                        @elseif($invoice->payment_status === 'overdue' && $invoice->due_date)
                            <span>
                                <span class="font-medium text-red-500">{{ __('Venció:') }}</span>
                                {{ $invoice->due_date->format('d/m/Y') }}
                            </span>
                        @endif
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-end gap-1 flex-wrap">

                                {{-- Ver --}}
                                <x-agro.action-button
                                    variant="view"
                                    href="{{ roleRoute('viticulturist.invoices.show', $invoice->id) }}"
                                    :title="__('Ver factura')"
                                />

                                {{-- Editar (no canceladas ni entregadas) --}}
                                @if($invoice->isEditable())
                                    <x-agro.action-button
                                        variant="edit"
                                        href="{{ roleRoute('viticulturist.invoices.edit', $invoice->id) }}"
                                        :title="__('Editar')"
                                    />
                                @endif

                                {{-- PDF Albarán --}}
                                @if($invoice->delivery_note_code)
                                    <x-agro.action-button
                                        icon="document-arrow-down"
                                        variant="default"
                                        href="{{ roleRoute('viticulturist.invoices.delivery-note-pdf', $invoice->id) }}"
                                        :title="__('Descargar albarán PDF')"
                                    />
                                @endif

                                {{-- PDF Factura --}}
                                @if($invoice->invoice_number)
                                    <x-agro.action-button
                                        icon="document-text"
                                        variant="default"
                                        href="{{ roleRoute('viticulturist.invoices.pdf', $invoice->id) }}"
                                        :title="__('Descargar factura PDF')"
                                    />
                                @endif

                                {{-- Emitir (solo draft) --}}
                                @if($invoice->status === 'draft')
                                    <x-agro.action-button
                                        icon="paper-airplane"
                                        variant="primary"
                                        wire:click="openEmitirModal({{ $invoice->id }})"
                                        :title="__('Emitir factura')"
                                    />
                                @endif

                                {{-- Marcar pagada --}}
                                @if($invoice->status !== 'cancelled' && $invoice->payment_status !== 'paid')
                                    <x-agro.action-button
                                        icon="banknotes"
                                        variant="success"
                                        wire:click="markPaid({{ $invoice->id }})"
                                        wire:confirm="{{ __('¿Marcar esta factura como pagada?') }}"
                                        :title="__('Marcar como pagada')"
                                    />
                                @endif

                                {{-- Enviar por email (emitidas con email) --}}
                                @if($invoice->status === 'sent' && ($invoice->billing_email ?: $invoice->client?->email))
                                    <x-agro.action-button
                                        icon="envelope"
                                        variant="primary"
                                        wire:click="sendEmail({{ $invoice->id }})"
                                        wire:loading.attr="disabled"
                                        :title="__('Enviar por email')"
                                    />
                                @endif

                                {{-- Rectificativa (facturas emitidas sin rectificativa previa) --}}
                                @if($invoice->status === 'sent' && !$invoice->corrective && $invoice->correctives_count === 0)
                                    <x-agro.action-button
                                        variant="restore"
                                        icon="arrow-uturn-left"
                                        wire:click="openCorrectiveModal({{ $invoice->id }})"
                                        :title="__('Crear factura rectificativa')"
                                    />
                                @endif

                                {{-- Cancelar (solo draft) --}}
                                @if($invoice->status === 'draft')
                                    <x-agro.action-button
                                        icon="x-circle"
                                        variant="warning"
                                        wire:click="cancel({{ $invoice->id }})"
                                        wire:confirm="{{ __('¿Cancelar esta factura? El stock quedará liberado.') }}"
                                        :title="__('Cancelar factura')"
                                    />
                                    <x-agro.action-button
                                        variant="delete"
                                        wire:click="delete({{ $invoice->id }})"
                                        wire:confirm="{{ __('¿Eliminar permanentemente esta factura? Esta acción no se puede deshacer.') }}"
                                        :title="__('Eliminar factura')"
                                    />
                                @endif

                            </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        <x-agro.pagination :paginator="$invoices" />

    @else
        <x-agro.empty-state
            icon="document-text"
            :message="__('No hay facturas')"
            :description="$search || $filterStatus || $filterPaymentStatus ? __('Ninguna factura coincide con los filtros aplicados.') : __('Crea tu primera factura para empezar a gestionar tu facturación.')"
        >
            @if($search || $filterStatus || $filterPaymentStatus)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">{{ __('Limpiar filtros') }}</flux:button>
                </x-slot:action>
            @else
                <x-slot:action>
                    <flux:button href="{{ roleRoute('viticulturist.invoices.create') }}" variant="primary" icon="plus">
                        {{ __('Nueva Factura') }}
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
                        <h3 class="text-base font-semibold text-zinc-900">{{ __('Emitir Factura') }}</h3>
                    </div>
                    <flux:button wire:click="closeEmitirModal" variant="ghost" size="sm" icon="x-mark" />
                </div>

                <div class="px-6 py-5 space-y-4">
                    <p class="text-sm text-zinc-500">
                        {{ __('Se generará un número de factura secuencial y la factura quedará emitida formalmente.') }}
                    </p>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">
                            {{ __('Fecha de factura') }} <span class="text-red-500">*</span>
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
                        {{ __('Cancelar') }}
                    </flux:button>
                    <flux:button
                        wire:click="confirmEmitir"
                        wire:loading.attr="disabled"
                        variant="primary"
                        size="sm"
                        icon="paper-airplane"
                    >
                        <span wire:loading.remove wire:target="confirmEmitir">{{ __('Emitir Factura') }}</span>
                        <span wire:loading wire:target="confirmEmitir">{{ __('Emitiendo...') }}</span>
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
                        <h3 class="text-base font-semibold text-zinc-900">{{ __('Crear Rectificativa') }}</h3>
                    </div>
                    <flux:button wire:click="closeCorrectiveModal" variant="ghost" size="sm" icon="x-mark" />
                </div>

                <div class="px-6 py-5 space-y-4">
                    <p class="text-sm text-zinc-500">
                        {{ __('Se creará una factura rectificativa con importes negativos. El stock de cosecha quedará restaurado como disponible.') }}
                    </p>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">
                            {{ __('Fecha de la rectificativa') }} <span class="text-red-500">*</span>
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
                            {{ __('Motivo') }} <span class="text-zinc-400 font-normal">({{ __('opcional') }})</span>
                        </label>
                        <textarea
                            wire:model="correctiveReason"
                            rows="2"
                            placeholder="{{ __('Error en precio, devolución, etc.') }}"
                            class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent resize-none"
                        ></textarea>
                        @error('correctiveReason')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 rounded-b-2xl flex items-center justify-end gap-3">
                    <flux:button wire:click="closeCorrectiveModal" variant="ghost" size="sm">
                        {{ __('Cancelar') }}
                    </flux:button>
                    <flux:button
                        wire:click="confirmCorrective"
                        wire:loading.attr="disabled"
                        variant="primary"
                        size="sm"
                        icon="arrow-uturn-left"
                        class="bg-orange-600 hover:bg-orange-700 focus:ring-orange-500"
                    >
                        <span wire:loading.remove wire:target="confirmCorrective">{{ __('Emitir Rectificativa') }}</span>
                        <span wire:loading wire:target="confirmCorrective">{{ __('Generando...') }}</span>
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Filtros --}}
    <x-agro.filter-modal name="invoice-filters" :hasActiveFilters="$filterStatus || $filterPaymentStatus" clearAction="clearFilters">
        <x-agro.filter-select :label="__('Estado de entrega')" wire:model.live="filterStatus" :placeholder="__('Todos')">
            <flux:select.option value="draft">{{ __('Borrador') }}</flux:select.option>
            <flux:select.option value="sent">{{ __('Enviada') }}</flux:select.option>
            <flux:select.option value="paid">{{ __('Pagada') }}</flux:select.option>
            <flux:select.option value="cancelled">{{ __('Cancelada') }}</flux:select.option>
        </x-agro.filter-select>
        <x-agro.filter-select :label="__('Estado de pago')" wire:model.live="filterPaymentStatus" :placeholder="__('Todos')">
            <flux:select.option value="unpaid">{{ __('Pendiente') }}</flux:select.option>
            <flux:select.option value="partial">{{ __('Parcial') }}</flux:select.option>
            <flux:select.option value="paid">{{ __('Pagado') }}</flux:select.option>
            <flux:select.option value="overdue">{{ __('Vencido') }}</flux:select.option>
        </x-agro.filter-select>
    </x-agro.filter-modal>

</div>
</div>
