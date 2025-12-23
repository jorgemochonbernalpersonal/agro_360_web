<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    {{-- Header --}}
    @php
        $reportIcon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';
    @endphp
    <x-page-header 
        :icon="$reportIcon"
        title="Generar Nuevo Informe" 
        description="Crea informes firmados electrónicamente para administración y certificaciones"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    />

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Botón volver --}}
        <div class="mb-6">
            <a 
                href="{{ route('viticulturist.official-reports.index') }}"
                wire:navigate
                class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Volver a lista de informes
            </a>
        </div>

        {{-- Formulario de Generación --}}
        <div class="bg-white rounded-2xl shadow-xl p-6 mb-8 border border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <svg class="w-8 h-8 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Configurar Informe
            </h2>

            {{-- Selector de Tipo de Informe --}}
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-3">Tipo de Informe</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Tratamientos Fitosanitarios --}}
                    <div 
                        wire:click="$set('reportType', 'phytosanitary_treatments')"
                        class="cursor-pointer border-2 rounded-xl p-4 transition-all duration-200 hover:shadow-lg
                               {{ $reportType === 'phytosanitary_treatments' ? 'border-green-500 bg-green-50' : 'border-gray-300 hover:border-green-300' }}"
                    >
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                                    <span class="text-2xl">🧪</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-bold text-gray-900">Tratamientos Fitosanitarios</h3>
                                <p class="text-sm text-gray-600">Informe obligatorio para inspecciones</p>
                            </div>
                        </div>
                    </div>

                    {{-- Cuaderno Digital Completo --}}
                    <div 
                        wire:click="$set('reportType', 'full_digital_notebook')"
                        class="cursor-pointer border-2 rounded-xl p-4 transition-all duration-200 hover:shadow-lg
                               {{ $reportType === 'full_digital_notebook' ? 'border-green-500 bg-green-50' : 'border-gray-300 hover:border-green-300' }}"
                    >
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="text-2xl">📔</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-bold text-gray-900">Cuaderno Digital Completo</h3>
                                <p class="text-sm text-gray-600">Todas las actividades de una campaña</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Formulario según tipo --}}
            @if($reportType === 'phytosanitary_treatments')
                {{-- Plantillas de Periodos Rápidos --}}
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Periodos Rápidos</label>
                    <div class="flex flex-wrap gap-2">
                        <button 
                            type="button"
                            wire:click="setQuickPeriod('last_week')"
                            class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-green-500 transition-colors"
                        >
                            📅 Última semana
                        </button>
                        <button 
                            type="button"
                            wire:click="setQuickPeriod('this_month')"
                            class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-green-500 transition-colors"
                        >
                            📅 Este mes
                        </button>
                        <button 
                            type="button"
                            wire:click="setQuickPeriod('last_month')"
                            class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-green-500 transition-colors"
                        >
                            📅 Mes pasado
                        </button>
                        <button 
                            type="button"
                            wire:click="setQuickPeriod('last_quarter')"
                            class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-green-500 transition-colors"
                        >
                            📅 Últimos 3 meses
                        </button>
                        <button 
                            type="button"
                            wire:click="setQuickPeriod('this_year')"
                            class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-green-500 transition-colors"
                        >
                            📅 Este año
                        </button>
                    </div>
                </div>

                {{-- Rango de Fechas --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha Inicio</label>
                        <input 
                            type="date" 
                            wire:model.live="startDate"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        >
                        @error('startDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha Fin</label>
                        <input 
                            type="date" 
                            wire:model.live="endDate"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        >
                        @error('endDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Contador de Registros --}}
                @if($startDate && $endDate)
                    <div class="mb-6 p-3 bg-gradient-to-r from-green-50 to-blue-50 border-l-4 border-green-500 rounded-r-lg">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span class="text-sm font-semibold text-gray-700">
                                @if($recordCount > 0)
                                    <span class="text-green-700">📊 {{ $recordCount }} tratamiento{{ $recordCount != 1 ? 's' : '' }}</span> encontrado{{ $recordCount != 1 ? 's' : '' }} en este periodo
                                @else
                                    <span class="text-amber-700">⚠️ No hay tratamientos en este periodo</span>
                                @endif
                            </span>
                        </div>
                    </div>
                @endif
            @else
                {{-- Selector de Campaña --}}
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Campaña</label>
                    <select 
                        wire:model.live="campaignId"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    >
                        <option value="">Selecciona una campaña</option>
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign->id }}">{{ $campaign->name }} ({{ $campaign->year }})</option>
                        @endforeach
                    </select>
                    @error('campaignId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Contador de Actividades --}}
                @if($campaignId)
                    <div class="mb-6 p-3 bg-gradient-to-r from-blue-50 to-purple-50 border-l-4 border-blue-500 rounded-r-lg">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span class="text-sm font-semibold text-gray-700">
                                @if($activitiesCount > 0)
                                    <span class="text-blue-700">📊 {{ $activitiesCount }} actividad{{ $activitiesCount != 1 ? 'es' : '' }}</span> registrada{{ $activitiesCount != 1 ? 's' : '' }} en esta campaña
                                @else
                                    <span class="text-amber-700">⚠️ No hay actividades en esta campaña</span>
                                @endif
                            </span>
                        </div>
                    </div>
                @endif
            @endif

            {{-- Aviso sobre firma digital --}}
            <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                <p class="text-sm text-blue-800 flex items-start gap-2">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>
                        <strong>Firma Digital:</strong> Se te pedirá tu contraseña de firma digital al confirmar la generación del informe. 
                        Si no la tienes configurada, créala en <a href="{{ route('viticulturist.settings', ['tab' => 'signature']) }}" class="text-blue-600 hover:text-blue-800 underline font-semibold">Configuración → Firma Digital</a>.
                    </span>
                </p>
            </div>

            {{-- Errores de generación --}}
            @error('generation')
                <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                    <p class="text-red-700 font-medium">{{ $message }}</p>
                </div>
            @enderror

            {{-- Botón Generar --}}
            <div class="relative">
                <button 
                    wire:click="calculateSummary"
                    wire:loading.attr="disabled"
                    wire:target="calculateSummary"
                    class="w-full md:w-auto flex items-center justify-center gap-2 px-8 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                >
                    <svg wire:loading.remove wire:target="calculateSummary" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <svg wire:loading wire:target="calculateSummary" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="calculateSummary">Generar y Firmar Informe</span>
                    <span wire:loading wire:target="calculateSummary">Calculando...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Include modals --}}
    @include('livewire.viticulturist.official-reports.partials._summary-modal')
    @include('livewire.viticulturist.official-reports.partials._success-modal')

    {{-- Indicador de carga mientras se genera el informe --}}
    <div wire:loading wire:target="confirmAndGenerateReport,generateReport" class="fixed z-50 inset-0 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 z-10">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                        <svg class="animate-spin h-10 w-10 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Generando Informe</h3>
                    <p class="text-gray-600 mb-4">
                        Por favor, espera mientras se genera y firma tu informe oficial...
                    </p>
                    <p class="text-sm text-gray-500">
                        Este proceso puede tardar varios segundos.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
