<div>
    <x-agro.page-header title="Lotes de Vino" description="Gestiona tu stock de vino para facturar a clientes">
        <x-slot name="actions">
            <flux:button href="{{ route('winery.wine-lots.create') }}" wire:navigate variant="primary" icon="plus">
                Nuevo Lote
            </flux:button>
        </x-slot>
    </x-agro.page-header>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <x-agro.stat-card label="Lotes activos"  :value="$stats['total']"                          icon="beaker" />
        <x-agro.stat-card label="Cantidad total" :value="number_format($stats['total_quantity'], 0)" icon="archive-box" />
        <x-agro.stat-card label="Disponible"     :value="number_format($stats['total_available'], 0)" icon="check-circle"  color="green" />
        <x-agro.stat-card label="Reservado"      :value="number_format($stats['total_reserved'], 0)"  icon="clock"         color="orange" />
        <x-agro.stat-card label="Vendido"        :value="number_format($stats['total_sold'], 0)"       icon="shopping-cart" color="blue" />
    </div>

    <!-- Filters -->
    <x-agro.filter-bar>
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre..." icon="magnifying-glass" clearable />

        <flux:select wire:model.live="typeFilter" class="w-40">
            <option value="">Todos los tipos</option>
            <option value="tinto">Tinto</option>
            <option value="blanco">Blanco</option>
            <option value="rosado">Rosado</option>
            <option value="espumoso">Espumoso</option>
            <option value="otro">Otro</option>
        </flux:select>

        <flux:select wire:model.live="statusFilter" class="w-40">
            <option value="active">Activos</option>
            <option value="archived">Archivados</option>
            <option value="all">Todos</option>
        </flux:select>

        @if ($search || $typeFilter || $statusFilter !== 'active')
            <flux:button wire:click="clearFilters" variant="ghost" size="sm">Limpiar</flux:button>
        @endif
    </x-agro.filter-bar>

    <!-- Table -->
    <x-agro.table>
        <x-slot name="head">
            <x-agro.th>Lote</x-agro.th>
            <x-agro.th>Tipo</x-agro.th>
            <x-agro.th>Añada</x-agro.th>
            <x-agro.th>Unidad</x-agro.th>
            <x-agro.th>Total</x-agro.th>
            <x-agro.th>Disponible</x-agro.th>
            <x-agro.th>Reservado</x-agro.th>
            <x-agro.th>Vendido</x-agro.th>
            <x-agro.th>Precio/ud</x-agro.th>
            <x-agro.th>Estado</x-agro.th>
            <x-agro.th class="text-right">Acciones</x-agro.th>
        </x-slot>

        @forelse ($lots as $lot)
            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $lot->name }}</td>
                <td class="px-4 py-3">
                    <flux:badge variant="outline" size="sm">{{ ucfirst($lot->wine_type) }}</flux:badge>
                </td>
                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $lot->vintage ?? '—' }}</td>
                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $lot->unit }}</td>
                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ number_format($lot->quantity, 0) }}</td>
                <td class="px-4 py-3">
                    @php $avail = (float)$lot->available_quantity; $total = (float)$lot->quantity; @endphp
                    <div class="flex items-center gap-2">
                        <span class="{{ $avail <= 0 ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-zinc-700 dark:text-zinc-300' }}">
                            {{ number_format($avail, 0) }}
                        </span>
                        @if ($total > 0)
                            <x-agro.progress-bar :percent="round($avail / $total * 100)" class="w-16" />
                        @endif
                    </div>
                </td>
                <td class="px-4 py-3 text-orange-600 dark:text-orange-400 text-sm">
                    {{ number_format((float)$lot->reserved_quantity, 0) ?: '—' }}
                </td>
                <td class="px-4 py-3 text-blue-600 dark:text-blue-400 text-sm">
                    {{ number_format((float)$lot->sold_quantity, 0) ?: '—' }}
                </td>
                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                    {{ $lot->price_per_unit > 0 ? number_format($lot->price_per_unit, 2) . ' €' : '—' }}
                </td>
                <td class="px-4 py-3">
                    @if ($lot->archived)
                        <flux:badge color="zinc" size="sm">Archivado</flux:badge>
                    @elseif ($avail <= 0)
                        <flux:badge color="red" size="sm">Sin stock</flux:badge>
                    @else
                        <flux:badge color="green" size="sm">Disponible</flux:badge>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex justify-end gap-2">
                        <flux:button href="{{ route('winery.wine-lots.edit', $lot) }}" wire:navigate size="sm" variant="ghost" icon="pencil" />

                        @if ($lot->archived)
                            <flux:button wire:click="unarchive({{ $lot->id }})" size="sm" variant="ghost" icon="arrow-path" title="Reactivar" />
                        @else
                            <flux:button wire:click="archive({{ $lot->id }})" size="sm" variant="ghost" icon="archive-box-arrow-down" title="Archivar" />
                        @endif

                        <flux:button wire:click="delete({{ $lot->id }})"
                            wire:confirm="¿Eliminar este lote de vino?"
                            size="sm" variant="ghost" icon="trash" class="text-red-500" />
                    </div>
                </td>
            </tr>
        @empty
            <x-agro.empty-row colspan="11" message="No hay lotes de vino registrados." />
        @endforelse
    </x-agro.table>

    <div class="mt-4">{{ $lots->links() }}</div>
</div>
