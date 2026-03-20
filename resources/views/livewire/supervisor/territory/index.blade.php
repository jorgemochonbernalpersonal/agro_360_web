<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Territorio"
        description="Distribución geográfica de parcelas y variedades en la denominación de origen."
    />

    {{-- Stats --}}
    <div x-data="{
        open: localStorage.getItem('supervisor-territory-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('supervisor-territory-stats-open', String(this.open));
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
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-agro.stat-card label="Parcelas activas" :value="$totalPlots" icon="map" color="blue" />
            <x-agro.stat-card label="Superficie total (ha)" :value="number_format($totalArea, 2)" icon="square-3-stack-3d" color="agro" />
            <x-agro.stat-card label="Parcelas ecológicas" :value="$organicPlots" icon="sparkles" color="agro" description="Certificadas ecológico" />
        </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div>
        <x-agro.tabs :tabs="$tabs" :active="$activeTab" wireMethod="setTab" />

        @if($activeTab === 'provinces')
            <x-agro.data-table
                :headers="['Provincia', 'Parcelas', 'Superficie (ha)', 'Ecológicas', '% del total']"
                emptyMessage="No hay parcelas registradas."
            >
                @foreach($byProvince as $row)
                    <tr class="hover:bg-zinc-50 transition">
                        <td class="px-6 py-3 text-sm font-medium text-zinc-800">{{ $row->province_name ?? '—' }}</td>
                        <td class="px-6 py-3 text-sm text-zinc-500">{{ $row->plot_count }}</td>
                        <td class="px-6 py-3 text-sm text-zinc-500">{{ number_format($row->total_area, 2) }} ha</td>
                        <td class="px-6 py-3 text-sm text-zinc-500">{{ $row->organic_count }}</td>
                        <td class="px-6 py-3 text-sm text-zinc-400">
                            @if($totalArea > 0)
                                {{ number_format(($row->total_area / $totalArea) * 100, 1) }}%
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-agro.data-table>

        @elseif($activeTab === 'varieties')
            <x-agro.data-table
                :headers="['Variedad', 'Color', 'Parcelas', 'Superficie plantada (ha)', '% del total']"
                emptyMessage="No hay plantaciones activas."
            >
                @php $totalPlanted = $byVariety->sum('planted_area'); @endphp
                @foreach($byVariety as $row)
                    <tr class="hover:bg-zinc-50 transition">
                        <td class="px-6 py-3 text-sm font-medium text-zinc-800">{{ $row->variety_name }}</td>
                        <td class="px-6 py-3 text-sm text-zinc-500 capitalize">{{ $row->variety_color ?? '—' }}</td>
                        <td class="px-6 py-3 text-sm text-zinc-500">{{ $row->plot_count }}</td>
                        <td class="px-6 py-3 text-sm text-zinc-500">{{ number_format($row->planted_area, 2) }} ha</td>
                        <td class="px-6 py-3 text-sm text-zinc-400">
                            @if($totalPlanted > 0)
                                {{ number_format(($row->planted_area / $totalPlanted) * 100, 1) }}%
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-agro.data-table>

        @else
            <x-agro.data-table
                :headers="['Municipio', 'Parcelas', 'Superficie (ha)']"
                emptyMessage="No hay municipios con parcelas."
            >
                @foreach($byMunicipality as $row)
                    <tr class="hover:bg-zinc-50 transition">
                        <td class="px-6 py-3 text-sm font-medium text-zinc-800">{{ $row->municipality_name ?? '—' }}</td>
                        <td class="px-6 py-3 text-sm text-zinc-500">{{ $row->plot_count }}</td>
                        <td class="px-6 py-3 text-sm text-zinc-500">{{ number_format($row->total_area, 2) }} ha</td>
                    </tr>
                @endforeach
            </x-agro.data-table>
        @endif
    </div>

</div>

