<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Estadísticas"
        description="Métricas agregadas de producción, superficie y actividad en la denominación de origen."
    />

    {{-- Stat cards --}}
    <div x-data="{
        open: localStorage.getItem('supervisor-statistics-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('supervisor-statistics-stats-open', String(this.open));
        }
    }">
        <button
            @click="toggle()"
            class="flex items-center gap-1.5 text-[11px] font-semibold text-zinc-400 uppercase tracking-widest hover:text-zinc-600 transition-colors mb-3"
        >
            <span>Estadísticas</span>
            <flux:icon icon="chevron-up" class="size-3.5 transition-transform duration-200" ::class="{ 'rotate-180': !open }" />
        </button>
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
        >
        <div class="grid grid-cols-2 gap-4">
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
        </div>
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

