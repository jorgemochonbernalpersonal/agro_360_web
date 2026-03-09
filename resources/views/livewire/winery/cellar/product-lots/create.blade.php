<div>
    <x-agro.form-card title="Nuevo Producto" description="Registra un producto para su venta y facturación"
        :back-url="route('winery.product-lots.index')">

        <form wire:submit.prevent="save" class="space-y-8">

            {{-- ── Información del Producto ────────────────────────────── --}}
            <x-agro.form-section title="Información del Producto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label for="name">Nombre del producto *</flux:label>
                        <flux:input wire:model="name" type="text" id="name" placeholder="Ej: Rioja Reserva 2022" required />
                        <flux:error name="name" />
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
                        <flux:label for="agingtime">Meses en barrica</flux:label>
                        <flux:input wire:model="agingtime" type="number" id="agingtime" min="0" placeholder="12" />
                        <flux:error name="agingtime" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="alcohol">Alcohol (% vol)</flux:label>
                        <flux:input wire:model="alcohol" type="number" step="0.1" id="alcohol" placeholder="13.5" />
                        <flux:error name="alcohol" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            {{-- ── Stock y Precio ──────────────────────────────────────── --}}
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
                        <flux:label for="available_quantity">Cantidad disponible para venta *</flux:label>
                        <flux:input wire:model="available_quantity" type="number" step="0.001" min="0" id="available_quantity" placeholder="0" required />
                        <flux:description>Por defecto igual a la cantidad total.</flux:description>
                        <flux:error name="available_quantity" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="price_per_unit">Precio de venta / ud (€)</flux:label>
                        <flux:input wire:model="price_per_unit" type="number" step="0.001" min="0" id="price_per_unit" placeholder="0.000" />
                        <flux:description>Se usará como precio por defecto al facturar.</flux:description>
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

            {{-- ── Formato / Comercial ─────────────────────────────────── --}}
            <x-agro.form-section title="Formato y Comercial">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label for="ean">EAN / Código de barras</flux:label>
                        <flux:input wire:model="ean" type="text" id="ean" placeholder="8412345678901" maxlength="14" />
                        <flux:error name="ean" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="bottle_format">Formato de botella</flux:label>
                        <flux:select wire:model="bottle_format" id="bottle_format">
                            <option value="">Seleccionar...</option>
                            <option value="18.7cl">18.7 cl (Piccolo)</option>
                            <option value="37.5cl">37.5 cl (½ botella)</option>
                            <option value="75cl">75 cl (Estándar)</option>
                            <option value="100cl">100 cl (1 L)</option>
                            <option value="150cl">150 cl (Magnum)</option>
                            <option value="225cl">225 cl (Jeroboam)</option>
                            <option value="300cl">300 cl (Doble Magnum)</option>
                            <option value="600cl">600 cl (Methuselah)</option>
                        </flux:select>
                        <flux:error name="bottle_format" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="units_per_case">Botellas por caja</flux:label>
                        <flux:input wire:model="units_per_case" type="number" id="units_per_case" min="1" max="255" placeholder="6" />
                        <flux:error name="units_per_case" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            {{-- ── Analíticos ──────────────────────────────────────────── --}}
            <x-agro.form-section title="Analíticos">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label for="residual_sugar">Azúcar residual (g/L)</flux:label>
                        <flux:input wire:model="residual_sugar" type="number" step="0.01" min="0" id="residual_sugar" placeholder="2.40" />
                        <flux:error name="residual_sugar" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="total_acidity">Acidez total (g/L)</flux:label>
                        <flux:input wire:model="total_acidity" type="number" step="0.01" min="0" id="total_acidity" placeholder="5.80" />
                        <flux:error name="total_acidity" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="volatile_acidity">Acidez volátil (g/L)</flux:label>
                        <flux:input wire:model="volatile_acidity" type="number" step="0.01" min="0" id="volatile_acidity" placeholder="0.42" />
                        <flux:error name="volatile_acidity" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="ph">pH</flux:label>
                        <flux:input wire:model="ph" type="number" step="0.01" min="0" max="14" id="ph" placeholder="3.52" />
                        <flux:error name="ph" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            {{-- ── Viñedo ──────────────────────────────────────────────── --}}
            <x-agro.form-section title="Viñedo">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label for="vine_age">Edad media viñas (años)</flux:label>
                        <flux:input wire:model="vine_age" type="number" id="vine_age" min="0" placeholder="35" />
                        <flux:error name="vine_age" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="altitude">Altitud (m.s.n.m.)</flux:label>
                        <flux:input wire:model="altitude" type="number" id="altitude" min="0" placeholder="620" />
                        <flux:error name="altitude" />
                    </flux:field>

                    <flux:field class="md:col-span-2">
                        <flux:label for="soil_type">Tipo de suelo</flux:label>
                        <flux:input wire:model="soil_type" type="text" id="soil_type" placeholder="Ej: Arcillo-calcáreo" />
                        <flux:error name="soil_type" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            {{-- ── Elaboración ─────────────────────────────────────────── --}}
            <x-agro.form-section title="Elaboración">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label for="winemaker">Enólogo/a</flux:label>
                        <flux:input wire:model="winemaker" type="text" id="winemaker" placeholder="Nombre del enólogo" />
                        <flux:error name="winemaker" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="harvest_method">Método de vendimia</flux:label>
                        <flux:select wire:model="harvest_method" id="harvest_method">
                            <option value="">Sin especificar</option>
                            <option value="manual">Manual</option>
                            <option value="mechanic">Mecánica</option>
                            <option value="mixed">Mixta</option>
                        </flux:select>
                        <flux:error name="harvest_method" />
                    </flux:field>

                    <flux:field class="md:col-span-2">
                        <flux:label for="fermentation_vessel">Recipiente de fermentación</flux:label>
                        <flux:input wire:model="fermentation_vessel" type="text" id="fermentation_vessel" placeholder="Ej: Depósito inox + barrica francesa" />
                        <flux:error name="fermentation_vessel" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="oak_type">Tipo de barrica</flux:label>
                        <flux:input wire:model="oak_type" type="text" id="oak_type" placeholder="Ej: Francesa 225L" />
                        <flux:error name="oak_type" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="oak_months">Meses en barrica</flux:label>
                        <flux:input wire:model="oak_months" type="number" id="oak_months" min="0" placeholder="12" />
                        <flux:error name="oak_months" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            {{-- ── Certificaciones ─────────────────────────────────────── --}}
            <x-agro.form-section title="Certificaciones">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

            {{-- ── Producción y Premios ─────────────────────────────────── --}}
            <x-agro.form-section title="Producción y Premios">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label for="production_quantity">Botellas producidas</flux:label>
                        <flux:input wire:model="production_quantity" type="number" id="production_quantity" min="0" placeholder="15000" />
                        <flux:error name="production_quantity" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="bottling_date">Fecha de embotellado</flux:label>
                        <flux:input wire:model="bottling_date" type="date" id="bottling_date" />
                        <flux:error name="bottling_date" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="release_date">Fecha de lanzamiento</flux:label>
                        <flux:input wire:model="release_date" type="date" id="release_date" />
                        <flux:error name="release_date" />
                    </flux:field>

                    <flux:field class="md:col-span-2">
                        <flux:label for="awards_notes">Premios y puntuaciones</flux:label>
                        <flux:textarea wire:model="awards_notes" id="awards_notes" rows="3"
                            placeholder="Ej: 95 pts Parker 2023, Medalla de oro Mundus Vini 2022..." />
                        <flux:error name="awards_notes" />
                    </flux:field>
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
