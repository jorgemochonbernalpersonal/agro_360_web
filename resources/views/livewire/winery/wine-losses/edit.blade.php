<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="{{ __('Editar Merma') }}"
        :description="__('Modifica los datos de la pérdida registrada')"
    />

    <x-agro.form-card>
        <x-agro.form-section title="{{ __('Identificación') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <flux:label required>{{ __('Vino') }}</flux:label>
                    <flux:select wire:model="wine_id" class="mt-1">
                        <flux:select.option value="">{{ __('Seleccionar vino...') }}</flux:select.option>
                        @foreach($wines as $wine)
                            <flux:select.option value="{{ $wine->id }}">{{ $wine->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('wine_id') <flux:error>{{ $message }}</flux:error> @enderror
                </div>
                <div>
                    <flux:label>{{ __('Contenedor origen') }}</flux:label>
                    <flux:select wire:model="container_id" class="mt-1">
                        <flux:select.option value="">{{ __('Sin contenedor específico') }}</flux:select.option>
                        @foreach($containers as $c)
                            <flux:select.option value="{{ $c->id }}">
                                {{ $c->name }}
                                @if($c->wine_volume_liters > 0)
                                    ({{ number_format($c->wine_volume_liters, 1) }} L)
                                @endif
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('container_id') <flux:error>{{ $message }}</flux:error> @enderror
                </div>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="{{ __('Clasificación') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <flux:label required>{{ __('Tipo de merma') }}</flux:label>
                    <flux:select wire:model="loss_type" class="mt-1">
                        @foreach($lossTypes as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('loss_type') <flux:error>{{ $message }}</flux:error> @enderror
                </div>
                <div>
                    <flux:label>{{ __('Autorización') }}</flux:label>
                    <flux:select wire:model="loss_authorization" class="mt-1">
                        <flux:select.option value="">{{ __('Sin clasificar') }}</flux:select.option>
                        @foreach($lossAuths as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('loss_authorization') <flux:error>{{ $message }}</flux:error> @enderror
                </div>
                <div>
                    <flux:label>{{ __('Referencia regulatoria') }}</flux:label>
                    <flux:input wire:model="regulatory_reference" class="mt-1" />
                    @error('regulatory_reference') <flux:error>{{ $message }}</flux:error> @enderror
                </div>
                <div>
                    <flux:label required>{{ __('Fecha') }}</flux:label>
                    <flux:input type="date" wire:model="loss_date" class="mt-1" />
                    @error('loss_date') <flux:error>{{ $message }}</flux:error> @enderror
                </div>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="{{ __('Cantidad') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <flux:label required>{{ __('Cantidad') }}</flux:label>
                    <flux:input type="number" step="0.001" min="0.001" wire:model="quantity" class="mt-1" />
                    @error('quantity') <flux:error>{{ $message }}</flux:error> @enderror
                </div>
                <div>
                    <flux:label required>{{ __('Unidad') }}</flux:label>
                    <flux:select wire:model="unit_of_measurement_id" class="mt-1">
                        <flux:select.option value="">{{ __('Seleccionar...') }}</flux:select.option>
                        @foreach($units as $unit)
                            <flux:select.option value="{{ $unit->id }}">{{ __($unit->name) }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('unit_of_measurement_id') <flux:error>{{ $message }}</flux:error> @enderror
                </div>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="{{ __('Observaciones') }}">
            <flux:textarea wire:model="notes" rows="3" />
            @error('notes') <flux:error>{{ $message }}</flux:error> @enderror
        </x-agro.form-section>

        <x-agro.form-actions
            submit-:label="__('Guardar cambios')"
            cancel-href="{{ roleRoute('wine-losses.index') }}"
            wire:submit="save"
        />
    </x-agro.form-card>

</div>
