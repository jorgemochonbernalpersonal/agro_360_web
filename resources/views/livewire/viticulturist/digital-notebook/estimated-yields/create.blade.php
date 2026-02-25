@php
    $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>';
@endphp

<x-agro.form-card
    title="Crear Rendimiento Estimado"
    description="Registra una estimaci�n de rendimiento para una plantaci�n y campa�a"
    :icon="$icon"
    icon-color="from-agro-500 to-agro-700"
    :back-url="route('viticulturist.digital-notebook.estimated-yields.index')"
>
    <form wire:submit="save" class="space-y-8">
        
        {{-- Filtros para seleccionar plantaci�n --}}
        <x-agro.form-section title="Seleccionar Plantaci�n" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label required>Campa�a</flux:label>
                    <flux:select wire:model.live="campaign_id" id="campaign_id" required>
                        <option value="">Selecciona una campa�a</option>
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign->id }}">Campa�a {{ $campaign->year }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="campaign_id" />
                </flux:field>

                @if($campaign_id)
                    <flux:field>
                        <flux:label>Parcela (Filtro opcional)</flux:label>
                        <flux:select wire:model.live="plot_id" id="plot_id">
                            <option value="">Todas las parcelas</option>
                            @foreach($plots as $plot)
                                <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="plot_id" />
                    </flux:field>
                @endif
            </div>

            <div class="mt-6">
                <flux:field>
                    <flux:label required>Plantaci�n</flux:label>
                    <flux:select wire:model.live="plot_planting_id" id="plot_planting_id" required>
                        <option value="">Selecciona una plantaci�n</option>
                        @foreach($plantings as $planting)
                            <option value="{{ $planting->id }}">
                                {{ $planting->plot->name ?? 'Sin parcela' }} -
                                @if($planting->name) {{ $planting->name }} - @endif
                                {{ $planting->grapeVariety->name ?? 'Sin variedad' }}
                                @if($planting->area_planted) ({{ number_format($planting->area_planted, 3) }} ha) @endif
                            </option>
                        @endforeach
                    </flux:select>
                    <flux:error name="plot_planting_id" />
                    @if($plantings->isEmpty())
                        <flux:description class="text-amber-600">No hay plantaciones disponibles. Primero debes crear plantaciones en tus parcelas.</flux:description>
                    @endif
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Ronda y Método de Estimación" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <flux:field>
                    <flux:label required>Ronda</flux:label>
                    <flux:select wire:model="estimation_round" id="estimation_round" required>
                        <option value="1">1 — Pre-envero</option>
                        <option value="2">2 — Envero</option>
                        <option value="3">3 — Pre-vendimia</option>
                        <option value="4">4 — Revisión final</option>
                    </flux:select>
                    <flux:description>Una estimación por ronda y plantación</flux:description>
                    <flux:error name="estimation_round" />
                </flux:field>

                <flux:field>
                    <flux:label required>Método</flux:label>
                    <flux:select wire:model="estimation_method" id="estimation_method" required>
                        <option value="visual">Visual</option>
                        <option value="sampling">Muestreo de campo</option>
                        <option value="historical">Histórico</option>
                        <option value="satellite">Satelital</option>
                        <option value="other">Otro</option>
                    </flux:select>
                    <flux:error name="estimation_method" />
                </flux:field>

                <flux:field>
                    <flux:label required>Fecha de Estimación</flux:label>
                    <flux:input wire:model="estimation_date" type="date" id="estimation_date" required />
                    <flux:error name="estimation_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Añada</flux:label>
                    <flux:input wire:model="vintage" type="number" min="1900" max="2100" id="vintage" placeholder="{{ date('Y') }}" />
                    <flux:error name="vintage" />
                </flux:field>

                <flux:field>
                    <flux:label required>Estado</flux:label>
                    <flux:select wire:model="status" id="status" required>
                        <option value="draft">Borrador</option>
                        <option value="confirmed">Confirmada</option>
                        <option value="archived">Archivada</option>
                    </flux:select>
                    <flux:error name="status" />
                </flux:field>
            </div>
        </x-agro.form-section>

        {{-- Muestreo de campo --}}
        <x-agro.form-section title="Muestreo de Campo" color="amber">
            <flux:callout variant="info" icon="information-circle">
                Rellena estos datos si has realizado un muestreo físico en el viñedo. El rendimiento se calculará automáticamente como: <strong>racimos/planta × peso/racimo × nº cepas × % salud</strong>.
            </flux:callout>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mt-4">
                <flux:field>
                    <flux:label>Yemas/cepa (poda)</flux:label>
                    <flux:input wire:model="thumbs_per_vine" type="number" min="0" max="100" id="thumbs_per_vine" placeholder="0" />
                    <flux:description>Yemas dejadas en poda</flux:description>
                    <flux:error name="thumbs_per_vine" />
                </flux:field>

                <flux:field>
                    <flux:label>Racimos/planta</flux:label>
                    <flux:input wire:model.live="bunches_per_plant" type="number" step="0.1" min="0" id="bunches_per_plant" placeholder="0.0" />
                    <flux:description>Media de racimos contados</flux:description>
                    <flux:error name="bunches_per_plant" />
                </flux:field>

                <flux:field>
                    <flux:label>Peso del racimo (g)</flux:label>
                    <flux:input wire:model.live="bunch_weight_grams" type="number" step="0.1" min="0" id="bunch_weight_grams" placeholder="0.0" />
                    <flux:description>Peso medio en gramos</flux:description>
                    <flux:error name="bunch_weight_grams" />
                </flux:field>

                <flux:field>
                    <flux:label>% Racimos sanos</flux:label>
                    <flux:input wire:model.live="health_percentage" type="number" step="0.1" min="0" max="100" id="health_percentage" placeholder="100" />
                    <flux:description>0–100%</flux:description>
                    <flux:error name="health_percentage" />
                </flux:field>

                <flux:field>
                    <flux:label>Plantas muestreadas</flux:label>
                    <flux:input wire:model="total_plants_sampled" type="number" min="1" id="total_plants_sampled" placeholder="0" />
                    <flux:error name="total_plants_sampled" />
                </flux:field>

                <flux:field>
                    <flux:label>% Superficie muestreada</flux:label>
                    <flux:input wire:model="sampling_area_pct" type="number" step="0.1" min="0" max="100" id="sampling_area_pct" placeholder="0.0" />
                    <flux:error name="sampling_area_pct" />
                </flux:field>

                <flux:field>
                    <flux:label>Alcohol probable (%)</flux:label>
                    <flux:input wire:model="potential_alcohol" type="number" step="0.1" min="0" max="25" id="potential_alcohol" placeholder="0.0" />
                    <flux:error name="potential_alcohol" />
                </flux:field>
            </div>

            @if($auto_calculated_yield)
                <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                    <div class="flex items-center gap-3">
                        <flux:icon icon="calculator" class="w-5 h-5 text-amber-600" />
                        <div>
                            <p class="text-sm font-medium text-amber-800">Rendimiento calculado desde muestreo</p>
                            <p class="text-2xl font-bold text-amber-700">{{ number_format($auto_calculated_yield, 0, ',', '.') }} kg</p>
                            <p class="text-xs text-amber-600">racimos/planta × peso × nº cepas × % salud</p>
                        </div>
                    </div>
                </div>
            @endif
        </x-agro.form-section>

        {{-- Rendimiento estimado manual --}}
        <x-agro.form-section title="Rendimiento Estimado" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label required>Rendimiento por Hectárea (kg/ha)</flux:label>
                    <flux:input wire:model.live="estimated_yield_per_hectare" type="number" step="0.001" min="0.01" id="estimated_yield_per_hectare" placeholder="0.00" required />
                    @if($auto_calculated_yield && $plot_planting_id)
                        @php
                            $planting = $plantings->find($plot_planting_id);
                            $suggestedKgHa = $planting && $planting->area_planted > 0
                                ? round($auto_calculated_yield / $planting->area_planted, 0)
                                : null;
                        @endphp
                        @if($suggestedKgHa)
                            <flux:description class="text-amber-600">
                                Muestreo sugiere: {{ number_format($suggestedKgHa, 0, ',', '.') }} kg/ha
                            </flux:description>
                        @endif
                    @endif
                    <flux:error name="estimated_yield_per_hectare" />
                </flux:field>

                @if($estimated_total_yield)
                    <flux:field>
                        <flux:label>Rendimiento Total Estimado (kg)</flux:label>
                        <flux:input wire:model="estimated_total_yield" type="number" step="0.001" id="estimated_total_yield" readonly class="bg-zinc-100" />
                        <flux:description>Calculado automáticamente según el área plantada</flux:description>
                    </flux:field>
                @endif
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Notas Adicionales" color="green">
            <flux:field>
                <flux:label>Notas</flux:label>
                <flux:textarea wire:model="notes" id="notes" rows="4" placeholder="Observaciones sobre la estimación, condiciones del viñedo, etc..." />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions :back-url="route('viticulturist.digital-notebook.estimated-yields.index')" submit-label="Guardar Estimaci�n" />
    </form>
</x-agro.form-card>


