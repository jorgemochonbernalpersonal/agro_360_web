<div>
    <x-agro.form-card title="Nuevo Producto" description="Registra un producto para su venta y facturación"
        :back-url="route('winery.product-lots.index')">

        <form wire:submit.prevent="save" class="space-y-8">

            {{-- ── Información básica ──────────────────────────────────── --}}
            <x-agro.form-section title="Información básica">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field class="md:col-span-2">
                        <flux:label for="name">Nombre del producto *</flux:label>
                        <flux:input wire:model="name" type="text" id="name" placeholder="Ej: Rioja Reserva 2022" required />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="sku">SKU / Referencia</flux:label>
                        <flux:input wire:model="sku" type="text" id="sku" placeholder="Ej: RRV-2022" />
                        <flux:error name="sku" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="vintage">Añada (año)</flux:label>
                        <flux:input wire:model="vintage" type="number" id="vintage" placeholder="{{ now()->year }}" min="1900" max="{{ now()->year + 1 }}" />
                        <flux:error name="vintage" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="wine_type">Tipo de vino *</flux:label>
                        <flux:select wire:model="wine_type" id="wine_type" required>
                            <option value="tinto">Tinto</option>
                            <option value="blanco">Blanco</option>
                            <option value="rosado">Rosado</option>
                            <option value="espumoso">Espumoso</option>
                            <option value="otro">Otro</option>
                        </flux:select>
                        <flux:error name="wine_type" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="aging_type">Crianza</flux:label>
                        <flux:select wire:model="aging_type" id="aging_type">
                            <option value="">Sin especificar</option>
                            <option value="joven">Joven</option>
                            <option value="crianza">Crianza</option>
                            <option value="reserva">Reserva</option>
                            <option value="gran_reserva">Gran Reserva</option>
                            <option value="autor">Vino de Autor</option>
                        </flux:select>
                        <flux:error name="aging_type" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="alcohol">Alcohol (% vol)</flux:label>
                        <flux:input wire:model="alcohol" type="number" step="0.1" id="alcohol" placeholder="13.5" />
                        <flux:error name="alcohol" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            {{-- ── Variedades de uva ───────────────────────────────────── --}}
            <x-agro.form-section title="Variedades de uva">
                <div class="space-y-3">
                    @foreach($this->grapes as $i => $grape)
                    <div class="flex gap-3 items-start" wire:key="grape-create-{{ $i }}">
                        <div class="flex-1">
                            <flux:select wire:model="grapes.{{ $i }}.grape_variety_id">
                                <option value="">Selecciona variedad...</option>
                                @foreach($this->grapeVarieties as $variety)
                                    <option value="{{ $variety->id }}">
                                        {{ $variety->name }}{{ $variety->color ? ' (' . $variety->color . ')' : '' }}
                                    </option>
                                @endforeach
                            </flux:select>
                            <flux:error name="grapes.{{ $i }}.grape_variety_id" />
                        </div>
                        <div class="w-28">
                            <flux:input wire:model.live="grapes.{{ $i }}.percentage"
                                type="number" step="0.01" min="0" max="100" placeholder="%" />
                            <flux:error name="grapes.{{ $i }}.percentage" />
                        </div>
                        <button type="button" wire:click="removeGrape({{ $i }})"
                            class="mt-1 p-1.5 text-zinc-400 hover:text-red-500 hover:bg-red-50 rounded transition-colors"
                            title="Eliminar">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    @endforeach

                    <div class="flex items-center justify-between pt-1">
                        <flux:button type="button" wire:click="addGrape" variant="ghost" size="sm">
                            + Añadir variedad
                        </flux:button>
                        <span class="text-sm {{ $this->grapeTotal > 100 ? 'text-red-600 font-semibold' : 'text-zinc-500' }}">
                            Total: {{ number_format($this->grapeTotal, 2) }}%
                        </span>
                    </div>

                    @error('grapes')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </x-agro.form-section>

            {{-- ── Stock y precio ──────────────────────────────────────── --}}
            <x-agro.form-section title="Stock y Precio">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label for="unit">Unidad *</flux:label>
                        <flux:select wire:model="unit" id="unit" required>
                            <option value="litros">Litros</option>
                            <option value="botellas">Botellas (75 cl)</option>
                            <option value="cajas">Cajas</option>
                        </flux:select>
                        <flux:error name="unit" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="quantity">Cantidad total *</flux:label>
                        <flux:input wire:model.live="quantity" type="number" step="0.001" min="0" id="quantity" placeholder="0" required />
                        <flux:error name="quantity" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="available_quantity">Disponible para venta *</flux:label>
                        <flux:input wire:model="available_quantity" type="number" step="0.001" min="0" id="available_quantity" placeholder="0" required />
                        <flux:description>Por defecto igual a la cantidad total.</flux:description>
                        <flux:error name="available_quantity" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="price_per_unit">Precio de venta / ud (€)</flux:label>
                        <flux:input wire:model="price_per_unit" type="number" step="0.001" min="0" id="price_per_unit" placeholder="0.000" />
                        <flux:description>Precio por defecto al facturar.</flux:description>
                        <flux:error name="price_per_unit" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="cost_price">Coste / ud (€)</flux:label>
                        <flux:input wire:model="cost_price" type="number" step="0.001" min="0" id="cost_price" placeholder="0.000" />
                        <flux:description>Solo visible internamente.</flux:description>
                        <flux:error name="cost_price" />
                    </flux:field>
                </div>
            </x-agro.form-section>

{{-- ── Certificaciones ─────────────────────────────────────── --}}
            <x-agro.form-section title="Certificaciones">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <flux:checkbox wire:model.live="sulfites" id="sulfites" />
                        <span class="text-sm font-medium text-zinc-700">Contiene sulfitos</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <flux:checkbox wire:model.live="ecological" id="ecological" />
                        <span class="text-sm font-medium text-zinc-700">Ecológico</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <flux:checkbox wire:model.live="is_vegan" id="is_vegan" />
                        <span class="text-sm font-medium text-zinc-700">Apto para veganos</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <flux:checkbox wire:model.live="is_biodynamic" id="is_biodynamic" />
                        <span class="text-sm font-medium text-zinc-700">Biodinámico</span>
                    </label>
                </div>
            </x-agro.form-section>

            {{-- ── Notas ───────────────────────────────────────────────── --}}
            <x-agro.form-section title="Notas internas">
                <flux:field>
                    <flux:label for="notes">Notas</flux:label>
                    <flux:textarea wire:model="notes" id="notes" rows="3" placeholder="Observaciones sobre este producto..." />
                    <flux:error name="notes" />
                </flux:field>
            </x-agro.form-section>

            <x-agro.form-actions :cancel-url="route('winery.product-lots.index')" submit-label="Crear Producto" />
        </form>
    </x-agro.form-card>
</div>
