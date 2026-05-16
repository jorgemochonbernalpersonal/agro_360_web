<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Mis vendimias"
        description="Declara y gestiona tus entregas de uva por añada"
    />

    {{-- Stats --}}
    <x-agro.stats-section key="harvests-stats">
        <div class="grid grid-cols-2 gap-4">
            <x-agro.stat-card
                label="Plantaciones"
                :value="$stats['delivered'] + $stats['pending']"
                description="'Con o sin entrega declarada'"
                icon="scissors"
                color="agro"
            />
            <x-agro.stat-card
                label="Con entregas"
                :value="$stats['delivered']"
                description="'Entrega registrada'"
                icon="check-circle"
                color="agro"
            />
            <x-agro.stat-card
                label="Sin entregar"
                :value="$stats['pending']"
                description="'Pendientes de entrega'"
                icon="clock"
                color="orange"
            />
            <x-agro.stat-card
                label="Kg vendimia"
                :value="number_format($stats['total_harvest_kg']) . ' kg'"
                description="'Total cosechado'"
                icon="scale"
                color="blue"
            />
        </div>
    </x-agro.stats-section>
    {{-- Tabs --}}
    <x-agro.tabs
        :tabs="[
            'pending'   => ['label' => 'Sin entregar',  'count' => $stats['pending']],
            'delivered' => ['label' => 'Con entregas', 'count' => $stats['delivered']],
        ]"
        :active="$currentTab"
        wireMethod="switchTab"
    />

    {{-- Toolbar --}}
    @php $filterCount = (int)(!empty($statusFilter)); @endphp

    <div class="flex items-center gap-3">

        <x-agro.search-input wire:model.live.debounce.300ms="search" placeholder="Buscar por variedad, parcela o comprador..." />

        <x-agro.filter-button modal="vendimia-filters" :count="$filterCount" />

        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        <a href="{{ roleRoute('viticulturist.harvests.export-pdf', ['vintage' => $vintageYear]) }}"
           target="_blank"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-50 shadow-sm transition-colors">
            <flux:icon icon="arrow-down-tray" class="size-4 text-zinc-500" />
            Resumen PDF
        </a>

        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        <flux:button href="{{ roleRoute('viticulturist.digital-notebook.harvest.create') }}" variant="primary" icon="plus">
            Añadir al cuaderno
        </flux:button>

    </div>

    {{-- Chips de filtros activos --}}
    @if($search || $statusFilter)
        <div class="flex flex-wrap items-center gap-2">
            @if($search)
                <x-agro.filter-chip icon="magnifying-glass" :label="'&quot;' . $search . '&quot;'" wireRemove="$set('search', '')" />
            @endif
            @if($statusFilter)
                @php
                    $statusLabels = [
                        'ok'            => 'Coincide',
                        'discrepancy'   => 'Con diferencia',
                        'not_delivered' => 'Sin entregar',
                        'delivery_only' => 'Sin cuaderno',
                        'pending'       => 'Pendiente',
                        'has_dispute'   => 'Con disputa activa',
                        'has_resolved'  => 'Con disputa resuelta',
                    ];
                @endphp
                <x-agro.filter-chip icon="flag" :label="$statusLabels[$statusFilter] ?? $statusFilter" wireRemove="$set('statusFilter', '')" />
            @endif
            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">
                Limpiar todo
            </button>
        </div>
    @endif

    {{-- Card grid --}}
    @if($rows->count() > 0)
        <div
            class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="search, statusFilter, vintageFilter, clearFilters, switchTab"
        >
            @foreach($rows as $i => $row)
                @php
                    $statusConfig = [
                        'ok'            => ['color' => 'green',  'label' => 'Coincide'],
                        'discrepancy'   => ['color' => 'yellow', 'label' => 'Diferencia'],
                        'not_delivered' => ['color' => 'amber',  'label' => 'Sin entregar'],
                        'delivery_only' => ['color' => 'blue',   'label' => 'Solo entrega'],
                        'pending'       => ['color' => null,     'label' => 'Pendiente'],
                    ];
                    $sc = $statusConfig[$row['status']] ?? $statusConfig['pending'];
                @endphp

                <x-agro.card
                    wire:key="row-{{ $row['key'] }}"
                    class="animate-fade-in-up hover:-translate-y-1"
                    style="animation-delay: {{ min($i * 50, 400) }}ms"
                >
                    {{-- Header --}}
                    <x-slot:header>
                        <x-agro.card-item-header
                            icon="archive-box-arrow-down"
                            :title="$row['variety']"
                            :subtitle="$row['plot'] . ($row['area'] ? ' · ' . number_format($row['area'], 2) . ' ha' : '')"
                            iconBg="bg-purple-50"
                            iconColor="text-purple-600"
                        >
                            @if($sc['color'])
                                <flux:badge color="{{ $sc['color'] }}" size="sm">{{ $sc['label'] }}</flux:badge>
                            @else
                                <flux:badge size="sm">{{ $sc['label'] }}</flux:badge>
                            @endif
                        </x-agro.card-item-header>
                    </x-slot:header>

                    {{-- Fecha y añada --}}
                    <div class="flex items-center gap-2 mb-3">
                        <flux:icon icon="calendar-days" class="size-3.5 text-zinc-400 shrink-0" />
                        <span class="text-xs font-medium text-zinc-600">
                            @if($row['last_harvest_date'])
                                {{ \Carbon\Carbon::parse($row['last_harvest_date'])->format('d/m/Y') }}
                            @else
                                Sin fecha de cosecha
                            @endif
                        </span>
                        <span class="ml-auto text-xs text-zinc-400">{{ $vintageYear }}</span>
                    </div>

                    {{-- Pills: Aforo / Cosechado / Entregado / Cupo PAC --}}
                    <div class="grid grid-cols-4 gap-1.5 mb-3">
                        <div class="bg-zinc-50 rounded-xl p-2 text-center">
                            <p class="text-[9px] text-zinc-400 font-medium uppercase tracking-wide mb-0.5">Aforo</p>
                            <p class="text-xs font-bold text-violet-700">
                                {{ $row['estimated_kg'] !== null ? number_format($row['estimated_kg'], 0) : '—' }}
                            </p>
                        </div>
                        <div class="bg-agro-50 rounded-xl p-2 text-center">
                            <p class="text-[9px] text-agro-600 font-medium uppercase tracking-wide mb-0.5">Cosechado</p>
                            <p class="text-xs font-bold text-agro-700">
                                {{ $row['harvest_kg'] > 0 ? number_format($row['harvest_kg'], 0) : '—' }}
                            </p>
                        </div>
                        <div class="bg-blue-50 rounded-xl p-2 text-center">
                            <p class="text-[9px] text-blue-500 font-medium uppercase tracking-wide mb-0.5">Entregado</p>
                            <p class="text-xs font-bold text-blue-700">
                                {{ $row['total_delivered_kg'] > 0 ? number_format($row['total_delivered_kg'], 0) : '—' }}
                            </p>
                        </div>
                        <div class="{{ $row['cupo_exceeded'] ? 'bg-red-50' : 'bg-zinc-50' }} rounded-xl p-2 text-center">
                            <p class="text-[9px] font-medium uppercase tracking-wide mb-0.5 {{ $row['cupo_exceeded'] ? 'text-red-500' : 'text-zinc-400' }}">Cupo PAC</p>
                            <p class="text-xs font-bold {{ $row['cupo_exceeded'] ? 'text-red-700' : ($row['cupo_kg'] ? 'text-zinc-700' : 'text-zinc-300') }}">
                                {{ $row['cupo_kg'] ? number_format($row['cupo_kg'], 0) : '—' }}
                            </p>
                        </div>
                    </div>

                    {{-- Barra de uso del cupo --}}
                    @if($row['cupo_kg'] && $row['cupo_pct'] !== null)
                        @php
                            $pct  = min($row['cupo_pct'], 100);
                            $barColor = $row['cupo_exceeded']
                                ? 'bg-red-500'
                                : ($row['cupo_pct'] >= 80 ? 'bg-amber-400' : 'bg-agro-500');
                        @endphp
                        <div class="mb-3">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-[10px] text-zinc-400">Uso del cupo</span>
                                <span class="text-[10px] font-bold {{ $row['cupo_exceeded'] ? 'text-red-600' : ($row['cupo_pct'] >= 80 ? 'text-amber-600' : 'text-zinc-600') }}">
                                    {{ $row['cupo_pct'] }}%
                                    @if($row['cupo_exceeded'])
                                        · <span class="text-red-600">EXCEDIDO</span>
                                    @endif
                                </span>
                            </div>
                            <div class="w-full bg-zinc-100 rounded-full h-1.5 overflow-hidden">
                                <div class="{{ $barColor }} h-1.5 rounded-full transition-all"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @elseif(!$row['cupo_kg'])
                        <div class="mb-3"></div>
                    @endif

                    {{-- Diferencia --}}
                    @if($row['status'] === 'ok')
                        <div class="flex items-center gap-1.5 text-green-600 mb-3">
                            <flux:icon icon="check-circle" class="size-3.5 shrink-0" />
                            <span class="text-xs font-medium">Cantidades coinciden</span>
                        </div>
                    @elseif($row['discrepancy_kg'] !== null && $row['discrepancy_kg'] > 0)
                        <div class="flex items-center gap-1.5 {{ $row['discrepancy_pct'] > 5 ? 'text-amber-600' : 'text-zinc-400' }} mb-3">
                            <flux:icon icon="arrows-right-left" class="size-3.5 shrink-0" />
                            <span class="text-xs font-medium">
                                Diferencia: {{ number_format($row['discrepancy_kg'], 0) }} kg
                                @if($row['discrepancy_pct'] !== null) ({{ $row['discrepancy_pct'] }}%) @endif
                            </span>
                        </div>
                    @else
                        <div class="mb-3"></div>
                    @endif

                    {{-- Sección de entregas --}}
                    @if($row['has_delivery'])
                        @php
                            $deliveryStatuses = $row['manual_deliveries']->where('disqualified', false)->groupBy('status');
                            $matchedCount  = $deliveryStatuses->get('matched',  collect())->count();
                            $disputedCount = $deliveryStatuses->get('disputed', collect())->count();
                            $resolvedCount = $deliveryStatuses->get('resolved', collect())->count();
                            $pendingCount  = $deliveryStatuses->get('pending',  collect())->count();
                        @endphp
                        <div class="space-y-1.5">
                            @foreach($row['winery_batches'] as $batch)
                                <div class="flex items-center gap-2 px-3 py-2 bg-zinc-50 rounded-lg">
                                    <flux:icon icon="building-storefront" class="size-3.5 text-zinc-400 shrink-0" />
                                    <span class="text-xs font-medium text-zinc-700 truncate flex-1">{{ $batch->winery?->name ?? '—' }}</span>
                                    <span class="text-xs font-bold text-blue-700 shrink-0">{{ number_format($batch->total_weight_kg, 0) }} kg</span>
                                </div>
                            @endforeach
                            @foreach($row['manual_deliveries'] as $delivery)
                                @php
                                    $dlvBadge = match(true) {
                                        $delivery->disqualified          => ['color' => 'red',   'label' => 'Descartada'],
                                        $delivery->status === 'matched'  => ['color' => 'green', 'label' => 'Confirmada'],
                                        $delivery->status === 'resolved' => ['color' => 'blue',  'label' => 'Resuelta'],
                                        $delivery->status === 'disputed' => ['color' => 'amber', 'label' => 'Diferencia'],
                                        default                          => ['color' => null,     'label' => null],
                                    };
                                @endphp
                                <div class="flex items-center gap-2 px-3 py-2 rounded-lg {{ $delivery->disqualified ? 'bg-red-50' : 'bg-zinc-50' }}">
                                    <flux:icon icon="user" class="size-3.5 {{ $delivery->disqualified ? 'text-red-300' : 'text-zinc-400' }} shrink-0" />
                                    <span class="text-xs font-medium truncate flex-1 {{ $delivery->disqualified ? 'text-red-400 line-through' : 'text-zinc-700' }}">{{ $delivery->buyer_name }}</span>
                                    @if($dlvBadge['label'])
                                        <flux:badge color="{{ $dlvBadge['color'] }}" size="sm" class="shrink-0 text-[9px]">{{ $dlvBadge['label'] }}</flux:badge>
                                    @endif
                                    @if(!$delivery->disqualified)
                                        <span class="text-xs font-bold text-blue-700 shrink-0">{{ number_format($delivery->delivered_kg, 0) }} kg</span>
                                    @endif
                                    <a href="{{ roleRoute('viticulturist.harvests.delivery.albaran', $delivery) }}"
                                       target="_blank"
                                       class="ml-1 p-1 rounded hover:bg-violet-100 text-zinc-400 hover:text-violet-600 transition-colors"
                                       title="Descargar albarán PDF">
                                        <flux:icon icon="document-arrow-down" class="size-3.5" />
                                    </a>
                                    <a href="{{ roleRoute('viticulturist.harvests.delivery.edit', $delivery) }}"
                                       class="p-1 rounded hover:bg-zinc-200 text-zinc-400 hover:text-zinc-700 transition-colors"
                                       title="Editar entrega">
                                        <flux:icon icon="pencil-square" class="size-3.5" />
                                    </a>
                                </div>
                            @endforeach

                            {{-- Añadir otra entrega --}}
                            @if($row['planting_id'])
                                <a href="{{ roleRoute('viticulturist.harvests.delivery.create', array_filter(['planting' => $row['planting_id'], 'vintage' => $vintageYear])) }}"
                                   class="flex items-center justify-center gap-1.5 w-full px-3 py-1.5 border border-dashed border-zinc-200 rounded-lg text-zinc-400 hover:border-agro-400 hover:text-agro-600 hover:bg-agro-50 transition-colors">
                                    <flux:icon icon="plus" class="size-3 shrink-0" />
                                    <span class="text-[10px] font-medium">Añadir otra entrega</span>
                                </a>
                            @endif

                            {{-- Resumen de estado de declaraciones --}}
                            @if($matchedCount + $disputedCount + $resolvedCount + $pendingCount > 0)
                                <div class="flex flex-wrap gap-1 pt-0.5">
                                    @if($matchedCount > 0)
                                        <span class="inline-flex items-center gap-0.5 text-[9px] font-semibold text-green-700 bg-green-50 border border-green-200 rounded-full px-2 py-0.5">
                                            <flux:icon icon="check-circle" class="size-2.5" />
                                            {{ $matchedCount }} confirmada{{ $matchedCount > 1 ? 's' : '' }}
                                        </span>
                                    @endif
                                    @if($resolvedCount > 0)
                                        <span class="inline-flex items-center gap-0.5 text-[9px] font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded-full px-2 py-0.5">
                                            <flux:icon icon="chat-bubble-left-right" class="size-2.5" />
                                            {{ $resolvedCount }} resuelta{{ $resolvedCount > 1 ? 's' : '' }}
                                        </span>
                                    @endif
                                    @if($disputedCount > 0)
                                        <span class="inline-flex items-center gap-0.5 text-[9px] font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded-full px-2 py-0.5">
                                            <flux:icon icon="exclamation-triangle" class="size-2.5" />
                                            {{ $disputedCount }} en disputa
                                        </span>
                                    @endif
                                    @if($pendingCount > 0)
                                        <span class="inline-flex items-center gap-0.5 text-[9px] font-medium text-zinc-500 bg-zinc-100 border border-zinc-200 rounded-full px-2 py-0.5">
                                            {{ $pendingCount }} pendiente{{ $pendingCount > 1 ? 's' : '' }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @else
                        <a href="{{ roleRoute('viticulturist.harvests.delivery.create', array_filter(['planting' => $row['planting_id'], 'vintage' => $vintageYear])) }}"
                           class="flex items-center justify-center gap-2 w-full px-3 py-2.5 border border-dashed border-zinc-300 rounded-lg text-zinc-400 hover:border-agro-400 hover:text-agro-600 hover:bg-agro-50 transition-colors">
                            <flux:icon icon="truck" class="size-3.5 shrink-0" />
                            <span class="text-xs font-medium">Registrar entrega</span>
                        </a>
                    @endif

                    {{-- Footer --}}
                    <x-slot:footer>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-zinc-400">
                                @if($row['harvest_count'] > 0)
                                    {{ $row['harvest_count'] }} {{ $row['harvest_count'] === 1 ? 'registro' : 'registros' }} de cosecha
                                @else
                                    Sin registros de cosecha
                                @endif
                            </span>
                            <div class="flex items-center gap-1">
                                @if($row['planting_id'])
                                    <x-agro.action-button
                                        variant="view"
                                        href="{{ roleRoute('viticulturist.harvests.show', ['planting' => $row['planting_id'], 'vintage' => $vintageYear]) }}"
                                        wire:navigate
                                        title="Ver detalle"
                                    />
                                @endif
                                <x-agro.action-button
                                    icon="document-plus"
                                    variant="default"
                                    href="{{ roleRoute('viticulturist.digital-notebook.harvest.create') }}"
                                    title="Nuevo registro en el cuaderno"
                                />
                            </div>
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

    @else
        <x-agro.empty-state
            icon="archive-box-arrow-down"
            :message="$currentTab === 'delivered' ? 'No hay plantaciones con entregas' : 'Ninguna plantación pendiente de entregar'"
            :description="($search || $statusFilter)
                ? 'Ningún resultado coincide con los filtros aplicados.'
                : ($currentTab === 'delivered'
                    ? 'Las plantaciones con al menos una entrega declarada aparecerán aquí.'
                    : 'Todas tus plantaciones ya tienen alguna entrega declarada. ¡Buen trabajo!')"
        >
            @if($search || $statusFilter)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                </x-slot:action>
            @else
                <x-slot:action>
                    <flux:button href="{{ roleRoute('viticulturist.digital-notebook.harvest.create') }}" variant="primary" icon="plus">
                        Añadir al cuaderno
                    </flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @endif

    {{-- Modal Filtros --}}
    <x-agro.modal name="vendimia-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'vendimia-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-4">
            <div>
                <x-agro.filter-select label="Añada" wire:model.live="vintageFilter">
                    @foreach($campaignYears as $year)
                        <flux:select.option value="{{ $year }}">{{ $year }}</flux:select.option>
                    @endforeach
                </x-agro.filter-select>
            </div>
            <div>
                <x-agro.filter-select label="Estado" wire:model.live="statusFilter" placeholder="Todos los estados">
                    <flux:select.option value="ok">Coincide</flux:select.option>
                    <flux:select.option value="discrepancy">Con diferencia (&gt;5%)</flux:select.option>
                    <flux:select.option value="not_delivered">Sin entregar</flux:select.option>
                    <flux:select.option value="delivery_only">Sin cuaderno</flux:select.option>
                    <flux:select.option value="pending">Pendiente</flux:select.option>
                    <flux:select.option value="has_dispute">Con disputa activa</flux:select.option>
                    <flux:select.option value="has_resolved">Con disputa resuelta</flux:select.option>
                </x-agro.filter-select>
            </div>
        </div>

        <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 flex items-center justify-between rounded-b-2xl">
            <button wire:click="clearFilters" x-on:click="$dispatch('close-modal', 'vendimia-filters')"
                    class="text-sm text-zinc-500 hover:text-zinc-700 transition-colors">
                Limpiar filtros
            </button>
            <flux:button x-on:click="$dispatch('close-modal', 'vendimia-filters')" variant="primary" size="sm">
                Aplicar
            </flux:button>
        </div>
    </x-agro.modal>

</div>
