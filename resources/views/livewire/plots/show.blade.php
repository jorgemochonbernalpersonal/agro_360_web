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
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-[var(--color-agro-green-dark)]">Datos SIGPAC</h2>
                        @if($plot->sigpacCodes->count() > 0)
                            @php
                                $hasGeometryForButton = \App\Models\MultipartPlotSigpac::where('plot_id', $plot->id)
                                    ->whereNotNull('plot_geometry_id')
                                    ->exists();
                            @endphp
                            @can('update', $plot)
                                @if(!$hasGeometryForButton)
                                    <button
                                        wire:click="generateMap({{ $plot->id }})"
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg transition-colors font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 disabled:opacity-50">
                                        <span wire:loading.remove wire:target="generateMap({{ $plot->id }})">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                            </svg>
                                            Generar Mapa
                                        </span>
                                        <span wire:loading wire:target="generateMap({{ $plot->id }})" class="flex items-center gap-2">
                                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Generando...
                                        </span>
                                    </button>
                                @endif
                            @endcan
                        @endif
                    </div>
                    
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
                                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                                            {{ $code->formatted_code ?? $code->code }}
                                        </span>
                                        @php
                                            $codeHasGeometry = \App\Models\MultipartPlotSigpac::where('plot_id', $plot->id)
                                                ->where('sigpac_code_id', $code->id)
                                                ->whereNotNull('plot_geometry_id')
                                                ->exists();
                                        @endphp
                                        @can('update', $plot)
                                            @if(!$codeHasGeometry)
                                                <button
                                                    wire:click="generateMap({{ $plot->id }}, {{ $code->id }})"
                                                    wire:loading.attr="disabled"
                                                    class="text-xs px-3 py-1 rounded-lg transition-colors font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 disabled:opacity-50">
                                                    <span wire:loading.remove wire:target="generateMap({{ $plot->id }}, {{ $code->id }})" class="flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                                        </svg>
                                                        Generar Mapa
                                                    </span>
                                                    <span wire:loading wire:target="generateMap({{ $plot->id }}, {{ $code->id }})" class="flex items-center gap-1">
                                                        <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                        </svg>
                                                        Generando...
                                                    </span>
                                                </button>
                                            @endif
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

            <!-- Mapa SIGPAC -->
            @php
                $hasGeometry = \App\Models\MultipartPlotSigpac::where('plot_id', $plot->id)
                    ->whereNotNull('plot_geometry_id')
                    ->exists();
                
                $plotGeometries = [];
                if ($hasGeometry) {
                    $relations = \App\Models\MultipartPlotSigpac::with(['plotGeometry', 'sigpacCode'])
                        ->where('plot_id', $plot->id)
                        ->whereNotNull('plot_geometry_id')
                        ->get();
                    
                    foreach ($relations as $relation) {
                        if ($relation->plotGeometry) {
                            $wkt = $relation->plotGeometry->getWktCoordinates();
                            if ($wkt) {
                                $plotGeometries[] = [
                                    'wkt' => $wkt,
                                    'sigpac_code' => $relation->sigpacCode?->code ?? null,
                                ];
                            }
                        }
                    }
                }
            @endphp

            @if($hasGeometry)
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold text-[var(--color-agro-green-dark)]">Mapa de la Parcela</h2>
                        <a href="/map/{{ $plot->id }}"
                           class="px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 transition-all duration-200 shadow-md hover:shadow-lg flex items-center gap-2 font-medium">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                            Ver Mapa Completo
                        </a>
                    </div>
                    
                    <div class="bg-gradient-to-br from-green-50 to-blue-50 rounded-lg p-6 border border-green-200">
                        <div class="flex items-start gap-4">
                            <div class="bg-white p-3 rounded-lg shadow-sm">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-bold text-lg text-gray-900 mb-2">Mapa Interactivo Disponible</h3>
                                <p class="text-gray-600 text-sm mb-3">
                                    Esta parcela tiene recintos SIGPAC con geometrías generadas. 
                                    Visualiza el mapa interactivo a pantalla completa con selector de recintos.
                                </p>
                                <ul class="text-sm text-gray-600 space-y-1">
                                    <li class="flex items-center gap-2">
                                        <span class="text-green-600">✓</span>
                                        Vista a pantalla completa
                                    </li>
                                   <li class="flex items-center gap-2">
                                        <span class="text-green-600">✓</span>
                                        Selector de recintos individuales
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <span class="text-green-600">✓</span>
                                        Zoom y navegación interactiva
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <span class="text-green-600">✓</span>
                                        Colores diferenciados por recinto
                                    </li>
                                </ul>
                            </div>
                        </div>
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

