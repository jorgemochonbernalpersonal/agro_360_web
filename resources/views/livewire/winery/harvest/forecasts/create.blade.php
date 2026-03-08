<div>
    <x-agro.form-card
        title="Nueva Previsión de Vendimia"
        description="Registra el aforo estimado de uva para un viticultor y plantación."
        :back-url="route('winery.harvest-forecasts.index')"
    >
        <form wire:submit.prevent="save" class="space-y-8">

            {{-- Viticultor --}}
            <x-agro.form-section title="Viticultor y Plantación">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Viticultor *</flux:label>
                        <flux:select wire:model.live="viticulturist_id">
                            <option value="">Seleccionar viticultor...</option>
                            @foreach($linkedViticulturists as $v)
                                <option value="{{ $v->id }}">{{ $v->name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="viticulturist_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Parcela</flux:label>
                        <flux:select wire:model.live="plot_id" :disabled="!$viticulturist_id">
                            <option value="">Seleccionar parcela...</option>
                            @foreach($availablePlots as $plot)
                                <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>

                    <flux:field class="md:col-span-2">
                        <flux:label>Plantación *</flux:label>
                        <flux:select wire:model.live="plot_planting_id" :disabled="!$plot_id">
                            <option value="">Seleccionar plantación...</option>
                            @foreach($availablePlantings as $planting)
                                <option value="{{ $planting->id }}">
                                    {{ $planting->grapeVariety?->name ?? $planting->name }}
                                    @if($planting->area_planted) — {{ number_format($planting->area_planted, 2) }} ha @endif
                                </option>
                            @endforeach
                        </flux:select>
                        <flux:error name="plot_planting_id" />
                    </flux:field>
                </div>

                {{-- Panel de referencia: PAC + aforo viticultor --}}
                @if($plot_planting_id)
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
                @endif
            </x-agro.form-section>

            {{-- Campaña y añada --}}
            <x-agro.form-section title="Campaña">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Campaña *</flux:label>
                        <flux:select wire:model.live="campaign_id">
                            <option value="">Seleccionar campaña...</option>
                            @foreach($campaigns as $campaign)
                                <option value="{{ $campaign->id }}">{{ $campaign->year }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="campaign_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Añada</flux:label>
                        <flux:input wire:model="vintage_year" type="number" min="2000" max="2100" readonly />
                    </flux:field>
                </div>
            </x-agro.form-section>

            {{-- Estimación --}}
            <x-agro.form-section title="Estimación">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <flux:field>
                        <flux:label>Kg estimados *</flux:label>
                        <flux:input wire:model="estimated_kg" type="number" step="0.001" min="1"
                            placeholder="0.000"
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
                        <flux:description>Solo las previsiones confirmadas actúan como límite operativo.</flux:description>
                    </flux:field>
                </div>

                <flux:field class="mt-4">
                    <flux:label>Notas</flux:label>
                    <flux:textarea wire:model="notes" rows="2" placeholder="Observaciones del aforo..." />
                </flux:field>
            </x-agro.form-section>

            <x-agro.form-actions
                :cancel-url="route('winery.harvest-forecasts.index')"
                submit-label="Guardar previsión"
            />

        </form>
    </x-agro.form-card>
</div>
