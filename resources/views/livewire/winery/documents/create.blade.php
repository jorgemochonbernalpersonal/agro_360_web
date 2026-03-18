<x-agro.form-card
    title="Nuevo Documento"
    description="Registra un documento oficial de la bodega."
    icon="folder-open"
    icon-color="from-amber-500 to-amber-700"
    :back-url="roleRoute('documents.index')"
>
    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section title="Identificación" color="amber">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <flux:field class="md:col-span-2">
                    <flux:label required>Título</flux:label>
                    <flux:input wire:model="title" type="text" placeholder="Ej. Licencia de apertura" required />
                    <flux:error name="title" />
                </flux:field>

                <flux:field>
                    <flux:label required>Tipo de documento</flux:label>
                    <flux:select wire:model="document_type" required>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="document_type" />
                </flux:field>

                <flux:field>
                    <flux:label>Número de referencia</flux:label>
                    <flux:input wire:model="reference_number" type="text" placeholder="Opcional" />
                    <flux:error name="reference_number" />
                </flux:field>

                <flux:field>
                    <flux:label>Organismo emisor</flux:label>
                    <flux:input wire:model="issuing_authority" type="text" placeholder="Ej. Consejería de Agricultura" />
                    <flux:error name="issuing_authority" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Vigencia" color="amber">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>Fecha de emisión</flux:label>
                    <flux:input wire:model="issue_date" type="date" />
                    <flux:error name="issue_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Fecha de caducidad</flux:label>
                    <flux:input wire:model="expiry_date" type="date" />
                    <flux:error name="expiry_date" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Notas" color="amber">
            <flux:field>
                <flux:label>Observaciones</flux:label>
                <flux:textarea wire:model="notes" rows="2" placeholder="Condiciones, observaciones..." />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions :back-url="roleRoute('documents.index')" submit-label="Guardar documento" />
    </form>
</x-agro.form-card>
