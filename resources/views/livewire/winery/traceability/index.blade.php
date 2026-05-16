<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Trazabilidad"
        description="Consulta el historial completo de cada vino desde la uva hasta la etiqueta."
    />

    <x-agro.filter-bar>
        <x-agro.filter-input wire:model.live.debounce.300ms="search" placeholder="Buscar vino o código..." />
        <x-agro.filter-select wire:model.live="vintageFilter" size="sm" class="min-w-32">
            <flux:select.option value="">Todas las añadas</flux:select.option>
            @foreach($vintages as $v)
                <flux:select.option value="{{ $v }}">{{ $v }}</flux:select.option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="statusFilter" size="sm" class="min-w-40">
            <flux:select.option value="">Todos los estados</flux:select.option>
            @foreach($statuses as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </x-agro.filter-select>
        @if($search || $vintageFilter || $statusFilter)
            <flux:button wire:click="resetFilters" variant="ghost" size="sm" icon="x-mark">Limpiar</flux:button>
        @endif
    </x-agro.filter-bar>

    <x-agro.loading-grid target="search, vintageFilter, statusFilter, resetFilters, nextPage, previousPage" />

    <div wire:loading.remove wire:target="search, vintageFilter, statusFilter, resetFilters, nextPage, previousPage">
        @if($wines->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($wines as $wine)
                    @php
                        $delay = min($loop->index * 50, 300);
                        $badgeColor = match($wine->status) {
                            'in_progress' => 'blue',
                            'aged'        => 'amber',
                            'bottled'     => 'green',
                            'sold'        => 'zinc',
                            default       => 'zinc',
                        };
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="wine-{{ $wine->id }}"
                    >
                        <x-slot:header>
                            <x-agro.card-item-header
                                icon="magnifying-glass-circle"
                                :title="$wine->name"
                                :subtitle="$wine->internal_code ?? '—'"
                                iconBg="bg-purple-100"
                                iconColor="text-purple-600"
                                size="md"
                                radius="xl"
                            >
                                <flux:badge color="{{ $badgeColor }}" size="sm">{{ $wine->status_label }}</flux:badge>
                            </x-agro.card-item-header>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">
                            <div class="grid grid-cols-2 gap-2">
                                <div class="bg-agro-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">Añada</p>
                                    <p class="text-2xl font-bold text-agro-700 leading-none">{{ $wine->vintage ?? '—' }}</p>
                                </div>
                                <div class="bg-agro-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">Orígenes</p>
                                    <p class="text-2xl font-bold text-agro-700 leading-none">{{ $wine->wineHarvests->count() }}</p>
                                </div>
                            </div>

                            <div class="space-y-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">Procesos</span>
                                    <span class="text-zinc-700 font-medium">{{ $wine->processDetails->count() }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">Embotellados</span>
                                    <span class="text-zinc-700 font-medium">{{ $wine->bottlings->count() }}</span>
                                </div>
                            </div>
                        </div>

                        <x-slot:footer>
                            @php $btnBase = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors'; @endphp
                            <div class="flex items-center justify-end gap-0.5">
                                <a href="{{ roleRoute('wines.show', $wine) }}" wire:navigate class="{{ $btnBase }}" title="Ver vino">
                                    <flux:icon icon="eye" class="size-4" />
                                </a>
                                @if($wine->trace_token)
                                    <a href="{{ roleRoute('wines.traceability-pdf', $wine) }}" target="_blank" class="{{ $btnBase }}" title="Descargar PDF">
                                        <flux:icon icon="arrow-down-tray" class="size-4" />
                                    </a>
                                @endif
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <div class="mt-6"><x-agro.pagination :paginator="$wines" /></div>
        @else
            <x-agro.empty-state
                icon="magnifying-glass-circle"
                title="No se encontraron vinos"
                :description="$search || $vintageFilter || $statusFilter ? 'Ningún vino coincide con los filtros actuales.' : 'Cuando registres vinos aparecerán aquí con su trazabilidad completa.'"
            >
                @if($search || $vintageFilter || $statusFilter)
                    <x-slot:action>
                        <flux:button wire:click="resetFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @endif
    </div>
</div>
