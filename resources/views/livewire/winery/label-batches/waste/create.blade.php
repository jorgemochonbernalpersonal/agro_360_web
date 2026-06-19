<x-agro.form-card
    :title="__('Registrar Merma') . ' — ' . $labelBatch->name"
    :description="__('Registra etiquetas inutilizadas, dañadas o destruidas.')"
    icon="exclamation-triangle"
    icon-color="from-red-500 to-red-700"
    :back-url="roleRoute('label-batches.waste.index', $labelBatch)"
>
    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section title="{{ __('Datos de la merma') }}" color="red">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <flux:field>
                    <flux:label required>{{ __('Cantidad de etiquetas') }}</flux:label>
                    <flux:input wire:model="quantity" type="number" min="1" step="1" required placeholder="0" />
                    <flux:description>
                        Disponibles: {{ $labelBatch->total_quantity - $labelBatch->used_quantity - $labelBatch->wasted_quantity }}
                    </flux:description>
                    <flux:error name="quantity" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Fecha') }}</flux:label>
                    <flux:input wire:model="waste_date" type="date" required />
                    <flux:error name="waste_date" />
                </flux:field>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <flux:field>
                    <flux:label>{{ __('Número desde') }}</flux:label>
                    <flux:input wire:model="from_number" type="number" min="0" :placeholder="__('Opcional')" />
                    <flux:error name="from_number" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Número hasta') }}</flux:label>
                    <flux:input wire:model="to_number" type="number" min="0" :placeholder="__('Opcional')" />
                    <flux:error name="to_number" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="{{ __('Motivo') }}" color="zinc">
            <flux:field>
                <flux:label>{{ __('Descripción de la merma') }}</flux:label>
                <flux:textarea wire:model="reason" rows="3"
                    placeholder="{{ __('Ej. Etiquetas dañadas en almacén, error de impresión, retirada por DO...') }}" />
                <flux:error name="reason" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions :cancel-url="roleRoute('label-batches.waste.index', $labelBatch)" :submit-label="__('Registrar merma')" />
    </form>
</x-agro.form-card>
