<div>
    <x-agro.form-card title="{{ __('Nuevo Producto') }}" :description="__('Registra un producto para su venta y facturación')"
        :back-url="roleRoute('product-lots.index')">

        <div x-data="{ tab: 'general' }">
            <form wire:submit.prevent="save" class="space-y-0">

                {{-- ── Tab nav ────────────────────────────────────────── --}}
                <div class="flex border-b border-zinc-200 mb-8 -mx-6 px-6 overflow-x-auto gap-0">
                    @foreach([
                        ['key' => 'general',     'label' => 'General'],
                        ['key' => 'stock',       'label' => 'Stock'],
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

                    <x-agro.form-section title="{{ __('Información básica') }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <flux:field class="md:col-span-2">
                                <flux:label for="wine_id">{{ __('Vino de origen') }}</flux:label>
                                <flux:select wire:model="wine_id" id="wine_id">
                                    <flux:select.option value="">{{ __('Sin vincular (externo / legacy)') }}</flux:select.option>
                                    @foreach($wines as $wine)
                                        <flux:select.option value="{{ $wine->id }}">
                                            {{ $wine->name }}{{ $wine->vintage ? ' · ' . $wine->vintage : '' }}
                                            · {{ $wine->typeLabel }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>
                                <flux:description>{{ __('Opcional. Vincula este producto con el vino elaborado en bodega para trazabilidad.') }}</flux:description>
                                <flux:error name="wine_id" />
                            </flux:field>

                            <flux:field class="md:col-span-2">
                                <flux:label for="name">{{ __('Nombre del producto *') }}</flux:label>
                                <flux:input wire:model="name" type="text" id="name" :placeholder="__('Ej: Rioja Reserva 2022')" required />
                                <flux:error name="name" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="sku">{{ __('SKU / Referencia') }}</flux:label>
                                <flux:input wire:model="sku" type="text" id="sku" :placeholder="__('Ej: RRV-2022')" />
                                <flux:error name="sku" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="vintage">{{ __('Añada (año)') }}</flux:label>
                                <flux:input wire:model="vintage" type="number" id="vintage" placeholder="{{ now()->year }}" min="1900" max="{{ now()->year + 1 }}" />
                                <flux:error name="vintage" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="wine_type">{{ __('Tipo de vino *') }}</flux:label>
                                <flux:select wire:model="wine_type" id="wine_type" required>
                                    <option value="tinto">{{ __('Tinto') }}</option>
                                    <option value="blanco">{{ __('Blanco') }}</option>
                                    <option value="rosado">{{ __('Rosado') }}</option>
                                    <option value="espumoso">{{ __('Espumoso') }}</option>
                                    <option value="otro">{{ __('Otro') }}</option>
                                </flux:select>
                                <flux:error name="wine_type" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="aging_type">{{ __('Crianza') }}</flux:label>
                                <flux:select wire:model="aging_type" id="aging_type">
                                    <option value="">{{ __('Sin especificar') }}</option>
                                    <option value="joven">{{ __('Joven') }}</option>
                                    <option value="crianza">{{ __('Crianza') }}</option>
                                    <option value="reserva">{{ __('Reserva') }}</option>
                                    <option value="gran_reserva">{{ __('Gran Reserva') }}</option>
                                    <option value="autor">{{ __('Vino de Autor') }}</option>
                                </flux:select>
                                <flux:error name="aging_type" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="agingtime">{{ __('Meses de crianza total') }}</flux:label>
                                <flux:input wire:model="agingtime" type="number" id="agingtime" min="0" />
                                <flux:error name="agingtime" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="alcohol">{{ __('Alcohol (% vol)') }}</flux:label>
                                <flux:input wire:model="alcohol" type="number" step="0.1" id="alcohol" placeholder="13.5" />
                                <flux:error name="alcohol" />
                            </flux:field>
                        </div>
                    </x-agro.form-section>

                    {{-- Variedades de uva --}}
                    <x-agro.form-section title="{{ __('Variedades de uva') }}">
                        <div class="space-y-3">
                            @foreach($this->grapes as $i => $grape)
                            <div class="flex gap-3 items-start" wire:key="grape-create-{{ $i }}">
                                <div class="flex-1">
                                    <flux:select wire:model="grapes.{{ $i }}.grape_variety_id">
                                        <option value="">{{ __('Selecciona variedad...') }}</option>
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
                                    title="{{ __('Eliminar') }}">
                                    <flux:icon icon="x-mark" class="size-4" />
                                </button>
                            </div>
                            @endforeach

                            <div class="flex items-center justify-between pt-1">
                                <flux:button type="button" wire:click="addGrape" variant="ghost" size="sm">{{ __('+ Añadir variedad') }}</flux:button>
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
                    <x-agro.form-section title="{{ __('Certificaciones') }}">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <flux:checkbox wire:model="sulfites" :label="__('Contiene sulfitos')" />
                            <flux:checkbox wire:model="ecological" :label="__('Ecológico')" />
                            <flux:checkbox wire:model="is_vegan" :label="__('Apto para veganos')" />
                            <flux:checkbox wire:model="is_biodynamic" :label="__('Biodinámico')" />
                        </div>
                    </x-agro.form-section>

                </div>{{-- /tab general --}}

                {{-- ══════════════════════════════════════════════════ --}}
                {{-- TAB: STOCK                                         --}}
                {{-- ══════════════════════════════════════════════════ --}}
                <div x-show="tab === 'stock'" x-cloak class="space-y-8">

                    <x-agro.form-section title="{{ __('Stock y Precio') }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <flux:field>
                                <flux:label for="unit">{{ __('Unidad *') }}</flux:label>
                                <flux:select wire:model="unit" id="unit" required>
                                    <option value="litros">{{ __('Litros') }}</option>
                                    <option value="botellas">{{ __('Botellas (75 cl)') }}</option>
                                    <option value="cajas">{{ __('Cajas') }}</option>
                                </flux:select>
                                <flux:error name="unit" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="quantity">{{ __('Cantidad total *') }}</flux:label>
                                <flux:input wire:model.live="quantity" type="number" step="0.001" min="0" id="quantity" placeholder="0" required />
                                <flux:error name="quantity" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="available_quantity">{{ __('Disponible para venta *') }}</flux:label>
                                <flux:input wire:model="available_quantity" type="number" step="0.001" min="0" id="available_quantity" placeholder="0" required />
                                <flux:description>{{ __('Por defecto igual a la cantidad total.') }}</flux:description>
                                <flux:error name="available_quantity" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="price_per_unit">{{ __('Precio de venta / ud (€)') }}</flux:label>
                                <flux:input wire:model="price_per_unit" type="number" step="0.001" min="0" id="price_per_unit" placeholder="0.000" />
                                <flux:description>{{ __('Precio por defecto al facturar.') }}</flux:description>
                                <flux:error name="price_per_unit" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="cost_price">{{ __('Coste / ud (€)') }}</flux:label>
                                <flux:input wire:model="cost_price" type="number" step="0.001" min="0" id="cost_price" placeholder="0.000" />
                                <flux:description>{{ __('Solo visible internamente.') }}</flux:description>
                                <flux:error name="cost_price" />
                            </flux:field>
                        </div>
                    </x-agro.form-section>

                    <x-agro.form-section title="{{ __('Formato y Comercial') }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <flux:field>
                                <flux:label for="ean">{{ __('EAN / Código de barras') }}</flux:label>
                                <flux:input wire:model="ean" type="text" id="ean" maxlength="14" />
                                <flux:error name="ean" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="bottle_format">{{ __('Formato de botella') }}</flux:label>
                                <flux:select wire:model="bottle_format" id="bottle_format">
                                    <option value="">{{ __('Seleccionar...') }}</option>
                                    <option value="18.7cl">{{ __('18.7 cl (Piccolo)') }}</option>
                                    <option value="37.5cl">{{ __('37.5 cl (½ botella)') }}</option>
                                    <option value="75cl">{{ __('75 cl (Estándar)') }}</option>
                                    <option value="100cl">{{ __('100 cl (1 L)') }}</option>
                                    <option value="150cl">{{ __('150 cl (Magnum)') }}</option>
                                    <option value="225cl">{{ __('225 cl (Jeroboam)') }}</option>
                                    <option value="300cl">{{ __('300 cl (Doble Magnum)') }}</option>
                                    <option value="600cl">{{ __('600 cl (Methuselah)') }}</option>
                                </flux:select>
                                <flux:error name="bottle_format" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="units_per_case">{{ __('Botellas por caja') }}</flux:label>
                                <flux:input wire:model="units_per_case" type="number" id="units_per_case" min="1" max="255" />
                                <flux:error name="units_per_case" />
                            </flux:field>
                        </div>
                    </x-agro.form-section>

                </div>{{-- /tab stock --}}

                {{-- ══════════════════════════════════════════════════ --}}
                {{-- TAB: DESCRIPCIÓN                                   --}}
                {{-- ══════════════════════════════════════════════════ --}}
                <div x-show="tab === 'descripcion'" x-cloak class="space-y-8">

                    <x-agro.form-section title="{{ __('Descripción del producto') }}">
                        <div class="space-y-6">
                            <flux:field>
                                <flux:label for="description">{{ __('Descripción') }}</flux:label>
                                <flux:textarea wire:model="description" id="description" rows="4" :placeholder="__('Descripción general del vino...')" />
                                <flux:error name="description" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="tasting_notes">{{ __('Notas de cata') }}</flux:label>
                                <flux:textarea wire:model="tasting_notes" id="tasting_notes" rows="3" :placeholder="__('Aromas, sabores, estructura...')" />
                                <flux:error name="tasting_notes" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="pairing">{{ __('Maridaje') }}</flux:label>
                                <flux:textarea wire:model="pairing" id="pairing" rows="3" :placeholder="__('Ej: Carnes rojas, quesos curados...')" />
                                <flux:error name="pairing" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="consumption_recommendation">{{ __('Recomendación de consumo') }}</flux:label>
                                <flux:textarea wire:model="consumption_recommendation" id="consumption_recommendation" rows="2" :placeholder="__('Ej: Consumir antes de 2028, decantar 30 minutos...')" />
                                <flux:error name="consumption_recommendation" />
                            </flux:field>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <flux:field>
                                    <flux:label for="recommended_temperature_min">{{ __('Temperatura mínima (°C)') }}</flux:label>
                                    <flux:input wire:model="recommended_temperature_min" type="number" step="0.5" id="recommended_temperature_min" placeholder="16" />
                                    <flux:error name="recommended_temperature_min" />
                                </flux:field>

                                <flux:field>
                                    <flux:label for="recommended_temperature_max">{{ __('Temperatura máxima (°C)') }}</flux:label>
                                    <flux:input wire:model="recommended_temperature_max" type="number" step="0.5" id="recommended_temperature_max" placeholder="18" />
                                    <flux:error name="recommended_temperature_max" />
                                </flux:field>
                            </div>

                            <flux:field>
                                <flux:label for="tags">{{ __('Etiquetas / Tags') }}</flux:label>
                                <flux:input wire:model="tags" type="text" id="tags" :placeholder="__('Ej: organico, reserva, DO Rioja')" />
                                <flux:description>{{ __('Separa con comas. Útil para búsquedas y filtros.') }}</flux:description>
                                <flux:error name="tags" />
                            </flux:field>
                        </div>
                    </x-agro.form-section>

                </div>{{-- /tab descripcion --}}

                {{-- ══════════════════════════════════════════════════ --}}
                {{-- TAB: TÉCNICO                                       --}}
                {{-- ══════════════════════════════════════════════════ --}}
                <div x-show="tab === 'tecnico'" x-cloak class="space-y-8">

                    <x-agro.form-section title="{{ __('Analíticos') }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <flux:field>
                                <flux:label for="residual_sugar">{{ __('Azúcar residual (g/L)') }}</flux:label>
                                <flux:input wire:model="residual_sugar" type="number" step="0.01" min="0" id="residual_sugar" placeholder="2.40" />
                                <flux:error name="residual_sugar" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="total_acidity">{{ __('Acidez total (g/L)') }}</flux:label>
                                <flux:input wire:model="total_acidity" type="number" step="0.01" min="0" id="total_acidity" placeholder="5.80" />
                                <flux:error name="total_acidity" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="volatile_acidity">{{ __('Acidez volátil (g/L)') }}</flux:label>
                                <flux:input wire:model="volatile_acidity" type="number" step="0.01" min="0" id="volatile_acidity" placeholder="0.42" />
                                <flux:error name="volatile_acidity" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="ph">{{ __('pH') }}</flux:label>
                                <flux:input wire:model="ph" type="number" step="0.01" min="0" max="14" id="ph" placeholder="3.52" />
                                <flux:error name="ph" />
                            </flux:field>
                        </div>
                    </x-agro.form-section>

                    <x-agro.form-section title="{{ __('Viñedo') }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <flux:field>
                                <flux:label for="vine_age">{{ __('Edad media viñas (años)') }}</flux:label>
                                <flux:input wire:model="vine_age" type="number" id="vine_age" min="0" placeholder="35" />
                                <flux:error name="vine_age" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="altitude">{{ __('Altitud (m.s.n.m.)') }}</flux:label>
                                <flux:input wire:model="altitude" type="number" id="altitude" min="0" placeholder="620" />
                                <flux:error name="altitude" />
                            </flux:field>

                            <flux:field class="md:col-span-2">
                                <flux:label for="soil_type">{{ __('Tipo de suelo') }}</flux:label>
                                <flux:input wire:model="soil_type" type="text" id="soil_type" :placeholder="__('Ej: Arcillo-calcáreo')" />
                                <flux:error name="soil_type" />
                            </flux:field>
                        </div>
                    </x-agro.form-section>

                    <x-agro.form-section title="{{ __('Elaboración') }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <flux:field>
                                <flux:label for="winemaker">{{ __('Enólogo/a') }}</flux:label>
                                <flux:input wire:model="winemaker" type="text" id="winemaker" />
                                <flux:error name="winemaker" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="harvest_method">{{ __('Método de vendimia') }}</flux:label>
                                <flux:select wire:model="harvest_method" id="harvest_method">
                                    <option value="">{{ __('Sin especificar') }}</option>
                                    <option value="manual">{{ __('Manual') }}</option>
                                    <option value="mechanic">{{ __('Mecánica') }}</option>
                                    <option value="mixed">{{ __('Mixta') }}</option>
                                </flux:select>
                                <flux:error name="harvest_method" />
                            </flux:field>

                            <flux:field class="md:col-span-2">
                                <flux:label for="fermentation_vessel">{{ __('Recipiente de fermentación') }}</flux:label>
                                <flux:input wire:model="fermentation_vessel" type="text" id="fermentation_vessel" :placeholder="__('Ej: Depósito inox + barrica francesa')" />
                                <flux:error name="fermentation_vessel" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="oak_type">{{ __('Tipo de barrica') }}</flux:label>
                                <flux:input wire:model="oak_type" type="text" id="oak_type" :placeholder="__('Ej: Francesa 225L')" />
                                <flux:error name="oak_type" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="oak_months">{{ __('Meses en barrica') }}</flux:label>
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

                    <x-agro.form-section title="{{ __('Producción y Premios') }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <flux:field>
                                <flux:label for="production_quantity">{{ __('Botellas producidas') }}</flux:label>
                                <flux:input wire:model="production_quantity" type="number" id="production_quantity" min="0" placeholder="15000" />
                                <flux:error name="production_quantity" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="bottling_date">{{ __('Fecha de embotellado') }}</flux:label>
                                <flux:input wire:model="bottling_date" type="date" id="bottling_date" />
                                <flux:error name="bottling_date" />
                            </flux:field>

                            <flux:field>
                                <flux:label for="release_date">{{ __('Fecha de lanzamiento') }}</flux:label>
                                <flux:input wire:model="release_date" type="date" id="release_date" />
                                <flux:error name="release_date" />
                            </flux:field>

                            <flux:field class="md:col-span-2">
                                <flux:label for="awards_notes">{{ __('Premios y puntuaciones') }}</flux:label>
                                <flux:textarea wire:model="awards_notes" id="awards_notes" rows="3"
                                    placeholder="{{ __('Ej: 95 pts Parker 2023, Medalla de oro Mundus Vini 2022...') }}" />
                                <flux:error name="awards_notes" />
                            </flux:field>
                        </div>
                    </x-agro.form-section>

                </div>{{-- /tab marketing --}}

                {{-- ── Notas (siempre visible) ─────────────────────────── --}}
                <div class="pt-4 mt-4 border-t border-zinc-100">
                    <x-agro.form-section title="{{ __('Notas internas') }}">
                        <flux:field>
                            <flux:label for="notes">{{ __('Notas') }}</flux:label>
                            <flux:textarea wire:model="notes" id="notes" rows="3" :placeholder="__('Observaciones sobre este producto...')" />
                            <flux:error name="notes" />
                        </flux:field>
                    </x-agro.form-section>
                </div>

                <x-agro.form-actions :cancel-url="roleRoute('product-lots.index')" submit-:label="__('Crear Producto')" />

            </form>
        </div>
    </x-agro.form-card>
</div>
