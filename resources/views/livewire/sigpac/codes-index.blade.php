<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Códigos SIGPAC"
        description="Gestiona los códigos de identificación SIGPAC"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    >
        <x-slot:actionButton>
            <a 
                href="{{ route('sigpac.codes.create') }}"
                class="px-4 py-2 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all font-semibold"
            >
                + Crear Código SIGPAC
            </a>
        </x-slot:actionButton>
    </x-page-header>

    <!-- Búsqueda -->
    <x-filter-section title="Buscar código" color="green">
        <x-filter-input 
            wire:model.live.debounce.300ms="search" 
            placeholder="Buscar por código..."
        />
    </x-filter-section>

    <!-- Tabla de códigos -->
    @php
        $headers = [
            ['label' => 'Código', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>'],
            ['label' => 'Parcela', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>'],
            ['label' => 'Parcelas', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>'],
            'Acciones',
        ];
    @endphp

    <x-data-table 
        :headers="$headers" 
        empty-message="No se encontraron códigos" 
        empty-description="{{ $search ? 'Intenta con otro término de búsqueda' : 'No hay códigos SIGPAC registrados' }}"
        color="green"
    >
        @if($codes->count() > 0)
            @foreach($codes as $code)
                <x-table-row>
                    <x-table-cell>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[var(--color-agro-green-light)] to-[var(--color-agro-green)] flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-200">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-900">{{ $code->code }}</div>
                            </div>
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        @php
                            $firstPlot = $code->plots->first();
                        @endphp
                        <span class="text-sm text-gray-700">
                            {{ $firstPlot ? $firstPlot->name : 'Sin parcela asociada' }}
                        </span>
                    </x-table-cell>
                    <x-table-cell>
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-[var(--color-agro-green-bg)] text-[var(--color-agro-green-dark)] text-sm font-semibold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                            {{ $code->plots_count }}
                        </span>
                    </x-table-cell>
                    <x-table-actions align="right">
                        <x-action-button 
                            variant="view" 
                            href="{{ route('plots.index', ['sigpac_code' => $code->id]) }}"
                            title="Ver parcelas"
                        />
                        <x-action-button 
                            variant="edit" 
                            href="{{ route('sigpac.codes.edit', $code->id) }}"
                            title="Editar código"
                        />
                        @php
                            // Verificar si tiene parcelas asociadas
                            $hasPlots = $code->plots_count > 0;
                            
                            // Obtener primera parcela asociada
                            $firstPlot = $code->plots->first();
                            
                            // Verificar si ya tiene geometría: existe MultipartPlotSigpac con plot_geometry_id para este código y parcela
                            $hasGeometry = false;
                            if ($hasPlots && $firstPlot && $code->multiplePlotSigpacs) {
                                $hasGeometry = $code->multiplePlotSigpacs
                                    ->where('plot_id', $firstPlot->id)
                                    ->where('sigpac_code_id', $code->id)
                                    ->whereNotNull('plot_geometry_id')
                                    ->isNotEmpty();
                            }
                        @endphp
                        
                        @if($hasPlots && $firstPlot)
                            @if($hasGeometry)
                                {{-- Si tiene geometría, mostrar "Ver Mapa" --}}
                                <a href="/map/{{ $firstPlot->id }}?return=sigpac"
                                   class="inline-flex items-center justify-center px-3 py-2 text-sm font-semibold text-green-600 bg-green-50 rounded-lg hover:bg-green-100 transition-colors duration-200"
                                   title="Ver Mapa">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                    </svg>
                                    <span class="ml-1">Ver Mapa</span>
                                </a>
                            @else
                                {{-- Si no tiene geometría, mostrar "Generar Mapa" --}}
                                <button
                                    wire:click="generateMap({{ $code->id }}, {{ $firstPlot->id }})"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center justify-center px-3 py-2 text-sm font-semibold text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors duration-200 disabled:opacity-50"
                                    title="Generar Mapa">
                                    <span wire:loading.remove wire:target="generateMap({{ $code->id }}, {{ $firstPlot->id }})">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                        </svg>
                                        <span class="ml-1">Generar Mapa</span>
                                    </span>
                                    <span wire:loading wire:target="generateMap({{ $code->id }}, {{ $firstPlot->id }})" class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Generando...
                                    </span>
                                </button>
                            @endif
                        @endif
                    </x-table-actions>
                </x-table-row>
            @endforeach
            <x-slot name="pagination">
                {{ $codes->links() }}
            </x-slot>
        @endif
    </x-data-table>
</div>

