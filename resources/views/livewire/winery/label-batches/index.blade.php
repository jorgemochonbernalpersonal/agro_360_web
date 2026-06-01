<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="{{ __('Lotes de Etiquetas') }}"
        :description="__('Series numeradas de contraetiquetas y etiquetas de bodega')"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('label-batches.create') }}" variant="primary" icon="plus">
                Nuevo Lote
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live.debounce.300ms="search"
            placeholder="{{ __('Buscar por nombre...') }}"
        />
        <flux:select wire:model.live="wineFilter" size="sm" class="w-52">
            <flux:select.option value="">{{ __('Todos los vinos') }}</flux:select.option>
            @foreach($wines as $wine)
                <flux:select.option value="{{ $wine->id }}">{{ $wine->name }}{{ $wine->vintage ? ' ' . $wine->vintage : '' }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="sourceFilter" size="sm" class="w-44">
            <flux:select.option value="">{{ __('Todos los orígenes') }}</flux:select.option>
            @foreach($sources as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
        @if($search || $wineFilter || $sourceFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">{{ __('Limpiar') }}</flux:button>
        @endif
    </x-agro.filter-bar>

    @if($batches->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="search, wineFilter, sourceFilter, clearFilters">

            @foreach($batches as $batch)
                @php
                    $pct   = $batch->usage_percent;
                    $delay = min($loop->index * 50, 300);
                @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="batch-{{ $batch->id }}"
                >
                    <x-slot:header>
                        <x-agro.card-item-header
                            icon="tag"
                            :title="$batch->name"
                            :subtitle="number_format($batch->start_number) . ' – ' . number_format($batch->end_number)"
                            iconBg="bg-violet-50"
                            iconColor="text-violet-600"
                            size="md"
                            radius="xl"
                        >
                            @if($batch->isEmpty())
                                <flux:badge color="red" size="sm">{{ __('Agotado') }}</flux:badge>
                            @elseif($pct >= 80)
                                <flux:badge color="yellow" size="sm">{{ __('Casi agotado') }}</flux:badge>
                            @else
                                <flux:badge color="green" size="sm">{{ __('Disponible') }}</flux:badge>
                            @endif
                        </x-agro.card-item-header>
                    </x-slot:header>

                    <div class="flex-1 space-y-4">
                        {{-- Barra de uso --}}
                        <div>
                            <div class="flex justify-between text-xs text-zinc-500 mb-1">
                                <span>{{ __('Uso') }}</span>
                                <span class="{{ $pct >= 100 ? 'text-red-600 font-semibold' : '' }}">{{ $pct }}%</span>
                            </div>
                            <x-agro.progress-bar :percent="$pct" />
                        </div>

                        {{-- Stats --}}
                        <div class="grid grid-cols-3 gap-2">
                            <div class="bg-zinc-50 rounded-lg p-2 text-center">
                                <p class="text-[10px] text-zinc-400 uppercase tracking-wide">{{ __('Total') }}</p>
                                <p class="text-base font-bold text-zinc-700">{{ number_format($batch->total_quantity) }}</p>
                            </div>
                            <div class="bg-agro-50 rounded-lg p-2 text-center">
                                <p class="text-[10px] text-agro-400 uppercase tracking-wide">{{ __('Disponibles') }}</p>
                                <p class="text-base font-bold text-agro-700">{{ number_format($batch->available_quantity) }}</p>
                            </div>
                            <div class="bg-zinc-50 rounded-lg p-2 text-center">
                                <p class="text-[10px] text-zinc-400 uppercase tracking-wide">{{ __('Mermas') }}</p>
                                <p class="text-base font-bold text-zinc-500">{{ number_format($batch->wasted_quantity) }}</p>
                            </div>
                        </div>

                        {{-- Meta --}}
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-zinc-400">{{ __('Origen') }}</span>
                                <flux:badge color="{{ $batch->source === 'do_assigned' ? 'blue' : 'zinc' }}" size="sm">
                                    {{ $batch->source_label }}
                                </flux:badge>
                            </div>
                            @if($batch->wine)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-zinc-400">{{ __('Vino') }}</span>
                                    <span class="text-zinc-700 font-medium truncate ml-4">{{ $batch->wine->name }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-end gap-1">
                            <x-agro.action-button variant="edit" href="{{ roleRoute('label-batches.edit', $batch) }}" wire:navigate title="{{ __('Editar') }}" />
                            <x-agro.action-button icon="exclamation-triangle" variant="danger" href="{{ roleRoute('label-batches.waste.index', $batch) }}" wire:navigate title="{{ __('Mermas') }}" />
                            @if($batch->canDelete())
                                <x-agro.action-button variant="delete" wire:click="delete({{ $batch->id }})" wire:confirm="{{ __('¿Eliminar este lote de etiquetas?') }}" wire:loading.attr="disabled" title="{{ __('Eliminar') }}" />
                            @endif
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        <x-agro.pagination :paginator="$batches" />
    @else
        <x-agro.empty-state
            icon="tag"
            title="{{ __('No hay lotes de etiquetas') }}"
            description="{{ $search || $wineFilter || $sourceFilter ? 'Ningún lote coincide con los filtros.' : 'Crea un lote para gestionar las series numeradas de contraetiquetas.' }}"
        >
            @if($search || $wineFilter || $sourceFilter)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">{{ __('Limpiar filtros') }}</flux:button>
                </x-slot:action>
            @else
                <x-slot:action>
                    <flux:button href="{{ roleRoute('label-batches.create') }}" variant="primary" icon="plus">
                        Nuevo Lote
                    </flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @endif
</div>
