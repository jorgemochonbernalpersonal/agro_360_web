<x-agro.form-card
    :title="__('Nuevo Plan de Fertilización')"
    :description="__('Crea un plan de fertilización y gestión de nitrógenos para la campaña')"
    :back-url="roleRoute('viticulturist.fertilization-plans.index')"
>
    <form wire:submit="save" class="space-y-8">

        {{-- 1. Datos del Plan --}}
        <x-agro.form-section :title="__('Datos del Plan')" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label for="campaign_id" badge="Obligatorio">{{ __('Campaña') }}</flux:label>
                    <flux:select wire:model="campaign_id" id="campaign_id">
                        <option value="">{{ __('Seleccionar campaña...') }}</option>
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign->id }}">{{ __('Campaña') }} {{ $campaign->year }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="campaign_id" />
                </flux:field>

                <flux:field>
                    <flux:label for="plan_year" badge="Obligatorio">{{ __('Año del Plan') }}</flux:label>
                    <flux:input
                        wire:model="plan_year"
                        type="number"
                        id="plan_year"
                        min="2000"
                        max="2100"
                        placeholder="{{ now()->year }}"
                    />
                    <flux:error name="plan_year" />
                </flux:field>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <flux:field>
                    <flux:label for="prepared_by">{{ __('Elaborado por') }}</flux:label>
                    <flux:input
                        wire:model="prepared_by"
                        type="text"
                        id="prepared_by"
                        :placeholder="__('Nombre del técnico o responsable')"
                    />
                    <flux:error name="prepared_by" />
                </flux:field>

                <flux:field>
                    <flux:label for="approval_date">{{ __('Fecha de Aprobación') }}</flux:label>
                    <flux:input
                        wire:model="approval_date"
                        type="date"
                        id="approval_date"
                    />
                    <flux:error name="approval_date" />
                </flux:field>
            </div>

            <div class="mt-6">
                <label class="flex items-center gap-3 cursor-pointer select-none group w-fit">
                    <div class="relative">
                        <input
                            wire:model="nitrate_zone"
                            type="checkbox"
                            id="nitrate_zone"
                            class="sr-only peer"
                        />
                        <div class="w-10 h-6 bg-zinc-200 peer-checked:bg-red-500 rounded-full transition-colors peer-focus:ring-2 peer-focus:ring-red-400 peer-focus:ring-offset-1"></div>
                        <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-zinc-800">{{ __('Zona vulnerable a nitratos (ZVN)') }}</span>
                        <p class="text-xs text-zinc-400 mt-0.5">{{ __('Activa si la explotación se encuentra en zona declarada vulnerable') }}</p>
                    </div>
                </label>
                <flux:error name="nitrate_zone" />
            </div>
        </x-agro.form-section>

        {{-- 2. Resumen de Nutrientes --}}
        <x-agro.form-section :title="__('Resumen de Nutrientes')" color="amber">
            <p class="text-xs text-zinc-500 mb-4">{{ __('Opcional. Puedes rellenarlo manualmente o se calculará automáticamente a partir de las parcelas.') }}</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <flux:field>
                    <flux:label for="total_surface_ha">{{ __('Superficie total (ha)') }}</flux:label>
                    <flux:input
                        wire:model="total_surface_ha"
                        type="number"
                        id="total_surface_ha"
                        step="0.0001"
                        min="0"
                        placeholder="0.0000"
                    />
                    <flux:error name="total_surface_ha" />
                </flux:field>

                <flux:field>
                    <flux:label for="total_n_kg_ha">{{ __('N total (kg/ha)') }}</flux:label>
                    <flux:input
                        wire:model="total_n_kg_ha"
                        type="number"
                        id="total_n_kg_ha"
                        step="0.001"
                        min="0"
                        placeholder="0.000"
                    />
                    <flux:error name="total_n_kg_ha" />
                </flux:field>

                <flux:field>
                    <flux:label for="total_p_kg_ha">{{ __('P total (kg/ha)') }}</flux:label>
                    <flux:input
                        wire:model="total_p_kg_ha"
                        type="number"
                        id="total_p_kg_ha"
                        step="0.001"
                        min="0"
                        placeholder="0.000"
                    />
                    <flux:error name="total_p_kg_ha" />
                </flux:field>

                <flux:field>
                    <flux:label for="total_k_kg_ha">{{ __('K total (kg/ha)') }}</flux:label>
                    <flux:input
                        wire:model="total_k_kg_ha"
                        type="number"
                        id="total_k_kg_ha"
                        step="0.001"
                        min="0"
                        placeholder="0.000"
                    />
                    <flux:error name="total_k_kg_ha" />
                </flux:field>
            </div>
        </x-agro.form-section>

        {{-- 3. Parcelas del Plan --}}
        <x-agro.form-section :title="__('Parcelas del Plan')" color="green">
            <p class="text-xs text-zinc-500 mb-4">{{ __('Añade las parcelas incluidas en el plan con sus dosis de fertilizante.') }}</p>

            <div class="space-y-3">
                @foreach($lines as $i => $line)
                <div class="grid grid-cols-2 md:grid-cols-7 gap-3 items-end p-3 bg-zinc-50 rounded-xl" wire:key="line-{{ $i }}">
                    <flux:field class="md:col-span-2">
                        <flux:label>{{ __('Parcela / zona') }}</flux:label>
                        <flux:input wire:model.live="lines.{{ $i }}.plot_name" type="text" :placeholder="__('Nombre parcela')" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Sup. (ha)') }}</flux:label>
                        <flux:input wire:model.live="lines.{{ $i }}.surface_ha" type="number" step="0.0001" min="0" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Rendim. (kg)') }}</flux:label>
                        <flux:input wire:model="lines.{{ $i }}.expected_yield_kg" type="number" step="1" min="0" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('N (kg/ha)') }}</flux:label>
                        <flux:input wire:model="lines.{{ $i }}.n_kg_ha" type="number" step="0.001" min="0" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('P (kg/ha)') }}</flux:label>
                        <flux:input wire:model="lines.{{ $i }}.p_kg_ha" type="number" step="0.001" min="0" />
                    </flux:field>
                    <flux:field class="flex items-end gap-2">
                        <div class="flex-1">
                            <flux:label>{{ __('K (kg/ha)') }}</flux:label>
                            <flux:input wire:model="lines.{{ $i }}.k_kg_ha" type="number" step="0.001" min="0" />
                        </div>
                        @if(count($lines) > 1)
                        <button type="button" wire:click="removeLine({{ $i }})" class="mb-0.5 p-1.5 text-zinc-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                            <flux:icon icon="trash" class="size-4" />
                        </button>
                        @endif
                    </flux:field>
                </div>
                @endforeach
            </div>

            <div class="mt-4">
                <button type="button" wire:click="addLine" class="inline-flex items-center gap-2 text-sm text-agro-600 hover:text-agro-700 font-medium transition-colors">
                    <flux:icon icon="plus-circle" class="size-4" />
                    {{ __('Añadir parcela') }}
                </button>
            </div>
        </x-agro.form-section>

        {{-- 4. Notas --}}
        <x-agro.form-section :title="__('Notas')" color="green">
            <flux:field>
                <flux:label for="notes">{{ __('Observaciones') }}</flux:label>
                <flux:textarea
                    wire:model="notes"
                    id="notes"
                    rows="4"
                    :placeholder="__('Observaciones, recomendaciones técnicas, condiciones especiales del suelo...')"
                />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.fertilization-plans.index')"
            :submit-label="__('Crear Plan')"
        />

    </form>
</x-agro.form-card>
