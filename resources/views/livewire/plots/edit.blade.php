<div class="p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-[var(--color-agro-green-dark)]">Editar Parcela</h1>
        <p class="text-gray-600 mt-1">Modifica los datos de la parcela</p>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-8 max-w-4xl">
        <form wire:submit="update" class="space-y-6">
            <!-- Información Básica -->
            <div class="border-b pb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Información Básica</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre -->
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                            Nombre de la Parcela *
                        </label>
                        <input 
                            wire:model="name" 
                            type="text" 
                            id="name"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-[var(--color-agro-green-dark)] transition bg-gray-50 focus:bg-white"
                            required
                        >
                        @error('name') 
                            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                        @enderror
                    </div>

                    <!-- Área -->
                    <div>
                        <label for="area" class="block text-sm font-semibold text-gray-700 mb-2">
                            Área (hectáreas)
                        </label>
                        <input 
                            wire:model="area" 
                            type="number" 
                            step="0.001"
                            id="area"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-[var(--color-agro-green-dark)] transition bg-gray-50 focus:bg-white"
                        >
                        @error('area') 
                            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                        @enderror
                    </div>
                </div>

                <!-- Descripción -->
                <div class="mt-6">
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                        Descripción
                    </label>
                    <textarea 
                        wire:model="description" 
                        id="description"
                        rows="3"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-[var(--color-agro-green-dark)] transition bg-gray-50 focus:bg-white"
                    ></textarea>
                    @error('description') 
                        <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                    @enderror
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
            </div>

            <!-- Asignaciones -->
            @if($this->canSelectWinery() || $this->canSelectViticulturist())
                <div class="border-b pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Asignaciones</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Bodega (Solo admin/supervisor) -->
                        @if($this->canSelectWinery())
                            <div>
                                <label for="winery_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Bodega *
                                </label>
                                <select 
                                    wire:model="winery_id" 
                                    id="winery_id"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-[var(--color-agro-green-dark)] transition bg-gray-50 focus:bg-white"
                                    required
                                >
                                    <option value="">Seleccionar bodega...</option>
                                    @foreach($this->wineries as $winery)
                                        <option value="{{ $winery->id }}">{{ $winery->name }}</option>
                                    @endforeach
                                </select>
                                @error('winery_id') 
                                    <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                                @enderror
                            </div>
                        @endif

                        <!-- Viticultor (Solo admin/supervisor/winery) -->
                        @if($this->canSelectViticulturist())
                            <div>
                                <label for="viticulturist_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Viticultor Asignado
                                </label>
                                <select 
                                    wire:model="viticulturist_id" 
                                    id="viticulturist_id"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-[var(--color-agro-green-dark)] transition bg-gray-50 focus:bg-white"
                                >
                                    <option value="">Sin asignar</option>
                                    @foreach($this->viticulturists as $viticulturist)
                                        <option value="{{ $viticulturist->id }}">{{ $viticulturist->name }}</option>
                                    @endforeach
                                </select>
                                @error('viticulturist_id') 
                                    <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                                @enderror
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Ubicación -->
            @if($this->canSelectLocation())
                <div class="border-b pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Ubicación</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Comunidad Autónoma -->
                        <div>
                            <label for="autonomous_community_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                Comunidad Autónoma *
                            </label>
                            <select 
                                wire:model.live="autonomous_community_id" 
                                id="autonomous_community_id"
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-[var(--color-agro-green-dark)] transition bg-gray-50 focus:bg-white"
                                required
                            >
                                <option value="">Seleccionar...</option>
                                @foreach($autonomousCommunities as $community)
                                    <option value="{{ $community->id }}">{{ $community->name }}</option>
                                @endforeach
                            </select>
                            @error('autonomous_community_id') 
                                <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                            @enderror
                        </div>

                        <!-- Provincia -->
                        <div>
                            <label for="province_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                Provincia *
                            </label>
                            <select 
                                wire:model.live="province_id" 
                                id="province_id"
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-[var(--color-agro-green-dark)] transition bg-gray-50 focus:bg-white disabled:opacity-50 disabled:cursor-not-allowed"
                                required
                                @if(!$autonomous_community_id) disabled @endif
                            >
                                <option value="">Seleccionar...</option>
                                @foreach($provinces as $province)
                                    <option value="{{ $province->id }}">{{ $province->name }}</option>
                                @endforeach
                            </select>
                            @error('province_id') 
                                <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                            @enderror
                        </div>

                        <!-- Municipio -->
                        <div>
                            <label for="municipality_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                Municipio *
                            </label>
                            <select 
                                wire:model.live="municipality_id" 
                                id="municipality_id"
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-[var(--color-agro-green-dark)] transition bg-gray-50 focus:bg-white disabled:opacity-50 disabled:cursor-not-allowed"
                                required
                                @if(!$province_id) disabled @endif
                            >
                                <option value="">Seleccionar...</option>
                                @foreach($municipalities as $municipality)
                                    <option value="{{ $municipality->id }}">{{ $municipality->name }}</option>
                                @endforeach
                            </select>
                            @error('municipality_id') 
                                <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                            @enderror
                        </div>
                    </div>
                </div>
            @endif

            <!-- SIGPAC -->
            @if($this->canSelectSigpac())
                <div class="border-b pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Datos SIGPAC</h3>
                    
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
                </div>

                <!-- Coordenadas Multiparte -->
                <div class="border-b pb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Coordenadas Multiparte</h3>
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
                </div>
            @endif

            <!-- Botones -->
            <div class="flex gap-4 pt-4">
                <button 
                    type="submit"
                    class="flex-1 bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white py-3.5 px-4 rounded-lg font-bold hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-agro-green-dark)] transition-all transform hover:scale-[1.02] shadow-lg"
                >
                    Actualizar Parcela
                </button>
                <a 
                    href="{{ route('plots.index') }}" 
                    class="px-6 py-3.5 border-2 border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-50 transition"
                >
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
