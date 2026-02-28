<x-agro.form-card
    title="Edit Warehouse"
    description="Update the details of this warehouse"
    :back-url="route('viticulturist.almacen.index', ['tab' => 'almacenes'])"
>
    <form wire:submit="save" class="space-y-6">

        <x-agro.form-section title="Warehouse Information">
            <div class="grid grid-cols-1 gap-6">
                <flux:field>
                    <flux:label required>Name</flux:label>
                    <flux:input wire:model="name" type="text" required />
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

                <div class="flex items-center gap-3">
                    <input wire:model="active" type="checkbox" id="active"
                        class="w-4 h-4 text-agro-700 border-zinc-300 rounded focus:ring-agro-700">
                    <label for="active" class="text-sm font-semibold text-zinc-700">Active warehouse</label>
                    <p class="text-xs text-zinc-500">Inactive warehouses will not appear in filters when registering stock</p>
                </div>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="route('viticulturist.almacen.index', ['tab' => 'almacenes'])"
            submit-label="Save Changes"
        />
    </form>
</x-agro.form-card>
