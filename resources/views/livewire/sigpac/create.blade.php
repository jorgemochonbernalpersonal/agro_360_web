<div class="space-y-6">
    <x-agro.page-header
        title="SIGPAC"
        description="Crear Códigos SIGPAC - Añade códigos SIGPAC a la parcela. Completa cada campo según el formato SIGPAC."
    >
        <x-slot:actions>
            <flux:button href="{{ route('sigpac.codes') }}" variant="outline" icon="arrow-left" data-cy="back-button">
                Volver
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.card>
        <form wire:submit.prevent="save" class="space-y-8">
            <!-- Parcela OBLIGATORIA -->
            <x-agro.form-section title="Parcela">
                <flux:field>
                    <flux:label for="plot_id">Parcela *</flux:label>
                    <flux:select
                        wire:model.live="plot_id"
                        id="plot_id"
                        required
                        :disabled="!!request('plot_id')"
                    >
                        <option value="">Seleccionar parcela</option>
                        @foreach ($plots as $plot)
                            <option value="{{ $plot->id }}">
                                {{ $plot->name }} - {{ $plot->name }},
                                @if($plot->autonomousCommunity)
                                    Comunidad autónoma - {{ $plot->autonomousCommunity->name }},
                                @endif
                                @if($plot->province)
                                    Provincia - {{ $plot->province->name }},
                                @endif
                                @if($plot->municipality)
                                    Municipio - {{ $plot->municipality->name }}
                                @endif
                            </option>
                        @endforeach
                    </flux:select>
                    @if(request('plot_id'))
                        <p class="mt-1 text-xs text-zinc-500">
                            Parcela pre-seleccionada desde la vista de detalle
                        </p>
                    @endif
                    <flux:error name="plot_id" />
                </flux:field>
            </x-agro.form-section>

            <!-- Códigos SIGPAC dinámicos con cajitas por campo -->
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-zinc-800">Códigos SIGPAC</h2>
                    <span class="text-sm text-zinc-500">
                        {{ count($sigpacCodes) }} {{ count($sigpacCodes) === 1 ? 'código' : 'códigos' }}
                    </span>
                </div>

                @foreach ($sigpacCodes as $index => $sigpac)
                    @php
                        $isValid = $this->isCodeValid($index);
                        $hasDuplicate = $this->hasDuplicate($index);
                        $fullCode = $this->getFullCode($index);
                        $hasErrors = $errors->has("sigpacCodes.{$index}.*") || $hasDuplicate;
                    @endphp

                    <div class="bg-white rounded-2xl shadow-lg border-2 transition-all duration-300
                        @if($isValid && !$hasDuplicate)
                            border-green-400 shadow-green-100
                        @elseif($hasErrors || $hasDuplicate)
                            border-red-400 shadow-red-100
                        @else
                            border-zinc-200 hover:border-agro-500 hover:shadow-xl
                        @endif">

                        <!-- Header de la cajita -->
                        <div class="px-6 py-4 border-b border-zinc-100 flex items-center justify-between
                            @if($isValid && !$hasDuplicate) bg-green-50 @elseif($hasErrors || $hasDuplicate) bg-red-50 @else bg-zinc-50 @endif">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-10 h-10 rounded-full font-bold text-sm
                                    @if($isValid && !$hasDuplicate)
                                        bg-green-500 text-white
                                    @elseif($hasErrors || $hasDuplicate)
                                        bg-red-500 text-white
                                    @else
                                        bg-zinc-300 text-zinc-600
                                    @endif">
                                    {{ $index + 1 }}
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-zinc-800">
                                        Código SIGPAC #{{ $index + 1 }}
                                    </h3>
                                    @if($isValid && !$hasDuplicate)
                                        <p class="text-xs text-green-700 font-medium flex items-center gap-1 mt-0.5">
                                            <flux:icon icon="check-circle" class="size-4" />
                                            Código válido: <span class="font-mono">{{ $fullCode }}</span>
                                        </p>
                                    @elseif($hasDuplicate)
                                        <p class="text-xs text-red-700 font-medium flex items-center gap-1 mt-0.5">
                                            <flux:icon icon="x-circle" class="size-4" />
                                            Duplicado: Polígono, Parcela y Recinto ya existen en otro código
                                        </p>
                                    @elseif($hasErrors)
                                        <p class="text-xs text-red-700 font-medium flex items-center gap-1 mt-0.5">
                                            <flux:icon icon="x-circle" class="size-4" />
                                            Revisa los campos
                                        </p>
                                    @else
                                        <p class="text-xs text-zinc-500 mt-0.5">Completa todos los campos</p>
                                    @endif
                                </div>
                            </div>

                            @if(count($sigpacCodes) > 1)
                                <flux:button
                                    type="button"
                                    wire:click="removeSigpacCode({{ $index }})"
                                    variant="ghost"
                                    size="sm"
                                    icon="trash"
                                    class="text-red-600 hover:bg-red-50"
                                />
                            @endif
                        </div>

                        <!-- Campos del código en grid -->
                        <div class="p-6">
                            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
                                <!-- Comunidad Autónoma -->
                                <div>
                                    <flux:label for="sigpacCodes.{{ $index }}.code_autonomous_community">
                                        CA
                                        <span class="text-xs text-zinc-500">(2)</span>
                                    </flux:label>
                                    <flux:input
                                        wire:model.live="sigpacCodes.{{ $index }}.code_autonomous_community"
                                        type="text"
                                        placeholder="13"
                                        maxlength="2"
                                        class="font-mono text-center {{ $errors->has('sigpacCodes.' . $index . '.code_autonomous_community') ? 'border-red-400' : '' }}"
                                    />
                                    @error("sigpacCodes.{$index}.code_autonomous_community")
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Provincia -->
                                <div>
                                    <flux:label for="sigpacCodes.{{ $index }}.code_province">
                                        Provincia
                                        <span class="text-xs text-zinc-500">(2)</span>
                                    </flux:label>
                                    <flux:input
                                        wire:model.live="sigpacCodes.{{ $index }}.code_province"
                                        type="text"
                                        placeholder="28"
                                        maxlength="2"
                                        class="font-mono text-center {{ $errors->has('sigpacCodes.' . $index . '.code_province') ? 'border-red-400' : '' }}"
                                    />
                                    @error("sigpacCodes.{$index}.code_province")
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Municipio -->
                                <div>
                                    <flux:label for="sigpacCodes.{{ $index }}.code_municipality">
                                        Municipio
                                        <span class="text-xs text-zinc-500">(3)</span>
                                    </flux:label>
                                    <flux:input
                                        wire:model.live="sigpacCodes.{{ $index }}.code_municipality"
                                        type="text"
                                        placeholder="079"
                                        maxlength="3"
                                        class="font-mono text-center {{ $errors->has('sigpacCodes.' . $index . '.code_municipality') ? 'border-red-400' : '' }}"
                                    />
                                    @error("sigpacCodes.{$index}.code_municipality")
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Agregado -->
                                <div>
                                    <flux:label for="sigpacCodes.{{ $index }}.code_aggregate">
                                        Agregado
                                        <span class="text-xs text-zinc-500">(1)</span>
                                    </flux:label>
                                    <flux:input
                                        wire:model.live="sigpacCodes.{{ $index }}.code_aggregate"
                                        type="text"
                                        placeholder="0"
                                        maxlength="1"
                                        value="0"
                                        class="font-mono text-center"
                                    />
                                </div>

                                <!-- Zona -->
                                <div>
                                    <flux:label for="sigpacCodes.{{ $index }}.code_zone">
                                        Zona
                                        <span class="text-xs text-zinc-500">(1)</span>
                                    </flux:label>
                                    <flux:input
                                        wire:model.live="sigpacCodes.{{ $index }}.code_zone"
                                        type="text"
                                        placeholder="0"
                                        maxlength="1"
                                        class="font-mono text-center {{ $errors->has('sigpacCodes.' . $index . '.code_zone') ? 'border-red-400' : '' }}"
                                    />
                                    @error("sigpacCodes.{$index}.code_zone")
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Polígono -->
                                <div>
                                    <flux:label for="sigpacCodes.{{ $index }}.code_polygon">
                                        Polígono
                                        <span class="text-xs text-zinc-500">(2)</span>
                                    </flux:label>
                                    <flux:input
                                        wire:model.live="sigpacCodes.{{ $index }}.code_polygon"
                                        type="text"
                                        placeholder="12"
                                        maxlength="2"
                                        class="font-mono text-center {{ $errors->has('sigpacCodes.' . $index . '.code_polygon') || $hasDuplicate ? 'border-red-400' : '' }} {{ $hasDuplicate ? 'ring-2 ring-red-200' : '' }}"
                                    />
                                    @error("sigpacCodes.{$index}.code_polygon")
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                    @if($hasDuplicate)
                                        <p class="mt-1 text-xs text-red-600 font-medium">
                                            Duplicado con otro código
                                        </p>
                                    @endif
                                </div>

                                <!-- Parcela -->
                                <div>
                                    <flux:label for="sigpacCodes.{{ $index }}.code_plot">
                                        Parcela
                                        <span class="text-xs text-zinc-500">(5)</span>
                                    </flux:label>
                                    <flux:input
                                        wire:model.live="sigpacCodes.{{ $index }}.code_plot"
                                        type="text"
                                        placeholder="00045"
                                        maxlength="5"
                                        class="font-mono text-center {{ $errors->has('sigpacCodes.' . $index . '.code_plot') || $hasDuplicate ? 'border-red-400' : '' }} {{ $hasDuplicate ? 'ring-2 ring-red-200' : '' }}"
                                    />
                                    @error("sigpacCodes.{$index}.code_plot")
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Recinto -->
                                <div>
                                    <flux:label for="sigpacCodes.{{ $index }}.code_enclosure">
                                        Recinto
                                        <span class="text-xs text-zinc-500">(3)</span>
                                    </flux:label>
                                    <flux:input
                                        wire:model.live="sigpacCodes.{{ $index }}.code_enclosure"
                                        type="text"
                                        placeholder="003"
                                        maxlength="3"
                                        class="font-mono text-center {{ $errors->has('sigpacCodes.' . $index . '.code_enclosure') || $hasDuplicate ? 'border-red-400' : '' }} {{ $hasDuplicate ? 'ring-2 ring-red-200' : '' }}"
                                    />
                                    @error("sigpacCodes.{$index}.code_enclosure")
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Vista previa del código completo -->
                            @if($isValid && $fullCode && !$hasDuplicate)
                                <div class="mt-4 p-4 bg-gradient-to-br from-green-50 to-green-100 border-2 border-green-300 rounded-xl">
                                    <div class="flex items-center gap-2 mb-2">
                                        <flux:icon icon="check-circle" class="size-5 text-green-600" />
                                        <p class="text-sm font-bold text-green-800">Código completo generado</p>
                                    </div>
                                    <p class="text-lg font-mono font-bold text-green-900">
                                        {{ $fullCode }}
                                    </p>
                                    <p class="text-xs text-green-700 mt-1">
                                        Formato con guiones:
                                        <span class="font-mono">
                                            {{ substr($fullCode, 0, 2) }}-{{ substr($fullCode, 2, 2) }}-{{ substr($fullCode, 4, 3) }}-{{ substr($fullCode, 7, 1) }}-{{ substr($fullCode, 8, 1) }}-{{ substr($fullCode, 9, 2) }}-{{ substr($fullCode, 11, 5) }}-{{ substr($fullCode, 16, 3) }}
                                        </span>
                                    </p>
                                </div>
                            @endif

                            <!-- Mensaje de duplicado -->
                            @if($hasDuplicate)
                                <div class="mt-4 p-4 bg-red-50 border-2 border-red-300 rounded-xl">
                                    <div class="flex items-center gap-2 mb-2">
                                        <flux:icon icon="x-circle" class="size-5 text-red-600" />
                                        <p class="text-sm font-bold text-red-800">Código duplicado</p>
                                    </div>
                                    <p class="text-sm text-red-700">
                                        Este código tiene el mismo <strong>Polígono</strong>, <strong>Parcela</strong> y <strong>Recinto</strong> que otro código en el formulario.
                                        Al menos uno de estos tres campos debe ser diferente.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Botón para añadir más códigos -->
            <button
                type="button"
                wire:click="addSigpacCode"
                class="w-full py-4 border-2 border-dashed border-zinc-300 rounded-xl text-zinc-600 hover:border-agro-500 hover:text-agro-600 hover:bg-agro-50 transition-all font-semibold"
            >
                <span class="flex items-center justify-center gap-2">
                    <flux:icon icon="plus" class="size-5" />
                    Añadir otro código SIGPAC
                </span>
            </button>

            <!-- Botones de acción -->
            <div class="flex justify-end gap-4 pt-6 border-t border-zinc-200">
                <flux:button href="{{ route('sigpac.codes') }}" variant="outline">
                    Cancelar
                </flux:button>
                <flux:button type="submit" variant="primary">
                    Crear {{ count($sigpacCodes) > 1 ? 'Códigos' : 'Código' }} SIGPAC
                </flux:button>
            </div>
        </form>
    </x-agro.card>
</div>
