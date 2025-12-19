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

                    @if($plot->sigpacCodes->count() > 0)
                        <div>
                            <label class="text-sm font-semibold text-gray-500 block mb-2">Códigos SIGPAC</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($plot->sigpacCodes as $code)
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                                        {{ $code->code }}@if($code->description) - {{ $code->description }}@endif
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
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