@push('scripts')
@if($hasGeometry && count($plotGeometries) > 0)
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    
    <script>
        function initMap() {
            console.log('=== Iniciando mapa ===');
            let plotGeometries = @json($plotGeometries);
            console.log('plotGeometries:', plotGeometries);
            
            if (plotGeometries.length === 0) {
                console.warn('No hay geometrías para mostrar');
                return;
            }

            // Verificar que el contenedor existe
            const mapContainer = document.getElementById('plot-map');
            if (!mapContainer) {
                console.error('No se encontró el contenedor #plot-map');
                return;
            }

            // Inicializar mapa
            let map = L.map('plot-map', {
                zoomControl: true
            });
            
            // Capas base
            let streetMap = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            });
            
            let satelliteMap = L.tileLayer(
                'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    attribution: '&copy; Esri'
                }
            );
            
            streetMap.addTo(map);
            
            // Control de capas
            let baseMaps = {
                "Mapa": streetMap,
                "Satélite": satelliteMap
            };
            L.control.layers(baseMaps).addTo(map);
            
            let bounds = [];
            
            // Función para parsear WKT
            function parseWKT(wkt) {
                if (!wkt || typeof wkt !== 'string') {
                    return [];
                }
                
                const trimmedWkt = wkt.trim();
                if (trimmedWkt.length === 0) {
                    return [];
                }
                
                if (trimmedWkt.startsWith("POLYGON")) {
                    return parsePolygon(trimmedWkt);
                } else if (trimmedWkt.startsWith("MULTIPOLYGON")) {
                    return parseMultiPolygon(trimmedWkt);
                } else {
                    return [];
                }
            }
            
            function parsePolygon(wkt) {
                try {
                    const ringMatches = wkt.match(/\(([^)]+)\)/g);
                    if (!ringMatches || ringMatches.length === 0) {
                        return [];
                    }
                    
                    const rings = [];
                    ringMatches.forEach((ringMatch) => {
                        const coordString = ringMatch.slice(1, -1);
                        const coordinates = parseCoordinateString(coordString);
                        if (coordinates.length >= 3) {
                            rings.push(coordinates);
                        }
                    });
                    
                    if (rings.length === 0) {
                        return [];
                    }
                    
                    if (rings.length === 1) {
                        return rings[0];
                    }
                    
                    return {
                        isComplex: true,
                        outerRing: rings[0],
                        holes: rings.slice(1)
                    };
                } catch (error) {
                    console.error('parsePolygon error:', error);
                    return [];
                }
            }
            
            function parseMultiPolygon(wkt) {
                try {
                    const polygons = [];
                    let inner = wkt.replace(/^MULTIPOLYGON\s*\(\s*/i, '').replace(/\s*\)$/i, '');
                    let polyStrings = inner.split(/\)\s*,\s*\(/);
                    
                    polyStrings.forEach(polyStr => {
                        let cleanStr = polyStr.replace(/^\(\s*/, '').replace(/\s*\)$/i, '');
                        let rings = cleanStr.match(/\(([^)]+)\)/g);
                        if (rings) {
                            const parsedRings = [];
                            rings.forEach(ringMatch => {
                                const coordString = ringMatch.slice(1, -1);
                                const coordinates = parseCoordinateString(coordString);
                                if (coordinates.length >= 3) {
                                    parsedRings.push(coordinates);
                                }
                            });
                            if (parsedRings.length === 1) {
                                polygons.push(parsedRings[0]);
                            } else if (parsedRings.length > 1) {
                                polygons.push({
                                    isComplex: true,
                                    outerRing: parsedRings[0],
                                    holes: parsedRings.slice(1)
                                });
                            }
                        }
                    });
                    return polygons;
                } catch (error) {
                    console.error('parseMultiPolygon error:', error);
                    return [];
                }
            }
            
            function parseCoordinateString(coordString) {
                if (!coordString || typeof coordString !== 'string') {
                    return [];
                }
                
                const coords = coordString.split(",");
                const validCoords = [];
                
                coords.forEach((coord) => {
                    try {
                        const trimmedCoord = coord.trim();
                        if (trimmedCoord.length === 0) return;
                        
                        const parts = trimmedCoord.split(/\s+/);
                        if (parts.length >= 2) {
                            const lon = parseFloat(parts[0]);
                            const lat = parseFloat(parts[1]);
                            
                            if (isNaN(lat) || isNaN(lon)) {
                                return;
                            }
                            
                            if (lat < -90 || lat > 90 || lon < -180 || lon > 180) {
                                return;
                            }
                            
                            validCoords.push([lat, lon]);
                        }
                    } catch (error) {
                        console.error('Error parsing coordinate:', error);
                    }
                });
                return validCoords;
            }
            
            // Renderizar geometrías
            let polygonsAdded = 0;
            plotGeometries.forEach((plot, index) => {
                console.log(`Procesando geometría ${index + 1}:`, plot.wkt.substring(0, 100));
                let geometries = parseWKT(plot.wkt);
                console.log('Geometrías parseadas:', geometries);
                
                if (!geometries || geometries.length === 0) {
                    console.warn(`No se pudieron parsear las geometrías para el índice ${index}`);
                    return;
                }
                
                let polygons = Array.isArray(geometries[0]) && Array.isArray(geometries[0][0]) ?
                    geometries : [geometries];
                
                console.log('Polígonos a renderizar:', polygons.length);
                
                polygons.forEach((coords, polyIndex) => {
                    let polygonCoords;
                    if (coords.isComplex) {
                        polygonCoords = [coords.outerRing, ...coords.holes];
                        bounds.push(...coords.outerRing);
                        console.log(`Polígono complejo ${polyIndex}:`, coords.outerRing.length, 'puntos');
                    } else if (Array.isArray(coords[0])) {
                        polygonCoords = [coords];
                        bounds.push(...coords);
                        console.log(`Polígono simple ${polyIndex}:`, coords.length, 'puntos');
                    } else {
                        polygonCoords = coords;
                        bounds.push(...coords);
                        console.log(`Polígono directo ${polyIndex}:`, coords.length, 'puntos');
                    }
                    
                    console.log('Coordenadas del polígono:', polygonCoords[0]?.slice(0, 3));
                    
                    try {
                        let plotPolygon = L.polygon(polygonCoords, {
                            color: '#10b981',
                            fillColor: '#86efac',
                            fillOpacity: 0.5,
                            weight: 2
                        }).addTo(map);
                        
                        polygonsAdded++;
                        console.log(`Polígono ${polygonsAdded} agregado al mapa`);
                        
                        let tooltipContent = `
                            <b>Parcela:</b> {{ $plot->name }}<br>
                            <b>Código SIGPAC:</b> ${plot.sigpac_code || '-'}
                        `;
                        
                        plotPolygon.bindPopup(tooltipContent);
                        plotPolygon.on('mouseover', function() {
                            this.bindTooltip(tooltipContent, { sticky: true }).openTooltip();
                        });
                    } catch (error) {
                        console.error('Error al agregar polígono:', error, polygonCoords);
                    }
                });
            });
            
            console.log(`Total polígonos agregados: ${polygonsAdded}`);
            console.log('Bounds:', bounds.length, 'puntos');
            
            // Ajustar vista al contenido
            if (bounds.length > 0) {
                try {
                    // Esperar un momento para que el mapa se renderice
                    setTimeout(function() {
                        map.fitBounds(bounds);
                        map.invalidateSize();
                        console.log('Vista ajustada a bounds');
                    }, 200);
                } catch (error) {
                    console.error('Error al ajustar bounds:', error);
                }
            } else {
                console.warn('No hay bounds para ajustar la vista');
                map.setView([40.4168, -3.7038], 13);
            }
            
            // Asegurar que el mapa se renderice correctamente
            setTimeout(function() {
                map.invalidateSize();
            }, 100);
        }
        
        // Inicializar cuando el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initMap);
        } else {
            initMap();
        }
    </script>
@endif
@endpush
