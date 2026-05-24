<x-agro.form-card
    :title="__('Editar Alerta Fitosanitaria')"
    :description="__('Modifica los datos de') . ' ' . $phytosanitaryAlert->title"
    :back-url="roleRoute('viticulturist.phytosanitary-alerts.index')"
>
    <form wire:submit="save" class="space-y-8">

        {{-- Sección 1: Información de la Alerta --}}
        <x-agro.form-section :title="__('Información de la Alerta')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field class="md:col-span-2">
                    <flux:label required>{{ __('Título') }}</flux:label>
                    <flux:input wire:model="title" type="text" />
                    <flux:error name="title" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Fuente') }}</flux:label>
                    <flux:select wire:model="source">
                        @foreach ($sources as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="source" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Tipo de alerta') }}</flux:label>
                    <flux:select wire:model="alert_type">
                        @foreach ($alertTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="alert_type" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Severidad') }}</flux:label>
                    <flux:select wire:model="severity">
                        @foreach ($severities as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="severity" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- Sección 2: Detalle --}}
        <x-agro.form-section :title="__('Detalle')">
            <div class="grid grid-cols-1 gap-6">

                <flux:field>
                    <flux:label>{{ __('Zona afectada') }}</flux:label>
                    <flux:input wire:model="affected_area" type="text" />
                    <flux:error name="affected_area" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Descripción') }}</flux:label>
                    <flux:textarea
                        wire:model="description"
                        rows="4"
                    />
                    <flux:error name="description" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Recomendaciones') }}</flux:label>
                    <flux:textarea
                        wire:model="recommendations"
                        rows="3"
                    />
                    <flux:error name="recommendations" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- Sección 3: Vigencia --}}
        <x-agro.form-section :title="__('Vigencia')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>{{ __('Fecha de la alerta') }}</flux:label>
                    <flux:input wire:model="alert_date" type="date" />
                    <flux:error name="alert_date" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Fecha de expiración') }}</flux:label>
                    <flux:input wire:model="expiry_date" type="date" />
                    <flux:description>{{ __('Dejar vacío si no tiene fecha de expiración') }}</flux:description>
                    <flux:error name="expiry_date" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.phytosanitary-alerts.index')"
            :submit-label="__('Guardar Cambios')"
        />
    </form>
</x-agro.form-card>
