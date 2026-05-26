<x-agro.form-card
    title="{{ __('Nuevo Vino') }}"
    :description="__('Crea un vino para iniciar su seguimiento en el cuaderno de bodega.')"
    icon="beaker"
    icon-color="from-purple-500 to-purple-700"
    :back-url="roleRoute('wines.index')"
>
    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section title="{{ __('Identificación') }}" color="purple">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <flux:field class="md:col-span-2">
                    <flux:label required>{{ __('Nombre del vino') }}</flux:label>
                    <flux:input wire:model="name" type="text" :placeholder="__('Ej. Reserva Tempranillo 2025')" required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Código interno') }}</flux:label>
                    <flux:input wire:model="internal_code" type="text" :placeholder="__('Ej. VT-2025-001')" />
                    <flux:error name="internal_code" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Tipo de vino') }}</flux:label>
                    <flux:select wire:model="wine_type" required>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="wine_type" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Añada') }}</flux:label>
                    <flux:input wire:model="vintage" type="number" min="1900" max="{{ date('Y') + 1 }}" placeholder="{{ date('Y') }}" />
                    <flux:error name="vintage" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Estado') }}</flux:label>
                    <flux:select wire:model="status" required>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="status" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Crianza') }}</flux:label>
                    <flux:select wire:model="aging_type">
                        <flux:select.option value="">{{ __('Sin especificar') }}</flux:select.option>
                        @foreach($agingTypes as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="aging_type" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Categoría / DOP') }}</flux:label>
                    <flux:select wire:model="category">
                        <flux:select.option value="">{{ __('Sin especificar') }}</flux:select.option>
                        @foreach($categories as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="category" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>{{ __('Variedad / Coupage') }}</flux:label>
                    <flux:input wire:model="variety" type="text" :placeholder="__('Ej. 80% Tempranillo, 20% Garnacha')" />
                    <flux:error name="variety" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Volumen (litros)') }}</flux:label>
                    <flux:input wire:model="volume_liters" type="number" step="0.001" min="0" placeholder="0" />
                    <flux:error name="volume_liters" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Enólogo responsable') }}</flux:label>
                    <flux:select wire:model="oenologist_id">
                        <flux:select.option value="">{{ __('Sin asignar') }}</flux:select.option>
                        @foreach($oenologists as $oenologist)
                            <flux:select.option value="{{ $oenologist->id }}">{{ $oenologist->full_name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="oenologist_id" />
                </flux:field>

                <div class="flex flex-col gap-3 justify-center">
                    <flux:checkbox wire:model="is_must" :label="__('Es mosto (sin fermentar)')" />
                    <flux:checkbox wire:model="is_organic" :label="__('Producción ecológica')" />
                </div>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="{{ __('Notas') }}" color="green">
            <flux:field>
                <flux:label>{{ __('Observaciones') }}</flux:label>
                <flux:textarea wire:model="notes" rows="3" :placeholder="__('Características, origen de uva...')" />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions :back-url="roleRoute('wines.index')" submit-:label="__('Crear vino')" />
    </form>
</x-agro.form-card>
