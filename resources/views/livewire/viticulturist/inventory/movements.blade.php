<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <x-agro.page-header
        title="Historial de Movimientos"
        :description="'Movimientos de stock para: ' . $stock->product->name"
    >
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.inventory.index') }}" variant="ghost" icon="arrow-left">
                Volver
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <!-- Info del stock -->
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-agro-50">
                    <flux:icon icon="archive-box" class="size-4 text-agro-600" />
                </div>
                <span class="font-semibold text-zinc-900 text-sm">Datos del Stock</span>
            </div>
        </x-slot:header>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <span class="text-zinc-500">Cantidad Actual:</span>
                <span class="font-semibold text-zinc-900 ml-2">{{ number_format($stock->getAvailableQuantity(), 3) }} {{ $stock->unit }}</span>
            </div>
            @if($stock->warehouse)
                <div>
                    <span class="text-zinc-500">Almacén:</span>
                    <span class="font-semibold text-zinc-900 ml-2">{{ $stock->warehouse->name }}</span>
                </div>
            @endif
            @if($stock->batch_number)
                <div>
                    <span class="text-zinc-500">Lote:</span>
                    <span class="font-semibold text-zinc-900 ml-2">{{ $stock->batch_number }}</span>
                </div>
            @endif
        </div>
    </x-agro.card>

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <flux:field>
            <flux:label>Desde</flux:label>
            <flux:input wire:model.live="dateFrom" type="date" id="dateFrom" />
        </flux:field>
        <flux:field>
            <flux:label>Hasta</flux:label>
            <flux:input wire:model.live="dateTo" type="date" id="dateTo" />
        </flux:field>
    </x-agro.filter-bar>

    {{-- Tabla de Movimientos --}}
    <x-agro.data-table
        :headers="['Fecha', 'Tipo', 'Cantidad', 'Stock Antes', 'Stock Después', 'Tratamiento', 'Notas']"
        empty-message="No hay movimientos registrados"
    >
        @forelse($movements as $movement)
            <x-agro.table-row>
                <x-agro.table-cell class="text-zinc-500 text-sm">{{ $movement->created_at->format('d/m/Y H:i') }}</x-agro.table-cell>
                <x-agro.table-cell>
                    <x-agro.status-badge
                        :status="$movement->isInbound() ? 'active' : 'expired'"
                        :label="$movement->getMovementDescription()"
                    />
                </x-agro.table-cell>
                <x-agro.table-cell>
                    <span class="font-medium {{ $movement->isInbound() ? 'text-green-600' : 'text-red-600' }}">
                        {{ $movement->isInbound() ? '+' : '-' }}{{ number_format(abs($movement->quantity_change), 3) }}
                    </span>
                </x-agro.table-cell>
                <x-agro.table-cell class="text-zinc-500 text-sm">{{ number_format($movement->quantity_before, 3) }}</x-agro.table-cell>
                <x-agro.table-cell class="text-zinc-900 font-medium text-sm">{{ number_format($movement->quantity_after, 3) }}</x-agro.table-cell>
                <x-agro.table-cell class="text-sm">
                    @if($movement->treatment)
                        <a href="{{ route('viticulturist.digital-notebook') }}" class="text-agro-600 hover:underline">Ver tratamiento</a>
                        @if($movement->treatment->activity->plot)
                            <div class="text-xs text-zinc-400 mt-0.5">{{ $movement->treatment->activity->plot->name }}</div>
                        @endif
                    @else
                        <span class="text-zinc-400">-</span>
                    @endif
                </x-agro.table-cell>
                <x-agro.table-cell class="text-zinc-500 text-sm">{{ $movement->notes ?? '-' }}</x-agro.table-cell>
            </x-agro.table-row>
        @empty
        @endforelse

        <x-slot:pagination>
            {{ $movements->links() }}
        </x-slot:pagination>
    </x-agro.data-table>
</div>
