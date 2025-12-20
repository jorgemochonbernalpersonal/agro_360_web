<div>
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>';
    @endphp

    <x-form-card 
        title="Crear Códigos SIGPAC" 
        description="Añade uno o más códigos SIGPAC a la parcela. Puedes usar formato con guiones (28-005-1-0032-015-002) o sin guiones (2800510032015002)"
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

            <!-- Códigos SIGPAC dinámicos -->
            <x-form-section title="Códigos SIGPAC" color="green">
                <div class="space-y-4">
                    @foreach ($sigpacCodes as $index => $sigpac)
                        <div class="border-2 border-gray-200 rounded-xl p-6 
                                    hover:border-[var(--color-agro-green)] transition-colors
                                    {{ $errors->has("sigpacCodes.{$index}.code") ? 'border-red-300' : '' }}">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-700">
                                    Código SIGPAC #{{ $index + 1 }}
                                </h3>
                                @if(count($sigpacCodes) > 1)
                                    <button 
                                        type="button"
                                        wire:click="removeSigpacCode({{ $index }})"
                                        class="text-red-600 hover:text-red-800 p-2 rounded-lg hover:bg-red-50 transition-colors"
                                        title="Eliminar código"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                            
                            <div>
                                <x-label for="sigpacCodes.{{ $index }}.code" required>
                                    Código SIGPAC completo
                                </x-label>
                                <x-input 
                                    wire:model="sigpacCodes.{{ $index }}.code" 
                                    type="text"
                                    placeholder="Ej: 28-005-1-0032-015-002 o 2800510032015002"
                                    maxlength="19"
                                    class="font-mono"
                                    :error="$errors->first('sigpacCodes.' . $index . '.code')"
                                />
                                <p class="mt-1 text-xs text-gray-500">
                                    Formato: Provincia(2) - Municipio(3) - Zona(1) - Polígono(4) - Parcela(3) - Recinto(3)
                                    <br>
                                    Ejemplo: <span class="font-mono">28-005-1-0032-015-002</span>
                                </p>
                                
                                @if($sigpac['code'] && strlen(preg_replace('/[-\s]/', '', $sigpac['code'])) === 16)
                                    @php
                                        try {
                                            $parsed = \App\Models\SigpacCode::parseSigpacCode($sigpac['code']);
                                        } catch (\Exception $e) {
                                            $parsed = null;
                                        }
                                    @endphp
                                    
                                    @if($parsed)
                                        <div class="mt-2 p-3 bg-green-50 border border-green-200 rounded-lg">
                                            <p class="text-xs font-semibold text-green-800 mb-2">Vista previa del parseo:</p>
                                            <div class="grid grid-cols-2 gap-2 text-xs">
                                                <div><span class="font-semibold">Provincia:</span> {{ $parsed['code_province'] }}</div>
                                                <div><span class="font-semibold">Municipio:</span> {{ $parsed['code_municipality'] }}</div>
                                                <div><span class="font-semibold">Zona:</span> {{ $parsed['code_zone'] }}</div>
                                                <div><span class="font-semibold">Polígono:</span> {{ $parsed['code_polygon'] }}</div>
                                                <div><span class="font-semibold">Parcela:</span> {{ $parsed['code_plot'] }}</div>
                                                <div><span class="font-semibold">Recinto:</span> {{ $parsed['code_enclosure'] }}</div>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                                
                                @error("sigpacCodes.{$index}.code")
                                    <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <button 
                    type="button"
                    wire:click="addSigpacCode"
                    class="mt-4 w-full py-3 border-2 border-dashed border-gray-300 
                           rounded-xl text-gray-600 hover:border-[var(--color-agro-green)] 
                           hover:text-[var(--color-agro-green)] transition-all font-medium"
                >
                    <span class="flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Añadir otro código SIGPAC
                    </span>
                </button>
            </x-form-section>

            <!-- Botones -->
            <div class="flex justify-end gap-4">
                <a 
                    href="{{ route('sigpac.codes') }}"
                    class="px-6 py-3 rounded-xl border-2 border-gray-300 text-gray-700 hover:bg-gray-50 transition-all font-semibold"
                >
                    Cancelar
                </a>
                <button 
                    type="submit"
                    class="px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all font-semibold"
                >
                    Crear {{ count($sigpacCodes) > 1 ? 'Códigos' : 'Código' }} SIGPAC
                </button>
            </div>
        </form>
    </x-form-card>
</div>
