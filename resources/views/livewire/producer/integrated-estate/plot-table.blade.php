<div>
    {{-- Buscador de parcela --}}
    <div class="mb-4">
        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar parcela..."
            icon="magnifying-glass"
            class="max-w-xs"
        />
    </div>

    @if($paginatedPlots->isEmpty())
        <x-agro.empty-state icon="map" title="Sin parcelas" description="No hay parcelas que coincidan con los filtros seleccionados." />
    @else
        @php
            $stageColors = ['joven' => 'bg-emerald-400', 'desarrollo' => 'bg-blue-400', 'productiva' => 'bg-green-500', 'madura' => 'bg-amber-400', 'vieja' => 'bg-red-400', 'desconocido' => 'bg-zinc-300'];
            $stageLabels = ['joven' => 'Joven', 'desarrollo' => 'En desarrollo', 'productiva' => 'Productiva', 'madura' => 'Madura', 'vieja' => 'Vieja', 'desconocido' => 'Desconocido'];
        @endphp

        <div class="space-y-4">
            @foreach($paginatedPlots as $plot)
                <x-agro.card wire:key="plot-{{ $plot->id }}">
                    <x-slot:header>
                        <div class="flex items-center justify-between w-full">
                            <div class="flex items-center gap-2">
                                <flux:icon icon="map" class="size-4 text-cyan-600" />
                                <span class="text-sm font-semibold text-zinc-800">{{ $plot->name }}</span>
                                @if($plot->municipality?->name)
                                    <span class="text-xs text-zinc-400">· {{ $plot->municipality->name }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 text-xs text-zinc-500">
                                @if($plot->area)
                                    <span>{{ number_format($plot->area, 2) }} ha</span>
                                @endif
                                @if($plot->latestRemoteSensing)
                                    <span class="flex items-center gap-1" title="NDVI ({{ $plot->latestRemoteSensing->image_date?->format('d/m/Y') }})">
                                        <flux:icon icon="globe-alt" class="size-3" />
                                        NDVI {{ number_format($plot->latestRemoteSensing->ndvi ?? 0, 2) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </x-slot:header>

                    @if($plot->plantings->isEmpty())
                        <p class="text-sm text-zinc-400">Sin plantaciones activas.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-xs text-zinc-500 uppercase bg-zinc-50/50">
                                        <th class="text-left px-3 py-2">Plantación</th>
                                        <th class="text-left px-3 py-2">Variedad</th>
                                        <th class="text-center px-3 py-2">Edad</th>
                                        <th class="text-center px-3 py-2">Etapa</th>
                                        <th class="text-center px-3 py-2">Fenología</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-100">
                                    @foreach($plot->plantings as $planting)
                                        @php
                                            $pheno = $latestPhenology[$planting->id] ?? null;
                                            $stage = $planting->life_cycle_stage ?? 'desconocido';
                                        @endphp
                                        <tr class="hover:bg-zinc-50/50">
                                            <td class="px-3 py-2 font-medium text-zinc-700">{{ $planting->name }}</td>
                                            <td class="px-3 py-2 text-zinc-600">{{ $planting->grapeVariety?->name ?? '—' }}</td>
                                            <td class="px-3 py-2 text-center text-zinc-600">{{ $planting->age ?? '—' }} años</td>
                                            <td class="px-3 py-2 text-center">
                                                <span class="inline-flex items-center gap-1">
                                                    <span class="w-2 h-2 rounded-full {{ $stageColors[$stage] ?? 'bg-zinc-300' }}"></span>
                                                    <span class="text-xs text-zinc-600">{{ $stageLabels[$stage] ?? ucfirst($stage) }}</span>
                                                </span>
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                @if($pheno)
                                                    <flux:badge size="sm" color="green">{{ $pheno->event_label ?? $pheno->event }}</flux:badge>
                                                    <span class="text-xs text-zinc-400 ml-1">{{ $pheno->obs_date?->format('d/m') }}</span>
                                                @else
                                                    <span class="text-xs text-zinc-400">Sin observar</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </x-agro.card>
            @endforeach
        </div>

        {{-- Paginación --}}
        <x-agro-pagination :paginator="$paginatedPlots" />
    @endif
</div>
