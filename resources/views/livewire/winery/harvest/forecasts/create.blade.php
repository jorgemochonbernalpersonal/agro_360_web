<div>
    <x-agro.form-card
        title="{{ __('Nueva Previsión de Vendimia') }}"
        :description="__('Registra el aforo estimado de uva para un viticultor y plantación.')"
        :back-url="roleRoute('harvest-forecasts.index')"
    >
        <form wire:submit.prevent="save" class="space-y-8">

            {{-- Viticultor --}}
            <x-agro.form-section title="{{ __('Viticultor y Plantación') }}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>{{ __('Viticultor *') }}</flux:label>
                        <flux:select wire:model.live="viticulturist_id">
                            <option value="">{{ __('Seleccionar viticultor...') }}</option>
                            @foreach($linkedViticulturists as $v)
                                <option value="{{ $v->id }}">{{ $v->name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="viticulturist_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Parcela') }}</flux:label>
                        <flux:select wire:model.live="plot_id" :disabled="!$viticulturist_id">
                            <option value="">{{ __('Seleccionar parcela...') }}</option>
                            @foreach($availablePlots as $plot)
                                <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>

                    <flux:field class="md:col-span-2">
                        <flux:label>{{ __('Plantación *') }}</flux:label>
                        <flux:select wire:model.live="plot_planting_id" :disabled="!$plot_id">
                            <option value="">{{ __('Seleccionar plantación...') }}</option>
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

                {{-- Panel de referencia: PAC + aforo viticultor + ya recibido --}}
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
                                <p class="text-xs text-blue-400 mt-1">{{ __('Techo regulatorio. Tu previsión no puede superarlo.') }}</p>
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
                                        <span class="text-xs font-normal text-amber-500">{{ __('(borrador)') }}</span>
                                    @endif
                                </div>
                                <div class="text-violet-700 font-semibold text-base">
                                    {{ number_format($viticEstimateKg, 0) }} kg
                                </div>
                                <p class="text-xs text-violet-400 mt-1">{{ __('Estimación registrada por el propio viticultor.') }}</p>
                            </div>
                        @else
                            <div class="p-3 rounded-lg bg-zinc-50 border border-zinc-200 text-sm text-zinc-400 flex items-center gap-2">
                                <flux:icon icon="user-circle" class="size-4" />
                                El viticultor aún no ha registrado aforo
                            </div>
                        @endif

                    </div>

                    {{-- Alerta: ya hay recepciones registradas --}}
                    @if($receivedKg !== null && $receivedKg > 0)
                        @php
                            $pctVsPac      = ($pacLimit && $pacLimit > 0) ? round(($receivedKg / $pacLimit) * 100, 1) : null;
                            $alreadyOver   = $pacLimit !== null && $receivedKg > $pacLimit;
                        @endphp
                        <div class="mt-3 p-4 rounded-xl border {{ $alreadyOver ? 'bg-red-50 border-red-300' : 'bg-amber-50 border-amber-300' }}">
                            <div class="flex items-start gap-3">
                                <flux:icon icon="{{ $alreadyOver ? 'exclamation-triangle' : 'information-circle' }}"
                                    class="size-5 shrink-0 mt-0.5 {{ $alreadyOver ? 'text-red-500' : 'text-amber-500' }}" />
                                <div class="flex-1 text-sm">
                                    <p class="font-semibold {{ $alreadyOver ? 'text-red-700' : 'text-amber-700' }}">
                                        {{ $alreadyOver ? 'Límite PAC ya superado' : 'Esta plantación ya tiene recepciones' }}
                                    </p>
                                    <p class="{{ $alreadyOver ? 'text-red-600' : 'text-amber-600' }} mt-0.5">
                                        Kg ya recibidos: <strong>{{ number_format($receivedKg, 0) }} kg</strong>
                                        @if($pctVsPac !== null)
                                            — {{ $pctVsPac }}% del límite PAC
                                        @endif
                                    </p>
                                    <p class="text-xs {{ $alreadyOver ? 'text-red-400' : 'text-amber-400' }} mt-1">
                                        La previsión que crees ahora se usará como referencia retroactiva en el Cuadro de Mando.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            </x-agro.form-section>

            {{-- Campaña y añada --}}
            <x-agro.form-section title="{{ __('Campaña') }}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>{{ __('Campaña *') }}</flux:label>
                        <flux:select wire:model.live="campaign_id">
                            <option value="">{{ __('Seleccionar campaña...') }}</option>
                            @foreach($campaigns as $campaign)
                                <option value="{{ $campaign->id }}">{{ $campaign->year }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="campaign_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Añada') }}</flux:label>
                        <flux:input wire:model="vintage_year" type="number" min="2000" max="2100" readonly />
                    </flux:field>
                </div>
            </x-agro.form-section>

            {{-- Estimación --}}
            <x-agro.form-section title="{{ __('Estimación') }}">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <flux:field>
                        <flux:label>{{ __('Kg estimados *') }}</flux:label>
                        <flux:input wire:model="estimated_kg" type="number" step="0.001" min="1"
                            placeholder="0.000"
                            :description="$pacLimit ? 'Máx. PAC: ' . number_format($pacLimit, 0) . ' kg' : ''" />
                        <flux:error name="estimated_kg" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Fecha del aforo *') }}</flux:label>
                        <flux:input wire:model="estimation_date" type="date" />
                        <flux:error name="estimation_date" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Estado') }}</flux:label>
                        <flux:select wire:model="status">
                            <option value="draft">{{ __('Borrador') }}</option>
                            <option value="confirmed">{{ __('Confirmada') }}</option>
                        </flux:select>
                        <flux:description>{{ __('Solo las previsiones confirmadas actúan como límite operativo.') }}</flux:description>
                    </flux:field>
                </div>

                <flux:field class="mt-4">
                    <flux:label>{{ __('Notas') }}</flux:label>
                    <flux:textarea wire:model="notes" rows="2" :placeholder="__('Observaciones del aforo...')" />
                </flux:field>
            </x-agro.form-section>

            <x-agro.form-actions
                :cancel-url="roleRoute('harvest-forecasts.index')"
                submit-:label="__('Guardar previsión')"
            />

        </form>
    </x-agro.form-card>
</div>
