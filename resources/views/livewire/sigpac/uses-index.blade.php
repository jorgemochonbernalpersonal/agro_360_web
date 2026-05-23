<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <x-agro.page-header
        title="Usos SIGPAC"
        description="Gestiona los tipos de usos del suelo SIGPAC"
    />

    <!-- Búsqueda y filtros -->
    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar por código o descripción..."
        />
        <div>
            <label class="block text-xs font-semibold text-zinc-500 mb-1">
                Filtrar por códigos de uso (múltiple)
            </label>
            <flux:select wire:model.live="selectedUses" multiple size="5" id="filter_sigpac_uses">
                @foreach ($allUses as $useOption)
                    <option value="{{ $useOption->id }}">
                        {{ $useOption->code }} - {{ $useOption->description }}
                    </option>
                @endforeach
            </flux:select>
            <p class="mt-1 text-[11px] text-zinc-500">
                Mantén pulsado Ctrl (o Cmd en Mac) para seleccionar varios códigos.
            </p>
        </div>
    </x-agro.filter-bar>

    <!-- Tabla de usos -->
    <x-agro.data-table
        :headers="['Código', 'Descripción', 'Parcelas', 'Acciones']"
        empty-message="No se encontraron usos"
        empty-description="{{ $search ? 'Intenta con otro término de búsqueda' : 'No hay usos SIGPAC registrados' }}"
    >
        @if($uses->count() > 0)
            @foreach($uses as $use)
                <x-agro.table-row>
                    <x-agro.table-cell>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-agro-50 flex items-center justify-center">
                                <flux:icon icon="map" class="size-5 text-agro-600" />
                            </div>
                            <div>
                                <div class="text-sm font-bold text-zinc-900">{{ $use->code }}</div>
                            </div>
                        </div>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-700">{{ $use->description ?: 'Sin descripción' }}</span>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <div class="flex flex-col gap-1">
                            <flux:badge color="agro" icon="map" size="sm">{{ $use->plots_count }}</flux:badge>

                            @if($use->plots->isNotEmpty())
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach($use->plots->take(3) as $plot)
                                        <flux:badge size="sm">{{ $plot->name }}</flux:badge>
                                    @endforeach
                                    @php
                                        $extra = $use->plots->count() - 3;
                                    @endphp
                                    @if($extra > 0)
                                        <span class="text-[11px] text-zinc-500">+ {{ $extra }} más</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </x-agro.table-cell>
                    <x-agro.table-cell align="right">
                        <x-agro.action-button
                            variant="view"
                            href="{{ route('plots.index', ['sigpac_use' => $use->id]) }}"
                        />
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach
            <x-slot name="pagination">
                <x-agro-pagination :paginator="$uses" />
            </x-slot>
        @endif
    </x-agro.data-table>
</div>
