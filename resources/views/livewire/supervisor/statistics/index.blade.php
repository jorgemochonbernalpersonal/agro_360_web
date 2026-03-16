<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Estadísticas"
        description="Métricas agregadas de producción, superficie y actividad en la denominación de origen."
    />

    {{-- Stat cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <x-agro.stat-card
            label="Bodegas"
            :value="$totalWineries"
            icon="building-office-2"
            color="blue"
        />
        <x-agro.stat-card
            label="Viticultores DO"
            :value="$totalViticulturists"
            icon="users"
            color="agro"
        />
        <x-agro.stat-card
            label="Superficie (ha)"
            :value="number_format($totalPlotAreaHa, 2)"
            icon="map"
            color="yellow"
        />
        <x-agro.stat-card
            label="Uva {{ $currentYear }} (kg)"
            :value="number_format($totalKgCurrentVintage, 0, ',', '.')"
            icon="scale"
            color="orange"
            :description="'Vendimia ' . $currentYear"
        />
    </div>

    {{-- Harvest by vintage table --}}
    <div>
        <h2 class="text-sm font-semibold text-zinc-700 mb-3">Histórico de vendimias</h2>

        <x-agro.data-table
            :headers="['Añada', 'Recepciones', 'Total uva (kg)', 'Brix medio', '']"
            emptyMessage="No hay datos de vendimias aún."
        >
            @foreach($harvestByVintage as $row)
                <tr class="hover:bg-zinc-50 transition">
                    <td class="px-6 py-3 text-sm font-semibold text-zinc-800">
                        {{ $row->vintage }}
                        @if($row->vintage === $currentYear)
                            <span class="ml-1.5 text-[10px] font-bold bg-agro-100 text-agro-700 px-1.5 py-0.5 rounded-full uppercase">actual</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-sm text-zinc-500">
                        {{ $row->reception_count }}
                    </td>
                    <td class="px-6 py-3 text-sm text-zinc-500">
                        {{ number_format($row->total_kg, 0, ',', '.') }} kg
                    </td>
                    <td class="px-6 py-3 text-sm text-zinc-500">
                        @if($row->avg_brix)
                            {{ $row->avg_brix }} °Bx
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-6 py-3"></td>
                </tr>
            @endforeach
        </x-agro.data-table>
    </div>

</div>
