{{-- Modal de Invalidar --}}
@if($showInvalidateModal && $reportToInvalidate)
    <div class="fixed z-50 inset-0 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-zinc-500 bg-opacity-75 transition-opacity" wire:click="closeInvalidateModal"></div>
            
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6">
                <h3 class="text-2xl font-bold text-zinc-900 mb-4">⚠️ Invalidar Informe</h3>
                <p class="text-zinc-600 mb-6">
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
                    <label class="block text-sm font-semibold text-zinc-700 mb-2">Motivo de invalidación</label>
                    <textarea 
                        wire:model="invalidateReason"
                        rows="3"
                        class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                        placeholder="Explica por qué invalidas este informe (mínimo 10 caracteres)..."
                    ></textarea>
                    @error('invalidateReason') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="mb-6" x-data="{ showPassword: false }">
                    <label class="block text-sm font-semibold text-zinc-700 mb-2">Confirma tu contraseña</label>
                    <div class="relative">
                        <input 
                            type="password" 
                            wire:model="invalidatePassword"
                            x-bind:type="showPassword ? 'text' : 'password'"
                            class="w-full px-4 py-2 pr-12 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                            placeholder="Introduce tu contraseña"
                        >
                        <button
                            type="button"
                            x-on:click="showPassword = !showPassword"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-zinc-500 hover:text-zinc-700 focus:outline-none"
                            tabindex="-1"
                        >
                            <flux:icon x-show="!showPassword" icon="eye" class="size-5" />
                            <flux:icon x-show="showPassword" icon="eye-slash" class="size-5" style="display: none;" />
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
                        class="px-4 py-2 bg-zinc-200 text-zinc-700 rounded-lg hover:bg-zinc-300 transition-colors disabled:opacity-50"
                    >
                        Cancelar
                    </button>
                    <button 
                        wire:click="invalidateReport"
                        wire:loading.attr="disabled"
                        wire:target="invalidateReport"
                        class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-semibold disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                    >
                        <flux:icon wire:loading wire:target="invalidateReport" icon="arrow-path" class="animate-spin size-4" />
                        <span wire:loading.remove wire:target="invalidateReport">Invalidar Informe</span>
                        <span wire:loading wire:target="invalidateReport">Invalidando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
