<x-agro.form-card
    title="Nueva Alerta Fitosanitaria"
    description="Registra una alerta fitosanitaria oficial para tu explotación"
    :back-url="roleRoute('viticulturist.phytosanitary-alerts.index')"
>
    <form wire:submit="save" class="space-y-8">

        {{-- Sección 1: Información de la Alerta --}}
        <x-agro.form-section title="Información de la Alerta">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field class="md:col-span-2">
                    <flux:label required>Título</flux:label>
                    <flux:input wire:model="title" type="text" placeholder="Ej: Alerta por Mildiu en viñedos de la zona" />
                    <flux:error name="title" />
                </flux:field>

                <flux:field>
                    <flux:label required>Fuente</flux:label>
                    <flux:select wire:model="source">
                        @foreach ($sources as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="source" />
                </flux:field>

                <flux:field>
                    <flux:label required>Tipo de alerta</flux:label>
                    <flux:select wire:model="alert_type">
                        @foreach ($alertTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="alert_type" />
                </flux:field>

                <flux:field>
                    <flux:label required>Severidad</flux:label>
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
        <x-agro.form-section title="Detalle">
            <div class="grid grid-cols-1 gap-6">

                <flux:field>
                    <flux:label>Zona afectada</flux:label>
                    <flux:input wire:model="affected_area" type="text" placeholder="Ej: Parcelas del Valle del Ebro, zona norte" />
                    <flux:error name="affected_area" />
                </flux:field>

                <flux:field>
                    <flux:label required>Descripción</flux:label>
                    <flux:textarea
                        wire:model="description"
                        rows="4"
                        placeholder="Descripción detallada de la alerta: situación, causas, alcance..."
                    />
                    <flux:error name="description" />
                </flux:field>

                <flux:field>
                    <flux:label>Recomendaciones</flux:label>
                    <flux:textarea
                        wire:model="recommendations"
                        rows="3"
                        placeholder="Medidas preventivas o correctoras recomendadas"
                    />
                    <flux:error name="recommendations" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- Sección 3: Vigencia --}}
        <x-agro.form-section title="Vigencia">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>Fecha de la alerta</flux:label>
                    <flux:input wire:model="alert_date" type="date" />
                    <flux:error name="alert_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Fecha de expiración</flux:label>
                    <flux:input wire:model="expiry_date" type="date" />
                    <flux:description>Dejar vacío si no tiene fecha de expiración</flux:description>
                    <flux:error name="expiry_date" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.phytosanitary-alerts.index')"
            submit-label="Registrar Alerta"
        />
    </form>
</x-agro.form-card>
