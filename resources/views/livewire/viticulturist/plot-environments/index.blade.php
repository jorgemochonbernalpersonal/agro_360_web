<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Entorno de Parcelas"
        subtitle="Zonas protegidas, captaciones de agua y condiciones ambientales"
        icon="map"
    >
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.plot-environments.create') }}" variant="primary" icon="plus">
                Nueva Ficha
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <flux:callout variant="info" icon="information-circle">
        El registro de zonas protegidas y captaciones de agua es obligatorio en programas de Producción Integrada y para el uso de fitosanitarios cerca de masas de agua (RD 1311/2012).
    </flux:callout>

    <x-agro.filter-bar>
        <x-agro.filter-select wire:model.live="filterCampaign" label="Campaña">
            <option value="">Todas</option>
            @foreach($campaigns as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </x-agro.filter-select>
        @if($filterCampaign)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">Limpiar</flux:button>
        @endif
    </x-agro.filter-bar>

    <x-agro.card>
        @if($entries->isEmpty())
            <x-agro.empty-state
                icon="map"
                title="Sin fichas de entorno"
                description="Registra las condiciones ambientales de cada parcela: zonas protegidas, distancia a captaciones de agua, pendiente..."
            >
                <x-slot:action>
                    <flux:button href="{{ route('viticulturist.plot-environments.create') }}" variant="primary" icon="plus">
                        Nueva Ficha
                    </flux:button>
                </x-slot:action>
            </x-agro.empty-state>
        @else
            <x-agro.data-table :headers="['Parcela', 'Variedad', 'Captación agua', 'Zona protegida', 'Pendiente', 'Riesgo erosión', 'Acciones']">
                @foreach($entries as $entry)
                    <x-agro.table-row>
                        <x-agro.table-cell class="font-medium">{{ $entry->plot->name ?? '-' }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $entry->plotPlanting?->grapeVariety?->name ?? 'Global' }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($entry->water_intake_nearby)
                                <span class="text-amber-600 font-medium text-sm flex items-center gap-1">
                                    <flux:icon icon="exclamation-triangle" class="size-3" /> Sí
                                </span>
                                @if($entry->water_intake_distance_m)
                                    <span class="text-zinc-400 text-xs block">a {{ $entry->water_intake_distance_m }} m</span>
                                @endif
                            @else
                                <span class="text-zinc-400 text-sm">No</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($entry->protected_zone_total)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">Total</span>
                            @elseif($entry->protected_zone_partial)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700">Parcial</span>
                                @if($entry->protection_zone_type)
                                    <span class="text-zinc-400 text-xs block">{{ $entry->protection_zone_type }}</span>
                                @endif
                            @else
                                <span class="text-zinc-400 text-sm">No</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell>{{ $entry->slope_pct ? $entry->slope_pct . '%' : '-' }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($entry->erosion_risk)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-700">⚠️ Sí</span>
                            @else
                                <span class="text-zinc-400 text-sm">No</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('viticulturist.plot-environments.edit', $entry) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors"
                                   title="Editar">
                                    <flux:icon icon="pencil-square" class="size-4" />
                                </a>
                                <button
                                    wire:click="delete({{ $entry->id }})"
                                    wire:confirm="¿Eliminar esta ficha?"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                    title="Eliminar">
                                    <flux:icon icon="trash" class="size-4" />
                                </button>
                            </div>
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach
            </x-agro.data-table>
            @if($entries->hasPages())
                <div class="mt-4">{{ $entries->links() }}</div>
            @endif
        @endif
    </x-agro.card>

</div>
