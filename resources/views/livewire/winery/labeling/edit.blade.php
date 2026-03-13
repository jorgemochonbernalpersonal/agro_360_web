<x-agro.form-card
    title="Editar Sesión de Etiquetado"
    description="Modifica los datos de esta sesión. Si cambias la cantidad o el lote, el stock se recalcula automáticamente."
    icon="tag"
    icon-color="from-violet-500 to-violet-700"
    :back-url="route('winery.labeling.index')"
>
    <form wire:submit="save" class="space-y-8">

        {{-- ── Vino y fecha ──────────────────────────────────────────────── --}}
        <x-agro.form-section title="Vino y fecha" color="violet">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <flux:field>
                    <flux:label required>Vino</flux:label>
                    <flux:select wire:model.live="wine_id" required>
                        <flux:select.option value="">Seleccionar vino...</flux:select.option>
                        @foreach($wines as $wine)
                            <flux:select.option value="{{ $wine->id }}">
                                {{ $wine->name }}{{ $wine->vintage ? ' · ' . $wine->vintage : '' }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="wine_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>Fecha de etiquetado</flux:label>
                    <flux:input wire:model="labeling_date" type="date" required />
                    <flux:error name="labeling_date" />
                </flux:field>

                @if($wine_id && $wineBottlings->isNotEmpty())
                    <flux:field>
                        <flux:label>Embotellado vinculado</flux:label>
                        <flux:select wire:model="wine_bottling_id">
                            <flux:select.option value="">Sin vincular</flux:select.option>
                            @foreach($wineBottlings as $bottling)
                                <flux:select.option value="{{ $bottling->id }}">
                                    {{ $bottling->bottling_date->format('d/m/Y') }} · {{ $bottling->format_label }} · {{ number_format($bottling->quantity_bottles) }} bot.
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="wine_bottling_id" />
                    </flux:field>
                @endif

            </div>
        </x-agro.form-section>

        {{-- ── Lote y cantidades ─────────────────────────────────────────── --}}
        <x-agro.form-section title="Etiquetas y numeración" color="blue">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                <flux:field class="lg:col-span-2">
                    <flux:label>Lote de etiquetas</flux:label>
                    <flux:select wire:model="label_batch_id">
                        <flux:select.option value="">Sin lote numerado</flux:select.option>
                        @foreach($labelBatches as $batch)
                            <flux:select.option value="{{ $batch->id }}">
                                {{ $batch->name }} ({{ number_format($batch->available_quantity) }} disp.)
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="label_batch_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>Botellas etiquetadas</flux:label>
                    <flux:input wire:model="quantity_labeled" type="number" min="1" step="1" required />
                    <flux:error name="quantity_labeled" />
                </flux:field>

            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>Número de etiqueta inicial</flux:label>
                    <flux:input wire:model="from_number" type="number" min="1" step="1" />
                    <flux:error name="from_number" />
                </flux:field>
                <flux:field>
                    <flux:label>Número de etiqueta final</flux:label>
                    <flux:input wire:model="to_number" type="number" min="1" step="1" />
                    <flux:error name="to_number" />
                </flux:field>
            </div>
        </x-agro.form-section>

        {{-- ── Notas ─────────────────────────────────────────────────────── --}}
        <x-agro.form-section title="Notas" color="zinc">
            <flux:field>
                <flux:label>Observaciones</flux:label>
                <flux:textarea wire:model="notes" rows="2" />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions :back-url="route('winery.labeling.index')" submit-label="Guardar cambios" />
    </form>
</x-agro.form-card>
