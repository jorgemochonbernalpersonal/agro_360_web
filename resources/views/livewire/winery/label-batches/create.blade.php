<x-agro.form-card
    title="{{ __('Nuevo Lote de Etiquetas') }}"
    :description="__('Define una serie numerada de contraetiquetas o etiquetas de bodega.')"
    icon="tag"
    icon-color="from-violet-500 to-violet-700"
    :back-url="roleRoute('label-batches.index')"
>
    <form wire:submit="save" class="space-y-8">

        <x-agro.form-section title="{{ __('Datos del lote') }}" color="violet">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <flux:field class="lg:col-span-2">
                    <flux:label required>{{ __('Nombre / descripción') }}</flux:label>
                    <flux:input wire:model="name" :placeholder="__('Ej: Contraetiqueta DO Rioja 2026 · Lote A')" required />
                    <flux:description>{{ __('Identifica el tipo de etiqueta y la campaña.') }}</flux:description>
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Vino asociado') }}</flux:label>
                    <flux:select wire:model="wine_id">
                        <flux:select.option value="">{{ __('Sin vino específico') }}</flux:select.option>
                        @foreach($wines as $wine)
                            <flux:select.option value="{{ $wine->id }}">
                                {{ $wine->name }}{{ $wine->vintage ? ' · ' . $wine->vintage : '' }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="wine_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Origen') }}</flux:label>
                    <flux:select wire:model="source" required>
                        @foreach($sources as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:description>{{ __('«Asignado por DO» para contraetiquetas numeradas oficiales.') }}</flux:description>
                    <flux:error name="source" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section title="{{ __('Numeración') }}" color="blue">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <flux:field>
                    <flux:label required>{{ __('Número inicial') }}</flux:label>
                    <flux:input wire:model.live="start_number" type="number" min="1" step="1"
                        placeholder="{{ __('Ej: 1000001') }}" required />
                    <flux:error name="start_number" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Número final') }}</flux:label>
                    <flux:input wire:model.live="end_number" type="number" min="1" step="1"
                        placeholder="{{ __('Ej: 1005000') }}" required />
                    <flux:error name="end_number" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Total de etiquetas') }}</flux:label>
                    <div class="flex items-center h-10 px-3 rounded-lg bg-zinc-50 border border-zinc-200">
                        @if($totalPreview !== null)
                            <span class="font-bold text-agro-700 text-lg">{{ number_format($totalPreview) }}</span>
                        @else
                            <span class="text-zinc-400">—</span>
                        @endif
                    </div>
                    <flux:description>{{ __('Calculado automáticamente.') }}</flux:description>
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section title="{{ __('Notas') }}" color="zinc">
            <flux:field>
                <flux:label>{{ __('Observaciones') }}</flux:label>
                <flux:textarea wire:model="notes" rows="2"
                    placeholder="{{ __('Proveedor, fecha de recepción, referencia de pedido...') }}" />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions :back-url="roleRoute('label-batches.index')" submit-:label="__('Crear lote')" />
    </form>
</x-agro.form-card>
