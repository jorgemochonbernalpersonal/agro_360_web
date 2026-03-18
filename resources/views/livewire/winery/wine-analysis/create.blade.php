<x-agro.form-card
    title="Nuevo Análisis de Laboratorio"
    description="Registra los resultados de un análisis fisicoquímico."
    icon="beaker"
    icon-color="from-purple-500 to-purple-700"
    :back-url="roleRoute('wine-analysis.index')"
>
    <form wire:submit="save" class="space-y-8">

        {{-- ── Identificación ─────────────────────────────────────────── --}}
        <x-agro.form-section title="Identificación" color="purple">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <flux:field>
                    <flux:label>Vino</flux:label>
                    <flux:select wire:model="wine_id">
                        <option value="">Sin vino asociado</option>
                        @foreach($wines as $wine)
                            <option value="{{ $wine->id }}">
                                {{ $wine->name }}{{ $wine->vintage ? ' (' . $wine->vintage . ')' : '' }}
                            </option>
                        @endforeach
                    </flux:select>
                    <flux:error name="wine_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>Tipo de análisis</flux:label>
                    <flux:select wire:model="analysis_type" required>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="analysis_type" />
                </flux:field>

                <flux:field>
                    <flux:label required>Fecha del análisis</flux:label>
                    <flux:input wire:model="analysis_date" type="date" required />
                    <flux:error name="analysis_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Laboratorio</flux:label>
                    <flux:input wire:model="laboratory" type="text" placeholder="Nombre del laboratorio" />
                    <flux:error name="laboratory" />
                </flux:field>

                <flux:field>
                    <flux:label>Referencia de muestra</flux:label>
                    <flux:input wire:model="sample_reference" type="text" placeholder="Ej. M-2026-001" />
                    <flux:error name="sample_reference" />
                </flux:field>

                <flux:field>
                    <flux:label required>Resultado</flux:label>
                    <flux:select wire:model="result" required>
                        @foreach($results as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="result" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- ── Parámetros fisicoquímicos ────────────────────────────────── --}}
        <x-agro.form-section title="Parámetros" color="blue">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">

                <flux:field>
                    <flux:label>Graduación alcohólica</flux:label>
                    <flux:input wire:model="alcoholic_strength" type="number" step="0.01" min="0" max="100"
                        placeholder="% vol" />
                    <flux:description>% vol</flux:description>
                    <flux:error name="alcoholic_strength" />
                </flux:field>

                <flux:field>
                    <flux:label>Acidez total</flux:label>
                    <flux:input wire:model="total_acidity" type="number" step="0.01" min="0"
                        placeholder="g/L tartárico" />
                    <flux:description>g/L tartárico</flux:description>
                    <flux:error name="total_acidity" />
                </flux:field>

                <flux:field>
                    <flux:label>Acidez volátil</flux:label>
                    <flux:input wire:model="volatile_acidity" type="number" step="0.01" min="0"
                        placeholder="g/L acético" />
                    <flux:description>g/L acético</flux:description>
                    <flux:error name="volatile_acidity" />
                </flux:field>

                <flux:field>
                    <flux:label>Azúcar residual</flux:label>
                    <flux:input wire:model="residual_sugar" type="number" step="0.01" min="0"
                        placeholder="g/L" />
                    <flux:description>g/L</flux:description>
                    <flux:error name="residual_sugar" />
                </flux:field>

                <flux:field>
                    <flux:label>SO₂ libre</flux:label>
                    <flux:input wire:model="free_so2" type="number" step="0.1" min="0"
                        placeholder="mg/L" />
                    <flux:description>mg/L</flux:description>
                    <flux:error name="free_so2" />
                </flux:field>

                <flux:field>
                    <flux:label>SO₂ total</flux:label>
                    <flux:input wire:model="total_so2" type="number" step="0.1" min="0"
                        placeholder="mg/L" />
                    <flux:description>mg/L</flux:description>
                    <flux:error name="total_so2" />
                </flux:field>

                <flux:field>
                    <flux:label>pH</flux:label>
                    <flux:input wire:model="ph" type="number" step="0.01" min="0" max="14"
                        placeholder="0 – 14" />
                    <flux:error name="ph" />
                </flux:field>

                <flux:field>
                    <flux:label>Densidad</flux:label>
                    <flux:input wire:model="density" type="number" step="0.0001" min="0"
                        placeholder="g/cm³" />
                    <flux:description>g/cm³</flux:description>
                    <flux:error name="density" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- ── Notas ───────────────────────────────────────────────────── --}}
        <x-agro.form-section title="Notas" color="zinc">
            <flux:field>
                <flux:label>Observaciones</flux:label>
                <flux:textarea wire:model="notes" rows="3"
                    placeholder="Comentarios adicionales sobre el análisis..." />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions :back-url="roleRoute('wine-analysis.index')" submit-label="Guardar análisis" />
    </form>
</x-agro.form-card>
