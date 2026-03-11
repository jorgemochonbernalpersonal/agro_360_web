<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Mis cosechas"
        description="Consulta lo que has cosechado y registra tus entregas"
    />

    {{-- Tabs --}}
    <x-agro.tabs
        :tabs="[
            'pending'   => ['label' => 'Pendientes de entrega', 'count' => $stats['pending']],
            'delivered' => ['label' => 'Entregadas',            'count' => $stats['delivered']],
        ]"
        :active="$currentTab"
        wireMethod="switchTab"
    />

    {{-- Toolbar --}}
    @php $filterCount = (int)(!empty($statusFilter)); @endphp

    <div class="flex items-center gap-3">

        <div class="flex-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Buscar por variedad, parcela o comprador..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
            />
        </div>

        <button
            x-on:click="$dispatch('open-modal', 'vendimia-filters')"
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

        <flux:button href="{{ route('viticulturist.digital-notebook.harvest.create') }}" variant="primary" icon="plus">
            Nueva cosecha
        </flux:button>

    </div>

    {{-- Chips de filtros activos --}}
    @if($search || $statusFilter)
        <div class="flex flex-wrap items-center gap-2">
            @if($search)
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="magnifying-glass" class="size-3" />
                    "{{ $search }}"
                    <button wire:click="$set('search', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if($statusFilter)
                @php
                    $statusLabels = [
                        'ok'            => 'Coincide',
                        'discrepancy'   => 'Con diferencia',
                        'not_delivered' => 'Sin entregar',
                        'delivery_only' => 'Solo entrega',
                        'pending'       => 'Pendiente',
                    ];
                @endphp
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="flag" class="size-3" />
                    {{ $statusLabels[$statusFilter] ?? $statusFilter }}
                    <button wire:click="$set('statusFilter', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
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
                    $btnBase = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors';
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
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-purple-50 rounded-full flex items-center justify-center shrink-0">
                                <flux:icon icon="archive-box-arrow-down" class="size-4 text-purple-600" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-zinc-900 text-sm truncate leading-tight" title="{{ $row['variety'] }}">
                                    {{ $row['variety'] }}
                                </p>
                                <p class="text-xs text-zinc-400 leading-tight mt-0.5 truncate">
                                    {{ $row['plot'] }}@if($row['area']) · {{ number_format($row['area'], 2) }} ha @endif
                                </p>
                            </div>
                            @if($sc['color'])
                                <flux:badge color="{{ $sc['color'] }}" size="sm" class="shrink-0">{{ $sc['label'] }}</flux:badge>
                            @else
                                <flux:badge size="sm" class="shrink-0">{{ $sc['label'] }}</flux:badge>
                            @endif
                        </div>
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

                    {{-- Tres pills: Aforo / Cosechado / Entregado --}}
                    <div class="grid grid-cols-3 gap-2 mb-3">
                        <div class="bg-zinc-50 rounded-xl p-2 text-center">
                            <p class="text-[10px] text-zinc-400 font-medium uppercase tracking-wide mb-0.5">Aforo</p>
                            <p class="text-xs font-bold text-violet-700">
                                {{ $row['estimated_kg'] !== null ? number_format($row['estimated_kg'], 0) . ' kg' : '—' }}
                            </p>
                        </div>
                        <div class="bg-agro-50 rounded-xl p-2 text-center">
                            <p class="text-[10px] text-agro-600 font-medium uppercase tracking-wide mb-0.5">Cosechado</p>
                            <p class="text-xs font-bold text-agro-700">
                                {{ $row['harvest_kg'] > 0 ? number_format($row['harvest_kg'], 0) . ' kg' : '—' }}
                            </p>
                        </div>
                        <div class="bg-blue-50 rounded-xl p-2 text-center">
                            <p class="text-[10px] text-blue-500 font-medium uppercase tracking-wide mb-0.5">Entregado</p>
                            <p class="text-xs font-bold text-blue-700">
                                {{ $row['total_delivered_kg'] > 0 ? number_format($row['total_delivered_kg'], 0) . ' kg' : '—' }}
                            </p>
                        </div>
                    </div>

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
                        <div class="space-y-1.5">
                            @foreach($row['winery_batches'] as $batch)
                                <div class="flex items-center gap-2 px-3 py-2 bg-zinc-50 rounded-lg">
                                    <flux:icon icon="building-storefront" class="size-3.5 text-zinc-400 shrink-0" />
                                    <span class="text-xs font-medium text-zinc-700 truncate flex-1">{{ $batch->winery?->name ?? '—' }}</span>
                                    <span class="text-xs font-bold text-blue-700 shrink-0">{{ number_format($batch->total_weight_kg, 0) }} kg</span>
                                </div>
                            @endforeach
                            @foreach($row['manual_deliveries'] as $delivery)
                                <div class="flex items-center gap-2 px-3 py-2 rounded-lg {{ $delivery->disqualified ? 'bg-red-50' : 'bg-zinc-50' }}">
                                    <flux:icon icon="user" class="size-3.5 {{ $delivery->disqualified ? 'text-red-300' : 'text-zinc-400' }} shrink-0" />
                                    <span class="text-xs font-medium truncate flex-1 {{ $delivery->disqualified ? 'text-red-400 line-through' : 'text-zinc-700' }}">{{ $delivery->buyer_name }}</span>
                                    @if($delivery->disqualified)
                                        <flux:badge color="red" size="sm" class="shrink-0">Descartado</flux:badge>
                                    @else
                                        <span class="text-xs font-bold text-blue-700 shrink-0">{{ number_format($delivery->delivered_kg, 0) }} kg</span>
                                    @endif
                                    <a href="{{ route('viticulturist.harvests.delivery.edit', $delivery) }}"
                                       class="ml-1 p-1 rounded hover:bg-zinc-200 text-zinc-400 hover:text-zinc-700 transition-colors"
                                       title="Editar entrega">
                                        <flux:icon icon="pencil-square" class="size-3.5" />
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <a href="{{ route('viticulturist.harvests.delivery.create', array_filter(['planting' => $row['planting_id'], 'vintage' => $vintageYear])) }}"
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
                            <a href="{{ route('viticulturist.digital-notebook.harvest.create') }}" class="{{ $btnBase }}" title="Añadir cosecha">
                                <flux:icon icon="plus" class="size-4" />
                            </a>
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

    @else
        <x-agro.empty-state
            icon="archive-box-arrow-down"
            :message="$currentTab === 'delivered' ? 'No hay cosechas entregadas' : 'No hay cosechas pendientes de entrega'"
            :description="($search || $statusFilter)
                ? 'Ningún resultado coincide con los filtros aplicados.'
                : ($currentTab === 'delivered'
                    ? 'Las cosechas que hayas entregado a bodega o comprador aparecerán aquí.'
                    : 'Todas tus cosechas han sido entregadas. ¡Buen trabajo!')"
        >
            @if($search || $statusFilter)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                </x-slot:action>
            @else
                <x-slot:action>
                    <flux:button href="{{ route('viticulturist.digital-notebook.harvest.create') }}" variant="primary" icon="plus">
                        Registrar cosecha
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
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Añada</label>
                <select wire:model.live="vintageFilter"
                        class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent">
                    @foreach($campaignYears as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Estado</label>
                <select wire:model.live="statusFilter"
                        class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent">
                    <option value="">Todos los estados</option>
                    <option value="ok">Coincide</option>
                    <option value="discrepancy">Con diferencia (&gt;5%)</option>
                    <option value="not_delivered">Sin entregar</option>
                    <option value="delivery_only">Solo entrega (sin cosecha en cuaderno)</option>
                    <option value="pending">Pendiente</option>
                </select>
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
