<div class="space-y-6 animate-fade-in">
    @php
        $plotIcon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>';
    @endphp
    <x-page-header
        :icon="$plotIcon"
        title="Gestión de Parcelas"
        description="Administra y visualiza todas tus parcelas agrícolas"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    >
        <x-slot:actionButton>
            <div class="flex items-center gap-3">
                @can('create', \App\Models\Plot::class)
                    <a href="{{ route('plots.create') }}" class="group">
                        <button class="flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold">
                            <svg class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Nueva Parcela
                        </button>
                    </a>
                @endcan

                <a href="{{ route('plots.plantings.index') }}" class="group">
                    <button
                        class="flex items-center gap-2 px-4 py-3 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 transition-all duration-300 shadow-sm hover:shadow-md font-semibold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 3v4a1 1 0 001 1h3m10-5v4a1 1 0 01-1 1h-3M5 21v-4a1 1 0 011-1h3m10 5v-4a1 1 0 00-1-1h-3"/>
                        </svg>
                        Ver plantaciones
                    </button>
                </a>
            </div>
        </x-slot:actionButton>
    </x-page-header>

    <x-filter-section title="Filtros de Búsqueda" color="green">
        <x-filter-input 
            wire:model.live="search" 
            placeholder="Buscar por nombre de parcela..."
        />
        <x-filter-select wire:model.live="activeFilter">
            <option value="">Todas las parcelas</option>
            <option value="1">Activas</option>
            <option value="0">Inactivas</option>
        </x-filter-select>
        <x-slot:actions>
            @if($search || $activeFilter !== '')
                <x-button wire:click="clearFilters" variant="ghost" size="sm">
                    Limpiar Filtros
                </x-button>
            @endif
        </x-slot:actions>
    </x-filter-section>

    @php
        $headers = [
            ['label' => 'Nombre', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>'],
            // Bodega ahora se deriva de la(s) winery(s) del viticultor de la parcela
            ['label' => 'Bodega', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>'],
        ];
        
        if(auth()->user()->canSelectViticulturist()) {
            $headers[] = ['label' => 'Viticultor', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'];
        }
        
        $headers[] = ['label' => 'Área', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>'];
        $headers[] = ['label' => 'Estado', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'];
        $headers[] = 'Acciones';
    @endphp

    <x-data-table :headers="$headers" empty-message="No hay parcelas registradas" empty-description="Comienza agregando tu primera parcela al sistema">
        @if($plots->count() > 0)
            @foreach($plots as $plot)
                <x-table-row>
                    <x-table-cell>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[var(--color-agro-green-light)] to-[var(--color-agro-green)] flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-200">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-900">{{ $plot->name }}</div>
                                @if($plot->description)
                                    <div class="text-sm text-gray-500 mt-1">{{ \Illuminate\Support\Str::limit($plot->description, 50) }}</div>
                                @endif
                            </div>
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        @php
                            // La bodega se infiere de las wineries asociadas al viticultor de la parcela
                            $wineryName = '-';
                            if ($plot->viticulturist && $plot->viticulturist->wineries->isNotEmpty()) {
                                $wineryName = $plot->viticulturist->wineries->first()->name;
                            }
                        @endphp
                        <span class="text-sm font-medium text-gray-900">{{ $wineryName }}</span>
                    </x-table-cell>
                    @if(auth()->user()->canSelectViticulturist())
                        <x-table-cell>
                            <span class="text-sm text-gray-700">{{ $plot->viticulturist?->name ?? 'Sin asignar' }}</span>
                        </x-table-cell>
                    @endif
                    <x-table-cell>
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-[var(--color-agro-blue-light)] text-[var(--color-agro-blue)] text-sm font-semibold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                            </svg>
                            {{ $plot->area ? number_format($plot->area, 3) . ' ha' : '-' }}
                        </span>
                    </x-table-cell>
                    <x-table-cell>
                        <x-status-badge :active="$plot->active" />
                    </x-table-cell>
                    <x-table-actions align="right">
                        <x-action-button variant="view" href="{{ route('plots.show', $plot) }}" />
                        @can('update', $plot)
                            {{-- Botón Generar Mapa si tiene códigos SIGPAC --}}
                            @php
                                $hasSigpacCodes = $plot->sigpacCodes->count() > 0;
                                $hasGeometry = $plot->multiplePlotSigpacs()
                                    ->whereNotNull('plot_geometry_id')
                                    ->exists();
                                $firstSigpacCode = $plot->sigpacCodes->first();
                            @endphp
                            
                            @if($hasSigpacCodes && $firstSigpacCode)
                                @if($hasGeometry)
                                    {{-- Botón Ver Mapa (solo lectura) --}}
                                    <a href="{{ route('sigpac.geometry.edit-plot', [
                                        'sigpacId' => $firstSigpacCode->id, 
                                        'plotId' => $plot->id,
                                        'view' => 'true'
                                    ]) }}"
                                       class="p-2 rounded-lg transition-all duration-200 group/btn text-green-600 hover:bg-green-50"
                                       title="Ver Mapa">
                                        <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                @else
                                    {{-- Botón Generar Mapa --}}
                                    <a href="{{ route('sigpac.geometry.edit-plot', [
                                        'sigpacId' => $firstSigpacCode->id, 
                                        'plotId' => $plot->id
                                    ]) }}"
                                       class="p-2 rounded-lg transition-all duration-200 group/btn text-purple-600 hover:bg-purple-50"
                                       title="Generar Mapa">
                                        <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                        </svg>
                                    </a>
                                @endif
                            @endif

                            {{-- Crear plantación sobre esta parcela --}}
                            <a href="{{ route('plots.plantings.create', $plot) }}"
                               class="p-2 rounded-lg transition-all duration-200 group/btn text-[var(--color-agro-green-dark)] hover:bg-[var(--color-agro-green-bg)]"
                               title="Añadir plantación">
                                <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </a>

                            <x-action-button variant="edit" href="{{ route('plots.edit', $plot) }}" />
                        @endcan
                        @can('delete', $plot)
                            <x-action-button 
                                variant="delete" 
                                wire:click="delete({{ $plot->id }})"
                                wire:confirm="¿Estás seguro de eliminar esta parcela?"
                            />
                        @endcan
                    </x-table-actions>
                </x-table-row>
            @endforeach
            <x-slot name="pagination">
                {{ $plots->links() }}
            </x-slot>
        @else
            <x-slot name="emptyAction">
                @can('create', \App\Models\Plot::class)
                    <x-button href="{{ route('plots.create') }}" variant="primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Crear mi primera parcela
                    </x-button>
                @endcan
            </x-slot>
        @endif
    </x-data-table>
</div>
