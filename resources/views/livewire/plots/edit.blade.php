@php
    $plotIcon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>';
@endphp

<x-form-card
    title="Editar Parcela"
    description="Modifica los datos de la parcela"
    :icon="$plotIcon"
    icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    :back-url="route('plots.index')"
>
    <form wire:submit="update" class="space-y-8">
        <x-form-section title="Información Básica" color="green">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre -->
                    <div>
                        <x-label for="name" required>Nombre de la Parcela</x-label>
                        <x-input 
                            wire:model="name" 
                            type="text" 
                            id="name"
                            :error="$errors->first('name')"
                            required
                        />
                    </div>

                    <!-- Área -->
                    <div>
                        <x-label for="area">Área (hectáreas)</x-label>
                        <x-input 
                            wire:model="area" 
                            type="number" 
                            step="0.001"
                            id="area"
                            :error="$errors->first('area')"
                        />
                    </div>
                </div>

                <!-- Descripción -->
                <div class="mt-6">
                    <x-label for="description">Descripción</x-label>
                    <x-textarea 
                        wire:model="description" 
                        id="description"
                        rows="3"
                        :error="$errors->first('description')"
                    />
                </div>

                <!-- Activa -->
                <div class="mt-6">
                    <label class="flex items-center">
                        <input 
                            wire:model="active" 
                            type="checkbox"
                            class="w-4 h-4 text-[var(--color-agro-green-dark)] border-gray-300 rounded focus:ring-[var(--color-agro-green-dark)]"
                        >
                        <span class="ml-2 text-sm font-semibold text-gray-700">Parcela activa</span>
                    </label>
                    @error('active') 
                        <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                    @enderror
                </div>
        </x-form-section>

        <!-- Asignaciones -->
        @if($this->canSelectWinery() || $this->canSelectViticulturist())
            <x-form-section title="Asignaciones" color="green">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Bodega (Solo admin/supervisor) -->
                        @if($this->canSelectWinery())
                            <div>
                                <x-label for="winery_id" required>Bodega</x-label>
                                <x-select 
                                    wire:model="winery_id" 
                                    id="winery_id"
                                    :error="$errors->first('winery_id')"
                                    required
                                >
                                    <option value="">Seleccionar bodega...</option>
                                    @foreach($this->wineries as $winery)
                                        <option value="{{ $winery->id }}">{{ $winery->name }}</option>
                                    @endforeach
                                </x-select>
                            </div>
                        @endif

                        <!-- Viticultor (Solo admin/supervisor/winery) -->
                        @if($this->canSelectViticulturist())
                            <div>
                                <x-label for="viticulturist_id">Viticultor Asignado</x-label>
                                <x-select 
                                    wire:model="viticulturist_id" 
                                    id="viticulturist_id"
                                    :error="$errors->first('viticulturist_id')"
                                >
                                    <option value="">Sin asignar</option>
                                    @foreach($this->viticulturists as $viticulturist)
                                        <option value="{{ $viticulturist->id }}">{{ $viticulturist->name }}</option>
                                    @endforeach
                                </x-select>
                            </div>
                        @endif
                    </div>
            </x-form-section>
        @endif

        <!-- Ubicación -->
        @if($this->canSelectLocation())
            <x-form-section title="Ubicación" color="green">
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Comunidad Autónoma -->
                        <div>
                            <x-label for="autonomous_community_id" required>Comunidad Autónoma</x-label>
                            <x-select 
                                wire:model.live="autonomous_community_id" 
                                id="autonomous_community_id"
                                :error="$errors->first('autonomous_community_id')"
                                required
                            >
                                <option value="">Seleccionar...</option>
                                @foreach($autonomousCommunities as $community)
                                    <option value="{{ $community->id }}">{{ $community->name }}</option>
                                @endforeach
                            </x-select>
                        </div>

                        <!-- Provincia -->
                        <div>
                            <x-label for="province_id" required>Provincia</x-label>
                            <x-select 
                                wire:model.live="province_id" 
                                id="province_id"
                                :error="$errors->first('province_id')"
                                required
                                @if(!$autonomous_community_id) disabled @endif
                            >
                                <option value="">Seleccionar...</option>
                                @foreach($provinces as $province)
                                    <option value="{{ $province->id }}">{{ $province->name }}</option>
                                @endforeach
                            </x-select>
                        </div>

                        <!-- Municipio -->
                        <div>
                            <x-label for="municipality_id" required>Municipio</x-label>
                            <x-select 
                                wire:model.live="municipality_id" 
                                id="municipality_id"
                                :error="$errors->first('municipality_id')"
                                required
                                @if(!$province_id) disabled @endif
                            >
                                <option value="">Seleccionar...</option>
                                @foreach($municipalities as $municipality)
                                    <option value="{{ $municipality->id }}">{{ $municipality->name }}</option>
                                @endforeach
                            </x-select>
                        </div>
                    </div>
            </x-form-section>
        @endif

        <!-- SIGPAC -->
        @if($this->canSelectSigpac())
            <x-form-section title="Datos SIGPAC" color="green">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Usos SIGPAC -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Usos SIGPAC * (múltiple)
                            </label>
                            <select 
                                wire:model="sigpac_use" 
                                multiple
                                size="8"
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-[var(--color-agro-green-dark)] transition bg-gray-50 focus:bg-white"
                                required
                            >
                                @foreach($sigpacUses as $use)
                                    <option value="{{ $use->id }}">{{ $use->code }} - {{ $use->description }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Mantén presionado Ctrl (Cmd en Mac) para seleccionar múltiples</p>
                            @error('sigpac_use') 
                                <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                            @enderror
                        </div>

                        <!-- Códigos SIGPAC -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Códigos SIGPAC * (múltiple)
                            </label>
                            <select 
                                wire:model="sigpac_code" 
                                multiple
                                size="8"
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-[var(--color-agro-green-dark)] transition bg-gray-50 focus:bg-white"
                                required
                            >
                                @foreach($sigpacCodes as $code)
                                    <option value="{{ $code->id }}">{{ $code->code }}@if($code->description) - {{ $code->description }}@endif</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Mantén presionado Ctrl (Cmd en Mac) para seleccionar múltiples</p>
                            @error('sigpac_code') 
                                <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                            @enderror
                        </div>
                    </div>
            </x-form-section>

            <!-- Coordenadas Multiparte -->
            <x-form-section title="Coordenadas Multiparte" color="green">
                <div class="flex justify-end mb-4">
                        <button 
                            type="button"
                            wire:click="addCoordinate"
                            class="px-4 py-2 bg-[var(--color-agro-green-dark)] text-white rounded-lg hover:bg-[var(--color-agro-green)] transition text-sm font-semibold"
                        >
                            + Agregar Coordenadas
                        </button>
                    </div>

                    @foreach($multipart_coordinates as $index => $coord)
                        <div class="mb-4 p-4 border border-gray-200 rounded-lg">
                            <div class="flex justify-between items-start mb-3">
                                <span class="text-sm font-semibold text-gray-700">Coordenadas #{{ $index + 1 }}</span>
                                <button 
                                    type="button"
                                    wire:click="removeCoordinate({{ $index }})"
                                    class="text-red-600 hover:text-red-800 text-sm"
                                >
                                    Eliminar
                                </button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Coordenadas
                                    </label>
                                    <textarea 
                                        wire:model="multipart_coordinates.{{ $index }}.coordinates" 
                                        rows="3"
                                        class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-[var(--color-agro-green-dark)] transition bg-gray-50 focus:bg-white"
                                        placeholder="Coordenadas GPS..."
                                    ></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Código SIGPAC (opcional)
                                    </label>
                                    <select 
                                        wire:model="multipart_coordinates.{{ $index }}.sigpac_code_id" 
                                        class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-[var(--color-agro-green-dark)] transition bg-gray-50 focus:bg-white"
                                    >
                                        <option value="">Sin código</option>
                                        @foreach($sigpacCodes as $code)
                                            <option value="{{ $code->id }}">{{ $code->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    @endforeach
            </x-form-section>
        @endif

        <x-form-actions 
            :cancel-url="route('plots.index')"
            submit-label="Actualizar Parcela"
        />
    </form>
</x-form-card>
