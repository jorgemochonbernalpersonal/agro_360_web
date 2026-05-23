<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="Subproductos"
        description="Registro de orujo, lías, vinaza y otros subproductos de elaboración"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('subproducts.create') }}" variant="primary" icon="plus">
                Nuevo subproducto
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs --}}
    <x-agro.stats-section key="subproducts-stats">
        <div class="grid grid-cols-2 gap-4">
            <x-agro.stat-card
                label="Total subproductos"
                :value="$stats['total']"
                icon="archive-box"
                color="zinc"
            />
            <x-agro.stat-card
                label="Este año"
                :value="$stats['this_year']"
                icon="calendar-days"
                color="agro"
            />
        </div>
    </x-agro.stats-section>

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">
        <x-agro.search-input wire:model.live.debounce.300ms="search" placeholder="Buscar por vino, destino o lote..." />

        <flux:select wire:model.live="typeFilter" class="w-44">
            <flux:select.option value="">Todos los tipos</flux:select.option>
            @foreach($types as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="wineFilter" class="w-48">
            <flux:select.option value="">Todos los vinos</flux:select.option>
            @foreach($wines as $wine)
                <flux:select.option value="{{ $wine->id }}">{{ $wine->name }}{{ $wine->vintage ? ' ' . $wine->vintage : '' }}</flux:select.option>
            @endforeach
        </flux:select>

        @if($search || $typeFilter || $wineFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                Limpiar
            </flux:button>
        @endif
    </div>

    {{-- Loading skeleton --}}
    <x-agro.loading-grid target="search, typeFilter, wineFilter, clearFilters, nextPage, previousPage" />

    {{-- Grid de cards --}}
    <div wire:loading.remove wire:target="search, typeFilter, wineFilter, clearFilters, nextPage, previousPage">
        @if($subproducts->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($subproducts as $sp)
                    @php
                        $delay = min($loop->index * 50, 300);
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="sp-{{ $sp->id }}"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-zinc-100">
                                    <flux:icon icon="archive-box" class="size-5 text-zinc-500" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">
                                        @if($sp->wine)
                                            {{ $sp->wine->name }}
                                            @if($sp->wine->vintage)
                                                <span class="text-zinc-400 font-normal text-sm ml-1">{{ $sp->wine->vintage }}</span>
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </h3>
                                    <p class="text-xs text-zinc-400">{{ $sp->subproduct_date->format('d/m/Y') }}</p>
                                </div>
                                <flux:badge color="{{ $sp->type_badge_color }}" size="sm" class="shrink-0">
                                    {{ $sp->type_label }}
                                </flux:badge>
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">
                            {{-- Cantidad --}}
                            <div class="bg-zinc-50 rounded-xl p-3">
                                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">Cantidad</p>
                                <p class="text-2xl font-bold text-zinc-700 leading-none">
                                    {{ number_format($sp->quantity, 3) }}
                                    @if($sp->unit)
                                        <span class="text-sm font-normal text-zinc-400 ml-1">{{ $sp->unit->abbreviation ?? $sp->unit->name }}</span>
                                    @endif
                                </p>
                            </div>

                            {{-- Destino --}}
                            <div class="flex items-center gap-2 text-sm text-zinc-600">
                                <flux:icon icon="truck" class="size-4 text-zinc-400 shrink-0" />
                                <div class="min-w-0">
                                    <span class="font-medium">{{ $sp->destination_label }}</span>
                                    @if($sp->destination_name)
                                        <span class="text-zinc-400 text-xs block">{{ $sp->destination_name }}</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Nº lote --}}
                            @if($sp->lot_number)
                                <div class="flex items-center gap-2">
                                    <flux:icon icon="hashtag" class="size-4 text-zinc-400 shrink-0" />
                                    <span class="text-xs font-mono text-zinc-600">{{ $sp->lot_number }}</span>
                                </div>
                            @endif
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5">
                                <x-agro.action-button variant="edit" href="{{ roleRoute('subproducts.edit', $sp) }}" title="Editar subproducto" />
                                <x-agro.action-button variant="delete" wire:click="delete({{ $sp->id }})" wire:confirm="¿Eliminar este registro de subproducto?" wire:loading.attr="disabled" title="Eliminar subproducto" />
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <x-agro-pagination :paginator="$subproducts" />
        @else
            <x-agro.empty-state
                icon="archive-box"
                title="{{ $search || $typeFilter || $wineFilter ? 'Ningún subproducto coincide con los filtros' : 'No hay subproductos' }}"
                description="{{ $search || $typeFilter || $wineFilter ? 'Prueba a cambiar o limpiar los filtros aplicados.' : 'Registra orujo, lías, vinaza y otros subproductos de elaboración.' }}"
            >
                @if($search || $typeFilter || $wineFilter)
                    <x-slot:action>
                        <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                    </x-slot:action>
                @else
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('subproducts.create') }}" variant="primary" icon="plus">Nuevo subproducto</flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @endif
    </div>

</div>

