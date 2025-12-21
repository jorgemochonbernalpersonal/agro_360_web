<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    {{-- Header --}}
    <x-page-header 
        title="Informes Oficiales" 
        subtitle="Genera informes firmados electrónicamente para administración y certificaciones"
    />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Generador de Informes --}}
        <div class="bg-white rounded-2xl shadow-xl p-6 mb-8 border border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <svg class="w-8 h-8 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Generar Nuevo Informe
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
                {{-- Rango de Fechas --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha Inicio</label>
                        <input 
                            type="date" 
                            wire:model="startDate"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        >
                        @error('startDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha Fin</label>
                        <input 
                            type="date" 
                            wire:model="endDate"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        >
                        @error('endDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
            @else
                {{-- Selector de Campaña --}}
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Campaña</label>
                    <select 
                        wire:model="campaignId"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    >
                        <option value="">Selecciona una campaña</option>
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign->id }}">{{ $campaign->name }} ({{ $campaign->year }})</option>
                        @endforeach
                    </select>
                    @error('campaignId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            @endif

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
                    class="w-full md:w-auto flex items-center gap-2 px-8 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
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

        {{-- Historial de Informes --}}
        <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-200">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Informes Generados</h2>
                
                {{-- Filtros y Búsqueda --}}
                <div class="flex items-center gap-3">
                    {{-- Búsqueda --}}
                    <div class="relative">
                        <input 
                            type="text"
                            wire:model.live="search"
                            placeholder="Buscar por código..."
                            class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm"
                        >
                        <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    {{-- Filtro Estado --}}
                    <select 
                        wire:model.live="statusFilter"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm"
                    >
                        <option value="all">Todos</option>
                        <option value="valid">Válidos</option>
                        <option value="invalid">Invalidados</option>
                    </select>

                    {{-- Resetear filtros --}}
                    @if($search || $statusFilter !== 'all')
                        <button 
                            wire:click="resetFilters"
                            class="text-sm text-gray-600 hover:text-gray-900"
                            title="Limpiar filtros"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @endif
                </div>
            </div>

            @if($reports->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periodo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Generado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tamaño</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($reports as $report)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-2xl mr-2">{{ $report->report_icon }}</span>
                                        <span class="text-sm font-medium text-gray-900">{{ $report->report_type_name }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $report->period_start->format('d/m/Y') }} - {{ $report->period_end->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $report->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $report->formatted_pdf_size }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($report->isValid())
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                ✓ Válido
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                ✗ Invalidado
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button 
                                            wire:click="openPreviewModal({{ $report->id }})"
                                            class="text-purple-600 hover:text-purple-900 mr-3"
                                            title="Vista previa"
                                        >
                                            <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Vista Previa
                                        </button>
                                        <button 
                                            wire:click="downloadReport({{ $report->id }})"
                                            class="text-green-600 hover:text-green-900 mr-3"
                                        >
                                            <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                            Descargar
                                        </button>
                                        <a 
                                            href="{{ route('reports.verify', ['code' => $report->verification_code]) }}" 
                                            target="_blank"
                                            class="text-blue-600 hover:text-blue-900 mr-3"
                                        >
                                            <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Verificar
                                        </a>
                                        <button 
                                            wire:click="openShareModal({{ $report->id }})"
                                            class="text-indigo-600 hover:text-indigo-900 mr-3"
                                            title="Compartir por email"
                                        >
                                            <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                            Compartir
                                        </button>
                                        @if($report->isValid())
                                            <button 
                                                wire:click="openInvalidateModal({{ $report->id }})"
                                                class="text-red-600 hover:text-red-900"
                                                title="Invalidar informe"
                                            >
                                                <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                                Invalidar
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                <div class="mt-4">
                    {{ $reports->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No hay informes generados</h3>
                    <p class="mt-1 text-sm text-gray-500">Genera tu primer informe oficial arriba.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal de Resumen --}}
    @if($showSummaryModal)
        <div class="fixed z-50 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeSummaryModal"></div>
                
                <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">📊 Resumen del Informe</h3>
                    <p class="text-gray-600 mb-6">
                        Revisa los datos antes de firmar electrónicamente. Este proceso puede tardar varios segundos.
                    </p>

                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6 mb-6 border-2 border-blue-200">
                        @if($reportSummary['type'] ?? '' === 'phytosanitary_treatments')
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Periodo</p>
                                    <p class="text-lg font-bold text-gray-900">{{ $reportSummary['period'] ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Total Tratamientos</p>
                                    <p class="text-lg font-bold text-blue-900">{{ $reportSummary['total_treatments'] ?? 0 }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Parcelas Afectadas</p>
                                    <p class="text-lg font-bold text-gray-900">{{ $reportSummary['plots_count'] ?? 0 }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Productos Usados</p>
                                    <p class="text-lg font-bold text-gray-900">{{ $reportSummary['products_count'] ?? 0 }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Área Total Tratada</p>
                                    <p class="text-lg font-bold text-green-900">{{ $reportSummary['total_area'] ?? 0 }} ha</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Tamaño Estimado</p>
                                    <p class="text-lg font-bold text-gray-900">{{ $reportSummary['estimated_size'] ?? '-' }}</p>
                                </div>
                            </div>
                        @else
                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <p class="text-sm text-gray-600 mb-1">Campaña</p>
                                    <p class="text-lg font-bold text-gray-900">{{ $reportSummary['campaign'] ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Total Actividades</p>
                                    <p class="text-lg font-bold text-blue-900">{{ $reportSummary['total_activities'] ?? 0 }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Tamaño Estimado</p>
                                    <p class="text-lg font-bold text-gray-900">{{ $reportSummary['estimated_size'] ?? '-' }}</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-r-lg mb-6">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-yellow-900">Tiempo Estimado de Generación</p>
                                <p class="text-sm text-yellow-800 mt-1">
                                    Este informe puede tardar <strong>{{ $reportSummary['estimated_time'] ?? '10-15' }} segundos</strong> en generarse. 
                                    Por favor, no cierres la ventana durante el proceso.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button 
                            wire:click="closeSummaryModal"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
                        >
                            Cancelar
                        </button>
                        <button 
                            wire:click="confirmAndOpenPasswordModal"
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold"
                        >
                            Continuar → Firmar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Contraseña --}}
    @if($showPasswordModal)
        <div class="fixed z-50 inset-0 overflow-y-auto" x-data>
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closePasswordModal"></div>
                
                <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">🔐 Firma Electrónica</h3>
                    <p class="text-gray-600 mb-6">
                        Introduce tu contraseña para firmar electrónicamente este informe. 
                        La firma garantiza la autenticidad e integridad del documento.
                    </p>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Contraseña</label>
                        <input 
                            type="password" 
                            wire:model="password"
                            wire:keydown.enter="generateReport"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Introduce tu contraseña"
                            autofocus
                        >
                        @error('password') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                        @error('generation') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button 
                            wire:click="closePasswordModal"
                            wire:loading.attr="disabled"
                            wire:target="generateReport"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors disabled:opacity-50"
                        >
                            Cancelar
                        </button>
                        <button 
                            wire:click="generateReport"
                            wire:loading.attr="disabled"
                            wire:target="generateReport"
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <svg wire:loading wire:target="generateReport" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="generateReport">Firmar y Generar</span>
                            <span wire:loading wire:target="generateReport">Generando PDF...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Éxito --}}
    @if($showSuccessModal && $generatedReport)
        <div class="fixed z-50 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeSuccessModal"></div>
                
                <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                            <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">✅ ¡Informe Generado!</h3>
                        <p class="text-gray-600 mb-6">
                            El informe ha sido generado y firmado electrónicamente con éxito.
                        </p>

                        <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
                            <p class="text-sm text-gray-600 mb-2"><strong>Tipo:</strong> {{ $generatedReport->report_type_name }}</p>
                            <p class="text-sm text-gray-600 mb-2"><strong>Periodo:</strong> {{ $generatedReport->period_start->format('d/m/Y') }} - {{ $generatedReport->period_end->format('d/m/Y') }}</p>
                            <p class="text-sm text-gray-600"><strong>Tamaño:</strong> {{ $generatedReport->formatted_pdf_size }}</p>
                        </div>

                        <div class="flex space-x-3">
                            <button 
                                wire:click="closeSuccessModal"
                                class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
                            >
                                Cerrar
                            </button>
                            <button 
                                wire:click="downloadReport({{ $generatedReport->id }})"
                                class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold"
                            >
                                Descargar PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Compartir --}}
    @if($showShareModal && $reportToShare)
        <div class="fixed z-50 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeShareModal"></div>
                
                <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">📧 Compartir Informe</h3>
                    <p class="text-gray-600 mb-6">
                        Envía este informe por email. El destinatario recibirá el PDF adjunto y un enlace para verificar su autenticidad.
                    </p>

                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email del destinatario</label>
                        <input 
                            type="email" 
                            wire:model="shareEmail"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="ejemplo@correo.com"
                        >
                        @error('shareEmail') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Mensaje personalizado (opcional)</label>
                        <textarea 
                            wire:model="shareMessage"
                            rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Añade un mensaje personal..."
                        ></textarea>
                        <p class="text-xs text-gray-500 mt-1">Máximo 500 caracteres</p>
                        @error('shareMessage') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                        @error('share') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg mb-6">
                        <p class="text-sm text-blue-800">
                            <strong>Incluye:</strong> PDF del informe + enlace de verificación QR
                        </p>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button 
                            wire:click="closeShareModal"
                            wire:loading.attr="disabled"
                            wire:target="shareReport"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors disabled:opacity-50"
                        >
                            Cancelar
                        </button>
                        <button 
                            wire:click="shareReport"
                            wire:loading.attr="disabled"
                            wire:target="shareReport"
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-semibold disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <svg wire:loading wire:target="shareReport" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="shareReport">Enviar Email</span>
                            <span wire:loading wire:target="shareReport">Enviando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Invalidar --}}
    @if($showInvalidateModal && $reportToInvalidate)
        <div class="fixed z-50 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeInvalidateModal"></div>
                
                <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">⚠️ Invalidar Informe</h3>
                    <p class="text-gray-600 mb-6">
                        Esta acción marcará el informe como <strong>INVALIDADO</strong>. El documento seguirá siendo visible pero NO será legalmente válido.
                    </p>

                    @if($reportToInvalidate && $reportToInvalidate->canBeInvalidated())
                        @php
                            $daysRemaining = $reportToInvalidate->getDaysRemainingToInvalidate();
                            $maxDays = config('reports.max_days_to_invalidate', 30);
                        @endphp
                        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-r-lg mb-6">
                            <p class="text-sm text-yellow-800">
                                <strong>⏰ Límite de tiempo:</strong> Este informe puede ser invalidado durante los primeros {{ $maxDays }} días desde su firma.
                                @if($daysRemaining !== null)
                                    <br>Quedan <strong>{{ $daysRemaining }} días</strong> para poder invalidarlo.
                                @endif
                            </p>
                        </div>
                    @elseif($reportToInvalidate && !$reportToInvalidate->canBeInvalidated())
                        @php
                            $daysSinceSigned = $reportToInvalidate->signed_at->diffInDays(now());
                            $maxDays = config('reports.max_days_to_invalidate', 30);
                        @endphp
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg mb-6">
                            <p class="text-sm text-red-800">
                                <strong>❌ No se puede invalidar:</strong> Han pasado {{ $daysSinceSigned }} días desde la firma. 
                                Solo se pueden invalidar informes con menos de {{ $maxDays }} días.
                            </p>
                        </div>
                    @endif

                    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg mb-6">
                        <p class="text-sm text-red-800">
                            <strong>⚠️ Esta acción NO se puede deshacer.</strong> El informe quedará permanentemente invalidado.
                        </p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Motivo de invalidación</label>
                        <textarea 
                            wire:model="invalidateReason"
                            rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                            placeholder="Explica por qué invalidas este informe (mínimo 10 caracteres)..."
                        ></textarea>
                        @error('invalidateReason') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Confirma tu contraseña</label>
                        <input 
                            type="password" 
                            wire:model="invalidatePassword"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                            placeholder="Introduce tu contraseña"
                        >
                        @error('invalidatePassword') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                        @error('invalidate') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button 
                            wire:click="closeInvalidateModal"
                            wire:loading.attr="disabled"
                            wire:target="invalidateReport"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors disabled:opacity-50"
                        >
                            Cancelar
                        </button>
                        <button 
                            wire:click="invalidateReport"
                            wire:loading.attr="disabled"
                            wire:target="invalidateReport"
                            class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-semibold disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <svg wire:loading wire:target="invalidateReport" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="invalidateReport">Invalidar Informe</span>
                            <span wire:loading wire:target="invalidateReport">Invalidando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Vista Previa PDF --}}
    @if($showPreviewModal && $reportToPreview)
        <div class="fixed z-50 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closePreviewModal"></div>
                
                <div class="relative bg-white rounded-2xl shadow-2xl max-w-6xl w-full h-[90vh] p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-2xl font-bold text-gray-900">👁️ Vista Previa - {{ $reportToPreview->report_type_name }}</h3>
                        <button 
                            wire:click="closePreviewModal"
                            class="text-gray-500 hover:text-gray-700"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-3 mb-4 flex items-center justify-between">
                        <div class="text-sm">
                            <span class="font-semibold">Periodo:</span> {{ $reportToPreview->period_start->format('d/m/Y') }} - {{ $reportToPreview->period_end->format('d/m/Y') }}
                            <span class="mx-2">|</span>
                            <span class="font-semibold">Código:</span> <code class="bg-white px-2 py-1 rounded">{{ $reportToPreview->verification_code }}</code>
                        </div>
                        <button 
                            wire:click="downloadReport({{ $reportToPreview->id }})"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-semibold"
                        >
                            ⬇️ Descargar PDF
                        </button>
                    </div>

                    <div class="h-[calc(90vh-180px)] rounded-lg overflow-hidden border-2 border-gray-200">
                        <iframe 
                            src="{{ \Storage::url($reportToPreview->pdf_path) }}" 
                            class="w-full h-full"
                            frameborder="0"
                        ></iframe>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
