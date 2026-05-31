<div class="space-y-6 animate-fade-in">
    <x-agro.page-header title="{{ __('Productos') }}" :description="__('Gestiona tu catálogo de productos y stock para facturar a clientes')" />

    {{-- Tabs --}}
    <x-agro.tabs :tabs="[
        'active'   => ['label' => 'Activos',   'count' => $stats['active']],
        'inactive' => ['label' => 'Inactivos', 'count' => $stats['inactive']],
    ]" :active="$currentTab" wireMethod="switchTab" />

    {{-- Toolbar: search + tipo + nuevo producto --}}
    <div class="flex items-center gap-3">
        <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar por nombre...')" />

        <flux:select wire:model.live="typeFilter" size="sm" class="w-44">
            <flux:select.option value="">{{ __('Todos los tipos') }}</flux:select.option>
            <flux:select.option value="tinto">{{ __('Tinto') }}</flux:select.option>
            <flux:select.option value="blanco">{{ __('Blanco') }}</flux:select.option>
            <flux:select.option value="rosado">{{ __('Rosado') }}</flux:select.option>
            <flux:select.option value="espumoso">{{ __('Espumoso') }}</flux:select.option>
            <flux:select.option value="otro">{{ __('Otro') }}</flux:select.option>
        </flux:select>

        @if ($search || $typeFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">{{ __('Limpiar') }}</flux:button>
        @endif

        {{-- Separador --}}
        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        <flux:button href="{{ roleRoute('product-lots.insights') }}" wire:navigate variant="ghost" icon="chart-bar">
            Insights
        </flux:button>

        <flux:button href="{{ roleRoute('product-lots.audit') }}" wire:navigate variant="ghost" icon="shield-check">
            Auditoría
        </flux:button>

        {{-- Nuevo Producto --}}
        <flux:button href="{{ roleRoute('product-lots.create') }}" wire:navigate variant="primary" icon="plus">
            Nuevo
        </flux:button>
    </div>

    {{-- Grid skeleton durante carga --}}
    <x-agro.loading-grid target="switchTab, search, typeFilter, nextPage, previousPage, gotoPage" :count="6" />

    {{-- Grid real --}}
    <div wire:loading.remove wire:target="switchTab, search, typeFilter, nextPage, previousPage, gotoPage">
        @if ($lots->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($lots as $lot)
                    @php
                        $avail    = (float) $lot->available_quantity;
                        $reserved = (float) $lot->reserved_quantity;
                        $sold     = (float) $lot->sold_quantity;
                        $total    = (float) $lot->quantity;
                        $pctAvail = $total > 0 ? round($avail    / $total * 100) : 0;
                        $pctRes   = $total > 0 ? round($reserved / $total * 100) : 0;
                        $pctSold  = $total > 0 ? round($sold     / $total * 100) : 0;
                        $delay    = min($loop->index * 50, 300);
                        $isActive = ! $lot->archived;

                        $typeColors = [
                            'tinto'    => ['bg' => 'bg-red-100',   'icon' => 'text-red-600'],
                            'blanco'   => ['bg' => 'bg-amber-100', 'icon' => 'text-amber-600'],
                            'rosado'   => ['bg' => 'bg-pink-100',  'icon' => 'text-pink-600'],
                            'espumoso' => ['bg' => 'bg-blue-100',  'icon' => 'text-blue-600'],
                            'otro'     => ['bg' => 'bg-zinc-100',  'icon' => 'text-zinc-500'],
                        ];
                        $tc = $typeColors[$lot->wine_type] ?? $typeColors['otro'];

                        $headerIconBg    = $isActive ? $tc['bg']   : 'bg-zinc-100';
                        $headerIconColor = $isActive ? $tc['icon'] : 'text-zinc-400';
                    @endphp

                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1 {{ ! $isActive ? 'opacity-60' : '' }}"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="lot-card-{{ $lot->id }}"
                    >
                        <x-slot:header>
                            <x-agro.card-item-header
                                icon="beaker"
                                :title="$lot->name"
                                :subtitle="__(ucfirst($lot->wine_type)) . ($lot->vintage ? ' · ' . $lot->vintage : '') . ($lot->aging_type ? ' · ' . ucfirst(str_replace('_', ' ', $lot->aging_type)) : '') . ($lot->alcohol ? ' · ' . $lot->alcohol . '°' : '')"
                                :iconBg="$headerIconBg"
                                :iconColor="$headerIconColor"
                                size="md"
                                radius="xl"
                            >
                                <x-agro.status-badge :active="$isActive" />
                            </x-agro.card-item-header>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">
                            {{-- Barra de stock apilada --}}
                            @if ($total > 0)
                                <div>
                                    <div class="flex justify-between text-xs text-zinc-500 mb-1.5">
                                        <span>{{ __('Stock') }}</span>
                                        <span class="{{ $avail <= 0 ? 'text-red-600 font-semibold' : 'text-zinc-500' }}">
                                            {{ $pctAvail }}% disp.
                                        </span>
                                    </div>
                                    <div class="flex h-2 w-full rounded-full overflow-hidden bg-zinc-100">
                                        @if ($pctAvail > 0)
                                            <div class="bg-green-500 h-full transition-all" style="width: {{ $pctAvail }}%"></div>
                                        @endif
                                        @if ($pctRes > 0)
                                            <div class="bg-orange-400 h-full transition-all" style="width: {{ $pctRes }}%"></div>
                                        @endif
                                        @if ($pctSold > 0)
                                            <div class="bg-blue-400 h-full transition-all" style="width: {{ $pctSold }}%"></div>
                                        @endif
                                    </div>
                                    <div class="flex gap-3 mt-1.5 text-[10px] text-zinc-400">
                                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>{{ __('Disp.') }}</span>
                                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-orange-400 inline-block"></span>{{ __('Res.') }}</span>
                                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-blue-400 inline-block"></span>{{ __('Vend.') }}</span>
                                    </div>
                                </div>
                            @endif

                            {{-- Grid de cantidades --}}
                            <div class="grid grid-cols-4 gap-2 text-center">
                                <div class="bg-zinc-50 rounded-xl p-2">
                                    <p class="text-[10px] text-zinc-400 uppercase tracking-wide mb-0.5">{{ __('Total') }}</p>
                                    <p class="text-sm font-bold text-zinc-700">{{ number_format($total, 0) }}</p>
                                </div>
                                <div class="bg-green-50 rounded-xl p-2">
                                    <p class="text-[10px] text-green-500 uppercase tracking-wide mb-0.5">{{ __('Disp.') }}</p>
                                    <p class="text-sm font-bold {{ $avail <= 0 ? 'text-red-600' : 'text-green-700' }}">
                                        {{ number_format($avail, 0) }}
                                    </p>
                                </div>
                                <div class="bg-orange-50 rounded-xl p-2">
                                    <p class="text-[10px] text-orange-400 uppercase tracking-wide mb-0.5">{{ __('Res.') }}</p>
                                    <p class="text-sm font-bold text-orange-600">
                                        {{ number_format((float) $lot->reserved_quantity, 0) ?: '—' }}
                                    </p>
                                </div>
                                <div class="bg-blue-50 rounded-xl p-2">
                                    <p class="text-[10px] text-blue-400 uppercase tracking-wide mb-0.5">{{ __('Vend.') }}</p>
                                    <p class="text-sm font-bold text-blue-600">
                                        {{ number_format((float) $lot->sold_quantity, 0) ?: '—' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Unidad, precio y SKU --}}
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">{{ __('Unidad') }}</span>
                                    <span class="text-zinc-700 font-medium">{{ __(ucfirst($lot->unit)) }}</span>
                                </div>
                                @if ($lot->price_per_unit > 0)
                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-400">{{ __('Precio/ud') }}</span>
                                        <span class="text-zinc-700 font-medium">{{ number_format($lot->price_per_unit, 2) }} €</span>
                                    </div>
                                @endif
                                @if ($lot->sku)
                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-400">{{ __('SKU') }}</span>
                                        <span class="text-zinc-500 font-mono text-xs">{{ $lot->sku }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Certificaciones --}}
                            @php
                                $certs = array_filter([
                                    $lot->ecological    ? 'Ecológico'  : null,
                                    $lot->is_vegan      ? 'Vegano'     : null,
                                    $lot->is_biodynamic ? 'Biodinámico': null,
                                    $lot->sulfites      ? 'Sulfitos'   : null,
                                ]);
                            @endphp
                            @if ($certs)
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($certs as $cert)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-agro-50 text-agro-700 border border-agro-200">
                                            {{ $cert }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-between">
                                {{-- Grupo izquierdo: editar + duplicar + ventas --}}
                                <div class="flex items-center gap-0.5">
                                    <x-agro.action-button variant="edit" href="{{ roleRoute('product-lots.edit', $lot) }}" wire:navigate title="{{ __('Editar') }}" />
                                    <x-agro.action-button icon="shopping-cart" variant="default" href="{{ roleRoute('product-lots.sales', $lot) }}" wire:navigate title="{{ __('Ver ventas') }}" />
                                    <x-agro.action-button icon="document-duplicate" variant="default" wire:click="duplicate({{ $lot->id }})" wire:loading.attr="disabled" wire:confirm="{{ __('¿Duplicar «:name»? Se creará una copia con stock en cero.', ['name' => $lot->name]) }}" title="{{ __('Duplicar') }}" />
                                </div>

                                <div class="w-px h-5 bg-zinc-200 mx-1"></div>

                                {{-- Grupo derecho: activar / desactivar --}}
                                <div class="flex items-center gap-0.5">
                                    @if ($isActive)
                                        <x-agro.action-button variant="deactivate" wire:click="toggleActive({{ $lot->id }})" wire:loading.attr="disabled" title="{{ __('Desactivar') }}" />
                                    @else
                                        <x-agro.action-button variant="activate" wire:click="toggleActive({{ $lot->id }})" wire:loading.attr="disabled" title="{{ __('Activar') }}" />
                                    @endif
                                </div>
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <x-agro.pagination :paginator="$lots" />
        @elseif ($currentTab === 'active')
            <x-agro.empty-state
                icon="beaker"
                title="{{ __('No hay productos activos') }}"
                :description="__('Crea el primer producto para gestionar tu stock')"
            >
                <x-slot:action>
                    <flux:button href="{{ roleRoute('product-lots.create') }}" wire:navigate variant="primary" icon="plus">
                        Nuevo Producto
                    </flux:button>
                </x-slot:action>
            </x-agro.empty-state>
        @else
            <x-agro.empty-state
                icon="archive-box"
                title="{{ __('No hay productos inactivos') }}"
                :description="__('Los productos desactivados aparecerán aquí')"
            />
        @endif
    </div>
</div>
