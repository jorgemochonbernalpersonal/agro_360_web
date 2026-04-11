<x-agro.form-card
    title="New Warehouse"
    description="Register a new warehouse or storage location"
    :back-url="roleRoute('viticulturist.warehouse.index', ['tab' => 'almacenes'])"
>
    <form wire:submit="save" class="space-y-6">

        <x-agro.form-section title="Warehouse Information">
            <div class="grid grid-cols-1 gap-6">
                <flux:field>
                    <flux:label required>Name</flux:label>
                    <flux:input wire:model="name" type="text" placeholder="e.g. Main Warehouse, North Shed..." required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Location</flux:label>
                    <flux:input wire:model="location" type="text" placeholder="e.g. Building A, Ground Floor, Room 3..." />
                    <flux:error name="location" />
                </flux:field>

                <flux:field>
                    <flux:label>Description</flux:label>
                    <flux:textarea wire:model="description" rows="3" placeholder="Additional information about this warehouse..." />
                    <flux:error name="description" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.warehouse.index', ['tab' => 'almacenes'])"
            submit-label="Create Warehouse"
        />
    </form>
</x-agro.form-card>
