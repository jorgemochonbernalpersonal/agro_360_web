<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        :title="__('Productos Fitosanitarios')"
        :description="__('Gestiona el catálogo de productos fitosanitarios para tus tratamientos')"
    />

    {{-- Tabs --}}
    <x-agro.tabs
        :tabs="[
            'active'   => ['label' => __('Activos'),   'count' => $stats['active']],
            'inactive' => ['label' => __('Inactivos'), 'count' => $stats['inactive']],
        ]"
        :active="$currentTab"
        wireMethod="switchTab"
    />

    {{-- Toolbar --}}
    @php
        $filterCount = (int)(!empty($typeFilter));
    @endphp

    <div class="flex items-center gap-3">

        <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar por nombre, materia activa, registro o fabricante...')" data-cy="product-search-input" />

        <x-agro.filter-button modal="product-filters" :count="$filterCount" />

        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        <flux:button href="{{ roleRoute('viticulturist.phytosanitary-products.create') }}" variant="primary" icon="plus" data-cy="create-product-button">
            {{ __('Nuevo Producto') }}
        </flux:button>

    </div>

    {{-- Chips de filtros activos --}}
    @if($search || $typeFilter)
        <div class="flex flex-wrap items-center gap-2">
            @if($search)
                <x-agro.filter-chip icon="magnifying-glass" :label="'&quot;' . $search . '&quot;'" wireRemove="$set('search', '')" />
            @endif
            @if($typeFilter)
                <x-agro.filter-chip :label="ucfirst($typeFilter)" wireRemove="$set('typeFilter', '')" />
            @endif
            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">
                {{ __('Limpiar todo') }}
            </button>
        </div>
    @endif

    {{-- Card grid --}}
    @if($products->count() > 0)

        <div
            class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="search, typeFilter, clearFilters, switchTab"
        >
            @foreach($products as $i => $product)
                <x-agro.card
                    wire:key="product-{{ $product->id }}"
                    class="animate-fade-in-up hover:-translate-y-1"
                    style="animation-delay: {{ min($i * 50, 400) }}ms"
                >
                    <x-slot:header>
                        <x-agro.card-item-header
                            icon="shield-exclamation"
                            :title="$product->name"
                            :subtitle="$product->manufacturer ?? null"
                            iconBg="bg-red-50"
                            iconColor="text-red-600"
                        >
                            @if($product->type)
                                <flux:badge size="sm">{{ ucfirst($product->type) }}</flux:badge>
                            @endif
                        </x-agro.card-item-header>
                    </x-slot:header>

                    {{-- Detalles --}}
                    <div class="bg-zinc-50 rounded-xl p-2.5 mb-3 space-y-1.5">
                        @if($product->active_ingredient)
                            <div class="flex items-start gap-2">
                                <span class="text-xs text-zinc-400 shrink-0 w-20">{{ __('Materia act.') }}</span>
                                <span class="text-xs font-medium text-zinc-700 truncate">{{ $product->active_ingredient }}</span>
                            </div>
                        @endif
                        @if($product->registration_number)
                            <div class="flex items-start gap-2">
                                <span class="text-xs text-zinc-400 shrink-0 w-20">{{ __('Nº Registro') }}</span>
                                <span class="text-xs font-medium text-zinc-700">{{ $product->registration_number }}</span>
                            </div>
                        @endif
                        <div class="flex items-start gap-2">
                            <span class="text-xs text-zinc-400 shrink-0 w-20">{{ __('Plazo seg.') }}</span>
                            @if($product->withdrawal_period_days !== null)
                                <span class="text-xs font-medium {{ $product->withdrawal_period_days > 0 ? 'text-amber-700' : 'text-zinc-500' }}">
                                    {{ $product->withdrawal_period_days > 0 ? $product->withdrawal_period_days . ' ' . __('días') : __('Sin plazo') }}
                                </span>
                            @else
                                <span class="text-xs text-zinc-400 italic">{{ __('No definido') }}</span>
                            @endif
                        </div>
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-end gap-1">
                            <x-agro.action-button
                                variant="edit"
                                href="{{ roleRoute('viticulturist.phytosanitary-products.edit', $product) }}"
                                :title="__('Editar')"
                                data-cy="edit-product-button"
                            />
                            <x-agro.action-button
                                variant="{{ $product->active ? 'deactivate' : 'activate' }}"
                                wire:click="toggleActive({{ $product->id }})"
                                wire:loading.attr="disabled"
                                wire:target="toggleActive({{ $product->id }})"
                                :title="$product->active ? __('Desactivar producto') : __('Activar producto')"
                            />
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        <x-agro.pagination :paginator="$products" />

    @else
        <x-agro.empty-state
            icon="shield-exclamation"
            :message="__('No hay productos registrados')"
            :description="($search || $typeFilter) ? __('Ningún producto coincide con los filtros aplicados.') : __('Comienza creando tu primer producto fitosanitario.')"
        >
            @if($search || $typeFilter)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">{{ __('Limpiar filtros') }}</flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @endif

    {{-- Modal Filtros --}}
    <x-agro.filter-modal
        name="product-filters"
        :hasActiveFilters="$filterCount > 0"
        clearAction="clearFilters"
    >
        <div>
            <x-agro.filter-select :label="__('Tipo de producto')" wire:model.live="typeFilter" :placeholder="__('Todos los tipos')" data-cy="product-type-filter">
                @foreach($types as $type)
                    <flux:select.option value="{{ $type }}">{{ ucfirst($type) }}</flux:select.option>
                @endforeach
            </x-agro.filter-select>
        </div>
    </x-agro.filter-modal>

</div>
