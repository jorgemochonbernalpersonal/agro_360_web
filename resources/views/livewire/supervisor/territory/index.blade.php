<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Territorio"
        description="Distribución geográfica de parcelas y variedades en la denominación de origen."
    />

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-agro.stat-card label="Parcelas activas" :value="$totalPlots" icon="map" color="blue" />
        <x-agro.stat-card label="Superficie total (ha)" :value="number_format($totalArea, 2)" icon="square-3-stack-3d" color="agro" />
        <x-agro.stat-card label="Parcelas ecológicas" :value="$organicPlots" icon="sparkles" color="agro" description="Certificadas ecológico" />
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
