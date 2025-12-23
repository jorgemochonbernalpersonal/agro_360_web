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

                <div class="mb-6" x-data="{ showPassword: false }">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Confirma tu contraseña</label>
                    <div class="relative">
                        <input 
                            type="password" 
                            wire:model="invalidatePassword"
                            x-bind:type="showPassword ? 'text' : 'password'"
                            class="w-full px-4 py-2 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                            placeholder="Introduce tu contraseña"
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
