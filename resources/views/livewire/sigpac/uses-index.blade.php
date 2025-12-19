<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Usos SIGPAC"
        description="Gestiona los tipos de usos del suelo SIGPAC"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    />

    <!-- Búsqueda y filtros -->
    <x-filter-section title="Filtros de usos SIGPAC" color="green">
        <div class="flex flex-col md:flex-row gap-8 w-full">
            <div class="w-full md:w-1/2">
                <x-filter-input 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Buscar por código o descripción..."
                />
            </div>

            <!-- Filtro por códigos (select múltiple como en create de parcelas) -->
            <div class="w-full md:w-1/2">
                <label class="block text-xs font-semibold text-gray-500 mb-1 sidebar-text">
                    Filtrar por códigos de uso (múltiple)
                </label>
                <x-select wire:model.live="selectedUses" multiple size="5" id="filter_sigpac_uses">
                    @foreach ($allUses as $useOption)
                        <option value="{{ $useOption->id }}">
                            {{ $useOption->code }} - {{ $useOption->description }}
                        </option>
                    @endforeach
                </x-select>
                <p class="mt-1 text-[11px] text-gray-500">
                    Mantén pulsado Ctrl (o Cmd en Mac) para seleccionar varios códigos.
                </p>
            </div>
        </div>
    </x-filter-section>

    <!-- Tabla de usos -->
    @php
        $headers = [
            ['label' => 'Código', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>'],
            ['label' => 'Descripción', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'],
            ['label' => 'Parcelas', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>'],
            'Acciones',
        ];
    @endphp

    <x-data-table 
        :headers="$headers" 
        empty-message="No se encontraron usos" 
        empty-description="{{ $search ? 'Intenta con otro término de búsqueda' : 'No hay usos SIGPAC registrados' }}"
        color="green"
    >
        @if($uses->count() > 0)
            @foreach($uses as $use)
                <x-table-row>
                    <x-table-cell>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[var(--color-agro-green-light)] to-[var(--color-agro-green)] flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-200">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-900">{{ $use->code }}</div>
                            </div>
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        <span class="text-sm text-gray-700">{{ $use->description ?: 'Sin descripción' }}</span>
                    </x-table-cell>
                    <x-table-cell>
                        <div class="flex flex-col gap-1">
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-[var(--color-agro-green-bg)] text-[var(--color-agro-green-dark)] text-sm font-semibold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                </svg>
                                {{ $use->plots_count }}
                            </span>

                            @if($use->plots->isNotEmpty())
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach($use->plots->take(3) as $plot)
                                        <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-[11px] font-medium">
                                            {{ $plot->name }}
                                        </span>
                                    @endforeach
                                    @php
                                        $extra = $use->plots->count() - 3;
                                    @endphp
                                    @if($extra > 0)
                                        <span class="text-[11px] text-gray-500">
                                            + {{ $extra }} más
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </x-table-cell>
                    <x-table-actions align="right">
                        <x-action-button 
                            variant="view" 
                            href="{{ route('plots.index', ['sigpac_use' => $use->id]) }}"
                            title="Ver parcelas"
                        />
                    </x-table-actions>
                </x-table-row>
            @endforeach
            <x-slot name="pagination">
                {{ $uses->links() }}
            </x-slot>
        @endif
    </x-data-table>
</div>

