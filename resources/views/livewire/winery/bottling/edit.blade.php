<x-agro.form-card
    title="Editar Embotellado"
    description="Modifica los datos de esta operación de embotellado."
    icon="archive-box-arrow-down"
    icon-color="from-emerald-500 to-emerald-700"
    :back-url="roleRoute('bottling.index')"
>
    <form wire:submit="save" class="space-y-8">

        {{-- ── Vino y fecha ──────────────────────────────────────────────── --}}
        <x-agro.form-section title="Vino y fecha" color="emerald">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <flux:field class="md:col-span-2 lg:col-span-1">
                    <flux:label required>Vino</flux:label>
                    <flux:select wire:model.live="wine_id" required>
                        <flux:select.option value="">Seleccionar vino...</flux:select.option>
                        @foreach($wines as $wine)
                            <flux:select.option value="{{ $wine->id }}">
                                {{ $wine->name }}{{ $wine->vintage ? ' · ' . $wine->vintage : '' }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="wine_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>Fecha de embotellado</flux:label>
                    <flux:input wire:model="bottling_date" type="date" required />
                    <flux:error name="bottling_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Enólogo responsable</flux:label>
                    <flux:select wire:model="oenologist_id">
                        <flux:select.option value="">Sin asignar</flux:select.option>
                        @foreach($oenologists as $oenologist)
                            <flux:select.option value="{{ $oenologist->id }}">
                                {{ $oenologist->surname }}, {{ $oenologist->name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="oenologist_id" />
                </flux:field>

                @if($wine_id && $bottlingProcessDetails->isNotEmpty())
                    <flux:field class="lg:col-span-3">
                        <flux:label>Operación de proceso vinculada</flux:label>
                        <flux:select wire:model="wine_process_detail_id">
                            <flux:select.option value="">Sin vincular</flux:select.option>
                            @foreach($bottlingProcessDetails as $detail)
                                <flux:select.option value="{{ $detail->id }}">
                                    Embotellado · {{ $detail->start_date->format('d/m/Y') }}
                                    @if($detail->quantity) · {{ number_format($detail->quantity, 0) }} @endif
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="wine_process_detail_id" />
                    </flux:field>
                @endif

                @if($originContainer)
                    <div class="lg:col-span-3">
                        <flux:callout variant="info" icon="cube">
                            <flux:callout.heading>Contenedor de origen</flux:callout.heading>
                            <flux:callout.text>
                                <strong>{{ $originContainer->name }}</strong>
                                @if($originContainer->containerType) · {{ $originContainer->containerType->name }}@endif
                                — El inventario de este contenedor ya fue descontado al registrar el embotellado.
                                Para corregirlo, elimina este registro y vuelve a crearlo.
                            </flux:callout.text>
                        </flux:callout>
                    </div>
                @endif

            </div>
        </x-agro.form-section>

        {{-- ── Formato y cantidades ──────────────────────────────────────── --}}
        <x-agro.form-section title="Formato y cantidades" color="blue">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                <flux:field>
                    <flux:label required>Formato de botella</flux:label>
                    <flux:select wire:model="bottle_format" required>
                        @foreach($bottleFormats as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="bottle_format" />
                </flux:field>

                <flux:field>
                    <flux:label required>Nº de botellas</flux:label>
                    <flux:input wire:model="quantity_bottles" type="number" min="1" step="1"
                        placeholder="0" required />
                    <flux:error name="quantity_bottles" />
                </flux:field>

                <flux:field>
                    <flux:label required>Litros embotellados</flux:label>
                    <flux:input wire:model="quantity_liters" type="number" min="0" step="0.001"
                        placeholder="0.000" required />
                    <flux:error name="quantity_liters" />
                </flux:field>

                <flux:field>
                    <flux:label>Nº de lote interno</flux:label>
                    <flux:input wire:model="lot_number" type="text" placeholder="Ej: EMB-2026-001" />
                    <flux:error name="lot_number" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- ── Insumos utilizados ───────────────────────────────────────── --}}
        <x-agro.form-section title="Insumos utilizados" color="amber">
            <flux:description class="mb-4">
                Tapones, cápsulas, botellas, cajas u otros materiales consumidos en el embotellado.
            </flux:description>

            @foreach($supplies as $i => $row)
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end mb-3 p-3 bg-zinc-50 rounded-xl border border-zinc-200"
                    wire:key="supply-row-{{ $i }}">

                    <div class="md:col-span-4">
                        <flux:field>
                            <flux:label>Insumo del inventario</flux:label>
                            <flux:select wire:model.live="supplies.{{ $i }}.winery_supply_id">
                                <flux:select.option value="">Ad-hoc (escribir nombre)</flux:select.option>
                                @foreach($winerySupplies as $supply)
                                    <flux:select.option value="{{ $supply->id }}">{{ $supply->name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </flux:field>
                    </div>

                    <div class="md:col-span-3">
                        <flux:field>
                            <flux:label>Nombre</flux:label>
                            <flux:input wire:model="supplies.{{ $i }}.supply_name"
                                placeholder="Ej: Tapones naturales" />
                        </flux:field>
                    </div>

                    <div class="md:col-span-2">
                        <flux:field>
                            <flux:label>Cantidad</flux:label>
                            <flux:input wire:model="supplies.{{ $i }}.quantity"
                                type="number" min="0" step="0.001" placeholder="0" />
                        </flux:field>
                    </div>

                    <div class="md:col-span-2">
                        <flux:field>
                            <flux:label>Unidad</flux:label>
                            <flux:select wire:model="supplies.{{ $i }}.unit_of_measurement_id">
                                <flux:select.option value="">—</flux:select.option>
                                @foreach($units as $unit)
                                    <flux:select.option value="{{ $unit->id }}">{{ $unit->symbol }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </flux:field>
                    </div>

                    <div class="md:col-span-1 flex items-end pb-0.5">
                        <flux:button type="button" variant="ghost" icon="trash" size="sm"
                            wire:click="removeSupply({{ $i }})" />
                    </div>

                </div>
            @endforeach

            <flux:button type="button" variant="ghost" icon="plus" size="sm" wire:click="addSupply">
                Añadir insumo
            </flux:button>
        </x-agro.form-section>

        {{-- ── Notas ─────────────────────────────────────────────────────── --}}
        <x-agro.form-section title="Notas" color="zinc">
            <flux:field>
                <flux:label>Observaciones</flux:label>
                <flux:textarea wire:model="notes" rows="3"
                    placeholder="Incidencias, condiciones del embotellado, notas del enólogo..." />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions :back-url="roleRoute('bottling.index')" submit-label="Guardar cambios" />
    </form>
</x-agro.form-card>
