<x-agro.form-card
    :title="__('Editar Operación') . ' — ' . $wine->name"
    :description="__('Modifica la etapa del proceso de vinificación.')"
    icon="arrow-path"
    icon-color="from-indigo-500 to-indigo-700"
    :back-url="roleRoute('wines.edit', $wine)"
>
    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section title="{{ __('Tipo de operación') }}" color="indigo">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <flux:field>
                    <flux:label required>{{ __('Operación') }}</flux:label>
                    <flux:select wire:model="process_type" required>
                        @foreach($processTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="process_type" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Depósito principal') }}</flux:label>
                    <flux:select wire:model="container_id">
                        <option value="">{{ __('Sin depósito') }}</option>
                        @foreach($containers as $container)
                            <option value="{{ $container->id }}">
                                {{ $container->name }}
                                @if($container->used_capacity > 0)
                                    ({{ number_format($container->used_capacity, 0) }}/{{ number_format($container->capacity, 0) }} kg)
                                @endif
                            </option>
                        @endforeach
                    </flux:select>
                    <flux:error name="container_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Fecha de inicio') }}</flux:label>
                    <flux:input wire:model="start_date" type="date" required />
                    <flux:error name="start_date" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Fecha de fin') }}</flux:label>
                    <flux:input wire:model="end_date" type="date" />
                    <flux:description>{{ __('Dejar vacío si aún está en curso') }}</flux:description>
                    <flux:error name="end_date" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Cantidad procesada') }}</flux:label>
                    <flux:input wire:model="quantity" type="number" step="0.001" min="0" placeholder="0" />
                    <flux:error name="quantity" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Unidad') }}</flux:label>
                    <flux:select wire:model="unit_of_measurement_id">
                        <option value="">{{ __('Sin unidad') }}</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ __($unit->name) }} ({{ $unit->symbol }})</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="unit_of_measurement_id" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="{{ __('Depósitos adicionales') }}" color="blue">
            <flux:description class="mb-4">Para operaciones como coupage o trasvase multi-depósito.</flux:description>

            @foreach($extraContainers as $i => $row)
                <div class="flex gap-4 items-end mb-3">
                    <flux:field class="flex-1">
                        <flux:label>{{ __('Depósito') }}</flux:label>
                        <flux:select wire:model="extraContainers.{{ $i }}.container_id">
                            <option value="">{{ __('Seleccionar...') }}</option>
                            @foreach($containers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                    <flux:field class="w-36">
                        <flux:label>{{ __('Cantidad') }}</flux:label>
                        <flux:input wire:model="extraContainers.{{ $i }}.quantity" type="number" step="0.001" min="0" />
                    </flux:field>
                    <flux:button type="button" variant="ghost" icon="trash" size="sm"
                        wire:click="removeExtraContainer({{ $i }})" />
                </div>
            @endforeach

            <flux:button type="button" variant="ghost" icon="plus" size="sm" wire:click="addExtraContainer">{{ __('Añadir depósito') }}</flux:button>
        </x-agro.form-section>

        <x-agro.form-section title="{{ __('Observaciones') }}" color="green">
            <flux:field>
                <flux:label>{{ __('Notas técnicas') }}</flux:label>
                <flux:textarea wire:model="observations" rows="3"
                    placeholder="{{ __('Temperatura, densidad, observaciones del enólogo...') }}" />
                <flux:error name="observations" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions :back-url="roleRoute('wines.edit', $wine)" submit-:label="__('Guardar cambios')" />
    </form>
</x-agro.form-card>
