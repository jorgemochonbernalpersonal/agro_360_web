<div>
    @php
        $plotIcon =
            '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>';
    @endphp

    <x-form-card title="Nueva Parcela" description="Crea una nueva parcela agrícola" :icon="$plotIcon"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]" :back-url="route('plots.index')">
        <form wire:submit.prevent="save" class="space-y-8" data-cy="plot-create-form">
            @error('general')
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                        <div>
                            <h3 class="text-sm font-semibold text-red-800">Error</h3>
                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        </div>
                    </div>
                </div>
            @enderror

            <x-form-section title="Información Básica" color="green">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre -->
                    <div>
                        <x-label for="name" required>Nombre de la Parcela</x-label>
                        <x-input wire:model="name" type="text" id="name" data-cy="plot-name" placeholder="Ej: Parcela Norte"
                            :error="$errors->first('name')" required />
                    </div>

                    <!-- Área -->
                    <div>
                        <x-label for="area">Área (hectáreas)</x-label>
                        <x-input wire:model="area" type="number" step="0.001" id="area" data-cy="plot-area" placeholder="0.000"
                            :error="$errors->first('area')" />
                    </div>
                </div>

                <!-- Descripción -->
                <div class="mt-6">
                    <x-label for="description">Descripción</x-label>
                    <x-textarea wire:model="description" id="description" data-cy="plot-description" rows="3"
                        placeholder="Descripción de la parcela..." :error="$errors->first('description')" />
                </div>
            </x-form-section>

            <!-- Asignaciones -->
            @if ($this->canSelectWinery() || $this->canSelectViticulturist() || $this->canSelectSigpac())
                <x-form-section title="Asignaciones" color="green">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Bodega removed: plots now belong to viticultor, not directly to a winery -->

                        <!-- Viticultor (Solo admin/supervisor/winery) -->
                        @if (in_array(auth()->user()->role, ['admin', 'supervisor', 'winery', 'viticulturist']))
                            <div>
                                <x-label for="viticulturist_id" required>Viticultor Asignado</x-label>
                                <x-select wire:model="viticulturist_id" id="viticulturist_id" data-cy="plot-viticulturist-id" :error="$errors->first('viticulturist_id')" required>
                                    <option value="">Seleccionar...</option>
                                    @forelse ($this->viticulturists as $viticulturist)
                                        <option value="{{ $viticulturist->id }}">{{ $viticulturist->name }}</option>
                                    @empty
                                        <option value="" disabled>No hay viticultores disponibles</option>
                                    @endforelse
                                </x-select>
                                @error('viticulturist_id')
                                    <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <!-- Usos SIGPAC (select múltiple junto al viticultor) -->
                        @if ($this->canSelectSigpac())
                            <div>
                                <x-label for="sigpac_use" required>Usos SIGPAC</x-label>
                                <x-select wire:model="sigpac_use" id="sigpac_use" data-cy="plot-sigpac-use" multiple size="5"
                                    :error="$errors->first('sigpac_use')" required>
                                    @forelse ($sigpacUses as $use)
                                        <option value="{{ $use->id }}">
                                            {{ $use->code }} - {{ $use->description }}
                                        </option>
                                    @empty
                                        <option value="" disabled>No hay usos SIGPAC disponibles</option>
                                    @endforelse
                                </x-select>
                                <p class="mt-1 text-xs text-gray-500">
                                    Mantén pulsado Ctrl (o Cmd en Mac) para seleccionar varios usos.
                                </p>
                                @error('sigpac_use')
                                    <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                    </div>
                </x-form-section>
            @endif

            <!-- Ubicación -->
            <x-form-section title="Ubicación" color="green">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Comunidad Autónoma -->
                    <div>
                        <x-label for="autonomous_community_id" required>Comunidad Autónoma</x-label>
                        <x-select wire:model.live="autonomous_community_id" id="autonomous_community_id" data-cy="plot-autonomous-community-id"
                            :error="$errors->first('autonomous_community_id')" required>
                            <option value="">Seleccionar...</option>
                            @foreach ($autonomousCommunities as $community)
                                <option value="{{ $community->id }}">
                                    {{ $community->code === '15' ? 'Comunidad Foral de Navarra' : $community->name }}
                                </option>
                            @endforeach
                        </x-select>
                    </div>

                    <!-- Provincia -->
                    <div>
                        <x-label for="province_id" required>Provincia</x-label>
                        <x-select wire:model.live="province_id" id="province_id" data-cy="plot-province-id" :error="$errors->first('province_id')" required
                            :disabled="!$autonomous_community_id">
                            <option value="">Seleccionar...</option>
                            @foreach ($provinces as $province)
                                <option value="{{ $province->id }}">{{ $province->name }}</option>
                            @endforeach
                        </x-select>
                    </div>

                    <!-- Municipio -->
                    <div>
                        <x-label for="municipality_id" required>Municipio</x-label>
                        <x-select wire:model.live="municipality_id" id="municipality_id" data-cy="plot-municipality-id" :error="$errors->first('municipality_id')" required
                            :disabled="!$province_id">
                            <option value="">Seleccionar...</option>
                            @foreach ($municipalities as $municipality)
                                <option value="{{ $municipality->id }}">{{ $municipality->name }}</option>
                            @endforeach
                        </x-select>
                    </div>
                </div>
            </x-form-section>

            <x-form-actions :cancel-url="route('plots.index')" submit-label="Crear Parcela" />
        </form>
    </x-form-card>
</div>
