{{-- Modal de Éxito --}}
@if($showSuccessModal && $generatedReport)
    <div class="fixed z-50 inset-0 overflow-y-auto" x-data="{ show: true }" x-show="show" x-transition>
        <div class="flex items-center justify-center min-h-screen px-4">
            {{-- Fondo --}}
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 z-10">
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
                        <p class="text-sm text-gray-600 mb-2">
                            <strong>Tipo:</strong> 
                            {{ $generatedReport->report_type_name ?? 'N/A' }}
                        </p>
                        <p class="text-sm text-gray-600 mb-2">
                            <strong>Periodo:</strong> 
                            {{ $generatedReport->period_start ? $generatedReport->period_start->format('d/m/Y') : 'N/A' }} - 
                            {{ $generatedReport->period_end ? $generatedReport->period_end->format('d/m/Y') : 'N/A' }}
                        </p>
                        <p class="text-sm text-gray-600">
                            <strong>Tamaño:</strong> 
                            {{ $generatedReport->formatted_pdf_size ?? 'N/A' }}
                        </p>
                        @if($generatedReport->verification_code ?? null)
                            <p class="text-sm text-gray-600 mt-2">
                                <strong>Código de Verificación:</strong> 
                                <code class="bg-gray-200 px-2 py-1 rounded text-xs font-mono">{{ $generatedReport->verification_code }}</code>
                            </p>
                        @endif
                    </div>

                    <div class="flex space-x-3">
                        <button 
                            wire:click="closeSuccessModal"
                            class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
                        >
                            Ver Lista
                        </button>
                        <a 
                            href="{{ route('viticulturist.official-reports.download', $generatedReport) }}"
                            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold text-center"
                        >
                            Descargar PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
