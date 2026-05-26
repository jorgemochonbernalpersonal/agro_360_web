<x-agro.form-card
    title="{{ __('Editar Subproducto') }}"
    :description="__('Modifica el registro de subproducto de elaboración.')"
    icon="archive-box"
    icon-color="from-amber-600 to-orange-700"
    :back-url="roleRoute('subproducts.index')"
>
    <form wire:submit="save" class="space-y-8">

        {{-- ── Identificación ──────────────────────────────────────────────── --}}
        <x-agro.form-section title="{{ __('Identificación') }}" color="amber">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <flux:field>
                    <flux:label required>{{ __('Tipo de subproducto') }}</flux:label>
                    <flux:select wire:model="type" required>
                        <flux:select.option value="">{{ __('Seleccionar tipo...') }}</flux:select.option>
                        @foreach($types as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="type" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Fecha') }}</flux:label>
                    <flux:input wire:model="subproduct_date" type="date" required />
                    <flux:error name="subproduct_date" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Vino origen') }}</flux:label>
                    <flux:select wire:model="wine_id">
                        <flux:select.option value="">{{ __('Sin vino asociado') }}</flux:select.option>
                        @foreach($wines as $wine)
                            <flux:select.option value="{{ $wine->id }}">
                                {{ $wine->name }}{{ $wine->vintage ? ' · ' . $wine->vintage : '' }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="wine_id" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- ── Cantidad ─────────────────────────────────────────────────────── --}}
        <x-agro.form-section title="{{ __('Cantidad') }}" color="orange">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>{{ __('Cantidad') }}</flux:label>
                    <flux:input wire:model="quantity" type="number" min="0.001" step="0.001" required />
                    <flux:error name="quantity" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Unidad de medida') }}</flux:label>
                    <flux:select wire:model="unit_of_measurement_id">
                        <flux:select.option value="">{{ __('Sin unidad') }}</flux:select.option>
                        @foreach($units as $unit)
                            <flux:select.option value="{{ $unit->id }}">
                                {{ $unit->name }}{{ $unit->abbreviation ? ' (' . $unit->abbreviation . ')' : '' }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="unit_of_measurement_id" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- ── Destino ──────────────────────────────────────────────────────── --}}
        <x-agro.form-section title="{{ __('Destino') }}" color="zinc">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <flux:field>
                    <flux:label required>{{ __('Destino') }}</flux:label>
                    <flux:select wire:model="destination" required>
                        <flux:select.option value="">{{ __('Seleccionar destino...') }}</flux:select.option>
                        @foreach($destinations as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="destination" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Nombre del destino') }}</flux:label>
                    <flux:input wire:model="destination_name" />
                    <flux:error name="destination_name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Número de lote / albarán') }}</flux:label>
                    <flux:input wire:model="lot_number" />
                    <flux:error name="lot_number" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- ── Notas ────────────────────────────────────────────────────────── --}}
        <x-agro.form-section title="{{ __('Notas') }}" color="zinc">
            <flux:field>
                <flux:label>{{ __('Observaciones') }}</flux:label>
                <flux:textarea wire:model="notes" rows="3" />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions :back-url="roleRoute('subproducts.index')" submit-:label="__('Guardar cambios')" />
    </form>
</x-agro.form-card>
