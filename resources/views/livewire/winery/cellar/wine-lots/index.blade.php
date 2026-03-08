<div class="space-y-6 animate-fade-in">
    <x-agro.page-header title="Productos" description="Gestiona tu catálogo de productos y stock para facturar a clientes">
        <x-slot:actions>
            <flux:button href="{{ route('winery.wine-lots.create') }}" wire:navigate variant="primary" icon="plus">
                Nuevo Producto
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar por nombre..."
        />
        <flux:select wire:model.live="typeFilter" size="sm" class="w-40">
            <flux:select.option value="">Todos los tipos</flux:select.option>
            <flux:select.option value="tinto">Tinto</flux:select.option>
            <flux:select.option value="blanco">Blanco</flux:select.option>
            <flux:select.option value="rosado">Rosado</flux:select.option>
            <flux:select.option value="espumoso">Espumoso</flux:select.option>
            <flux:select.option value="otro">Otro</flux:select.option>
        </flux:select>
        <flux:select wire:model.live="statusFilter" size="sm" class="w-40">
            <flux:select.option value="active">Activos</flux:select.option>
            <flux:select.option value="archived">Archivados</flux:select.option>
            <flux:select.option value="all">Todos</flux:select.option>
        </flux:select>
        @if ($search || $typeFilter || $statusFilter !== 'active')
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                Limpiar
            </flux:button>
        @endif
    </x-agro.filter-bar>

    @if ($lots->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($lots as $lot)
                @php
                    $avail = (float) $lot->available_quantity;
                    $total = (float) $lot->quantity;
                    $pct   = $total > 0 ? round($avail / $total * 100) : 0;
                    $delay = min($loop->index * 50, 300);

                    $typeColors = [
                        'tinto'    => ['bg' => 'bg-red-50',    'icon' => 'text-red-600'],
                        'blanco'   => ['bg' => 'bg-amber-50',  'icon' => 'text-amber-600'],
                        'rosado'   => ['bg' => 'bg-pink-50',   'icon' => 'text-pink-600'],
                        'espumoso' => ['bg' => 'bg-blue-50',   'icon' => 'text-blue-600'],
                        'otro'     => ['bg' => 'bg-zinc-100',  'icon' => 'text-zinc-500'],
                    ];
                    $tc = $typeColors[$lot->wine_type] ?? $typeColors['otro'];
                @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col {{ $lot->archived ? 'opacity-60' : '' }}"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="lot-card-{{ $lot->id }}"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl {{ $tc['bg'] }} flex items-center justify-center shrink-0">
                                <flux:icon icon="beaker" class="size-5 {{ $tc['icon'] }}" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-zinc-900 truncate">{{ $lot->name }}</h3>
                                <p class="text-xs text-zinc-500">
                                    {{ ucfirst($lot->wine_type) }}{{ $lot->vintage ? ' · ' . $lot->vintage : '' }}
                                </p>
                            </div>
                            @if ($lot->archived)
                                <flux:badge color="zinc" size="sm" class="shrink-0">Archivado</flux:badge>
                            @elseif ($avail <= 0)
                                <flux:badge color="red" size="sm" class="shrink-0">Sin stock</flux:badge>
                            @else
                                <flux:badge color="green" size="sm" class="shrink-0">Disponible</flux:badge>
                            @endif
                        </div>
                    </x-slot:header>

                    <div class="flex-1 space-y-4">
                        {{-- Barra de stock disponible --}}
                        @if ($total > 0)
                            <div>
                                <div class="flex justify-between text-xs text-zinc-500 mb-1">
                                    <span>Stock disponible</span>
                                    <span class="{{ $avail <= 0 ? 'text-red-600 font-semibold' : 'text-zinc-700' }}">
                                        {{ $pct }}%
                                    </span>
                                </div>
                                <x-agro.progress-bar :percent="$pct" />
                            </div>
                        @endif

                        {{-- Grid de cantidades --}}
                        <div class="grid grid-cols-4 gap-2 text-center">
                            <div class="bg-zinc-50 rounded-lg p-2">
                                <p class="text-[10px] text-zinc-400 uppercase tracking-wide mb-0.5">Total</p>
                                <p class="text-sm font-bold text-zinc-700">{{ number_format($total, 0) }}</p>
                            </div>
                            <div class="bg-green-50 rounded-lg p-2">
                                <p class="text-[10px] text-green-500 uppercase tracking-wide mb-0.5">Disp.</p>
                                <p class="text-sm font-bold {{ $avail <= 0 ? 'text-red-600' : 'text-green-700' }}">
                                    {{ number_format($avail, 0) }}
                                </p>
                            </div>
                            <div class="bg-orange-50 rounded-lg p-2">
                                <p class="text-[10px] text-orange-400 uppercase tracking-wide mb-0.5">Res.</p>
                                <p class="text-sm font-bold text-orange-600">
                                    {{ number_format((float) $lot->reserved_quantity, 0) ?: '—' }}
                                </p>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-2">
                                <p class="text-[10px] text-blue-400 uppercase tracking-wide mb-0.5">Vend.</p>
                                <p class="text-sm font-bold text-blue-600">
                                    {{ number_format((float) $lot->sold_quantity, 0) ?: '—' }}
                                </p>
                            </div>
                        </div>

                        {{-- Unidad y precio --}}
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-400">Unidad</span>
                            <span class="text-zinc-700 font-medium">{{ $lot->unit }}</span>
                        </div>
                        @if ($lot->price_per_unit > 0)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-zinc-400">Precio/ud</span>
                                <span class="text-zinc-700 font-medium">{{ number_format($lot->price_per_unit, 2) }} €</span>
                            </div>
                        @endif
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('winery.wine-lots.edit', $lot) }}" wire:navigate title="Editar">
                                <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                    <flux:icon icon="pencil" class="size-4" />
                                </button>
                            </a>
                            @if ($lot->archived)
                                <button wire:click="unarchive({{ $lot->id }})" title="Reactivar"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                    <flux:icon icon="arrow-path" class="size-4" />
                                </button>
                            @else
                                <button wire:click="archive({{ $lot->id }})" title="Archivar"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                    <flux:icon icon="archive-box-arrow-down" class="size-4" />
                                </button>
                            @endif
                            <button wire:click="delete({{ $lot->id }})"
                                wire:confirm="¿Eliminar este lote de vino?"
                                title="Eliminar"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                <flux:icon icon="trash" class="size-4" />
                            </button>
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        <div class="mt-2">
            {{ $lots->links() }}
        </div>
    @else
        <x-agro.empty-state
            icon="beaker"
            title="No hay productos registrados"
            description="Crea el primer producto para gestionar tu stock"
        >
            <x-slot:action>
                <flux:button href="{{ route('winery.wine-lots.create') }}" wire:navigate variant="primary" icon="plus">
                    Nuevo Producto
                </flux:button>
            </x-slot:action>
        </x-agro.empty-state>
    @endif
</div>
