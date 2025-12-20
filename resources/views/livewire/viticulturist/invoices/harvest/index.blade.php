<div class="space-y-6 animate-fade-in">
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Facturar Cosechas"
        description="Selecciona cosechas para facturar"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    />

    <!-- Filtros -->
    <div class="glass-card rounded-xl p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-input wire:model.live="search" type="text" placeholder="Buscar cosechas..." />
        </div>
    </div>

    <!-- Tabla -->
    <div class="glass-card rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Parcela</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Variedad</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peso (kg)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($harvests as $harvest)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $harvest->activity->plot->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $harvest->plotPlanting->grapeVariety->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $harvest->harvest_start_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($harvest->total_weight, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $harvest->total_value ? number_format($harvest->total_value, 2) . ' €' : 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('viticulturist.invoices.create', ['harvest_id' => $harvest->id]) }}" 
                                   wire:navigate
                                   class="inline-flex items-center gap-1 px-4 py-2 bg-[var(--color-agro-green)] text-white rounded-lg hover:bg-[var(--color-agro-green-dark)] transition font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Facturar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No hay cosechas disponibles para facturar</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">
            {{ $harvests->links() }}
        </div>
    </div>
</div>
