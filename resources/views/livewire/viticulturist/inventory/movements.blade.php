<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Historial de Movimientos"
        description="Movimientos de stock para: {{ $stock->product->name }}"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
        :back-url="route('viticulturist.inventory.index')"
    >
        <div class="mt-4 p-4 bg-gray-50 rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="text-gray-600">Cantidad Actual:</span>
                    <span class="font-semibold text-gray-900 ml-2">{{ number_format($stock->getAvailableQuantity(), 3) }} {{ $stock->unit }}</span>
                </div>
                @if($stock->warehouse)
                    <div>
                        <span class="text-gray-600">Almacén:</span>
                        <span class="font-semibold text-gray-900 ml-2">{{ $stock->warehouse->name }}</span>
                    </div>
                @endif
                @if($stock->batch_number)
                    <div>
                        <span class="text-gray-600">Lote:</span>
                        <span class="font-semibold text-gray-900 ml-2">{{ $stock->batch_number }}</span>
                    </div>
                @endif
            </div>
        </div>
    </x-page-header>

    <!-- Filtros -->
    <div class="glass-card rounded-xl p-6 border border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-label for="dateFrom">Desde</x-label>
                <x-input 
                    wire:model.live="dateFrom" 
                    type="date" 
                    id="dateFrom"
                />
            </div>
            <div>
                <x-label for="dateTo">Hasta</x-label>
                <x-input 
                    wire:model.live="dateTo" 
                    type="date" 
                    id="dateTo"
                />
            </div>
        </div>
    </div>

    <!-- Tabla de Movimientos -->
    <div class="glass-card rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Antes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Después</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tratamiento</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notas</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($movements as $movement)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $movement->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $movement->isInbound() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $movement->getMovementDescription() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium {{ $movement->isInbound() ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $movement->isInbound() ? '+' : '-' }}{{ number_format(abs($movement->quantity_change), 3) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($movement->quantity_before, 3) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ number_format($movement->quantity_after, 3) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($movement->treatment)
                                    <a href="{{ route('viticulturist.digital-notebook') }}" class="text-[var(--color-agro-green)] hover:underline">
                                        Ver tratamiento
                                    </a>
                                    @if($movement->treatment->activity->plot)
                                        <div class="text-xs text-gray-400">
                                            Parcela: {{ $movement->treatment->activity->plot->name }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $movement->notes ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p class="mt-4 text-sm">No hay movimientos registrados</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $movements->links() }}
        </div>
    </div>
</div>
