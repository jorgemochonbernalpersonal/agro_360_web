<div>
    <x-agro.form-card
        title="Editar Previsión de Vendimia"
        description="Modifica el aforo estimado de uva."
        :back-url="route('winery.harvest-forecasts.index')"
    >
        <form wire:submit.prevent="save" class="space-y-8">

            {{-- Contexto (read-only) --}}
            <x-agro.form-section title="Información">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <p class="text-zinc-500">Viticultor</p>
                        <p class="font-medium text-zinc-900">{{ $viticulturistName }}</p>
                    </div>
                    <div>
                        <p class="text-zinc-500">Plantación / Variedad</p>
                        <p class="font-medium text-zinc-900">{{ $plantingLabel }}</p>
                    </div>
                    <div>
                        <p class="text-zinc-500">Campaña</p>
                        <p class="font-medium text-zinc-900">{{ $campaignLabel }}</p>
                    </div>
                    <div>
                        <p class="text-zinc-500">Añada</p>
                        <p class="font-medium text-zinc-900">{{ $vintageYear }}</p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">

                    {{-- Límite PAC --}}
                    @if($pacLimit !== null)
                        <div class="p-3 rounded-lg bg-blue-50 border border-blue-200 text-sm">
                            <div class="flex items-center gap-2 text-blue-700 font-medium mb-1">
                                <flux:icon icon="shield-check" class="size-4" />
                                Límite PAC
                            </div>
                            <div class="space-y-0.5 text-blue-600">
                                <div>Base: <strong>{{ number_format($pacLimitRaw, 0) }} kg</strong></div>
                                @if($ageFactor !== null && $ageFactor < 100)
                                    <div>Factor edad: <strong>{{ $ageFactor }}%</strong></div>
                                @endif
                                <div class="text-blue-700 font-semibold">Efectivo: {{ number_format($pacLimit, 0) }} kg</div>
                            </div>
                            <p class="text-xs text-blue-400 mt-1">Techo regulatorio. Tu previsión no puede superarlo.</p>
                        </div>
                    @else
                        <div class="p-3 rounded-lg bg-zinc-50 border border-zinc-200 text-sm text-zinc-400 flex items-center gap-2">
                            <flux:icon icon="shield-exclamation" class="size-4" />
                            Sin límite PAC registrado
                        </div>
                    @endif

                    {{-- Aforo del viticultor --}}
                    @if($viticEstimateKg !== null)
                        <div class="p-3 rounded-lg bg-violet-50 border border-violet-200 text-sm">
                            <div class="flex items-center gap-2 text-violet-700 font-medium mb-1">
                                <flux:icon icon="user-circle" class="size-4" />
                                Aforo del viticultor
                                @if($viticEstimateStatus === 'draft')
                                    <span class="text-xs font-normal text-amber-500">(borrador)</span>
                                @endif
                            </div>
                            <div class="text-violet-700 font-semibold text-base">
                                {{ number_format($viticEstimateKg, 0) }} kg
                            </div>
                            <p class="text-xs text-violet-400 mt-1">Estimación registrada por el propio viticultor.</p>
                        </div>
                    @else
                        <div class="p-3 rounded-lg bg-zinc-50 border border-zinc-200 text-sm text-zinc-400 flex items-center gap-2">
                            <flux:icon icon="user-circle" class="size-4" />
                            El viticultor aún no ha registrado aforo
                        </div>
                    @endif

                </div>
            </x-agro.form-section>

            {{-- Estimación editable --}}
            <x-agro.form-section title="Estimación">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <flux:field>
                        <flux:label>Kg estimados *</flux:label>
                        <flux:input wire:model="estimated_kg" type="number" step="0.001" min="1"
                            :description="$pacLimit ? 'Máx. PAC: ' . number_format($pacLimit, 0) . ' kg' : ''" />
                        <flux:error name="estimated_kg" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Fecha del aforo *</flux:label>
                        <flux:input wire:model="estimation_date" type="date" />
                        <flux:error name="estimation_date" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Estado</flux:label>
                        <flux:select wire:model="status">
                            <option value="draft">Borrador</option>
                            <option value="confirmed">Confirmada</option>
                        </flux:select>
                        <flux:description>Solo las confirmadas actúan como límite operativo.</flux:description>
                    </flux:field>
                </div>

                <flux:field class="mt-4">
                    <flux:label>Notas</flux:label>
                    <flux:textarea wire:model="notes" rows="2" />
                </flux:field>
            </x-agro.form-section>

            <x-agro.form-actions
                :cancel-url="route('winery.harvest-forecasts.index')"
                submit-label="Guardar cambios"
            />

        </form>
    </x-agro.form-card>
</div>
