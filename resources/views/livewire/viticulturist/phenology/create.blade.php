<div>
<x-agro.form-card
    title="Nueva Observación Fenológica"
    description="Registra un estadio fenológico para una plantación. Si ya existe un registro para ese estadio/plantación/campaña, se actualizará."
    :back-url="$plantingLocked ? route('viticulturist.phenology.index', ['filter_planting_id' => $plot_planting_id]) : route('plots.plantings.index')"
>
    <form wire:submit.prevent="save" class="space-y-8">
        <x-agro.form-section title="Datos del Registro">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label required>Plantación</flux:label>
                    @if($plantingLocked)
                        @php $locked = $plantings->firstWhere('id', $plot_planting_id); @endphp
                        <div class="flex items-center gap-2 px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-lg text-sm text-zinc-700">
                            <flux:icon icon="scissors" class="size-4 text-zinc-400 shrink-0" />
                            <span>{{ $locked?->plot->name }} — {{ $locked?->grapeVariety->name ?? $locked?->name ?? 'Sin nombre' }}</span>
                        </div>
                        <input type="hidden" wire:model="plot_planting_id" />
                    @else
                        <flux:select wire:model.live="plot_planting_id">
                            <option value="">Selecciona plantación</option>
                            @foreach($plantings as $planting)
                                <option value="{{ $planting->id }}">
                                    {{ $planting->plot->name }} — {{ $planting->grapeVariety->name ?? $planting->name ?? 'Sin nombre' }}
                                </option>
                            @endforeach
                        </flux:select>
                    @endif
                    <flux:error name="plot_planting_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>Campaña</flux:label>
                    <flux:select wire:model="campaign_id">
                        <option value="">Selecciona campaña</option>
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign->id }}">Campaña {{ $campaign->year }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="campaign_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>Estadio fenológico</flux:label>
                    <flux:select wire:model.live="event">
                        <option value="">Selecciona estadio</option>
                        @foreach($events as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="event" />
                </flux:field>

                <flux:field>
                    <flux:label required>Fecha de observación</flux:label>
                    <flux:input wire:model="obs_date" type="date" />
                    <flux:error name="obs_date" />
                </flux:field>

                <flux:field>
                    <flux:label required>Fuente del dato</flux:label>
                    <flux:select wire:model="source">
                        @foreach($sources as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="source" />
                </flux:field>

                <flux:field>
                    <flux:label required>Nivel de confianza (%)</flux:label>
                    <flux:input wire:model="confidence" type="number" min="0" max="100" />
                    <flux:description>0 = incierto, 100 = certeza absoluta</flux:description>
                    <flux:error name="confidence" />
                </flux:field>

                <flux:field>
                    <flux:label>Grados-día acumulados</flux:label>
                    <flux:input wire:model="degree_days_accumulated" type="number" step="0.1" min="0" placeholder="0.0" />
                    <flux:description>Desde desborre hasta este estadio</flux:description>
                    <flux:error name="degree_days_accumulated" />
                </flux:field>

                <flux:field>
                    <flux:label>Código BBCH</flux:label>
                    <flux:input wire:model="bbch_code" type="number" min="0" max="99" />
                    <flux:description>Se autocompletará al seleccionar estadio</flux:description>
                    <flux:error name="bbch_code" />
                </flux:field>
            </div>

            <div class="mt-6">
                <flux:field>
                    <flux:label>Notas</flux:label>
                    <flux:textarea wire:model="notes" rows="3" placeholder="Observaciones adicionales..." />
                    <flux:error name="notes" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions :cancel-url="$plantingLocked ? route('viticulturist.phenology.index', ['filter_planting_id' => $plot_planting_id]) : route('plots.plantings.index')" submit-label="Guardar Observación" />
    </form>
</x-agro.form-card>
</div>
