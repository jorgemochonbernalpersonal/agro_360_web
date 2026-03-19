<x-agro.form-card
    title="Nuevo Control de Fermentación"
    description="Registra los parámetros de fermentación de un vino en un depósito."
    icon="beaker"
    icon-color="from-amber-500 to-amber-700"
    :back-url="roleRoute('fermentation-controls.index')"
>
    <form wire:submit="save" class="space-y-8">

        {{-- ── Identificación ─────────────────────────────────────────── --}}
        <x-agro.form-section title="Identificación" color="amber">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <flux:field>
                    <flux:label required>Vino</flux:label>
                    <flux:select wire:model.live="wine_id" required>
                        <option value="">Seleccionar vino...</option>
                        @foreach($wines as $wine)
                            <option value="{{ $wine->id }}">
                                {{ $wine->name }}{{ $wine->vintage ? ' (' . $wine->vintage . ')' : '' }}
                            </option>
                        @endforeach
                    </flux:select>
                    <flux:error name="wine_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>Depósito</flux:label>
                    <flux:select wire:model="container_id" required>
                        <option value="">Seleccionar depósito...</option>
                        @foreach($containers as $container)
                            <option value="{{ $container->id }}">{{ $container->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="container_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>Fecha y hora del control</flux:label>
                    <flux:input wire:model="control_date" type="datetime-local" required />
                    <flux:error name="control_date" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- ── Parámetros ───────────────────────────────────────────────── --}}
        <x-agro.form-section title="Parámetros de Fermentación" color="orange">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-6">

                <flux:field>
                    <flux:label>Temperatura</flux:label>
                    <flux:input wire:model="temperature" type="number" step="0.1" min="-20" max="60"
                        placeholder="°C" />
                    <flux:description>°C</flux:description>
                    <flux:error name="temperature" />
                </flux:field>

                <flux:field>
                    <flux:label>Brix</flux:label>
                    <flux:input wire:model="brix_degree" type="number" step="0.1" min="0" max="100"
                        placeholder="°Bx" />
                    <flux:description>°Bx</flux:description>
                    <flux:error name="brix_degree" />
                </flux:field>

                <flux:field>
                    <flux:label>Baumé</flux:label>
                    <flux:input wire:model="baume_degree" type="number" step="0.1" min="0" max="25"
                        placeholder="°Bé" />
                    <flux:description>°Bé</flux:description>
                    <flux:error name="baume_degree" />
                </flux:field>

                <flux:field>
                    <flux:label>Densidad</flux:label>
                    <flux:input wire:model="density" type="number" step="0.0001" min="0.9" max="1.3"
                        placeholder="g/cm³" />
                    <flux:description>g/cm³</flux:description>
                    <flux:error name="density" />
                </flux:field>

                <flux:field>
                    <flux:label>pH</flux:label>
                    <flux:input wire:model="ph" type="number" step="0.01" min="0" max="14"
                        placeholder="0 – 14" />
                    <flux:error name="ph" />
                </flux:field>

                <flux:field>
                    <flux:label>Acidez volátil</flux:label>
                    <flux:input wire:model="volatile_acidity" type="number" step="0.01" min="0"
                        placeholder="g/L acético" />
                    <flux:description>g/L acético</flux:description>
                    <flux:error name="volatile_acidity" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- ── Notas ───────────────────────────────────────────────────── --}}
        <x-agro.form-section title="Observaciones" color="zinc">
            <flux:field>
                <flux:label>Notas</flux:label>
                <flux:textarea wire:model="notes" rows="3"
                    placeholder="Observaciones sobre el estado de la fermentación..." />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions :back-url="roleRoute('fermentation-controls.index')" submit-label="Guardar control" />
    </form>
</x-agro.form-card>
