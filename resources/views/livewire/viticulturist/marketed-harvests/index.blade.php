<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Cosecha Comercializada"
        subtitle="Registro de entregas y ventas por campaña"
        icon="truck"
    >
        <x-slot:actions>
            <flux:button variant="primary" icon="plus" wire:click="openCreate">Nueva Entrega</flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Filtros --}}
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
    </x-agro.filter-bar>

    {{-- Tabla --}}
    <x-agro.card>
        @if($entries->isEmpty())
            <x-agro.empty-state
                icon="truck"
                title="Sin entregas registradas"
                description="Registra las entregas de uva a bodega, cooperativa o venta directa."
            />
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
                            <div class="flex items-center justify-end gap-2">
                                <flux:button size="sm" variant="ghost" icon="pencil" wire:click="openEdit({{ $entry->id }})">Editar</flux:button>
                                <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $entry->id }})" wire:confirm="¿Eliminar esta entrega?">Eliminar</flux:button>
                            </div>
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach
            </x-agro.data-table>
            <div class="mt-4">{{ $entries->links() }}</div>
        @endif
    </x-agro.card>

    {{-- Modal --}}
    <flux:modal wire:model="showModal" class="max-w-2xl">
        <div class="p-6 space-y-6">
            <h2 class="text-lg font-semibold text-zinc-900">
                {{ $editingId ? 'Editar Entrega' : 'Nueva Entrega' }}
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field class="md:col-span-2">
                    <flux:label required>Cosecha asociada</flux:label>
                    <flux:select wire:model="harvest_id">
                        <option value="">Seleccionar cosecha...</option>
                        @foreach($harvests as $h)
                            <option value="{{ $h->id }}">
                                {{ $h->plotPlanting->plot->name ?? '?' }} — {{ $h->plotPlanting->grape->name ?? '?' }}
                                ({{ $h->harvest_start_date->format('d/m/Y') }}, {{ number_format($h->total_weight, 0, ',', '.') }} kg)
                            </option>
                        @endforeach
                    </flux:select>
                    <flux:error name="harvest_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>Fecha de entrega</flux:label>
                    <flux:input wire:model="delivery_date" type="date" />
                    <flux:error name="delivery_date" />
                </flux:field>
                <flux:field>
                    <flux:label required>Cantidad (kg)</flux:label>
                    <flux:input wire:model.live="quantity_kg" type="number" step="0.001" min="0" placeholder="0.000" />
                    <flux:error name="quantity_kg" />
                </flux:field>

                <flux:field>
                    <flux:label required>Destino</flux:label>
                    <flux:select wire:model="destination_type">
                        <option value="">Seleccionar...</option>
                        @foreach($destinations as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="destination_type" />
                </flux:field>
                <flux:field>
                    <flux:label>Comprador / Bodega</flux:label>
                    <flux:input wire:model="buyer_name" type="text" placeholder="Nombre del comprador" />
                    <flux:error name="buyer_name" />
                </flux:field>

                <flux:field>
                    <flux:label>REGA del destino</flux:label>
                    <flux:input wire:model="buyer_rega_code" type="text" placeholder="Código REGA" />
                    <flux:error name="buyer_rega_code" />
                </flux:field>
                <flux:field>
                    <flux:label>Documento de transporte</flux:label>
                    <flux:input wire:model="transport_document" type="text" placeholder="Nº albarán/CMR" />
                    <flux:error name="transport_document" />
                </flux:field>

                <flux:field>
                    <flux:label>Matrícula vehículo</flux:label>
                    <flux:input wire:model="vehicle_plate" type="text" placeholder="0000-AAA" />
                    <flux:error name="vehicle_plate" />
                </flux:field>
                <flux:field>
                    <flux:label>Precio/kg (€)</flux:label>
                    <flux:input wire:model.live="price_per_kg" type="number" step="0.0001" min="0" placeholder="0.0000" />
                    <flux:error name="price_per_kg" />
                </flux:field>

                <flux:field>
                    <flux:label>Valor total (€)</flux:label>
                    <flux:input wire:model="total_value" type="number" step="0.01" min="0" placeholder="0.00" />
                    <flux:error name="total_value" />
                </flux:field>
                <flux:field>
                    <flux:label>Notas</flux:label>
                    <flux:textarea wire:model="notes" rows="2" />
                    <flux:error name="notes" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200">
                <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancelar</flux:button>
                <flux:button variant="primary" wire:click="save">
                    {{ $editingId ? 'Actualizar' : 'Registrar Entrega' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
