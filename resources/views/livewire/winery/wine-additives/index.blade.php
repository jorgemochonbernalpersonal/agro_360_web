<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="{{ __('Aditivos Enológicos') }}"
        :description="__('Registro de aditivos y tratamientos aplicados a los vinos')"
        icon="beaker"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('wines.index') }}" variant="ghost" icon="arrow-left" wire:navigate>
                Vinos
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs --}}
    <x-agro.stats-section key="wine-additives" :columns="3">
        <x-agro.stat-card :label="__('Total aditivos')" :value="$stats['total']"     icon="beaker"         color="zinc" />
        <x-agro.stat-card :label="__('Este año')"        :value="$stats['this_year']" icon="calendar-days"  color="agro" />
        <x-agro.stat-card :label="__('Vinos tratados')"  :value="$stats['wines']"     icon="arrows-right-left" color="amber" />
    </x-agro.stats-section>

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">
        <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar por aditivo o vino...')" />

        <flux:select wire:model.live="wineFilter" class="w-48">
            <flux:select.option value="">{{ __('Todos los vinos') }}</flux:select.option>
            @foreach($wines as $wine)
                <flux:select.option value="{{ $wine->id }}">{{ $wine->name }}</flux:select.option>
            @endforeach
        </flux:select>

        @if($search || $wineFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">{{ __('Limpiar') }}</flux:button>
        @endif
    </div>

    {{-- Loading --}}
    <x-agro.loading-grid target="search, wineFilter, clearFilters, nextPage, previousPage" :cols="3" :count="6" />

    {{-- Grid --}}
    <div wire:loading.remove wire:target="search, wineFilter, clearFilters, nextPage, previousPage">
        @if($additives->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($additives as $additive)
                    @php $delay = min($loop->index * 50, 300); @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="additive-{{ $additive->id }}"
                    >
                        <x-slot:header>
                            <x-agro.card-item-header
                                icon="beaker"
                                :title="$additive->additive_name"
                                :subtitle="$additive->wine?->name ?? '—'"
                                iconBg="bg-agro-100"
                                iconColor="text-agro-600"
                                size="md"
                                radius="xl"
                            />
                        </x-slot:header>

                        <div class="flex-1 space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-zinc-500">{{ __('Fecha') }}</span>
                                <span class="font-medium text-zinc-800">
                                    {{ $additive->application_date?->format('d/m/Y') ?? '—' }}
                                </span>
                            </div>
                            @if($additive->quantity !== null)
                            <div class="flex justify-between">
                                <span class="text-zinc-500">{{ __('Cantidad') }}</span>
                                <span class="font-medium text-zinc-800">
                                    {{ number_format($additive->quantity, 3) }}
                                    {{ $additive->unitOfMeasurement?->symbol ?? '' }}
                                </span>
                            </div>
                            @endif
                            @if($additive->supply)
                            <div class="flex justify-between">
                                <span class="text-zinc-500">{{ __('Insumo') }}</span>
                                <span class="font-medium text-zinc-800 truncate max-w-[55%] text-right">{{ $additive->supply->name }}</span>
                            </div>
                            @endif
                            @if($additive->oenologist)
                            <div class="flex justify-between">
                                <span class="text-zinc-500">{{ __('Enólogo') }}</span>
                                <span class="font-medium text-zinc-800 truncate max-w-[55%] text-right">{{ $additive->oenologist->full_name }}</span>
                            </div>
                            @endif
                            @if($additive->notes)
                            <p class="text-xs text-zinc-400 italic pt-1">{{ Str::limit($additive->notes, 80) }}</p>
                            @endif
                        </div>

                        <x-slot:footer>
                            <div class="flex gap-2 justify-end">
                                <flux:button
                                    wire:click="delete({{ $additive->id }})"
                                    wire:confirm="{{ __('¿Eliminar este aditivo?') }}"
                                    variant="ghost" size="sm" icon="trash"
                                    class="text-red-500 hover:text-red-700"
                                />
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <x-agro-pagination :paginator="$additives" />
        @else
            <x-agro.empty-state
                icon="beaker"
                title="{{ __('Sin aditivos registrados') }}"
                description="{{ $search || $wineFilter ? 'No hay aditivos que coincidan con los filtros.' : 'Los aditivos se registran desde el detalle de cada vino.' }}"
            />
        @endif
    </div>

</div>
