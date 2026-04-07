<x-agro.form-card
    title="Crear Rendimiento Estimado"
    description="Registra una estimaci�n de rendimiento para una plantaci�n y campa�a"
    icon="chart-bar"
    icon-color="from-agro-500 to-agro-700"
    :back-url="roleRoute('viticulturist.digital-notebook.estimated-yields.index')"
>
    <form wire:submit="save" class="space-y-8" data-cy="create-estimated-yield-form">
        
        {{-- Filtros para seleccionar plantaci�n --}}
        <x-agro.form-section title="Seleccionar Plantaci�n" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label required>Campa�a</flux:label>
                    <flux:select wire:model.live="campaign_id" id="campaign_id" data-cy="campaign-select" required>
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
                    <flux:select wire:model.live="plot_planting_id" id="plot_planting_id" data-cy="planting-select" required>
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
                    <flux:select wire:model="estimation_round" id="estimation_round" data-cy="estimation-round-select" required>
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
                    <flux:select wire:model="estimation_method" id="estimation_method" data-cy="estimation-method-select" required>
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
                    <flux:input wire:model="potential_alcohol" type="number" step="0.1" min="0" max="25" id="potential_alcohol" data-cy="potential-alcohol-input" placeholder="0.0" />
                    <flux:error name="potential_alcohol" />
                </flux:field>

                <flux:field>
                    <flux:label>Estado sanitario</flux:label>
                    <flux:select wire:model="health_status" id="health_status">
                        <option value="">Sin evaluar</option>
                        @foreach(\App\Models\EstimatedYield::HEALTH_STATUSES as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="health_status" />
                </flux:field>
            </div>

            <div class="mt-4 flex items-center gap-3">
                <flux:checkbox wire:model="other_wineries" id="other_wineries" />
                <flux:label for="other_wineries" class="cursor-pointer">
                    Entrego uva a otras bodegas además de la principal
                </flux:label>
            </div>

            @if($auto_calculated_yield)
                <flux:callout variant="info" icon="calculator" class="mt-4">
                    <flux:callout.heading>Rendimiento calculado desde muestreo</flux:callout.heading>
                    <flux:callout.text>
                        <span class="text-2xl font-bold">{{ number_format($auto_calculated_yield, 0, ',', '.') }} kg</span>
                        · racimos/planta × peso × nº cepas × % salud
                    </flux:callout.text>
                </flux:callout>
            @endif
        </x-agro.form-section>

        {{-- Rendimiento estimado manual --}}
        <x-agro.form-section title="Rendimiento Estimado" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label required>Rendimiento por Hectárea (kg/ha)</flux:label>
                    <flux:input wire:model.live="estimated_yield_per_hectare" type="number" step="0.001" min="0.01" id="estimated_yield_per_hectare" data-cy="yield-per-hectare-input" placeholder="0.00" required />
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

        <x-agro.form-actions :back-url="roleRoute('viticulturist.digital-notebook.estimated-yields.index')" submit-label="Guardar Estimaci�n" />
    </form>
</x-agro.form-card>


