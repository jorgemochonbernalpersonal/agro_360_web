<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="Subcontratación"
        description="Gestión de servicios subcontratados a empresas externas"
        icon="user-plus"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.subcontracting.create') }}" variant="primary" icon="plus">
                Registrar Servicio
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-agro.stat-card
            label="Total servicios"
            :value="$stats['total']"
            icon="user-plus"
            color="zinc"
        />
        <x-agro.stat-card
            label="Importe total"
            :value="number_format($stats['total_amount'], 2) . ' €'"
            icon="banknotes"
            color="zinc"
        />
        <x-agro.stat-card
            label="Facturados"
            :value="$stats['invoiced']"
            icon="document-check"
            color="agro"
        />
        <x-agro.stat-card
            label="Pendientes"
            :value="$stats['pending']"
            icon="clock"
            color="amber"
        />
    </div>

    @php
        $filterCount = (int) !empty($filter_campaign_id) + (int) !empty($filter_plot_id) + (int) !empty($filter_service_type) + (int) ($filter_invoiced !== '');
    @endphp

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">
        <x-agro.filter-button modal="subcontracting-filters" :count="$filterCount" />
    </div>

    {{-- Chips de filtros activos --}}
    @if($filterCount > 0)
        <div class="flex flex-wrap items-center gap-2">
            @if($filter_campaign_id)
                @php $campaignLabel = $campaigns->firstWhere('id', $filter_campaign_id)?->name ?? $filter_campaign_id; @endphp
                <x-agro.filter-chip icon="calendar-days" :label="$campaignLabel" wireRemove="$set('filter_campaign_id', '')" />
            @endif
            @if($filter_plot_id)
                @php $plotLabel = $plots->firstWhere('id', $filter_plot_id)?->name ?? $filter_plot_id; @endphp
                <x-agro.filter-chip icon="map" :label="$plotLabel" wireRemove="$set('filter_plot_id', '')" />
            @endif
            @if($filter_service_type)
                @php $typeLabel = $serviceTypes[$filter_service_type] ?? $filter_service_type; @endphp
                <x-agro.filter-chip icon="tag" :label="$typeLabel" wireRemove="$set('filter_service_type', '')" />
            @endif
            @if($filter_invoiced !== '')
                <x-agro.filter-chip icon="document-check" :label="$filter_invoiced ? 'Facturado' : 'Pendiente'" wireRemove="$set('filter_invoiced', '')" />
            @endif
            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">
                Limpiar todo
            </button>
        </div>
    @endif

    {{-- Grid de cards --}}
    @if($records->count() === 0)
        <x-agro.empty-state
            icon="user-plus"
            title="{{ $filterCount > 0 ? 'Ningún servicio coincide con los filtros' : 'Sin servicios subcontratados' }}"
            description="{{ $filterCount > 0 ? 'Prueba a cambiar o limpiar los filtros.' : 'Registra los servicios que contratas externamente: vendimia mecanizada, tratamientos, transporte, etc.' }}"
        >
            <x-slot:action>
                @if($filterCount > 0)
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                @else
                    <flux:button href="{{ roleRoute('viticulturist.subcontracting.create') }}" variant="primary" icon="plus">
                        Registrar primer servicio
                    </flux:button>
                @endif
            </x-slot:action>
        </x-agro.empty-state>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($records as $record)
                @php $delay = min($loop->index * 50, 300); @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="sub-{{ $record->id }}"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-orange-100">
                                <flux:icon icon="user-plus" class="size-5 text-orange-600" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-zinc-900 truncate">{{ $record->company_name }}</h3>
                                <p class="text-xs text-zinc-400">{{ $record->service_date->format('d/m/Y') }}{{ $record->service_end_date ? ' — ' . $record->service_end_date->format('d/m/Y') : '' }}</p>
                            </div>
                            <button wire:click="toggleInvoiced({{ $record->id }})" class="shrink-0">
                                <flux:badge color="{{ $record->invoiced ? 'green' : 'amber' }}" size="sm">
                                    {{ $record->invoiced ? 'Facturado' : 'Pendiente' }}
                                </flux:badge>
                            </button>
                        </div>
                    </x-slot:header>

                    <div class="flex-1 space-y-3">
                        <flux:badge color="orange" size="sm">{{ $record->service_type_label }}</flux:badge>

                        @if($record->contact_person)
                            <div class="flex items-center gap-2 text-xs text-zinc-500">
                                <flux:icon icon="user" class="size-3.5 text-zinc-400 shrink-0" />
                                <span class="truncate">{{ $record->contact_person }}</span>
                            </div>
                        @endif

                        @if($record->plot)
                            <div class="flex items-center gap-2 text-xs text-zinc-500">
                                <flux:icon icon="map" class="size-3.5 text-zinc-400 shrink-0" />
                                <span>{{ $record->plot->name }}</span>
                            </div>
                        @endif

                        @if($record->amount)
                            <div class="bg-orange-50 rounded-xl p-3">
                                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">Importe</p>
                                <p class="text-xl font-bold text-orange-700 leading-none">
                                    {{ number_format($record->amount, 2) }}<span class="text-xs font-normal text-zinc-400 ml-0.5">€</span>
                                </p>
                            </div>
                        @endif
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-end gap-0.5">
                            <a href="{{ roleRoute('viticulturist.subcontracting.edit', $record->id) }}"
                               title="Editar"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                <flux:icon icon="pencil-square" class="size-4" />
                            </a>
                            <button
                                wire:click="delete({{ $record->id }})"
                                wire:confirm="¿Eliminar este registro?"
                                title="Eliminar"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                                <flux:icon icon="trash" class="size-4" />
                            </button>
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        <div class="mt-6">{{ $records->links() }}</div>
    @endif

    {{-- Modal Filtros --}}
    <x-agro.modal name="subcontracting-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'subcontracting-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-5">
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Campaña</label>
                <flux:select wire:model.live="filter_campaign_id">
                    <option value="">Todas las campañas</option>
                    @foreach($campaigns as $campaign)
                        <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Parcela</label>
                <flux:select wire:model.live="filter_plot_id">
                    <option value="">Todas las parcelas</option>
                    @foreach($plots as $plot)
                        <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Tipo de servicio</label>
                <flux:select wire:model.live="filter_service_type">
                    <option value="">Todos los tipos</option>
                    @foreach($serviceTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Facturación</label>
                <flux:select wire:model.live="filter_invoiced">
                    <option value="">Todos</option>
                    <option value="1">Facturado</option>
                    <option value="0">Pendiente</option>
                </flux:select>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-zinc-200 flex items-center justify-between">
            @if($filterCount > 0)
                <button wire:click="clearFilters" class="text-sm text-zinc-400 hover:text-zinc-600 transition-colors">
                    Limpiar filtros
                </button>
            @else
                <span></span>
            @endif
            <flux:button x-on:click="$dispatch('close-modal', 'subcontracting-filters')" variant="primary">
                Aplicar
            </flux:button>
        </div>
    </x-agro.modal>

</div>
