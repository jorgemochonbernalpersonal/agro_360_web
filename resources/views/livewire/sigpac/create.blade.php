<div>
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>';
    @endphp

    <x-form-card 
        title="Crear Código SIGPAC" 
        description="Crea un nuevo código de identificación SIGPAC" 
        :icon="$icon"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]" 
        :back-url="route('sigpac.codes')"
    >
        <form wire:submit.prevent="save" class="space-y-8">
            <x-form-section title="Información del Código" color="green">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Código principal -->
                    <div>
                        <x-label for="code" required>Código</x-label>
                        <x-input 
                            wire:model="code" 
                            id="code" 
                            type="text"
                            placeholder="Ej: 123456789"
                            :error="$errors->first('code')"
                        />
                        @error('code')
                            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Código Polígono -->
                    <div>
                        <x-label for="code_polygon">Código Polígono</x-label>
                        <x-input 
                            wire:model="code_polygon" 
                            id="code_polygon" 
                            type="text"
                            maxlength="10"
                            :error="$errors->first('code_polygon')"
                        />
                    </div>

                    <!-- Código Parcela -->
                    <div>
                        <x-label for="code_plot">Código Parcela</x-label>
                        <x-input 
                            wire:model="code_plot" 
                            id="code_plot" 
                            type="text"
                            maxlength="10"
                            :error="$errors->first('code_plot')"
                        />
                    </div>

                    <!-- Código Recinto -->
                    <div>
                        <x-label for="code_enclosure">Código Recinto</x-label>
                        <x-input 
                            wire:model="code_enclosure" 
                            id="code_enclosure" 
                            type="text"
                            maxlength="10"
                            :error="$errors->first('code_enclosure')"
                        />
                    </div>

                    <!-- Código Agregado -->
                    <div>
                        <x-label for="code_aggregate">Código Agregado</x-label>
                        <x-input 
                            wire:model="code_aggregate" 
                            id="code_aggregate" 
                            type="text"
                            maxlength="10"
                            :error="$errors->first('code_aggregate')"
                        />
                    </div>

                    <!-- Código Provincia -->
                    <div>
                        <x-label for="code_province">Código Provincia</x-label>
                        <x-input 
                            wire:model="code_province" 
                            id="code_province" 
                            type="text"
                            maxlength="10"
                            :error="$errors->first('code_province')"
                        />
                    </div>

                    <!-- Código Zona -->
                    <div>
                        <x-label for="code_zone">Código Zona</x-label>
                        <x-input 
                            wire:model="code_zone" 
                            id="code_zone" 
                            type="text"
                            maxlength="10"
                            :error="$errors->first('code_zone')"
                        />
                    </div>

                    <!-- Código Municipio -->
                    <div>
                        <x-label for="code_municipality">Código Municipio</x-label>
                        <x-input 
                            wire:model="code_municipality" 
                            id="code_municipality" 
                            type="text"
                            maxlength="10"
                            :error="$errors->first('code_municipality')"
                        />
                    </div>
                </div>
            </x-form-section>

            <!-- Selección de Parcela (Opcional) -->
            <x-form-section title="Asociar a Parcela (Opcional)" color="blue">
                <div>
                    <x-label for="plot_id">Parcela</x-label>
                    <x-select 
                        wire:model="plot_id" 
                        id="plot_id" 
                        :error="$errors->first('plot_id')"
                    >
                        <option value="">Seleccionar parcela (opcional)</option>
                        @foreach ($plots as $plot)
                            <option value="{{ $plot->id }}">
                                {{ $plot->name }} 
                                @if($plot->municipality)
                                    - {{ $plot->municipality->name }}
                                @endif
                            </option>
                        @endforeach
                    </x-select>
                    <p class="mt-1 text-xs text-gray-500">
                        Si seleccionas una parcela, se asociará automáticamente y podrás configurar su geometría.
                    </p>
                    @error('plot_id')
                        <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
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
                    Crear Código SIGPAC
                </button>
            </div>
        </form>
    </x-form-card>
</div>

