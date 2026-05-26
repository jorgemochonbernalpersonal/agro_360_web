<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="{{ __('Etiquetado') }}"
        :description="__('Registro de sesiones de etiquetado y consumo de etiquetas')"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('label-batches.index') }}" variant="ghost" icon="tag" size="sm">
                Gestionar lotes
            </flux:button>
            <flux:button href="{{ roleRoute('labeling.create') }}" variant="primary" icon="plus">
                Nueva Sesión
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live.debounce.300ms="search"
            placeholder="{{ __('Buscar por vino...') }}"
        />
        <x-agro.filter-select wire:model.live="wineFilter" size="sm" class="w-52">
            <flux:select.option value="">{{ __('Todos los vinos') }}</flux:select.option>
            @foreach($wines as $wine)
                <flux:select.option value="{{ $wine->id }}">{{ $wine->name }}{{ $wine->vintage ? ' ' . $wine->vintage : '' }}</flux:select.option>
            @endforeach
        </x-agro.filter-select>
        @if($search || $wineFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">{{ __('Limpiar') }}</flux:button>
        @endif
    </x-agro.filter-bar>

    <x-agro.loading-grid target="search, wineFilter, clearFilters, nextPage, previousPage" />

    <div wire:loading.remove wire:target="search, wineFilter, clearFilters, nextPage, previousPage">
        @if($labelings->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($labelings as $labeling)
                    @php $delay = min($loop->index * 50, 300); @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="labeling-{{ $labeling->id }}"
                    >
                        <x-slot:header>
                            <x-agro.card-item-header
                                icon="tag"
                                :title="$labeling->wine->name"
                                :subtitle="$labeling->labeling_date->format('d/m/Y')"
                                iconBg="bg-violet-100"
                                iconColor="text-violet-600"
                                size="md"
                                radius="xl"
                            >
                                @if($labeling->wine->vintage)
                                    <flux:badge color="violet" size="sm">{{ $labeling->wine->vintage }}</flux:badge>
                                @endif
                            </x-agro.card-item-header>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">
                            <div class="grid grid-cols-2 gap-2">
                                <div class="bg-agro-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">{{ __('Botellas') }}</p>
                                    <p class="text-2xl font-bold text-agro-700 leading-none">{{ number_format($labeling->quantity_labeled) }}</p>
                                </div>
                                <div class="bg-agro-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">{{ __('Rango') }}</p>
                                    <p class="text-sm font-bold text-agro-700 leading-none font-mono">{{ $labeling->label_range ?? '—' }}</p>
                                </div>
                            </div>

                            <div class="space-y-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">{{ __('Lote') }}</span>
                                    <span class="text-zinc-700 font-medium">
                                        @if($labeling->labelBatch)
                                            <a href="{{ roleRoute('label-batches.edit', $labeling->label_batch_id) }}"
                                                class="text-violet-600 hover:text-violet-800 underline underline-offset-2">
                                                {{ $labeling->labelBatch->name }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">{{ __('Embotellado') }}</span>
                                    <span class="text-zinc-700 font-medium">
                                        @if($labeling->bottling)
                                            <a href="{{ roleRoute('bottling.edit', $labeling->wine_bottling_id) }}"
                                                class="text-emerald-600 hover:text-emerald-800 underline underline-offset-2">
                                                {{ $labeling->bottling->bottling_date->format('d/m/Y') }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5">
                                <x-agro.action-button icon="pencil" variant="default" href="{{ roleRoute('labeling.edit', $labeling) }}" wire:navigate title="{{ __('Editar') }}" />
                                <x-agro.action-button variant="delete" wire:click="delete({{ $labeling->id }})" wire:confirm="{{ __('¿Eliminar esta sesión? Se devolverán las etiquetas al lote.') }}" title="{{ __('Eliminar') }}" />
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <div class="mt-6"><x-agro.pagination :paginator="$labelings" /></div>
        @else
            <x-agro.empty-state
                icon="tag"
                title="{{ __('No hay sesiones de etiquetado') }}"
                :description="$search || $wineFilter ? 'Ninguna sesión coincide con los filtros.' : 'Registra la primera sesión de etiquetado.'"
            >
                @if($search || $wineFilter)
                    <x-slot:action>
                        <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">{{ __('Limpiar filtros') }}</flux:button>
                    </x-slot:action>
                @else
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('labeling.create') }}" variant="primary" icon="plus">{{ __('Nueva Sesión') }}</flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @endif
    </div>
</div>
