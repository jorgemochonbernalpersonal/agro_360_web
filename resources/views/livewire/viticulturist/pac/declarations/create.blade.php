<div>
    <x-agro.form-card
        title="Nueva Declaración PAC"
        description="Solicitud Única anual de superficies agrarias"
        :back-url="roleRoute('viticulturist.pac.declarations.index')"
    >
        <form wire:submit.prevent="save('draft')" class="space-y-8">

            @error('items')
                <flux:callout variant="danger" icon="x-circle">
                    <flux:callout.heading>Error</flux:callout.heading>
                    <flux:callout.text>{{ $message }}</flux:callout.text>
                </flux:callout>
            @enderror

            {{-- Datos generales --}}
            <x-agro.form-section title="Datos de la Declaración">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <flux:field>
                        <flux:label for="year">Año de campaña *</flux:label>
                        <flux:input wire:model.live="year" type="number" id="year"
                            min="2000" max="{{ now()->year + 1 }}" />
                        <flux:error name="year" />
                        @if(in_array($year, $existingYears))
                            <flux:description class="text-amber-600">Ya existe una declaración para {{ $year }}.</flux:description>
                        @endif
                    </flux:field>
                    <flux:field>
                        <flux:label for="reference_number">Referencia FEGA</flux:label>
                        <flux:input wire:model="reference_number" type="text" id="reference_number"
                            placeholder="Nº expediente" />
                        <flux:error name="reference_number" />
                    </flux:field>
                    <flux:field>
                        <flux:label for="notes">Notas</flux:label>
                        <flux:input wire:model="notes" type="text" id="notes"
                            placeholder="Observaciones opcionales" />
                        <flux:error name="notes" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            {{-- Selección de parcelas --}}
            <x-agro.form-section title="Parcelas Declaradas">
                <p class="text-sm text-zinc-500 mb-4">
                    Selecciona las parcelas a incluir en la declaración y ajusta las superficies y eco-regímenes de cada una.
                </p>

                @if(empty($items))
                    <flux:callout variant="warning" icon="exclamation-triangle">
                        <flux:callout.text>No tienes parcelas activas. Crea parcelas antes de crear una declaración PAC.</flux:callout.text>
                    </flux:callout>
                @else
                    <div class="space-y-3">
                        @foreach($items as $plotId => $item)
                            <div wire:key="item-{{ $plotId }}"
                                 class="border rounded-xl p-4 transition-colors {{ ($item['selected'] ?? false) ? 'border-agro-300 bg-agro-50/30' : 'border-zinc-200 bg-white' }}">
                                <div class="flex items-start gap-3">
                                    <flux:checkbox
                                        wire:model.live="items.{{ $plotId }}.selected"
                                        id="plot-{{ $plotId }}"
                                        class="mt-0.5"
                                    />
                                    <div class="flex-1 min-w-0">
                                        <label for="plot-{{ $plotId }}" class="font-medium text-zinc-900 cursor-pointer">
                                            {{ $item['name'] }}
                                        </label>
                                        <p class="text-xs text-zinc-400 mt-0.5">Área total: {{ number_format($item['area'], 3) }} ha</p>

                                        @if($item['selected'] ?? false)
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                                <flux:field>
                                                    <flux:label>Superficie declarada (ha) *</flux:label>
                                                    <flux:input
                                                        wire:model="items.{{ $plotId }}.declared_area"
                                                        type="number" step="0.001" min="0.001"
                                                        placeholder="0.000"
                                                    />
                                                    <flux:error name="items.{{ $plotId }}.declared_area" />
                                                </flux:field>
                                                <flux:field>
                                                    <flux:label>Superficie admisible PAC (ha) *</flux:label>
                                                    <flux:input
                                                        wire:model="items.{{ $plotId }}.eligible_area"
                                                        type="number" step="0.001" min="0"
                                                        placeholder="0.000"
                                                    />
                                                    <flux:error name="items.{{ $plotId }}.eligible_area" />
                                                </flux:field>
                                                <div class="md:col-span-2">
                                                    <flux:label class="text-sm font-medium text-zinc-700 mb-2 block">Eco-regímenes</flux:label>
                                                    <div class="flex flex-wrap gap-3">
                                                        @foreach($ecoSchemes as $key => $label)
                                                            <label class="flex items-center gap-2 cursor-pointer">
                                                                <flux:checkbox
                                                                    wire:model="items.{{ $plotId }}.eco_schemes"
                                                                    value="{{ $key }}"
                                                                />
                                                                <span class="text-sm text-zinc-700">{{ $label }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-agro.form-section>

            {{-- Resumen --}}
            @if($this->selectedCount > 0)
                <x-agro.card>
                    <x-slot:header>
                        <div class="flex items-center gap-2">
                            <flux:icon icon="calculator" class="size-4 text-agro-600" />
                            <span class="font-semibold text-zinc-900 text-sm">Resumen de la declaración</span>
                        </div>
                    </x-slot:header>
                    <div class="grid grid-cols-3 gap-6 text-sm">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-zinc-900">{{ $this->selectedCount }}</p>
                            <p class="text-zinc-500 mt-1">Parcelas</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-zinc-900">{{ number_format($this->totalDeclared, 2) }} ha</p>
                            <p class="text-zinc-500 mt-1">Declaradas</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-green-600">{{ number_format($this->totalEligible, 2) }} ha</p>
                            <p class="text-zinc-500 mt-1">Admisibles</p>
                        </div>
                    </div>
                </x-agro.card>
            @endif

            {{-- Acciones --}}
            <div class="flex items-center justify-between pt-4 border-t border-zinc-100">
                <flux:button variant="ghost" href="{{ roleRoute('viticulturist.pac.declarations.index') }}" wire:navigate>
                    Cancelar
                </flux:button>
                <div class="flex gap-3">
                    <flux:button type="submit" variant="ghost" icon="document">
                        Guardar borrador
                    </flux:button>
                    <flux:button wire:click="save('submitted')" variant="primary" icon="paper-airplane">
                        Guardar y presentar
                    </flux:button>
                </div>
            </div>

        </form>
    </x-agro.form-card>
</div>
