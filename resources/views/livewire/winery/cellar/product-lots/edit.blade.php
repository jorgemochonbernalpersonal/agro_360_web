<div>
    <x-agro.form-card title="Editar Producto" description="Modifica los datos del producto"
        :back-url="route('winery.product-lots.index')">

        <div x-data="{ tab: 'general' }">
            <form wire:submit.prevent="update" class="space-y-0">

                {{-- ── Tab nav ────────────────────────────────────────── --}}
                <div class="flex border-b border-zinc-200 mb-8 -mx-6 px-6 overflow-x-auto gap-0">
                    @foreach([
                        ['key' => 'general',     'label' => 'General'],
                        ['key' => 'stock',       'label' => 'Stock e impuestos'],
                        ['key' => 'descripcion', 'label' => 'Descripción'],
                        ['key' => 'tecnico',     'label' => 'Técnico'],
                        ['key' => 'marketing',   'label' => 'Marketing'],
                    ] as $t)
                    <button type="button"
                        @click="tab = '{{ $t['key'] }}'"
                        :class="tab === '{{ $t['key'] }}'
                            ? 'border-b-2 border-zinc-800 text-zinc-800'
                            : 'border-b-2 border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300'"
                        class="px-4 py-2.5 text-sm font-medium whitespace-nowrap transition-colors -mb-px">
                        {{ $t['label'] }}
                    </button>
                    @endforeach
                </div>

                {{-- ══════════════════════════════════════════════════ --}}
                {{-- TAB: GENERAL                                       --}}
                {{-- ══════════════════════════════════════════════════ --}}
                <div x-show="tab === 'general'" x-cloak class="space-y-8">

                    <x-agro.form-section title="Información básica">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <flux:field class="md:col-span-2">
                                <flux:label for="name">Nombre del producto *</flux:label>
                                <flux:input wire:model="name" type="text" id="name" required />
                                <flux:error name="name" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="sku">SKU / Referencia</flux:label>
                                <flux:input wire:model="sku" type="text" id="sku" />
                                <flux:error name="sku" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="vintage">Añada (año)</flux:label>
                                <flux:input wire:model="vintage" type="number" id="vintage" min="1900" max="{{ now()->year + 1 }}" />
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
                                <flux:label for="agingtime">Meses de crianza total</flux:label>
                                <flux:input wire:model="agingtime" type="number" id="agingtime" min="0" />
                                <flux:error name="agingtime" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="alcohol">Alcohol (% vol)</flux:label>
                                <flux:input wire:model="alcohol" type="number" step="0.1" id="alcohol" />
                                <flux:error name="alcohol" />
                            </flux:field>
                        </div>
                    </x-agro.form-section>

                    {{-- Variedades de uva --}}
                    <x-agro.form-section title="Variedades de uva">
                        <div class="space-y-3">
                            @foreach($this->grapes as $i => $grape)
                            <div class="flex gap-3 items-start" wire:key="grape-edit-{{ $i }}">
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

                    {{-- Certificaciones --}}
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

                </div>{{-- /tab general --}}

                {{-- ══════════════════════════════════════════════════ --}}
                {{-- TAB: STOCK E IMPUESTOS                             --}}
                {{-- ══════════════════════════════════════════════════ --}}
                <div x-show="tab === 'stock'" x-cloak class="space-y-8">

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
                                <flux:input wire:model="quantity" type="number" step="0.001" min="0" id="quantity" required />
                                <flux:description>Comprometido: {{ number_format((float)$lot->reserved_quantity + (float)$lot->sold_quantity, 3) }} {{ $lot->unit }}</flux:description>
                                <flux:error name="quantity" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="available_quantity">Disponible para venta *</flux:label>
                                <flux:input wire:model="available_quantity" type="number" step="0.001" min="0" id="available_quantity" required />
                                <flux:error name="available_quantity" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="price_per_unit">Precio de venta / ud (€)</flux:label>
                                <flux:input wire:model="price_per_unit" type="number" step="0.001" min="0" id="price_per_unit" />
                                <flux:error name="price_per_unit" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="cost_price">Coste / ud (€)</flux:label>
                                <flux:input wire:model="cost_price" type="number" step="0.001" min="0" id="cost_price" />
                                <flux:description>Solo visible internamente.</flux:description>
                                <flux:error name="cost_price" />
                            </flux:field>
                        </div>
                    </x-agro.form-section>

                    <x-agro.form-section title="Formato y Comercial">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <flux:field>
                                <flux:label for="ean">EAN / Código de barras</flux:label>
                                <flux:input wire:model="ean" type="text" id="ean" maxlength="14" />
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
                                <flux:input wire:model="units_per_case" type="number" id="units_per_case" min="1" max="255" />
                                <flux:error name="units_per_case" />
                            </flux:field>
                        </div>
                    </x-agro.form-section>

                    <x-agro.form-section title="Impuestos">
                        @if($this->userTaxes->isEmpty())
                            <p class="text-sm text-zinc-500">No tienes impuestos configurados. Ve a Configuración para añadirlos.</p>
                        @else
                            <div class="space-y-2">
                                @foreach($this->userTaxes as $tax)
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <flux:checkbox wire:model="selectedTaxIds" value="{{ $tax->id }}" id="tax-edit-{{ $tax->id }}" />
                                        <span class="text-sm text-zinc-700">
                                            {{ $tax->name }}
                                            <span class="text-zinc-400">— {{ number_format($tax->rate, 2) }}%</span>
                                        </span>
                                        @if($tax->user_is_default)
                                            <span class="text-xs text-zinc-400">(por defecto)</span>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        @endif
                        <flux:error name="selectedTaxIds" />
                    </x-agro.form-section>

                </div>{{-- /tab stock --}}

                {{-- ══════════════════════════════════════════════════ --}}
                {{-- TAB: DESCRIPCIÓN                                   --}}
                {{-- ══════════════════════════════════════════════════ --}}
                <div x-show="tab === 'descripcion'" x-cloak class="space-y-8">

                    <x-agro.form-section title="Descripción del producto">
                        <div class="space-y-6">
                            <flux:field>
                                <flux:label for="description">Descripción</flux:label>
                                <flux:textarea wire:model="description" id="description" rows="4" placeholder="Descripción general del vino..." />
                                <flux:error name="description" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="tasting_notes">Notas de cata</flux:label>
                                <flux:textarea wire:model="tasting_notes" id="tasting_notes" rows="3" placeholder="Aromas, sabores, estructura..." />
                                <flux:error name="tasting_notes" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="pairing">Maridaje</flux:label>
                                <flux:textarea wire:model="pairing" id="pairing" rows="3" placeholder="Ej: Carnes rojas, quesos curados..." />
                                <flux:error name="pairing" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="consumption_recommendation">Recomendación de consumo</flux:label>
                                <flux:textarea wire:model="consumption_recommendation" id="consumption_recommendation" rows="2" placeholder="Ej: Consumir antes de 2028, decantar 30 minutos..." />
                                <flux:error name="consumption_recommendation" />
                            </flux:field>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <flux:field>
                                    <flux:label for="recommended_temperature_min">Temperatura mínima (°C)</flux:label>
                                    <flux:input wire:model="recommended_temperature_min" type="number" step="0.5" id="recommended_temperature_min" placeholder="16" />
                                    <flux:error name="recommended_temperature_min" />
                                </flux:field>

                                <flux:field>
                                    <flux:label for="recommended_temperature_max">Temperatura máxima (°C)</flux:label>
                                    <flux:input wire:model="recommended_temperature_max" type="number" step="0.5" id="recommended_temperature_max" placeholder="18" />
                                    <flux:error name="recommended_temperature_max" />
                                </flux:field>
                            </div>

                            <flux:field>
                                <flux:label for="tags">Etiquetas / Tags</flux:label>
                                <flux:input wire:model="tags" type="text" id="tags" placeholder="Ej: organico, reserva, DO Rioja" />
                                <flux:description>Separa con comas. Útil para búsquedas y filtros.</flux:description>
                                <flux:error name="tags" />
                            </flux:field>
                        </div>
                    </x-agro.form-section>

                </div>{{-- /tab descripcion --}}

                {{-- ══════════════════════════════════════════════════ --}}
                {{-- TAB: TÉCNICO                                       --}}
                {{-- ══════════════════════════════════════════════════ --}}
                <div x-show="tab === 'tecnico'" x-cloak class="space-y-8">

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

                    <x-agro.form-section title="Elaboración">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <flux:field>
                                <flux:label for="winemaker">Enólogo/a</flux:label>
                                <flux:input wire:model="winemaker" type="text" id="winemaker" />
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
                                <flux:input wire:model="oak_months" type="number" id="oak_months" min="0" />
                                <flux:error name="oak_months" />
                            </flux:field>
                        </div>
                    </x-agro.form-section>

                </div>{{-- /tab tecnico --}}

                {{-- ══════════════════════════════════════════════════ --}}
                {{-- TAB: MARKETING                                     --}}
                {{-- ══════════════════════════════════════════════════ --}}
                <div x-show="tab === 'marketing'" x-cloak class="space-y-8">

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

                </div>{{-- /tab marketing --}}

                {{-- ── Notas (siempre visible) ─────────────────────────── --}}
                <div class="pt-4 mt-4 border-t border-zinc-100">
                    <x-agro.form-section title="Notas internas">
                        <flux:field>
                            <flux:label for="notes">Notas</flux:label>
                            <flux:textarea wire:model="notes" id="notes" rows="3" />
                            <flux:error name="notes" />
                        </flux:field>
                    </x-agro.form-section>
                </div>

                <x-agro.form-actions :cancel-url="route('winery.product-lots.index')" submit-label="Guardar Cambios" />

            </form>
        </div>
    </x-agro.form-card>
</div>
