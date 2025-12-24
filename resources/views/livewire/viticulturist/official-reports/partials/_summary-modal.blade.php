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

                {{-- Opción de generación por lotes --}}
                @if($showBatchOption && isset($batchPeriods) && count($batchPeriods) > 0)
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-300 rounded-xl p-6 mb-6">
                        <div class="flex items-start gap-3 mb-4">
                            <div class="flex-shrink-0 w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-lg font-bold text-blue-900 mb-2">
                                    📦 Generación por Lotes Recomendada
                                </h4>
                                <p class="text-sm text-blue-800 mb-3">
                                    Tu campaña tiene <strong>{{ $reportSummary['total_activities'] ?? 0 }} actividades</strong>. 
                                    Para mejor rendimiento, se generarán <strong>{{ $totalBatches }} informes</strong> automáticamente:
                                </p>
                                <div class="bg-white rounded-lg p-4 mb-4 border border-blue-200">
                                    <ul class="space-y-2">
                                        @foreach($batchPeriods as $period)
                                            <li class="flex items-center justify-between text-sm">
                                                <span class="font-semibold text-gray-700">{{ $period['label'] }}</span>
                                                <span class="text-blue-600 font-medium">{{ $period['count'] }} actividades</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <p class="text-xs text-blue-700 mb-4">
                                    💡 Cada informe se generará por separado y recibirás una notificación cuando cada uno esté listo.
                                </p>
                            </div>
                        </div>
                    </div>
                @else
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
                @endif

                {{-- Advertencia si no tiene contraseña configurada --}}
                @if(!$hasDigitalSignature)
                    <div class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded-r-lg">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-yellow-800">No tienes contraseña de firma configurada</p>
                                <p class="text-sm text-yellow-700 mt-1">
                                    Debes crear una contraseña de firma digital en 
                                    <a href="{{ route('viticulturist.settings', ['tab' => 'signature']) }}" 
                                       wire:navigate
                                       class="underline font-semibold hover:text-yellow-900">
                                        Configuración → Firma Digital
                                    </a> antes de poder generar informes.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- HINT VISUAL IMPORTANTE: No es la contraseña de login --}}
                <div class="mb-6 p-4 bg-gradient-to-r from-purple-50 to-indigo-50 border-2 border-purple-400 rounded-xl shadow-md">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-base font-bold text-purple-900 mb-1">⚠️ IMPORTANTE: Usa tu Contraseña de Firma Digital</h4>
                            <p class="text-sm text-purple-800 leading-relaxed">
                                Esta <strong class="underline">NO es tu contraseña de login</strong>. Es la contraseña específica que creaste en 
                                <a href="{{ route('viticulturist.settings', ['tab' => 'signature']) }}" 
                                   wire:navigate
                                   target="_blank"
                                   class="inline-flex items-center gap-1 text-purple-700 hover:text-purple-900 font-bold underline decoration-2">
                                    Configuración → Firma Digital
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Campo de Contraseña en el Modal --}}
                <div class="mb-6" x-data="{ showPassword: false }">
                    <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Contraseña de Firma Digital
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            wire:model="password"
                            x-bind:type="showPassword ? 'text' : 'password'"
                            wire:keydown.enter="confirmAndGenerateReport"
                            @if(!$hasDigitalSignature) 
                                disabled
                                readonly
                            @endif
                            class="w-full px-4 py-3 pr-12 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition {{ $hasDigitalSignature ? '' : 'opacity-50 cursor-not-allowed bg-gray-100' }}"
                            placeholder="{{ $hasDigitalSignature ? 'Introduce tu contraseña de firma digital' : 'Configura tu contraseña primero' }}"
                            @if($hasDigitalSignature) autofocus @endif
                        >
                        <button
                            type="button"
                            x-on:click="showPassword = !showPassword"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none"
                            tabindex="-1"
                        >
                            <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    <div class="mt-2 flex items-center gap-2 text-xs text-gray-600">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <span>Se usa exclusivamente para firmar documentos oficiales</span>
                    </div>
                    @error('password') 
                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> 
                    @enderror
                    @error('generation') 
                        <div class="mt-3 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-red-800">Error al generar el informe</p>
                                    <p class="text-sm text-red-700 mt-1">{{ $message }}</p>
                                </div>
                            </div>
                        </div>
                    @enderror
                </div>

                <div class="flex justify-end space-x-3">
                    <button 
                        wire:click="closeSummaryModal"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
                    >
                        Cancelar
                    </button>
                    
                    @if($showBatchOption && isset($batchPeriods) && count($batchPeriods) > 0)
                        {{-- Botón para generar por lotes --}}
                        <button 
                            wire:click="generateBatchReports"
                            wire:loading.attr="disabled"
                            wire:target="generateBatchReports"
                            @if(!$hasDigitalSignature) 
                                disabled
                                type="button"
                            @endif
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <svg wire:loading wire:target="generateBatchReports" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="generateBatchReports">📦 Generar {{ $totalBatches }} Informes</span>
                            <span wire:loading wire:target="generateBatchReports">Generando...</span>
                        </button>
                        
                        {{-- Opción alternativa: generar uno solo --}}
                        <button 
                            wire:click="forceGenerateSingle"
                            wire:loading.attr="disabled"
                            class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors text-sm"
                            title="Generar un solo informe (puede tardar más tiempo)"
                        >
                            Un solo informe
                        </button>
                    @else
                        {{-- Botón normal para generar un solo informe --}}
                        <button 
                            wire:click="confirmAndGenerateReport"
                            wire:loading.attr="disabled"
                            wire:target="confirmAndGenerateReport"
                            @if(!$hasDigitalSignature) 
                                disabled
                                type="button"
                            @endif
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <svg wire:loading wire:target="confirmAndGenerateReport" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="confirmAndGenerateReport">Firmar y Generar</span>
                            <span wire:loading wire:target="confirmAndGenerateReport">Generando...</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
