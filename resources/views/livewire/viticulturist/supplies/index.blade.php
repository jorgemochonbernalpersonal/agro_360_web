<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Almacén de Insumos"
        subtitle="Gestión de stock de fitosanitarios, fertilizantes y otros insumos"
        icon="archive-box"
    >
        <x-slot:actions>
            <flux:button variant="primary" icon="plus" wire:click="openCreate">Nuevo Insumo</flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <x-agro.filter-select wire:model.live="filterType" label="Tipo">
            <option value="">Todos</option>
            @foreach($supplyTypes as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>
        <label class="flex items-center gap-2 text-sm text-zinc-700 cursor-pointer">
            <flux:checkbox wire:model.live="filterLow" />
            Solo stock bajo
        </label>
    </x-agro.filter-bar>

    {{-- Tabla --}}
    <x-agro.card>
        @if($supplies->isEmpty())
            <x-agro.empty-state
                icon="archive-box"
                title="Almacén vacío"
                description="Registra los insumos de tu almacén para controlar el stock de fitosanitarios, abonos y otros productos."
            />
        @else
            <x-agro.data-table :headers="['Producto', 'Tipo', 'Stock actual', 'Caducidad', 'Estado', 'Acciones']">
                @foreach($supplies as $supply)
                    <x-agro.table-row>
                        <x-agro.table-cell>
                            <span class="font-medium">{{ $supply->name }}</span>
                            @if($supply->commercial_name)
                                <span class="text-zinc-400 text-xs block">{{ $supply->commercial_name }}</span>
                            @endif
                            @if($supply->registration_number)
                                <span class="text-zinc-400 text-xs block font-mono">Reg: {{ $supply->registration_number }}</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            <x-agro.status-badge :status="$supply->supply_type" :label="$supply->supply_type_label" />
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            <span class="{{ $supply->isLowStock() ? 'text-red-600 font-semibold' : 'text-zinc-900' }}">
                                {{ number_format($supply->current_stock, 3, ',', '.') }} {{ $supply->unit_of_measurement }}
                            </span>
                            @if($supply->min_stock_alert)
                                <span class="text-zinc-400 text-xs block">Mín: {{ $supply->min_stock_alert }} {{ $supply->unit_of_measurement }}</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($supply->expiry_date)
                                <span class="{{ $supply->isExpiringSoon() ? 'text-amber-600 font-medium' : 'text-zinc-600' }}">
                                    {{ $supply->expiry_date->format('d/m/Y') }}
                                </span>
                                @if($supply->isExpiringSoon())
                                    <span class="text-amber-500 text-xs block">⚠️ Próximo a caducar</span>
                                @endif
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($supply->isLowStock())
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                    📉 Stock bajo
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                    ✅ OK
                                </span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <div class="flex items-center justify-end gap-2">
                                <flux:button size="sm" variant="ghost" icon="shopping-cart" wire:click="openPurchase({{ $supply->id }})">Compra</flux:button>
                                <flux:button size="sm" variant="ghost" icon="pencil" wire:click="openEdit({{ $supply->id }})">Editar</flux:button>
                                <flux:button size="sm" variant="ghost" icon="archive-box" wire:click="deactivate({{ $supply->id }})" wire:confirm="¿Archivar este insumo?">Archivar</flux:button>
                            </div>
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach
            </x-agro.data-table>
            <div class="mt-4">{{ $supplies->links() }}</div>
        @endif
    </x-agro.card>

    {{-- Modal Insumo --}}
    <flux:modal wire:model="showModal" class="max-w-2xl">
        <div class="p-6 space-y-6">
            <h2 class="text-lg font-semibold text-zinc-900">{{ $editingId ? 'Editar Insumo' : 'Nuevo Insumo' }}</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field class="md:col-span-2">
                    <flux:label required>Nombre del producto</flux:label>
                    <flux:input wire:model="name" type="text" placeholder="Nombre en el almacén" />
                    <flux:error name="name" />
                </flux:field>
                <flux:field>
                    <flux:label>Nombre comercial</flux:label>
                    <flux:input wire:model="commercial_name" type="text" placeholder="Nombre del fabricante" />
                    <flux:error name="commercial_name" />
                </flux:field>
                <flux:field>
                    <flux:label>Nº Registro MAPA</flux:label>
                    <flux:input wire:model="registration_number" type="text" placeholder="ES-00000" />
                    <flux:error name="registration_number" />
                </flux:field>

                <flux:field>
                    <flux:label required>Tipo de insumo</flux:label>
                    <flux:select wire:model="supply_type">
                        @foreach($supplyTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="supply_type" />
                </flux:field>
                <flux:field>
                    <flux:label required>Unidad de medida</flux:label>
                    <flux:select wire:model="unit_of_measurement">
                        <option value="L">Litros (L)</option>
                        <option value="mL">Mililitros (mL)</option>
                        <option value="kg">Kilogramos (kg)</option>
                        <option value="g">Gramos (g)</option>
                        <option value="ud">Unidades</option>
                    </flux:select>
                    <flux:error name="unit_of_measurement" />
                </flux:field>

                <flux:field>
                    <flux:label>Stock inicial</flux:label>
                    <flux:input wire:model="initial_stock" type="number" step="0.001" min="0" placeholder="0.000" />
                    <flux:error name="initial_stock" />
                </flux:field>
                <flux:field>
                    <flux:label>Stock actual</flux:label>
                    <flux:input wire:model="current_stock" type="number" step="0.001" min="0" placeholder="0.000" />
                    <flux:error name="current_stock" />
                </flux:field>

                <flux:field>
                    <flux:label>Alerta stock mínimo</flux:label>
                    <flux:input wire:model="min_stock_alert" type="number" step="0.001" min="0" placeholder="0.000" />
                    <flux:description>Aviso cuando el stock baje de este valor</flux:description>
                    <flux:error name="min_stock_alert" />
                </flux:field>
                <flux:field>
                    <flux:label>Fecha caducidad</flux:label>
                    <flux:input wire:model="expiry_date" type="date" />
                    <flux:error name="expiry_date" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Notas</flux:label>
                    <flux:textarea wire:model="notes" rows="2" />
                    <flux:error name="notes" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200">
                <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancelar</flux:button>
                <flux:button variant="primary" wire:click="saveSupply">{{ $editingId ? 'Actualizar' : 'Añadir al Almacén' }}</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal Compra --}}
    <flux:modal wire:model="showPurchaseModal" class="max-w-xl">
        <div class="p-6 space-y-6">
            <h2 class="text-lg font-semibold text-zinc-900">Registrar Compra — {{ $purchaseSupplyName }}</h2>
            <flux:callout variant="info" icon="information-circle">
                Al guardar, el stock del insumo se incrementará automáticamente con la cantidad comprada.
            </flux:callout>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label required>Fecha de factura</flux:label>
                    <flux:input wire:model="p_invoice_date" type="date" />
                    <flux:error name="p_invoice_date" />
                </flux:field>
                <flux:field>
                    <flux:label>Nº Factura</flux:label>
                    <flux:input wire:model="p_invoice_number" type="text" placeholder="FAC-2026-001" />
                    <flux:error name="p_invoice_number" />
                </flux:field>

                <flux:field>
                    <flux:label required>Cantidad comprada</flux:label>
                    <flux:input wire:model.live="p_quantity" type="number" step="0.001" min="0.001" placeholder="0.000" />
                    <flux:error name="p_quantity" />
                </flux:field>
                <flux:field>
                    <flux:label required>Unidad</flux:label>
                    <flux:input wire:model="p_unit_of_measurement" type="text" placeholder="L, kg..." />
                    <flux:error name="p_unit_of_measurement" />
                </flux:field>

                <flux:field>
                    <flux:label>Precio/unidad (€)</flux:label>
                    <flux:input wire:model.live="p_price_per_unit" type="number" step="0.0001" min="0" placeholder="0.0000" />
                    <flux:error name="p_price_per_unit" />
                </flux:field>
                <flux:field>
                    <flux:label>Coste total (€)</flux:label>
                    <flux:input wire:model="p_total_cost" type="number" step="0.01" min="0" placeholder="0.00" />
                    <flux:error name="p_total_cost" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Proveedor</flux:label>
                    <flux:input wire:model="p_supplier_name" type="text" placeholder="Nombre del proveedor" />
                    <flux:error name="p_supplier_name" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Campaña</flux:label>
                    <flux:select wire:model="p_campaign_id">
                        <option value="">Sin campaña</option>
                        @foreach($campaigns as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="p_campaign_id" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200">
                <flux:button variant="ghost" wire:click="$set('showPurchaseModal', false)">Cancelar</flux:button>
                <flux:button variant="primary" wire:click="savePurchase">Registrar Compra</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
