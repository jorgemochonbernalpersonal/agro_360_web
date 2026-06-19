<x-agro.form-card
    title="{{ __('Nueva Partida Externa') }}"
    :description="__('Registra uva, mosto o vino a granel de un proveedor externo.')"
    icon="archive-box"
    icon-color="from-agro-500 to-agro-700"
    :back-url="roleRoute('external-grape.index')"
>
    <form wire:submit="save" class="space-y-8">

        <x-agro.form-section title="{{ __('Proveedor y Tipo') }}" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <flux:field class="md:col-span-2">
                    <flux:label required>{{ __('Proveedor') }}</flux:label>
                    <flux:input wire:model="supplier_name" type="text" :placeholder="__('Nombre del proveedor')" required />
                    <flux:error name="supplier_name" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Tipo de partida') }}</flux:label>
                    <flux:select wire:model="grape_type" required>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="grape_type" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Variedad de uva') }}</flux:label>
                    <flux:select wire:model="grape_variety_id">
                        <option value="">{{ __('Sin especificar') }}</option>
                        @foreach($varieties as $v)
                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="grape_variety_id" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Color') }}</flux:label>
                    <flux:select wire:model="color">
                        <option value="">{{ __('Sin especificar') }}</option>
                        @foreach($colors as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="color" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Nivel de protección (DO, IGP…)') }}</flux:label>
                    <flux:input wire:model="protection_level" type="text" :placeholder="__('ej. Rioja DOCa')" />
                    <flux:error name="protection_level" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Procedencia geográfica') }}</flux:label>
                    <flux:input wire:model="geographic_origin" type="text" :placeholder="__('ej. La Rioja')" />
                    <flux:error name="geographic_origin" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Añada') }}</flux:label>
                    <flux:input wire:model="vintage_year" type="number" min="1900" max="2100" placeholder="{{ date('Y') }}" />
                    <flux:error name="vintage_year" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Alcohol probable (%)') }}</flux:label>
                    <flux:input wire:model="alcohol_pct" type="number" step="0.1" min="0" max="100" placeholder="0.0" />
                    <flux:description>{{ __('Solo para mosto / vino a granel') }}</flux:description>
                    <flux:error name="alcohol_pct" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="{{ __('Cantidades y Fechas') }}" color="blue">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <flux:field>
                    <flux:label required>{{ __('Cantidad total (kg)') }}</flux:label>
                    <flux:input wire:model="total_weight_kg" type="number" step="0.001" min="0.001" placeholder="0.000" required />
                    <flux:error name="total_weight_kg" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Fecha de entrada') }}</flux:label>
                    <flux:input wire:model="entry_date" type="date" required />
                    <flux:error name="entry_date" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Fecha de vendimia') }}</flux:label>
                    <flux:input wire:model="harvest_date" type="date" />
                    <flux:error name="harvest_date" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Fecha de caducidad') }}</flux:label>
                    <flux:input wire:model="expiration_date" type="date" />
                    <flux:error name="expiration_date" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Contenedor de destino') }}</flux:label>
                    <flux:select wire:model="container_id">
                        <option value="">{{ __('Sin asignar') }}</option>
                        @foreach($containers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="container_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Estado') }}</flux:label>
                    <flux:select wire:model="status" required>
                        <option value="available">{{ __('Disponible') }}</option>
                        <option value="used">{{ __('Usado') }}</option>
                        <option value="archived">{{ __('Archivado') }}</option>
                    </flux:select>
                    <flux:error name="status" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="{{ __('Notas') }}" color="green">
            <flux:field>
                <flux:label>{{ __('Observaciones') }}</flux:label>
                <flux:textarea wire:model="notes" rows="3" :placeholder="__('Observaciones sobre la partida...')" />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions :cancel-url="roleRoute('external-grape.index')" :submit-label="__('Guardar partida')" />
    </form>
</x-agro.form-card>
