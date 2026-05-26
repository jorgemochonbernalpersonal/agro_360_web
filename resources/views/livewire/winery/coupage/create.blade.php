<x-agro.form-card
    title="{{ __('Nuevo Coupage') }}"
    :description="__('Define el vino resultante, los vinos fuente y sus proporciones.')"
    icon="funnel"
    icon-color="from-violet-500 to-purple-600"
    :back-url="roleRoute('coupage.index')"
>
    <form wire:submit="save" class="space-y-8">

        {{-- Vino resultante y destino ──────────────────────────────────── --}}
        <x-agro.form-section title="{{ __('Vino resultante') }}" color="violet">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <flux:field>
                    <flux:label required>{{ __('Vino resultante') }}</flux:label>
                    <flux:select wire:model="target_wine_id" required>
                        <option value="">{{ __('Seleccionar vino...') }}</option>
                        @foreach($wines as $wine)
                            <option value="{{ $wine->id }}">
                                {{ $wine->name }}{{ $wine->vintage ? ' (' . $wine->vintage . ')' : '' }}
                            </option>
                        @endforeach
                    </flux:select>
                    <flux:description>El vino que se obtiene como resultado del coupage.</flux:description>
                    <flux:error name="target_wine_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Depósito destino') }}</flux:label>
                    <flux:select wire:model="to_container_id" required>
                        <option value="">{{ __('Seleccionar depósito...') }}</option>
                        @foreach($containers as $container)
                            <option value="{{ $container->id }}">{{ $container->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="to_container_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Fecha') }}</flux:label>
                    <flux:input wire:model="coupage_date" type="date" required />
                    <flux:error name="coupage_date" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Unidad de medida') }}</flux:label>
                    <flux:select wire:model="unit_of_measurement_id" required>
                        <option value="">{{ __('Seleccionar unidad...') }}</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->symbol }})</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="unit_of_measurement_id" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Enólogo responsable') }}</flux:label>
                    <flux:select wire:model="oenologist_id">
                        <option value="">{{ __('Sin enólogo asignado') }}</option>
                        @foreach($oenologists as $oenologist)
                            <option value="{{ $oenologist->id }}">{{ $oenologist->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="oenologist_id" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- Vinos fuente ─────────────────────────────────────────────────── --}}
        <x-agro.form-section title="{{ __('Vinos fuente') }}" color="purple">
            <div class="space-y-3">
                @foreach($sources as $index => $source)
                    <div
                        wire:key="source-{{ $index }}"
                        class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 rounded-xl bg-zinc-50 border border-zinc-200"
                    >
                        <flux:field>
                            <flux:label required>{{ __('Vino origen') }}</flux:label>
                            <flux:select wire:model="sources.{{ $index }}.wine_id" required>
                                <option value="">{{ __('Seleccionar vino...') }}</option>
                                @foreach($wines as $wine)
                                    <option value="{{ $wine->id }}">
                                        {{ $wine->name }}{{ $wine->vintage ? ' (' . $wine->vintage . ')' : '' }}
                                    </option>
                                @endforeach
                            </flux:select>
                            <flux:error name="sources.{{ $index }}.wine_id" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Depósito origen') }}</flux:label>
                            <flux:select wire:model="sources.{{ $index }}.from_container_id">
                                <option value="">{{ __('Sin depósito origen') }}</option>
                                @foreach($containers as $container)
                                    <option value="{{ $container->id }}">{{ $container->name }}</option>
                                @endforeach
                            </flux:select>
                            <flux:error name="sources.{{ $index }}.from_container_id" />
                        </flux:field>

                        <div class="flex items-end gap-2">
                            <flux:field class="flex-1">
                                <flux:label required>{{ __('Cantidad') }}</flux:label>
                                <flux:input
                                    wire:model="sources.{{ $index }}.quantity"
                                    type="number"
                                    step="0.001"
                                    min="0.001"
                                    placeholder="0.000"
                                    required
                                />
                                <flux:error name="sources.{{ $index }}.quantity" />
                            </flux:field>

                            @if(count($sources) > 1)
                                <button
                                    type="button"
                                    wire:click="removeSource({{ $index }})"
                                    title="{{ __('Eliminar fila') }}"
                                    class="mb-0.5 p-2 rounded-lg text-red-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                >
                                    <flux:icon icon="trash" class="size-4" />
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach

                <flux:error name="sources" />

                <flux:button
                    type="button"
                    wire:click="addSource"
                    variant="ghost"
                    icon="plus"
                    size="sm"
                >{{ __('Añadir vino fuente') }}</flux:button>
            </div>
        </x-agro.form-section>

        {{-- Notas ───────────────────────────────────────────────────────── --}}
        <x-agro.form-section title="{{ __('Notas') }}" color="zinc">
            <flux:field>
                <flux:label>{{ __('Observaciones') }}</flux:label>
                <flux:textarea
                    wire:model="notes"
                    rows="3"
                    placeholder="{{ __('Proporciones buscadas, objetivo del coupage, características organolépticas...') }}"
                />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions :back-url="roleRoute('coupage.index')" submit-:label="__('Registrar coupage')" />
    </form>
</x-agro.form-card>
