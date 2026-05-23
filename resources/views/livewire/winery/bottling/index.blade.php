<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="Embotellado"
        description="Registro de operaciones de embotellado y materiales utilizados"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('bottling.create') }}" variant="primary" icon="plus">
                Nuevo Embotellado
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs --}}
    <x-agro.stats-section key="bottling-stats">
        <div class="grid grid-cols-2 gap-4">
            <x-agro.stat-card
                label="Total embotellamientos"
                :value="$stats['total']"
                icon="archive-box-arrow-down"
                color="zinc"
            />
            <x-agro.stat-card
                label="Este año"
                :value="$stats['this_year']"
                icon="calendar-days"
                color="agro"
            />
            <x-agro.stat-card
                label="Botellas totales"
                :value="number_format($stats['total_bottles'])"
                icon="sparkles"
                color="zinc"
            />
            <x-agro.stat-card
                label="Litros embotellados"
                :value="number_format($stats['total_liters'], 0) . ' L'"
                icon="beaker"
                color="amber"
            />
        </div>
    </x-agro.stats-section>

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">
        <x-agro.search-input wire:model.live.debounce.300ms="search" placeholder="Buscar por vino o nº lote..." />

        <flux:select wire:model.live="wineFilter" class="w-48">
            <flux:select.option value="">Todos los vinos</flux:select.option>
            @foreach($wines as $wine)
                <flux:select.option value="{{ $wine->id }}">{{ $wine->name }}{{ $wine->vintage ? ' ' . $wine->vintage : '' }}</flux:select.option>
            @endforeach
        </flux:select>

        @if($search || $wineFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                Limpiar
            </flux:button>
        @endif
    </div>

    {{-- Loading skeleton --}}
    <x-agro.loading-grid target="search, wineFilter, clearFilters, nextPage, previousPage" />

    {{-- Grid de cards --}}
    <div wire:loading.remove wire:target="search, wineFilter, clearFilters, nextPage, previousPage">
        @if($bottlings->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($bottlings as $bottling)
                    @php
                        $delay = min($loop->index * 50, 300);
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="bottling-{{ $bottling->id }}"
                    >
                        <x-slot:header>
                            <x-agro.card-item-header
                                icon="archive-box-arrow-down"
                                :title="$bottling->wine->name . ($bottling->wine->vintage ? ' ' . $bottling->wine->vintage : '')"
                                :subtitle="$bottling->bottling_date->format('d/m/Y')"
                                iconBg="bg-agro-100"
                                iconColor="text-agro-600"
                                size="md"
                                radius="xl"
                            >
                                <flux:badge color="zinc" size="sm">{{ $bottling->format_label }}</flux:badge>
                            </x-agro.card-item-header>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">
                            {{-- Stats --}}
                            <div class="grid grid-cols-2 gap-2">
                                <div class="bg-agro-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">Botellas</p>
                                    <p class="text-2xl font-bold text-agro-700 leading-none">
                                        {{ number_format($bottling->quantity_bottles) }}
                                    </p>
                                </div>
                                <div class="bg-zinc-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">Litros</p>
                                    <p class="text-2xl font-bold text-zinc-700 leading-none">
                                        {{ number_format($bottling->quantity_liters, 1) }}
                                    </p>
                                </div>
                            </div>

                            {{-- Lote y enólogo --}}
                            @if($bottling->lot_number)
                                <div class="flex items-center gap-2 text-sm text-zinc-600">
                                    <flux:icon icon="hashtag" class="size-4 text-zinc-400 shrink-0" />
                                    <span class="text-xs font-mono">{{ $bottling->lot_number }}</span>
                                </div>
                            @endif

                            @if($bottling->oenologist)
                                <div class="flex items-center gap-2 text-sm text-zinc-600">
                                    <flux:icon icon="user" class="size-4 text-zinc-400 shrink-0" />
                                    <span class="text-xs truncate">{{ $bottling->oenologist->surname }}, {{ $bottling->oenologist->name }}</span>
                                </div>
                            @endif

                            {{-- Lote de producto --}}
                            @if($bottling->productLot)
                                <div class="flex items-center gap-1">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium text-agro-700 bg-agro-50 px-2 py-0.5 rounded-full border border-agro-200">
                                        <flux:icon icon="cube" class="size-3" />
                                        <a href="{{ roleRoute('product-lots.edit', $bottling->product_lot_id) }}"
                                           class="hover:underline">{{ $bottling->productLot->name }}</a>
                                    </span>
                                </div>
                            @endif
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5">
                                <x-agro.action-button variant="edit" href="{{ roleRoute('bottling.edit', $bottling) }}" title="Editar embotellado" />
                                @if(! $bottling->product_lot_id)
                                    <x-agro.action-button variant="delete" wire:click="delete({{ $bottling->id }})" wire:confirm="¿Eliminar este registro de embotellado?" wire:loading.attr="disabled" title="Eliminar embotellado" />
                                @endif
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <x-agro-pagination :paginator="$bottlings" />
        @else
            <x-agro.empty-state
                icon="archive-box-arrow-down"
                title="{{ $search || $wineFilter ? 'Ningún embotellado coincide con los filtros' : 'No hay registros de embotellado' }}"
                description="{{ $search || $wineFilter ? 'Prueba a cambiar o limpiar los filtros aplicados.' : 'Registra tu primera operación de embotellado.' }}"
            >
                @if($search || $wineFilter)
                    <x-slot:action>
                        <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">
                            Limpiar filtros
                        </flux:button>
                    </x-slot:action>
                @else
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('bottling.create') }}" variant="primary" icon="plus">
                            Nuevo Embotellado
                        </flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @endif
    </div>

</div>

