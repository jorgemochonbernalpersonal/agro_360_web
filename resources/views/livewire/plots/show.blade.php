<div class="p-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-[var(--color-agro-green-dark)]">{{ $plot->name }}</h1>
            <p class="text-gray-600 mt-1">Detalles de la parcela</p>
        </div>
        <div class="flex gap-2">
            @can('update', $plot)
                <a href="{{ route('plots.edit', $plot) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    Editar
                </a>
            @endcan
            <a href="{{ route('plots.index') }}" class="border-2 border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition">
                Volver
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información Principal -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Información General -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-4">Información General</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-gray-500">Nombre</label>
                        <p class="text-gray-900 text-lg">{{ $plot->name }}</p>
                    </div>

                    @if($plot->area)
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Área</label>
                            <p class="text-gray-900">{{ number_format($plot->area, 3) }} hectáreas</p>
                        </div>
                    @endif

                    <div>
                        <label class="text-sm font-semibold text-gray-500">Estado</label>
                        <span class="inline-block px-3 py-1 text-sm font-semibold rounded-full
                            {{ $plot->active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}
                        ">
                            {{ $plot->active ? 'Activa' : 'Inactiva' }}
                        </span>
                    </div>

                    @if($plot->description)
                        <div class="md:col-span-2">
                            <label class="text-sm font-semibold text-gray-500">Descripción</label>
                            <p class="text-gray-900">{{ $plot->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Asignaciones -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-4">Asignaciones</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-gray-500">Bodega</label>
                        @php
                            $wineryName = '-';
                            if ($plot->viticulturist && $plot->viticulturist->wineries->isNotEmpty()) {
                                $wineryName = $plot->viticulturist->wineries->first()->name;
                            }
                        @endphp
                        <p class="text-gray-900">{{ $wineryName }}</p>
                    </div>

                    @if($plot->viticulturist)
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Viticultor Asignado</label>
                            <p class="text-gray-900">{{ $plot->viticulturist->name }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Ubicación -->
            @if($plot->autonomousCommunity)
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-4">Ubicación</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm font-semibold text-gray-500">Comunidad Autónoma</label>
                            <p class="text-gray-900">{{ $plot->autonomousCommunity->name }}</p>
                        </div>

                        @if($plot->province)
                            <div>
                                <label class="text-sm font-semibold text-gray-500">Provincia</label>
                                <p class="text-gray-900">{{ $plot->province->name }}</p>
                            </div>
                        @endif

                        @if($plot->municipality)
                            <div>
                                <label class="text-sm font-semibold text-gray-500">Municipio</label>
                                <p class="text-gray-900">{{ $plot->municipality->name }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- SIGPAC -->
            @if($plot->sigpacUses->count() > 0 || $plot->sigpacCodes->count() > 0)
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-4">Datos SIGPAC</h2>
                    
                    @if($plot->sigpacUses->count() > 0)
                        <div class="mb-4">
                            <label class="text-sm font-semibold text-gray-500 block mb-2">Usos SIGPAC</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($plot->sigpacUses as $use)
                                    <span class="px-3 py-1 bg-[var(--color-agro-green-bg)] text-[var(--color-agro-green-dark)] rounded-full text-sm font-medium">
                                        {{ $use->code }} - {{ $use->description }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-semibold text-gray-500">Códigos SIGPAC</label>
                            <a href="{{ route('sigpac.codes.create', ['plot_id' => $plot->id]) }}"
                               class="text-sm text-[var(--color-agro-green)] hover:text-[var(--color-agro-green-dark)] 
                                      font-medium flex items-center gap-1 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Añadir código SIGPAC
                            </a>
                        </div>
                        @if($plot->sigpacCodes->count() > 0)
                            <div class="space-y-2">
                                @foreach($plot->sigpacCodes as $code)
                                    @php
                                        $hasGeometry = $plot->multiplePlotSigpacs()
                                            ->where('sigpac_code_id', $code->id)
                                            ->whereNotNull('plot_geometry_id')
                                            ->exists();
                                    @endphp
                                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                                            {{ $code->formatted_code ?? $code->code }}
                                        </span>
                                        @can('update', $plot)
                                            <a href="{{ route('sigpac.geometry.edit-plot', [
                                                'sigpacId' => $code->id, 
                                                'plotId' => $plot->id
                                            ]) }}"
                                               class="text-xs px-3 py-1 rounded-lg transition-colors font-medium {{ $hasGeometry 
                                                   ? 'text-green-600 bg-green-50 hover:bg-green-100' 
                                                   : 'text-blue-600 bg-blue-50 hover:bg-blue-100' }}">
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                                    </svg>
                                                    {{ $hasGeometry ? 'Ver/Editar Mapa' : 'Generar Mapa' }}
                                                </span>
                                            </a>
                                        @endcan
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-400 italic">No hay códigos SIGPAC asociados</p>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Códigos SIGPAC con Geometrías -->
            @php
                $geometries = $plot->multiplePlotSigpacs()
                    ->with(['sigpacCode', 'plotGeometry'])
                    ->whereNotNull('plot_geometry_id')
                    ->get();
            @endphp
            @if($geometries->count() > 0)
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-4">Mapas SIGPAC</h2>
                    
                    <div class="space-y-4">
                        @foreach($geometries as $geometry)
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-[var(--color-agro-green-light)] to-[var(--color-agro-green)] flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            @if($geometry->sigpacCode)
                                                Código: {{ $geometry->sigpacCode->code }}
                                            @else
                                                Código: N/A
                                            @endif
                                        </h3>
                                        <p class="text-sm text-gray-500">Geometría configurada</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($geometry->sigpacCode)
                                        <a
                                            href="{{ route('sigpac.geometry.edit-plot', ['sigpacId' => $geometry->sigpac_code_id, 'plotId' => $plot->id]) }}"
                                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"
                                            title="Ver Mapa"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                            </svg>
                                            Ver Mapa
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Coordenadas Multiparte -->
            @if($plot->multipartCoordinates->count() > 0)
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-4">Coordenadas Multiparte</h2>
                    
                    <div class="space-y-4">
                        @foreach($plot->multipartCoordinates as $coord)
                            <div class="p-4 border border-gray-200 rounded-lg">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="text-sm font-semibold text-gray-700">Coordenadas #{{ $loop->iteration }}</span>
                                    @if($coord->sigpacCode)
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">
                                            {{ $coord->sigpacCode->code }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-gray-900 font-mono text-sm whitespace-pre-wrap">{{ $coord->coordinates }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Fechas -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-bold text-[var(--color-agro-green-dark)] mb-4">Fechas</h3>
                <div class="space-y-2 text-sm">
                    <div>
                        <label class="text-gray-500">Creada</label>
                        <p class="text-gray-900">{{ $plot->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <label class="text-gray-500">Actualizada</label>
                        <p class="text-gray-900">{{ $plot->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
