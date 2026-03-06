<div class="space-y-6 animate-fade-in">
    <x-agro.page-header title="Lotes de Vino" description="Gestiona tu stock de vino para facturar a clientes">
        <x-slot:actions>
            <flux:button href="{{ route('winery.wine-lots.create') }}" wire:navigate variant="primary" icon="plus">
                Nuevo Lote
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <x-agro.stat-card label="Lotes activos"  :value="$stats['total']"                             icon="beaker" />
        <x-agro.stat-card label="Cantidad total" :value="number_format($stats['total_quantity'], 0)"  icon="archive-box" />
        <x-agro.stat-card label="Disponible"     :value="number_format($stats['total_available'], 0)" icon="check-circle" color="green" />
        <x-agro.stat-card label="Reservado"      :value="number_format($stats['total_reserved'], 0)"  icon="clock"        color="orange" />
        <x-agro.stat-card label="Vendido"        :value="number_format($stats['total_sold'], 0)"       icon="shopping-cart" color="blue" />
    </div>

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

    <x-agro.data-table
        :headers="['Lote', 'Tipo', 'Añada', 'Unidad', 'Total', 'Disponible', 'Reservado', 'Vendido', 'Precio/ud', 'Estado', 'Acciones']"
        empty-message="No hay lotes de vino registrados"
        empty-description="Crea el primer lote para gestionar tu stock"
    >
        @if ($lots->count() > 0)
            @foreach ($lots as $lot)
                @php
                    $avail = (float) $lot->available_quantity;
                    $total = (float) $lot->quantity;
                @endphp
                <x-agro.table-row>
                    <x-agro.table-cell>
                        <span class="text-sm font-semibold text-zinc-900">{{ $lot->name }}</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <flux:badge variant="outline" size="sm">{{ ucfirst($lot->wine_type) }}</flux:badge>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-600">{{ $lot->vintage ?? '—' }}</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-600">{{ $lot->unit }}</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-600">{{ number_format($total, 0) }}</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <div class="flex items-center gap-2">
                            <span class="{{ $avail <= 0 ? 'text-red-600 font-semibold' : 'text-zinc-700' }} text-sm">
                                {{ number_format($avail, 0) }}
                            </span>
                            @if ($total > 0)
                                <x-agro.progress-bar :percent="round($avail / $total * 100)" class="w-16" />
                            @endif
                        </div>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm text-orange-600">{{ number_format((float) $lot->reserved_quantity, 0) ?: '—' }}</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm text-blue-600">{{ number_format((float) $lot->sold_quantity, 0) ?: '—' }}</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-600">
                            {{ $lot->price_per_unit > 0 ? number_format($lot->price_per_unit, 2) . ' €' : '—' }}
                        </span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @if ($lot->archived)
                            <flux:badge color="zinc" size="sm">Archivado</flux:badge>
                        @elseif ($avail <= 0)
                            <flux:badge color="red" size="sm">Sin stock</flux:badge>
                        @else
                            <flux:badge color="green" size="sm">Disponible</flux:badge>
                        @endif
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('winery.wine-lots.edit', $lot) }}" wire:navigate title="Editar">
                                <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                    <flux:icon icon="pencil" class="size-4" />
                                </button>
                            </a>
                            @if ($lot->archived)
                                <button
                                    wire:click="unarchive({{ $lot->id }})"
                                    title="Reactivar"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors"
                                >
                                    <flux:icon icon="arrow-path" class="size-4" />
                                </button>
                            @else
                                <button
                                    wire:click="archive({{ $lot->id }})"
                                    title="Archivar"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors"
                                >
                                    <flux:icon icon="archive-box-arrow-down" class="size-4" />
                                </button>
                            @endif
                            <button
                                wire:click="delete({{ $lot->id }})"
                                wire:confirm="¿Eliminar este lote de vino?"
                                title="Eliminar"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                            >
                                <flux:icon icon="trash" class="size-4" />
                            </button>
                        </div>
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach

            <x-slot name="pagination">
                {{ $lots->links() }}
            </x-slot>
        @endif
    </x-agro.data-table>
</div>
