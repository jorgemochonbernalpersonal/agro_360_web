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
