<div>
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>';
    @endphp

    <x-form-card 
        title="Crear Códigos SIGPAC" 
        description="Añade uno o más códigos SIGPAC a la parcela. Completa cada campo según el formato SIGPAC."
        :icon="$icon"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]" 
        :back-url="route('sigpac.codes')"
    >
        <form wire:submit.prevent="save" class="space-y-8">
            <!-- Parcela OBLIGATORIA -->
            <x-form-section title="Parcela" color="blue">
                <div>
                    <x-label for="plot_id" required>Parcela</x-label>
                    <x-select 
                        wire:model="plot_id" 
                        id="plot_id" 
                        :error="$errors->first('plot_id')"
                        required
                        :disabled="!!request('plot_id')"
                    >
                        <option value="">Seleccionar parcela</option>
                        @foreach ($plots as $plot)
                            <option value="{{ $plot->id }}">
                                {{ $plot->name }} 
                                @if($plot->municipality)
                                    - {{ $plot->municipality->name }}
                                @endif
                            </option>
                        @endforeach
                    </x-select>
                    @if(request('plot_id'))
                        <p class="mt-1 text-xs text-gray-500">
                            Parcela pre-seleccionada desde la vista de detalle
                        </p>
                    @endif
                    @error('plot_id')
                        <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </x-form-section>

            <!-- Códigos SIGPAC dinámicos con cajitas por campo -->
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-800">Códigos SIGPAC</h2>
                    <span class="text-sm text-gray-500">
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
                            border-gray-200 hover:border-[var(--color-agro-green)] hover:shadow-xl
                        @endif">
                        
                        <!-- Header de la cajita -->
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between
                            @if($isValid && !$hasDuplicate) bg-green-50 @elseif($hasErrors || $hasDuplicate) bg-red-50 @else bg-gray-50 @endif">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-10 h-10 rounded-full font-bold text-sm
                                    @if($isValid && !$hasDuplicate)
                                        bg-green-500 text-white
                                    @elseif($hasErrors || $hasDuplicate)
                                        bg-red-500 text-white
                                    @else
                                        bg-gray-300 text-gray-600
                                    @endif">
                                    {{ $index + 1 }}
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-800">
                                        Código SIGPAC #{{ $index + 1 }}
                                    </h3>
                                    @if($isValid && !$hasDuplicate)
                                        <p class="text-xs text-green-700 font-medium flex items-center gap-1 mt-0.5">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            Código válido: <span class="font-mono">{{ $fullCode }}</span>
                                        </p>
                                    @elseif($hasDuplicate)
                                        <p class="text-xs text-red-700 font-medium flex items-center gap-1 mt-0.5">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                            Duplicado: Polígono, Parcela y Recinto ya existen en otro código
                                        </p>
                                    @elseif($hasErrors)
                                        <p class="text-xs text-red-700 font-medium flex items-center gap-1 mt-0.5">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                            Revisa los campos
                                        </p>
                                    @else
                                        <p class="text-xs text-gray-500 mt-0.5">Completa todos los campos</p>
                                    @endif
                                </div>
                            </div>
                            
                            @if(count($sigpacCodes) > 1)
                                <button 
                                    type="button"
                                    wire:click="removeSigpacCode({{ $index }})"
                                    class="p-2 rounded-lg text-red-600 hover:bg-red-100 hover:text-red-800 transition-colors"
                                    title="Eliminar código"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            @endif
                        </div>

                        <!-- Campos del código en grid -->
                        <div class="p-6">
                            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
                                <!-- Comunidad Autónoma -->
                                <div>
                                    <x-label for="sigpacCodes.{{ $index }}.code_autonomous_community" required>
                                        CA
                                        <span class="text-xs text-gray-500">(2)</span>
                                    </x-label>
                                    <x-input 
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
                                    <x-label for="sigpacCodes.{{ $index }}.code_province" required>
                                        Provincia
                                        <span class="text-xs text-gray-500">(2)</span>
                                    </x-label>
                                    <x-input 
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
                                    <x-label for="sigpacCodes.{{ $index }}.code_municipality" required>
                                        Municipio
                                        <span class="text-xs text-gray-500">(3)</span>
                                    </x-label>
                                    <x-input 
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
                                    <x-label for="sigpacCodes.{{ $index }}.code_aggregate">
                                        Agregado
                                        <span class="text-xs text-gray-500">(1)</span>
                                    </x-label>
                                    <x-input 
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
                                    <x-label for="sigpacCodes.{{ $index }}.code_zone" required>
                                        Zona
                                        <span class="text-xs text-gray-500">(1)</span>
                                    </x-label>
                                    <x-input 
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
                                    <x-label for="sigpacCodes.{{ $index }}.code_polygon" required>
                                        Polígono
                                        <span class="text-xs text-gray-500">(2)</span>
                                    </x-label>
                                    <x-input 
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
                                            ⚠️ Duplicado con otro código
                                        </p>
                                    @endif
                                </div>

                                <!-- Parcela -->
                                <div>
                                    <x-label for="sigpacCodes.{{ $index }}.code_plot" required>
                                        Parcela
                                        <span class="text-xs text-gray-500">(5)</span>
                                    </x-label>
                                    <x-input 
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
                                    <x-label for="sigpacCodes.{{ $index }}.code_enclosure" required>
                                        Recinto
                                        <span class="text-xs text-gray-500">(3)</span>
                                    </x-label>
                                    <x-input 
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
                                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
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
                                        <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
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
                class="w-full py-4 border-2 border-dashed border-gray-300 
                       rounded-xl text-gray-600 hover:border-[var(--color-agro-green)] 
                       hover:text-[var(--color-agro-green)] hover:bg-[var(--color-agro-green-bg)]
                       transition-all font-semibold group"
            >
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-5 h-5 group-hover:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Añadir otro código SIGPAC
                </span>
            </button>

            <!-- Botones de acción -->
            <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
                <a 
                    href="{{ route('sigpac.codes') }}"
                    class="px-6 py-3 rounded-xl border-2 border-gray-300 text-gray-700 hover:bg-gray-50 transition-all font-semibold"
                >
                    Cancelar
                </a>
                <button 
                    type="submit"
                    class="px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all font-semibold shadow-lg hover:shadow-xl"
                >
                    Crear {{ count($sigpacCodes) > 1 ? 'Códigos' : 'Código' }} SIGPAC
                </button>
            </div>
        </form>
    </x-form-card>
</div>
