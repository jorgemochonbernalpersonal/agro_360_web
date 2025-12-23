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
                    <a 
                        href="{{ route('viticulturist.official-reports.download', $reportToPreview) }}"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-semibold"
                    >
                        ⬇️ Descargar PDF
                    </a>
                </div>

                <div class="h-[calc(90vh-180px)] rounded-lg overflow-hidden border-2 border-gray-200">
                    <iframe 
                        src="{{ route('viticulturist.official-reports.preview', $reportToPreview) }}" 
                        class="w-full h-full"
                        frameborder="0"
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
@endif
