<div class="space-y-6 animate-fade-in">
    <x-agro.page-header title="Productos" description="Gestiona tu catálogo de productos y stock para facturar a clientes" />

    {{-- Tabs --}}
    <x-agro.tabs :tabs="[
        'active'   => ['label' => 'Activos',   'count' => $stats['active']],
        'inactive' => ['label' => 'Inactivos', 'count' => $stats['inactive']],
    ]" :active="$currentTab" wireMethod="switchTab" />

    {{-- Toolbar: search + tipo + nuevo producto --}}
    <div class="flex items-center gap-3">
        <div class="flex-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
            </div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por nombre..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition" />
        </div>

        <flux:select wire:model.live="typeFilter" size="sm" class="w-44">
            <flux:select.option value="">Todos los tipos</flux:select.option>
            <flux:select.option value="tinto">Tinto</flux:select.option>
            <flux:select.option value="blanco">Blanco</flux:select.option>
            <flux:select.option value="rosado">Rosado</flux:select.option>
            <flux:select.option value="espumoso">Espumoso</flux:select.option>
            <flux:select.option value="otro">Otro</flux:select.option>
        </flux:select>

        @if ($search || $typeFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                Limpiar
            </flux:button>
        @endif

        {{-- Separador --}}
        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        <flux:button href="{{ route('winery.product-lots.insights') }}" wire:navigate variant="ghost" icon="chart-bar">
            Insights
        </flux:button>

        {{-- Nuevo Producto --}}
        <flux:button href="{{ route('winery.product-lots.create') }}" wire:navigate variant="primary" icon="plus">
            Nuevo
        </flux:button>
    </div>

    {{-- Grid skeleton durante carga --}}
    <div wire:loading wire:target="switchTab, search, typeFilter, nextPage, previousPage, gotoPage">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @for ($i = 0; $i < 6; $i++)
                <x-agro.skeleton-card />
            @endfor
        </div>
    </div>

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
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl {{ $headerIconBg }} flex items-center justify-center shrink-0">
                                    <flux:icon icon="beaker" class="size-5 {{ $headerIconColor }}" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">{{ $lot->name }}</h3>
                                    <p class="text-xs text-zinc-500 truncate">
                                        {{ ucfirst($lot->wine_type) }}{{ $lot->vintage ? ' · ' . $lot->vintage : '' }}{{ $lot->aging_type ? ' · ' . ucfirst(str_replace('_', ' ', $lot->aging_type)) : '' }}{{ $lot->alcohol ? ' · ' . $lot->alcohol . '°' : '' }}
                                    </p>
                                </div>
                                <x-agro.status-badge :active="$isActive" class="shrink-0" />
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">
                            {{-- Barra de stock apilada --}}
                            @if ($total > 0)
                                <div>
                                    <div class="flex justify-between text-xs text-zinc-500 mb-1.5">
                                        <span>Stock</span>
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
                                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>Disp.</span>
                                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-orange-400 inline-block"></span>Res.</span>
                                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-blue-400 inline-block"></span>Vend.</span>
                                    </div>
                                </div>
                            @endif

                            {{-- Grid de cantidades --}}
                            <div class="grid grid-cols-4 gap-2 text-center">
                                <div class="bg-zinc-50 rounded-xl p-2">
                                    <p class="text-[10px] text-zinc-400 uppercase tracking-wide mb-0.5">Total</p>
                                    <p class="text-sm font-bold text-zinc-700">{{ number_format($total, 0) }}</p>
                                </div>
                                <div class="bg-green-50 rounded-xl p-2">
                                    <p class="text-[10px] text-green-500 uppercase tracking-wide mb-0.5">Disp.</p>
                                    <p class="text-sm font-bold {{ $avail <= 0 ? 'text-red-600' : 'text-green-700' }}">
                                        {{ number_format($avail, 0) }}
                                    </p>
                                </div>
                                <div class="bg-orange-50 rounded-xl p-2">
                                    <p class="text-[10px] text-orange-400 uppercase tracking-wide mb-0.5">Res.</p>
                                    <p class="text-sm font-bold text-orange-600">
                                        {{ number_format((float) $lot->reserved_quantity, 0) ?: '—' }}
                                    </p>
                                </div>
                                <div class="bg-blue-50 rounded-xl p-2">
                                    <p class="text-[10px] text-blue-400 uppercase tracking-wide mb-0.5">Vend.</p>
                                    <p class="text-sm font-bold text-blue-600">
                                        {{ number_format((float) $lot->sold_quantity, 0) ?: '—' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Unidad, precio y SKU --}}
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">Unidad</span>
                                    <span class="text-zinc-700 font-medium">{{ ucfirst($lot->unit) }}</span>
                                </div>
                                @if ($lot->price_per_unit > 0)
                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-400">Precio/ud</span>
                                        <span class="text-zinc-700 font-medium">{{ number_format($lot->price_per_unit, 2) }} €</span>
                                    </div>
                                @endif
                                @if ($lot->sku)
                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-400">SKU</span>
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
                            @php
                                $btnBase    = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors';
                                $btnDanger  = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors';
                                $btnSuccess = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-agro-600 hover:bg-agro-50 transition-colors';
                            @endphp
                            <div class="flex items-center justify-between">
                                {{-- Grupo izquierdo: editar + duplicar + ventas --}}
                                <div class="flex items-center gap-0.5">
                                    <a href="{{ route('winery.product-lots.edit', $lot) }}" wire:navigate
                                        class="{{ $btnBase }}" title="Editar">
                                        <flux:icon icon="pencil-square" class="size-4" />
                                    </a>
                                    <a href="{{ route('winery.product-lots.sales', $lot) }}" wire:navigate
                                        class="{{ $btnBase }}" title="Ver ventas">
                                        <flux:icon icon="shopping-cart" class="size-4" />
                                    </a>
                                    <button wire:click="duplicate({{ $lot->id }})"
                                        wire:loading.attr="disabled"
                                        wire:confirm="¿Duplicar «{{ $lot->name }}»? Se creará una copia con stock en cero."
                                        class="{{ $btnBase }}" title="Duplicar">
                                        <flux:icon icon="document-duplicate" class="size-4" />
                                    </button>
                                </div>

                                <div class="w-px h-5 bg-zinc-200 mx-1"></div>

                                {{-- Grupo derecho: activar / desactivar --}}
                                <div class="flex items-center gap-0.5">
                                    @if ($isActive)
                                        <button wire:click="toggleActive({{ $lot->id }})"
                                        wire:loading.attr="disabled"
                                            class="{{ $btnDanger }}" title="Desactivar">
                                            <flux:icon icon="no-symbol" class="size-4" />
                                        </button>
                                    @else
                                        <button wire:click="toggleActive({{ $lot->id }})"
                                        wire:loading.attr="disabled"
                                            class="{{ $btnSuccess }}" title="Activar">
                                            <flux:icon icon="check-circle" class="size-4" />
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $lots->links() }}
            </div>
        @elseif ($currentTab === 'active')
            <x-agro.empty-state
                icon="beaker"
                title="No hay productos activos"
                description="Crea el primer producto para gestionar tu stock"
            >
                <x-slot:action>
                    <flux:button href="{{ route('winery.product-lots.create') }}" wire:navigate variant="primary" icon="plus">
                        Nuevo Producto
                    </flux:button>
                </x-slot:action>
            </x-agro.empty-state>
        @else
            <x-agro.empty-state
                icon="archive-box"
                title="No hay productos inactivos"
                description="Los productos desactivados aparecerán aquí"
            />
        @endif
    </div>
</div>
