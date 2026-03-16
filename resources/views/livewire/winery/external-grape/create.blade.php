<x-agro.form-card
    title="Nueva Partida Externa"
    description="Registra uva, mosto o vino a granel de un proveedor externo."
    icon="archive-box"
    icon-color="from-agro-500 to-agro-700"
    :back-url="roleRoute('external-grape.index')"
>
    <form wire:submit="save" class="space-y-8">

        <x-agro.form-section title="Proveedor y Tipo" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <flux:field class="md:col-span-2">
                    <flux:label required>Proveedor</flux:label>
                    <flux:input wire:model="supplier_name" type="text" placeholder="Nombre del proveedor" required />
                    <flux:error name="supplier_name" />
                </flux:field>

                <flux:field>
                    <flux:label required>Tipo de partida</flux:label>
                    <flux:select wire:model="grape_type" required>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="grape_type" />
                </flux:field>

                <flux:field>
                    <flux:label>Variedad de uva</flux:label>
                    <flux:select wire:model="grape_variety_id">
                        <option value="">Sin especificar</option>
                        @foreach($varieties as $v)
                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="grape_variety_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Color</flux:label>
                    <flux:select wire:model="color">
                        <option value="">Sin especificar</option>
                        @foreach($colors as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="color" />
                </flux:field>

                <flux:field>
                    <flux:label>Nivel de protección (DO, IGP…)</flux:label>
                    <flux:input wire:model="protection_level" type="text" placeholder="ej. Rioja DOCa" />
                    <flux:error name="protection_level" />
                </flux:field>

                <flux:field>
                    <flux:label>Procedencia geográfica</flux:label>
                    <flux:input wire:model="geographic_origin" type="text" placeholder="ej. La Rioja" />
                    <flux:error name="geographic_origin" />
                </flux:field>

                <flux:field>
                    <flux:label>Añada</flux:label>
                    <flux:input wire:model="vintage_year" type="number" min="1900" max="2100" placeholder="{{ date('Y') }}" />
                    <flux:error name="vintage_year" />
                </flux:field>

                <flux:field>
                    <flux:label>Alcohol probable (%)</flux:label>
                    <flux:input wire:model="alcohol_pct" type="number" step="0.1" min="0" max="100" placeholder="0.0" />
                    <flux:description>Solo para mosto / vino a granel</flux:description>
                    <flux:error name="alcohol_pct" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Cantidades y Fechas" color="blue">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <flux:field>
                    <flux:label required>Cantidad total (kg)</flux:label>
                    <flux:input wire:model="total_weight_kg" type="number" step="0.001" min="0.001" placeholder="0.000" required />
                    <flux:error name="total_weight_kg" />
                </flux:field>

                <flux:field>
                    <flux:label required>Fecha de entrada</flux:label>
                    <flux:input wire:model="entry_date" type="date" required />
                    <flux:error name="entry_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Fecha de vendimia</flux:label>
                    <flux:input wire:model="harvest_date" type="date" />
                    <flux:error name="harvest_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Fecha de caducidad</flux:label>
                    <flux:input wire:model="expiration_date" type="date" />
                    <flux:error name="expiration_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Contenedor de destino</flux:label>
                    <flux:select wire:model="container_id">
                        <option value="">Sin asignar</option>
                        @foreach($containers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="container_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>Estado</flux:label>
                    <flux:select wire:model="status" required>
                        <option value="available">Disponible</option>
                        <option value="used">Usado</option>
                        <option value="archived">Archivado</option>
                    </flux:select>
                    <flux:error name="status" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Notas" color="green">
            <flux:field>
                <flux:label>Observaciones</flux:label>
                <flux:textarea wire:model="notes" rows="3" placeholder="Observaciones sobre la partida..." />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions :back-url="roleRoute('external-grape.index')" submit-label="Guardar partida" />
    </form>
</x-agro.form-card>
