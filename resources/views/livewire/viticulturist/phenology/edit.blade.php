<div>
<x-agro.form-card
    :title="__('Editar Observación Fenológica')"
    :description="__('Modifica el estadio fenológico registrado.')"
    :back-url="roleRoute('viticulturist.phenology.index', ['filter_planting_id' => $plot_planting_id])"
>
    <form wire:submit.prevent="update" class="space-y-8">
        <x-agro.form-section :title="__('Datos del Registro')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label required>{{ __('Plantación') }}</flux:label>
                    @php $lockedPlanting = $plantings->firstWhere('id', $plot_planting_id); @endphp
                    <div class="flex items-center gap-2 px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-lg text-sm text-zinc-700">
                        <flux:icon icon="scissors" class="size-4 text-zinc-400 shrink-0" />
                        <span>{{ $lockedPlanting?->plot->name }} — {{ $lockedPlanting?->grapeVariety->name ?? $lockedPlanting?->name ?? __('Sin nombre') }}</span>
                    </div>
                    <input type="hidden" wire:model="plot_planting_id" />
                    <flux:error name="plot_planting_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Campaña') }}</flux:label>
                    @php $lockedCampaign = $campaigns->firstWhere('id', $campaign_id); @endphp
                    <div class="flex items-center gap-2 px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-lg text-sm text-zinc-700">
                        <flux:icon icon="calendar" class="size-4 text-zinc-400 shrink-0" />
                        <span>{{ __('Campaña') }} {{ $lockedCampaign?->year ?? '—' }}</span>
                    </div>
                    <input type="hidden" wire:model="campaign_id" />
                    <flux:error name="campaign_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Estadio fenológico') }}</flux:label>
                    <flux:select wire:model.live="event">
                        <option value="">{{ __('Selecciona estadio') }}</option>
                        @foreach($events as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="event" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Fecha de observación') }}</flux:label>
                    <flux:input wire:model="obs_date" type="date" />
                    <flux:error name="obs_date" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Fuente del dato') }}</flux:label>
                    <flux:select wire:model="source">
                        @foreach($sources as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="source" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Nivel de confianza (%)') }}</flux:label>
                    <flux:input wire:model="confidence" type="number" min="0" max="100" />
                    <flux:description>{{ __('0 = incierto, 100 = certeza absoluta') }}</flux:description>
                    <flux:error name="confidence" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Grados-día acumulados') }}</flux:label>
                    <flux:input wire:model="degree_days_accumulated" type="number" step="0.1" min="0" placeholder="0.0" />
                    <flux:description>{{ __('Desde desborre hasta este estadio') }}</flux:description>
                    <flux:error name="degree_days_accumulated" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Código BBCH') }}</flux:label>
                    <flux:input wire:model="bbch_code" type="number" min="0" max="99" />
                    <flux:description>{{ __('Se autocompletará al seleccionar estadio') }}</flux:description>
                    <flux:error name="bbch_code" />
                </flux:field>
            </div>

            <div class="mt-6">
                <flux:field>
                    <flux:label>{{ __('Notas') }}</flux:label>
                    <flux:textarea wire:model="notes" rows="3" :placeholder="__('Observaciones adicionales...')" />
                    <flux:error name="notes" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions :cancel-url="roleRoute('viticulturist.phenology.index', ['filter_planting_id' => $plot_planting_id])" :submit-label="__('Actualizar Observación')" />
    </form>
</x-agro.form-card>
</div>
