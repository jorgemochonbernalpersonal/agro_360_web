@php
    $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>';
@endphp

@if($estimatedYield)
    <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div>
                <h4 class="text-sm font-semibold text-blue-900">Información de la Estimación</h4>
                <p class="text-xs text-blue-800 mt-1">
                    Parcela: <strong>{{ $estimatedYield->plotPlanting->plot->name ?? 'Sin parcela' }}</strong> | 
                    Variedad: <strong>{{ $estimatedYield->plotPlanting->grapeVariety->name ?? 'Sin variedad' }}</strong> | 
                    Campaña: <strong>{{ $estimatedYield->campaign->name ?? 'Sin campaña' }}</strong>
                </p>
                @if($estimatedYield->hasActualYield())
                    <p class="text-xs text-green-700 mt-1 font-semibold">
                        ✓ Rendimiento real disponible: {{ number_format($estimatedYield->actual_total_yield, 2) }} kg
                    </p>
                @endif
            </div>
        </div>
    </div>
@endif

<x-agro.form-card
    title="Editar Rendimiento Estimado"
    description="Modifica la estimación de rendimiento"
    :icon="$icon"
    icon-color="from-agro-500 to-agro-700"
    :back-url="route('viticulturist.digital-notebook.estimated-yields.index')"
>
    <form wire:submit="update" class="space-y-8">
        
        {{-- Filtros para seleccionar plantación --}}
        <x-agro.form-section title="Seleccionar Plantación" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label required>Campaña</flux:label>
                    <flux:select wire:model.live="campaign_id" id="campaign_id" required>
                        <option value="">Selecciona una campaña</option>
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign->id }}">Campaña {{ $campaign->year }}</option>
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
                    <flux:label required>Plantación</flux:label>
                    <flux:select wire:model.live="plot_planting_id" id="plot_planting_id" required>
                        <option value="">Selecciona una plantación</option>
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

        <x-agro.form-section title="Rendimiento Estimado" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label required>Rendimiento por Hectárea (kg/ha)</flux:label>
                    <flux:input wire:model.live="estimated_yield_per_hectare" type="number" step="0.001" min="0.01" id="estimated_yield_per_hectare" placeholder="0.00" required />
                    <flux:error name="estimated_yield_per_hectare" />
                </flux:field>

                @if($estimated_total_yield)
                    <flux:field>
                        <flux:label>Rendimiento Total Estimado (kg)</flux:label>
                        <flux:input wire:model="estimated_total_yield" type="number" step="0.001" id="estimated_total_yield" readonly class="bg-zinc-100" />
                        <flux:description>Calculado automáticamente según el área plantada</flux:description>
                    </flux:field>
                @endif

                <flux:field>
                    <flux:label required>Fecha de Estimación</flux:label>
                    <flux:input wire:model="estimation_date" type="date" id="estimation_date" required />
                    <flux:error name="estimation_date" />
                </flux:field>

                <flux:field>
                    <flux:label required>Método de Estimación</flux:label>
                    <flux:select wire:model="estimation_method" id="estimation_method" required>
                        <option value="visual">Visual</option>
                        <option value="sampling">Muestreo</option>
                        <option value="historical">Histórico</option>
                        <option value="satellite">Satelital</option>
                        <option value="other">Otro</option>
                    </flux:select>
                    <flux:error name="estimation_method" />
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

        <x-agro.form-section title="Notas Adicionales" color="green">
            <flux:field>
                <flux:label>Notas</flux:label>
                <flux:textarea wire:model="notes" id="notes" rows="4" placeholder="Observaciones sobre la estimación, condiciones del viñedo, etc..." />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions :back-url="route('viticulturist.digital-notebook.estimated-yields.index')" submit-label="Actualizar Estimación" />
    </form>
</x-agro.form-card>

