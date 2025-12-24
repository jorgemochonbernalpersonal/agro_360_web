<div class="container mx-auto px-4 py-6">
    {{-- Breadcrumb --}}
    <nav class="mb-6">
        <a href="{{ route('viticulturist.pest-management.index') }}" class="text-blue-600 hover:underline">← Volver al catálogo</a>
    </nav>

    {{-- Header --}}
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <div class="flex items-start justify-between">
            <div class="flex items-start space-x-4">
                <span class="text-6xl">{{ $pest->icon }}</span>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $pest->name }}</h1>
                    @if($pest->scientific_name)
                        <p class="text-lg text-gray-600 italic mt-1">{{ $pest->scientific_name }}</p>
                    @endif
                    <div class="flex items-center space-x-2 mt-3">
                        <span class="px-3 py-1 rounded-full text-sm font-medium {{ $pest->type === 'pest' ? 'bg-orange-100 text-orange-800' : 'bg-purple-100 text-purple-800' }}">
                            {{ $pest->type === 'pest' ? '🐛 Plaga' : '🦠 Enfermedad' }}
                        </span>
                        @if($pest->isInRiskPeriod())
                            <span class="px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                ⚠️ En período de riesgo
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Columna Principal --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Descripción --}}
            @if($pest->description)
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">📝 Descripción</h2>
                    <p class="text-gray-700">{{ $pest->description }}</p>
                </div>
            @endif

            {{-- Síntomas --}}
            @if($pest->symptoms)
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">🔍 Síntomas y Signos</h2>
                    <p class="text-gray-700">{{ $pest->symptoms }}</p>
                </div>
            @endif

            {{-- Ciclo de Vida --}}
            @if($pest->lifecycle)
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">🔄 Ciclo de Vida</h2>
                    <p class="text-gray-700">{{ $pest->lifecycle }}</p>
                </div>
            @endif

            {{-- Prevención --}}
            @if($pest->prevention_methods)
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">🛡️ Métodos de Prevención</h2>
                    <p class="text-gray-700">{{ $pest->prevention_methods }}</p>
                </div>
            @endif

            {{-- Productos Eficaces --}}
            @if($pest->products->count() > 0)
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">💊 Productos Fitosanitarios Eficaces</h2>
                    <div class="space-y-3">
                        @foreach($pest->products as $product)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $product->commercial_name }}</p>
                                    <p class="text-sm text-gray-600">{{ $product->active_substance }}</p>
                                </div>
                                <div class="flex items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-5 h-5 {{ $i <= $product->pivot->effectiveness_rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Columna Lateral --}}
        <div class="space-y-6">
            {{-- Umbral de Tratamiento --}}
            @if($pest->threshold)
                <div class="bg-blue-50 rounded-lg p-4 border-l-4 border-blue-500">
                    <h3 class="font-semibold text-blue-900 mb-2">📊 Umbral de Tratamiento</h3>
                    <p class="text-sm text-blue-800">{{ $pest->threshold }}</p>
                </div>
            @endif

            {{-- Meses de Riesgo --}}
            @if($pest->risk_months && count($pest->risk_months) > 0)
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="font-semibold text-gray-900 mb-3">📅 Meses de Riesgo</h3>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'] as $index => $month)
                            <div class="text-center p-2 rounded {{ in_array($index + 1, $pest->risk_months) ? 'bg-red-100 text-red-800 font-semibold' : 'bg-gray-100 text-gray-500' }}">
                                {{ $month }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Acciones Rápidas --}}
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold text-gray-900 mb-3">⚡ Acciones Rápidas</h3>
                <div class="space-y-2">
                    <a href="{{ route('viticulturist.digital-notebook.observation.create', ['pest_id' => $pest->id]) }}" 
                       class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-4 rounded-lg transition-colors">
                        📝 Registrar Observación
                    </a>
                    <a href="{{ route('viticulturist.digital-notebook.treatment.create', ['pest_id' => $pest->id]) }}" 
                       class="block w-full bg-green-600 hover:bg-green-700 text-white text-center py-2 px-4 rounded-lg transition-colors">
                        💉 Registrar Tratamiento
                    </a>
                </div>
            </div>

            {{-- Historial --}}
            @if($pest->observations->count() > 0 || $pest->treatments->count() > 0)
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="font-semibold text-gray-900 mb-3">📊 Historial</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Observaciones:</span>
                            <span class="font-semibold">{{ $pest->observations->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tratamientos:</span>
                            <span class="font-semibold">{{ $pest->treatments->count() }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
