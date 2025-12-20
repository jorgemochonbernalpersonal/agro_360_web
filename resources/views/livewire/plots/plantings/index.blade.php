<div class="space-y-6 animate-fade-in">
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>';
    @endphp

    <x-page-header
        :icon="$icon"
        title="Plantaciones"
        description="Listado global de plantaciones de variedades en tus parcelas"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    >
        <x-slot:actionButton>
            <a href="{{ route('plots.index') }}" class="group">
                <button
                    class="flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 transition-all duration-300 shadow-sm hover:shadow-md font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Ver parcelas
                </button>
            </a>
        </x-slot:actionButton>
    </x-page-header>

    <x-filter-section title="Filtros de Búsqueda" color="green">
        <x-filter-input
            wire:model.live="search"
            placeholder="Buscar por parcela o variedad..."
        />
        <x-filter-select wire:model.live="status">
            <option value="">Todos los estados</option>
            <option value="active">Activa</option>
            <option value="removed">Arrancada</option>
            <option value="experimental">Experimental</option>
            <option value="replanting">Replantación</option>
        </x-filter-select>
        <x-filter-select wire:model.live="year">
            <option value="">Todos los años</option>
            @foreach ($years as $yearOption)
                <option value="{{ $yearOption }}">{{ $yearOption }}</option>
            @endforeach
        </x-filter-select>
        <x-slot:actions>
            @if($search || $status !== '' || $year !== '')
                <x-button wire:click="clearFilters" variant="ghost" size="sm">
                    Limpiar Filtros
                </x-button>
            @endif
        </x-slot:actions>
    </x-filter-section>

    @php
        $headers = [
            ['label' => 'Nombre', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>'],
            ['label' => 'Parcela', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>'],
            ['label' => 'Variedad', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4a1 1 0 001 1h3m10-5v4a1 1 0 01-1 1h-3M5 21v-4a1 1 0 011-1h3m10 5v-4a1 1 0 00-1-1h-3"/></svg>'],
            ['label' => 'Superficie (ha)', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>'],
            ['label' => 'Año', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5A2 2 0 003 7v12a2 2 0 002 2z"/></svg>'],
            ['label' => 'Estado', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'],
            ['label' => 'Viticultor', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'],
            'Acciones',
        ];
    @endphp

    <x-data-table :headers="$headers" empty-message="No hay plantaciones registradas" empty-description="Comienza creando una plantación sobre una parcela">
        @if($plantings->count() > 0)
            @foreach($plantings as $planting)
                <x-table-row>
                    <x-table-cell>
                        @if($planting->name)
                            <span class="text-sm font-semibold text-purple-700">
                                {{ $planting->name }}
                            </span>
                        @else
                            <span class="text-sm text-gray-400 italic">—</span>
                        @endif
                    </x-table-cell>

                    <x-table-cell>
                        <div class="flex flex-col">
                            <a href="{{ route('plots.show', $planting->plot) }}"
                               class="text-sm font-bold text-[var(--color-agro-green-dark)] hover:underline">
                                {{ $planting->plot->name }}
                            </a>
                            <span class="text-xs text-gray-500">
                                {{ $planting->plot->municipality?->name ?? '' }}
                            </span>
                        </div>
                    </x-table-cell>

                    <x-table-cell>
                        @if($planting->grapeVariety)
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-900">
                                    {{ $planting->grapeVariety->name }}
                                    @if($planting->grapeVariety->code)
                                        ({{ $planting->grapeVariety->code }})
                                    @endif
                                </span>
                                @php
                                    $colorMap = [
                                        'red' => ['Tinto', 'bg-red-100 text-red-800'],
                                        'white' => ['Blanco', 'bg-yellow-100 text-yellow-800'],
                                        'rose' => ['Rosado', 'bg-pink-100 text-pink-800'],
                                    ];
                                    $colorInfo = $planting->grapeVariety->color ? ($colorMap[$planting->grapeVariety->color] ?? null) : null;
                                @endphp
                                @if($colorInfo)
                                    <span class="inline-flex mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $colorInfo[1] }}">
                                        {{ $colorInfo[0] }}
                                    </span>
                                @endif
                            </div>
                        @else
                            <span class="text-sm text-gray-500">Sin variedad</span>
                        @endif
                    </x-table-cell>

                    <x-table-cell>
                        <span class="text-sm font-medium text-gray-900">
                            {{ number_format($planting->area_planted, 3) }} ha
                        </span>
                    </x-table-cell>

                    <x-table-cell>
                        <div class="flex flex-col">
                            <span class="text-sm text-gray-700">
                                {{ $planting->planting_year ?? '—' }}
                            </span>
                            @if($planting->planting_date)
                                <span class="text-xs text-gray-500">
                                    {{ $planting->planting_date->format('d/m/Y') }}
                                </span>
                            @endif
                        </div>
                    </x-table-cell>

                    <x-table-cell>
                        @php
                            $statusColors = [
                                'active' => 'bg-green-100 text-green-800',
                                'removed' => 'bg-red-100 text-red-800',
                                'experimental' => 'bg-purple-100 text-purple-800',
                                'replanting' => 'bg-yellow-100 text-yellow-800',
                            ];
                            $label = [
                                'active' => 'Activa',
                                'removed' => 'Arrancada',
                                'experimental' => 'Experimental',
                                'replanting' => 'Replantación',
                            ][$planting->status] ?? $planting->status;
                        @endphp
                        <div class="flex flex-col gap-1">
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$planting->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $label }}
                            </span>
                            @if($planting->irrigated)
                                <span class="inline-flex items-center gap-1 text-[11px] text-[var(--color-agro-blue)]">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2.69l5.66 5.66a6 6 0 11-11.32 0L12 2.69z" />
                                    </svg>
                                    Con riego
                                </span>
                            @endif
                        </div>
                    </x-table-cell>

                    <x-table-cell>
                        <span class="text-sm text-gray-700">
                            {{ $planting->plot->viticulturist?->name ?? 'Sin asignar' }}
                        </span>
                    </x-table-cell>

                    <x-table-cell>
                        <x-table-actions align="right">
                            <x-action-button variant="view" href="{{ route('plots.show', $planting->plot) }}" />
                            @can('update', $planting->plot)
                                <x-action-button variant="edit" href="{{ route('plots.plantings.edit', $planting) }}" />
                            @endcan
                        </x-table-actions>
                    </x-table-cell>
                </x-table-row>
            @endforeach

            <x-slot name="pagination">
                {{ $plantings->links() }}
            </x-slot>
        @endif
    </x-data-table>
</div>


