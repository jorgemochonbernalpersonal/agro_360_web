<x-agro.form-card
    title="Nueva Autorización de Embotellado"
    description="Registra una autorización oficial de embotellado."
    icon="identification"
    icon-color="from-violet-500 to-violet-700"
    :back-url="roleRoute('bottling-authorizations.index')"
>
    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section title="Identificación" color="violet">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <flux:field>
                    <flux:label required>N.º de autorización</flux:label>
                    <flux:input wire:model="authorization_number" type="text" placeholder="Ej. EMB-2026-00123" required />
                    <flux:error name="authorization_number" />
                </flux:field>

                <flux:field>
                    <flux:label required>Tipo</flux:label>
                    <flux:select wire:model="authorization_type" required>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="authorization_type" />
                </flux:field>

                <flux:field>
                    <flux:label required>Estado</flux:label>
                    <flux:select wire:model="status" required>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="status" />
                </flux:field>

                <flux:field>
                    <flux:label>Vino asociado</flux:label>
                    <flux:select wire:model="wine_id">
                        <option value="">Sin vino específico</option>
                        @foreach($wines as $wine)
                            <option value="{{ $wine->id }}">{{ $wine->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="wine_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Volumen autorizado (litros)</flux:label>
                    <flux:input wire:model="authorized_volume_liters" type="number" step="0.01" min="0" placeholder="0.00" />
                    <flux:error name="authorized_volume_liters" />
                </flux:field>

                <flux:field>
                    <flux:label>Organismo emisor</flux:label>
                    <flux:input wire:model="issuing_authority" type="text" placeholder="Ej. Consejo Regulador DO..." />
                    <flux:error name="issuing_authority" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Vigencia" color="violet">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>Válido desde</flux:label>
                    <flux:input wire:model="valid_from" type="date" />
                    <flux:error name="valid_from" />
                </flux:field>

                <flux:field>
                    <flux:label>Válido hasta</flux:label>
                    <flux:input wire:model="valid_until" type="date" />
                    <flux:error name="valid_until" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Condiciones y Notas" color="violet">
            <div class="space-y-4">
                <flux:field>
                    <flux:label>Condiciones</flux:label>
                    <flux:textarea wire:model="conditions" rows="2" placeholder="Condiciones específicas de la autorización..." />
                    <flux:error name="conditions" />
                </flux:field>

                <flux:field>
                    <flux:label>Observaciones</flux:label>
                    <flux:textarea wire:model="notes" rows="2" placeholder="Notas adicionales..." />
                    <flux:error name="notes" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions :back-url="roleRoute('bottling-authorizations.index')" submit-label="Guardar autorización" />
    </form>
</x-agro.form-card>
