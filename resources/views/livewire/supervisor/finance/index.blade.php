<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Negocio DO"
        description="Suscripciones de viticultores y actividad económica de bodegas adscritas."
    />

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-agro.stat-card label="Suscripciones activas" :value="$activeSubscriptions" icon="credit-card" color="agro" />
        <x-agro.stat-card label="Ingresos mensuales (€)" :value="number_format($monthlyRevenue, 2, ',', '.')" icon="banknotes" color="blue" description="Planes mensuales activos" />
        <x-agro.stat-card label="Ingresos anuales (€)" :value="number_format($yearlyRevenue, 2, ',', '.')" icon="banknotes" color="yellow" description="Planes anuales activos" />
    </div>

    {{-- Tabs --}}
    <div>
        <x-agro.tabs :tabs="$tabs" :active="$activeTab" wireMethod="setTab" />

        @if($activeTab === 'subscriptions')
            <x-agro.data-table
                :headers="['Viticultor', 'Email', 'Plan', 'Estado', 'Importe (€)', 'Válida hasta']"
                emptyMessage="No hay suscripciones de viticultores DO."
            >
                @foreach($subscriptions as $sub)
                    <tr class="hover:bg-zinc-50 transition">
                        <td class="px-6 py-3 text-sm font-medium text-zinc-800">{{ $sub->user?->name ?? '—' }}</td>
                        <td class="px-6 py-3 text-sm text-zinc-500">{{ $sub->user?->email ?? '—' }}</td>
                        <td class="px-6 py-3 text-sm text-zinc-500 capitalize">{{ $sub->plan_type }}</td>
                        <td class="px-6 py-3 text-sm">
                            <x-agro.status-badge :status="$sub->status" :labels="['active' => 'Activa', 'cancelled' => 'Cancelada', 'expired' => 'Expirada']" />
                        </td>
                        <td class="px-6 py-3 text-sm text-zinc-500">{{ number_format($sub->amount, 2, ',', '.') }}</td>
                        <td class="px-6 py-3 text-sm text-zinc-500">{{ $sub->ends_at?->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                @endforeach

                <x-slot name="pagination">{{ $subscriptions->links() }}</x-slot>
            </x-agro.data-table>

        @else
            <x-agro.data-table
                :headers="['Bodega', 'Uva recibida (kg) ' . $currentYear, 'Valor total (€) ' . $currentYear]"
                emptyMessage="No hay datos de recepciones para el año actual."
            >
                @foreach($harvestValueByWinery as $row)
                    <tr class="hover:bg-zinc-50 transition">
                        <td class="px-6 py-3 text-sm font-medium text-zinc-800">{{ $row->winery_name }}</td>
                        <td class="px-6 py-3 text-sm text-zinc-500">{{ number_format($row->total_kg, 0, ',', '.') }} kg</td>
                        <td class="px-6 py-3 text-sm text-zinc-500">
                            @if($row->total_value > 0)
                                {{ number_format($row->total_value, 2, ',', '.') }} €
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-agro.data-table>
        @endif
    </div>

</div>
