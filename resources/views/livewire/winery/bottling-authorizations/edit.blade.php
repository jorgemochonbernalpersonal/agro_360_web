<x-agro.form-card
    title="{{ __('Editar Autorización de Embotellado') }}"
    :description="__('Modifica los datos de la autorización de embotellado.')"
    icon="identification"
    icon-color="from-violet-500 to-violet-700"
    :back-url="roleRoute('bottling-authorizations.index')"
>
    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section title="{{ __('Identificación') }}" color="violet">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <flux:field>
                    <flux:label required>{{ __('N.º de autorización') }}</flux:label>
                    <flux:input wire:model="authorization_number" type="text" required />
                    <flux:error name="authorization_number" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Tipo') }}</flux:label>
                    <flux:select wire:model="authorization_type" required>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="authorization_type" />
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
                    <flux:label>{{ __('Vino asociado') }}</flux:label>
                    <flux:select wire:model="wine_id">
                        <option value="">{{ __('Sin vino específico') }}</option>
                        @foreach($wines as $wine)
                            <option value="{{ $wine->id }}">{{ $wine->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="wine_id" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Volumen autorizado (litros)') }}</flux:label>
                    <flux:input wire:model="authorized_volume_liters" type="number" step="0.01" min="0" />
                    <flux:error name="authorized_volume_liters" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Organismo emisor') }}</flux:label>
                    <flux:input wire:model="issuing_authority" type="text" />
                    <flux:error name="issuing_authority" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="{{ __('Vigencia') }}" color="violet">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>{{ __('Válido desde') }}</flux:label>
                    <flux:input wire:model="valid_from" type="date" />
                    <flux:error name="valid_from" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Válido hasta') }}</flux:label>
                    <flux:input wire:model="valid_until" type="date" />
                    <flux:error name="valid_until" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="{{ __('Condiciones y Notas') }}" color="violet">
            <div class="space-y-4">
                <flux:field>
                    <flux:label>{{ __('Condiciones') }}</flux:label>
                    <flux:textarea wire:model="conditions" rows="2" />
                    <flux:error name="conditions" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Observaciones') }}</flux:label>
                    <flux:textarea wire:model="notes" rows="2" />
                    <flux:error name="notes" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions :back-url="roleRoute('bottling-authorizations.index')" submit-:label="__('Actualizar autorización')" />
    </form>
</x-agro.form-card>
