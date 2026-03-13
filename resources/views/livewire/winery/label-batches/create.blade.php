<x-agro.form-card
    title="Nuevo Lote de Etiquetas"
    description="Define una serie numerada de contraetiquetas o etiquetas de bodega."
    icon="tag"
    icon-color="from-violet-500 to-violet-700"
    :back-url="route('winery.label-batches.index')"
>
    <form wire:submit="save" class="space-y-8">

        <x-agro.form-section title="Datos del lote" color="violet">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <flux:field class="lg:col-span-2">
                    <flux:label required>Nombre / descripción</flux:label>
                    <flux:input wire:model="name" placeholder="Ej: Contraetiqueta DO Rioja 2026 · Lote A" required />
                    <flux:description>Identifica el tipo de etiqueta y la campaña.</flux:description>
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Vino asociado</flux:label>
                    <flux:select wire:model="wine_id">
                        <flux:select.option value="">Sin vino específico</flux:select.option>
                        @foreach($wines as $wine)
                            <flux:select.option value="{{ $wine->id }}">
                                {{ $wine->name }}{{ $wine->vintage ? ' · ' . $wine->vintage : '' }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="wine_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>Origen</flux:label>
                    <flux:select wire:model="source" required>
                        @foreach($sources as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:description>«Asignado por DO» para contraetiquetas numeradas oficiales.</flux:description>
                    <flux:error name="source" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Numeración" color="blue">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <flux:field>
                    <flux:label required>Número inicial</flux:label>
                    <flux:input wire:model.live="start_number" type="number" min="1" step="1"
                        placeholder="Ej: 1000001" required />
                    <flux:error name="start_number" />
                </flux:field>

                <flux:field>
                    <flux:label required>Número final</flux:label>
                    <flux:input wire:model.live="end_number" type="number" min="1" step="1"
                        placeholder="Ej: 1005000" required />
                    <flux:error name="end_number" />
                </flux:field>

                <flux:field>
                    <flux:label>Total de etiquetas</flux:label>
                    <div class="flex items-center h-10 px-3 rounded-lg bg-zinc-50 border border-zinc-200">
                        @if($totalPreview !== null)
                            <span class="font-bold text-agro-700 text-lg">{{ number_format($totalPreview) }}</span>
                        @else
                            <span class="text-zinc-400">—</span>
                        @endif
                    </div>
                    <flux:description>Calculado automáticamente.</flux:description>
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Notas" color="zinc">
            <flux:field>
                <flux:label>Observaciones</flux:label>
                <flux:textarea wire:model="notes" rows="2"
                    placeholder="Proveedor, fecha de recepción, referencia de pedido..." />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions :back-url="route('winery.label-batches.index')" submit-label="Crear lote" />
    </form>
</x-agro.form-card>
