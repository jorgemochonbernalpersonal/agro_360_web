<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Cosecha Comercializada"
        subtitle="Registro de entregas y ventas por campaña"
        icon="truck"
    >
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.marketed-harvests.create') }}" variant="primary" icon="plus">
                Nueva Entrega
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.filter-bar>
        <x-agro.filter-select wire:model.live="filterCampaign" label="Campaña">
            <option value="">Todas las campañas</option>
            @foreach($campaigns as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="filterDestination" label="Destino">
            <option value="">Todos</option>
            @foreach($destinations as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>
        @if($filterCampaign || $filterDestination)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">Limpiar</flux:button>
        @endif
    </x-agro.filter-bar>

    <x-agro.card>
        @if($entries->isEmpty())
            <x-agro.empty-state
                icon="truck"
                title="Sin entregas registradas"
                description="Registra las entregas de uva a bodega, cooperativa o venta directa."
            >
                <x-slot:action>
                    <flux:button href="{{ route('viticulturist.marketed-harvests.create') }}" variant="primary" icon="plus">
                        Nueva Entrega
                    </flux:button>
                </x-slot:action>
            </x-agro.empty-state>
        @else
            <x-agro.data-table :headers="['Fecha', 'Cosecha', 'Destino', 'Cantidad (kg)', 'Precio/kg', 'Valor Total', 'Factura', 'Acciones']">
                @foreach($entries as $entry)
                    <x-agro.table-row>
                        <x-agro.table-cell>{{ $entry->delivery_date->format('d/m/Y') }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            {{ $entry->harvest->plotPlanting->plot->name ?? '-' }}
                            <span class="text-zinc-400 text-xs block">{{ $entry->harvest->plotPlanting->grape->name ?? '' }}</span>
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            <x-agro.status-badge :status="$entry->destination_type" :label="$entry->destination_type_label" />
                        </x-agro.table-cell>
                        <x-agro.table-cell>{{ number_format($entry->quantity_kg, 0, ',', '.') }} kg</x-agro.table-cell>
                        <x-agro.table-cell>{{ $entry->price_per_kg ? number_format($entry->price_per_kg, 4, ',', '.') . ' €' : '-' }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $entry->total_value ? number_format($entry->total_value, 2, ',', '.') . ' €' : '-' }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($entry->destination_type === 'third_party')
                                @if($entry->invoice_id)
                                    <a href="{{ route('viticulturist.invoices.show', $entry->invoice_id) }}"
                                       class="text-agro-400 hover:text-agro-300 text-xs font-medium flex items-center gap-1">
                                        <flux:icon icon="document-check" class="size-4" />
                                        Ver factura
                                    </a>
                                @else
                                    <flux:button size="xs" variant="ghost" icon="document-plus"
                                                 wire:click="generateInvoice({{ $entry->id }})"
                                                 wire:confirm="¿Generar factura para esta entrega?">
                                        Generar
                                    </flux:button>
                                @endif
                            @else
                                <span class="text-zinc-500 text-xs">—</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('viticulturist.marketed-harvests.edit', $entry) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors"
                                   title="Editar">
                                    <flux:icon icon="pencil-square" class="size-4" />
                                </a>
                                <button
                                    wire:click="delete({{ $entry->id }})"
                                    wire:confirm="¿Eliminar esta entrega?"
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
