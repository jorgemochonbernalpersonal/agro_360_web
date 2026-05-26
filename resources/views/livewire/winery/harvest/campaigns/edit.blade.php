<x-agro.form-card
    title="{{ __('Editar Campaña de Vendimia') }}"
    :description="__('Modifica el periodo y nombre de la campaña')"
    :back-url="roleRoute('campaigns.index')"
>
    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section title="{{ __('Datos de la Campaña') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <flux:field>
                    <flux:label>{{ __('Nombre de la campaña') }}</flux:label>
                    <flux:input wire:model="name" :placeholder="__('Ej: Vendimia 2026')" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Año') }}</flux:label>
                    <flux:input wire:model="year" type="number" min="2000" max="2099" />
                    <flux:error name="year" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Fecha de inicio') }}</flux:label>
                    <flux:input wire:model="start_date" type="date" />
                    <flux:error name="start_date" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Fecha de fin') }}</flux:label>
                    <flux:input wire:model="end_date" type="date" />
                    <flux:error name="end_date" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>{{ __('Descripción (opcional)') }}</flux:label>
                    <flux:textarea wire:model="description" rows="3" :placeholder="__('Notas sobre esta campaña...')" />
                    <flux:error name="description" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('campaigns.index')"
            submit-:label="__('Guardar Cambios')"
        />
    </form>
</x-agro.form-card>
