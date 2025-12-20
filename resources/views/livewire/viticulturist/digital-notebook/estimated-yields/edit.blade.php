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

<x-form-card
    title="Editar Rendimiento Estimado"
    description="Modifica la estimación de rendimiento"
    :icon="$icon"
    icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    :back-url="route('viticulturist.digital-notebook.estimated-yields.index')"
>
    <form wire:submit="update" class="space-y-8">
        
        {{-- Filtros para seleccionar plantación --}}
        <x-form-section title="Seleccionar Plantación" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Campaña --}}
                <div>
                    <x-label for="campaign_id" required>Campaña</x-label>
                    <x-select 
                        wire:model.live="campaign_id" 
                        id="campaign_id"
                        :error="$errors->first('campaign_id')"
                        required
                    >
                        <option value="">Selecciona una campaña</option>
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign->id }}">Campaña {{ $campaign->year }}</option>
                        @endforeach
                    </x-select>
                </div>

                {{-- Parcela (opcional) --}}
                @if($campaign_id)
                    <div>
                        <x-label for="plot_id">Parcela (Filtro opcional)</x-label>
                        <x-select 
                            wire:model.live="plot_id" 
                            id="plot_id"
                            :error="$errors->first('plot_id')"
                        >
                            <option value="">Todas las parcelas</option>
                            @foreach($plots as $plot)
                                <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                            @endforeach
                        </x-select>
                    </div>
                @endif
            </div>

            {{-- Plantación --}}
            <div class="mt-6">
                <x-label for="plot_planting_id" required>Plantación</x-label>
                <x-select 
                    wire:model.live="plot_planting_id" 
                    id="plot_planting_id"
                    :error="$errors->first('plot_planting_id')"
                    required
                >
                    <option value="">Selecciona una plantación</option>
                    @foreach($plantings as $planting)
                        <option value="{{ $planting->id }}">
                            {{ $planting->plot->name ?? 'Sin parcela' }} - 
                            @if($planting->name)
                                {{ $planting->name }} - 
                            @endif
                            {{ $planting->grapeVariety->name ?? 'Sin variedad' }}
                            @if($planting->area_planted)
                                ({{ number_format($planting->area_planted, 3) }} ha)
                            @endif
                        </option>
                    @endforeach
                </x-select>
                @if($plantings->isEmpty())
                    <p class="mt-2 text-sm text-amber-600">
                        No hay plantaciones disponibles. Primero debes crear plantaciones en tus parcelas.
                    </p>
                @endif
            </div>
        </x-form-section>

        {{-- Rendimiento Estimado --}}
        <x-form-section title="Rendimiento Estimado" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Rendimiento por hectárea --}}
                <div>
                    <x-label for="estimated_yield_per_hectare" required>Rendimiento por Hectárea (kg/ha)</x-label>
                    <x-input 
                        wire:model.live="estimated_yield_per_hectare" 
                        type="number" 
                        step="0.001"
                        min="0.01"
                        id="estimated_yield_per_hectare"
                        placeholder="0.00"
                        :error="$errors->first('estimated_yield_per_hectare')"
                        required
                    />
                </div>

                {{-- Rendimiento total (calculado) --}}
                @if($estimated_total_yield)
                    <div>
                        <x-label for="estimated_total_yield">Rendimiento Total Estimado (kg)</x-label>
                        <x-input 
                            wire:model="estimated_total_yield" 
                            type="number" 
                            step="0.001"
                            id="estimated_total_yield"
                            readonly
                            class="bg-gray-100"
                        />
                        <p class="mt-1 text-xs text-gray-500">Calculado automáticamente según el área plantada</p>
                    </div>
                @endif

                {{-- Fecha de estimación --}}
                <div>
                    <x-label for="estimation_date" required>Fecha de Estimación</x-label>
                    <x-input 
                        wire:model="estimation_date" 
                        type="date" 
                        id="estimation_date"
                        :error="$errors->first('estimation_date')"
                        required
                    />
                </div>

                {{-- Método de estimación --}}
                <div>
                    <x-label for="estimation_method" required>Método de Estimación</x-label>
                    <x-select 
                        wire:model="estimation_method" 
                        id="estimation_method"
                        :error="$errors->first('estimation_method')"
                        required
                    >
                        <option value="visual">Visual</option>
                        <option value="sampling">Muestreo</option>
                        <option value="historical">Histórico</option>
                        <option value="satellite">Satelital</option>
                        <option value="other">Otro</option>
                    </x-select>
                </div>

                {{-- Estado --}}
                <div>
                    <x-label for="status" required>Estado</x-label>
                    <x-select 
                        wire:model="status" 
                        id="status"
                        :error="$errors->first('status')"
                        required
                    >
                        <option value="draft">Borrador</option>
                        <option value="confirmed">Confirmada</option>
                        <option value="archived">Archivada</option>
                    </x-select>
                </div>
            </div>
        </x-form-section>

        {{-- Notas --}}
        <x-form-section title="Notas Adicionales" color="green">
            <div>
                <x-label for="notes">Notas</x-label>
                <x-textarea 
                    wire:model="notes" 
                    id="notes"
                    rows="4"
                    placeholder="Observaciones sobre la estimación, condiciones del viñedo, etc..."
                    :error="$errors->first('notes')"
                />
            </div>
        </x-form-section>

        {{-- Botones de acción --}}
        <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
            <a 
                href="{{ route('viticulturist.digital-notebook.estimated-yields.index') }}"
                class="px-6 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition font-semibold"
            >
                Cancelar
            </a>
            <button 
                type="submit"
                class="px-6 py-2 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition font-semibold"
            >
                Actualizar Estimación
            </button>
        </div>
    </form>
</x-form-card>

