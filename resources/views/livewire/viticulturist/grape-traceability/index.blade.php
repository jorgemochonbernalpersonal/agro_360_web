<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        :title="__('Trazabilidad de Uva')"
        :description="__('Seguimiento de la uva desde la parcela hasta el destino final. Cumple con la normativa EU de trazabilidad alimentaria.')"
        icon="arrow-trending-up"
    />

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-agro.stat-card
            :label="__('Entradas registradas')"
            :value="$stats->total_entries"
            icon="clipboard-document-list"
            color="blue"
        />
        <x-agro.stat-card
            :label="__('Parcelas de origen')"
            :value="$stats->plots_count"
            icon="map"
            color="cyan"
        />
        <x-agro.stat-card
            :label="__('Total transportado')"
            :value="number_format($stats->total_kg, 0, ',', '.') . ' kg'"
            icon="truck"
            color="agro"
        />
        <x-agro.stat-card
            :label="__('Destinos')"
            :value="$stats->destinations_count"
            icon="building-office-2"
            color="violet"
        />
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-[200px] relative">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                :placeholder="__('Buscar por parcela, variedad, destino, transportista...')"
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
            />
        </div>
        <flux:select wire:model.live="filterCampaign" class="w-48">
            <option value="">{{ __('Todas las campañas') }}</option>
            @foreach ($campaigns as $c)
                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->year }})</option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="filterPlot" class="w-48">
            <option value="">{{ __('Todas las parcelas') }}</option>
            @foreach ($plots as $plot)
                <option value="{{ $plot->id }}">{{ $plot->name }}</option>
            @endforeach
        </flux:select>
    </div>

    {{-- Tabla de trazabilidad --}}
    <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-zinc-50 text-zinc-500 text-xs uppercase tracking-wide">
                        <th class="text-left px-5 py-3 font-semibold">{{ __('Fecha') }}</th>
                        <th class="text-left px-3 py-3 font-semibold">{{ __('Parcela') }}</th>
                        <th class="text-left px-3 py-3 font-semibold">{{ __('Variedad') }}</th>
                        <th class="text-right px-3 py-3 font-semibold">{{ __('Peso (kg)') }}</th>
                        <th class="text-right px-3 py-3 font-semibold">{{ __('Baumé') }}</th>
                        <th class="text-left px-3 py-3 font-semibold">{{ __('Destino') }}</th>
                        <th class="text-left px-3 py-3 font-semibold">{{ __('Comprador') }}</th>
                        <th class="text-left px-3 py-3 font-semibold">{{ __('Documento') }}</th>
                        <th class="text-left px-5 py-3 font-semibold">{{ __('Vehículo') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($entries as $entry)
                        <tr class="hover:bg-zinc-50 transition-colors" wire:key="trace-{{ $entry->id }}">
                            <td class="px-5 py-3 whitespace-nowrap">
                                <span class="font-medium text-zinc-900">{{ $entry->harvest_start_date?->format('d/m/Y') }}</span>
                            </td>
                            <td class="px-3 py-3">
                                <span class="font-medium text-zinc-700">{{ $entry->plot_name }}</span>
                            </td>
                            <td class="px-3 py-3 text-zinc-600">
                                {{ $entry->variety_name ?? '—' }}
                            </td>
                            <td class="px-3 py-3 text-right tabular-nums font-medium text-zinc-900">
                                {{ number_format($entry->total_weight, 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-3 text-right tabular-nums text-zinc-600">
                                {{ $entry->baume_degree ? number_format($entry->baume_degree, 1, ',', '.') . '°' : '—' }}
                            </td>
                            <td class="px-3 py-3 text-zinc-600 max-w-[150px] truncate" title="{{ $entry->destination }}">
                                {{ $entry->destination ?? '—' }}
                            </td>
                            <td class="px-3 py-3 text-zinc-600 max-w-[130px] truncate" title="{{ $entry->buyer_name }}">
                                {{ $entry->buyer_name ?? '—' }}
                            </td>
                            <td class="px-3 py-3">
                                @if ($entry->transport_document_number)
                                    <span class="text-xs font-mono bg-zinc-100 px-2 py-0.5 rounded text-zinc-600">{{ $entry->transport_document_number }}</span>
                                @else
                                    <span class="text-zinc-300">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-zinc-500 text-xs">
                                {{ $entry->vehicle_plate ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-12 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <flux:icon icon="arrow-trending-up" class="size-8 text-zinc-300" />
                                    <p class="text-sm text-zinc-400">{{ __('Sin registros de trazabilidad para los filtros seleccionados') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-agro-pagination :paginator="$entries" />

</div>
