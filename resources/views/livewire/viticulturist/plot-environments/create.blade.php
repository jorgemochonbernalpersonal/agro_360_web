<x-agro.form-card
    :title="__('Nueva Ficha de Entorno de Parcela')"
    :description="__('Registra las condiciones ambientales y zonas protegidas de la parcela')"
    :back-url="roleRoute('viticulturist.plot-environments.index')"
>
    <form wire:submit="save" class="space-y-8">

        <x-agro.form-section :title="__('Ubicación')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>{{ __('Campaña') }}</flux:label>
                    <flux:select wire:model="campaign_id">
                        @foreach($campaigns as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="campaign_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Parcela') }}</flux:label>
                    <flux:select wire:model="plot_id">
                        <option value="">{{ __('Seleccionar...') }}</option>
                        @foreach($plots as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="plot_id" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>{{ __('Plantación específica (opcional)') }}</flux:label>
                    <flux:select wire:model="plot_planting_id">
                        <option value="">{{ __('Global parcela') }}</option>
                        @foreach($plantings as $p)
                            <option value="{{ $p->id }}">{{ $p->plot->name }} — {{ $p->grapeVariety->name ?? '' }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="plot_planting_id" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section :title="__('Captación de Agua')">
            <div class="space-y-4">
                <flux:checkbox wire:model.live="water_intake_nearby" :label="__('¿Hay captación de agua cercana (río, pozo, embalse)?')" />
                @if($water_intake_nearby)
                    <flux:field>
                        <flux:label>{{ __('Distancia a la captación (m)') }}</flux:label>
                        <flux:input wire:model="water_intake_distance_m" type="number" step="0.01" min="0" placeholder="0.00" />
                        <flux:error name="water_intake_distance_m" />
                    </flux:field>
                @endif
            </div>
        </x-agro.form-section>

        <x-agro.form-section :title="__('Zonas Protegidas')">
            <div class="space-y-4">
                <div class="flex flex-col gap-2">
                    <flux:checkbox wire:model.live="protected_zone_total" :label="__('Zona de exclusión total (no se puede tratar)')" />
                    <flux:checkbox wire:model.live="protected_zone_partial" :label="__('Zona de exclusión parcial (restricciones de uso)')" />
                </div>
                @if($protected_zone_total || $protected_zone_partial)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <flux:field>
                            <flux:label>{{ __('Tipo de zona') }}</flux:label>
                            <flux:input wire:model="protection_zone_type" type="text" placeholder="N2000, LIC, ZEPA, ZEC..." />
                            <flux:error name="protection_zone_type" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Zona tampón (m)') }}</flux:label>
                            <flux:input wire:model="buffer_zone_m" type="number" step="0.01" min="0" placeholder="0.00" />
                            <flux:error name="buffer_zone_m" />
                        </flux:field>
                    </div>
                @endif
            </div>
        </x-agro.form-section>

        <x-agro.form-section :title="__('Topografía y Riesgo')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label>{{ __('Pendiente media (%)') }}</flux:label>
                    <flux:input wire:model="slope_pct" type="number" step="0.01" min="0" max="100" placeholder="0.00" />
                    <flux:error name="slope_pct" />
                </flux:field>

                <div class="flex items-center pt-6">
                    <flux:checkbox wire:model="erosion_risk" :label="__('Riesgo significativo de erosión')" />
                </div>

                <flux:field class="md:col-span-2">
                    <flux:label>{{ __('Notas') }}</flux:label>
                    <flux:textarea wire:model="notes" rows="3" />
                    <flux:error name="notes" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.plot-environments.index')"
            :submit-label="__('Registrar Entorno')"
        />
    </form>
</x-agro.form-card>
