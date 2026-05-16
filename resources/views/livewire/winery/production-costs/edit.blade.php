<div class="space-y-6 animate-fade-in max-w-2xl">

    <x-agro.page-header
        title="Editar coste de producción"
        description="Modifica o elimina este registro de coste."
        icon="currency-euro"
    />

    <x-agro.card>
        <form wire:submit="save" class="space-y-5 p-1">

            {{-- Vino --}}
            <div>
                <flux:label>Vino <span class="text-red-500">*</span></flux:label>
                <flux:select wire:model="wine_id">
                    <flux:select.option value="">Selecciona un vino...</flux:select.option>
                    @foreach($wines as $wine)
                        <flux:select.option value="{{ $wine->id }}">{{ $wine->name }} — Añada {{ $wine->vintage }}</flux:select.option>
                    @endforeach
                </flux:select>
                @error('wine_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Categoría + Descripción --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <flux:label>Categoría <span class="text-red-500">*</span></flux:label>
                    <flux:select wire:model="category">
                        <flux:select.option value="">Selecciona categoría...</flux:select.option>
                        @foreach($categories as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('category') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1">Descripción <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="description"
                           class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
                    @error('description') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Importe + Fecha --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1">Importe (€) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0.01" wire:model="amount"
                           class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
                    @error('amount') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1">Fecha <span class="text-red-500">*</span></label>
                    <input type="date" wire:model="cost_date"
                           class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
                    @error('cost_date') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Proveedor + Referencia --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1">Proveedor</label>
                    <input type="text" wire:model="supplier"
                           class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
                    @error('supplier') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1">Referencia factura</label>
                    <input type="text" wire:model="invoice_reference"
                           class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
                    @error('invoice_reference') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Notas --}}
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Notas</label>
                <textarea wire:model="notes" rows="3"
                          class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300"></textarea>
                @error('notes') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Acciones --}}
            <div class="flex items-center justify-between pt-2 border-t border-zinc-100">
                <div class="flex items-center gap-3">
                    <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                        Guardar cambios
                    </flux:button>
                    <a href="{{ roleRoute('production-costs.index') }}" wire:navigate
                       class="text-sm text-zinc-500 hover:text-zinc-700">Cancelar</a>
                </div>
                <flux:button type="button" variant="danger" wire:click="delete"
                             wire:confirm="¿Eliminar este coste? Esta acción no se puede deshacer.">
                    Eliminar
                </flux:button>
            </div>

        </form>
    </x-agro.card>

</div>
