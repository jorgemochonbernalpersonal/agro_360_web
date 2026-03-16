<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Campañas"
        description="Resumen de campañas de vendimia y actividad por viticultor en la denominación."
    />

    {{-- Tabs --}}
    <div>
        <x-agro.tabs :tabs="$tabs" :active="$activeTab" wireMethod="setTab" />

        @if($activeTab === 'resumen')

            {{-- Summary by year --}}
            <x-agro.data-table
                :headers="['Año', 'Campañas viticultores', 'Activas', 'Recepciones uva', 'Total uva (kg)']"
                emptyMessage="No hay datos de campañas aún."
            >
                @foreach($summaryRows as $row)
                    <tr class="hover:bg-zinc-50 transition">
                        <td class="px-6 py-3 text-sm font-semibold text-zinc-800">
                            {{ $row->year }}
                            @if($row->year === $currentYear)
                                <span class="ml-1.5 text-[10px] font-bold bg-agro-100 text-agro-700 px-1.5 py-0.5 rounded-full uppercase">actual</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-sm text-zinc-500">
                            {{ $row->campaign_count }}
                        </td>
                        <td class="px-6 py-3 text-sm text-zinc-500">
                            {{ $row->active_count }}
                        </td>
                        <td class="px-6 py-3 text-sm text-zinc-500">
                            {{ $row->reception_count > 0 ? $row->reception_count : '—' }}
                        </td>
                        <td class="px-6 py-3 text-sm text-zinc-500">
                            @if($row->total_kg > 0)
                                {{ number_format($row->total_kg, 0, ',', '.') }} kg
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-agro.data-table>

        @else

            {{-- Per viticulturist --}}
            <x-agro.data-table
                :headers="['Viticultor', 'Campañas', 'Última campaña', 'Campaña activa', 'Kg entregados ' . $currentYear]"
                emptyMessage="No hay viticultores adscritos a esta denominación."
            >
                @foreach($viticulturistList as $vit)
                    @php $cs = $campaignSummaryByVit[$vit->id] ?? null; @endphp
                    <tr class="hover:bg-zinc-50 transition">
                        <td class="px-6 py-3 text-sm font-medium text-zinc-800">
                            <div>{{ $vit->name }}</div>
                            <div class="text-xs text-zinc-400">{{ $vit->email }}</div>
                        </td>
                        <td class="px-6 py-3 text-sm text-zinc-500">
                            {{ $cs?->total_campaigns ?? 0 }}
                        </td>
                        <td class="px-6 py-3 text-sm text-zinc-500">
                            {{ $cs?->latest_year ?? '—' }}
                        </td>
                        <td class="px-6 py-3">
                            @if($cs?->has_active)
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-agro-700 bg-agro-50 px-2 py-0.5 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-agro-500 flex-shrink-0"></span>
                                    Activa
                                </span>
                            @else
                                <span class="text-xs text-zinc-400">Sin campaña activa</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-sm text-zinc-500">
                            @if(isset($kgByVit[$vit->id]) && $kgByVit[$vit->id] > 0)
                                {{ number_format($kgByVit[$vit->id], 0, ',', '.') }} kg
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach

                <x-slot name="pagination">
                    {{ $viticulturistList->links() }}
                </x-slot>
            </x-agro.data-table>

        @endif
    </div>

</div>
