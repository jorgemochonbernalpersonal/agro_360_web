{{-- Modal de Resumen --}}
@if($showSummaryModal)
    <div class="fixed z-50 inset-0 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-zinc-500 bg-opacity-75 transition-opacity" wire:click="closeSummaryModal"></div>

            <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-6">
                <h3 class="text-2xl font-bold text-zinc-900 mb-4 flex items-center gap-2"><flux:icon icon="chart-bar" class="size-7" /> {{ __('Resumen del Informe') }}</h3>
                <p class="text-zinc-600 mb-6">
                    {{ __('Revisa los datos antes de firmar electrónicamente. Este proceso puede tardar varios segundos.') }}
                </p>

                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6 mb-6 border-2 border-blue-200">
                    @if($reportSummary['type'] ?? '' === 'phytosanitary_treatments')
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-zinc-600 mb-1">{{ __('Periodo') }}</p>
                                <p class="text-lg font-bold text-zinc-900">{{ $reportSummary['period'] ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-zinc-600 mb-1">{{ __('Total Tratamientos') }}</p>
                                <p class="text-lg font-bold text-blue-900">{{ $reportSummary['total_treatments'] ?? 0 }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-zinc-600 mb-1">{{ __('Parcelas Afectadas') }}</p>
                                <p class="text-lg font-bold text-zinc-900">{{ $reportSummary['plots_count'] ?? 0 }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-zinc-600 mb-1">{{ __('Productos Usados') }}</p>
                                <p class="text-lg font-bold text-zinc-900">{{ $reportSummary['products_count'] ?? 0 }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-zinc-600 mb-1">{{ __('Área Total Tratada') }}</p>
                                <p class="text-lg font-bold text-green-900">{{ $reportSummary['total_area'] ?? 0 }} ha</p>
                            </div>
                            <div>
                                <p class="text-sm text-zinc-600 mb-1">{{ __('Tamaño Estimado') }}</p>
                                <p class="text-lg font-bold text-zinc-900">{{ $reportSummary['estimated_size'] ?? '-' }}</p>
                            </div>
                        </div>
                    @else
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <p class="text-sm text-zinc-600 mb-1">{{ __('Campaña') }}</p>
                                <p class="text-lg font-bold text-zinc-900">{{ $reportSummary['campaign'] ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-zinc-600 mb-1">{{ __('Total Actividades') }}</p>
                                <p class="text-lg font-bold text-blue-900">{{ $reportSummary['total_activities'] ?? 0 }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-zinc-600 mb-1">{{ __('Tamaño Estimado') }}</p>
                                <p class="text-lg font-bold text-zinc-900">{{ $reportSummary['estimated_size'] ?? '-' }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Opción de generación por lotes --}}
                @if($showBatchOption && isset($batchPeriods) && count($batchPeriods) > 0)
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-300 rounded-xl p-6 mb-6">
                        <div class="flex items-start gap-3 mb-4">
                            <div class="flex-shrink-0 w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                                <flux:icon icon="cube" class="size-6 text-white" />
                            </div>
                            <div class="flex-1">
                                <h4 class="text-lg font-bold text-blue-900 mb-2">
                                    📦 {{ __('Generación por Lotes Recomendada') }}
                                </h4>
                                <p class="text-sm text-blue-800 mb-3">
                                    {{ __('Tu campaña tiene') }} <strong>{{ $reportSummary['total_activities'] ?? 0 }} {{ __('actividades') }}</strong>.
                                    {{ __('Para mejor rendimiento, se generarán') }} <strong>{{ $totalBatches }} {{ __('informes') }}</strong> {{ __('automáticamente') }}:
                                </p>
                                <div class="bg-white rounded-lg p-4 mb-4 border border-blue-200">
                                    <ul class="space-y-2">
                                        @foreach($batchPeriods as $period)
                                            <li class="flex items-center justify-between text-sm">
                                                <span class="font-semibold text-zinc-700">{{ $period['label'] }}</span>
                                                <span class="text-blue-600 font-medium">{{ $period['count'] }} {{ __('actividades') }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <p class="text-xs text-blue-700 mb-4">
                                    💡 {{ __('Cada informe se generará por separado y recibirás una notificación cuando cada uno esté listo.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-r-lg mb-6">
                        <div class="flex items-start gap-3">
                            <flux:icon icon="exclamation-triangle" variant="solid" class="size-5 text-yellow-600 flex-shrink-0 mt-0.5" />
                            <div>
                                <p class="text-sm font-semibold text-yellow-900">{{ __('Tiempo Estimado de Generación') }}</p>
                                <p class="text-sm text-yellow-800 mt-1">
                                    {{ __('Este informe puede tardar') }} <strong>{{ $reportSummary['estimated_time'] ?? '10-15' }} {{ __('segundos') }}</strong> {{ __('en generarse.') }}
                                    {{ __('Por favor, no cierres la ventana durante el proceso.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Advertencia si no tiene contraseña configurada --}}
                @if(!$hasDigitalSignature)
                    <div class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded-r-lg">
                        <div class="flex items-start gap-3">
                            <flux:icon icon="exclamation-triangle" variant="solid" class="size-5 text-yellow-600 flex-shrink-0 mt-0.5" />
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-yellow-800">{{ __('No tienes contraseña de firma configurada') }}</p>
                                <p class="text-sm text-yellow-700 mt-1">
                                    {{ __('Debes crear una contraseña de firma digital en') }}
                                    <a href="{{ roleRoute('viticulturist.settings', ['tab' => 'signature']) }}"
                                       wire:navigate
                                       class="underline font-semibold hover:text-yellow-900">
                                        {{ __('Configuración → Firma Digital') }}
                                    </a> {{ __('antes de poder generar informes.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- HINT VISUAL IMPORTANTE: No es la contraseña de login --}}
                <div class="mb-6 p-4 bg-gradient-to-r from-purple-50 to-indigo-50 border-2 border-purple-400 rounded-xl shadow-md">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center">
                            <flux:icon icon="lock-closed" class="size-6 text-white" />
                        </div>
                        <div class="flex-1">
                            <h4 class="text-base font-bold text-purple-900 mb-1">⚠️ {{ __('IMPORTANTE: Usa tu Contraseña de Firma Digital') }}</h4>
                            <p class="text-sm text-purple-800 leading-relaxed">
                                {{ __('Esta') }} <strong class="underline">{{ __('NO es tu contraseña de login') }}</strong>. {{ __('Es la contraseña específica que creaste en') }}
                                <a href="{{ roleRoute('viticulturist.settings', ['tab' => 'signature']) }}"
                                   wire:navigate
                                   target="_blank"
                                   class="inline-flex items-center gap-1 text-purple-700 hover:text-purple-900 font-bold underline decoration-2">
                                    {{ __('Configuración → Firma Digital') }}
                                    <flux:icon icon="arrow-top-right-on-square" class="size-3" />
                                </a>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Campo de Contraseña en el Modal --}}
                <div class="mb-6" x-data="{ showPassword: false }">
                    <label class="block text-sm font-semibold text-zinc-700 mb-2 flex items-center gap-2">
                        <flux:icon icon="lock-closed" class="size-5 text-green-600" />
                        {{ __('Contraseña de Firma Digital') }}
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
                            class="w-full px-4 py-3 pr-12 border-2 border-zinc-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition {{ $hasDigitalSignature ? '' : 'opacity-50 cursor-not-allowed bg-zinc-100' }}"
                            placeholder="{{ $hasDigitalSignature ? __('Introduce tu contraseña de firma digital') : __('Configura tu contraseña primero') }}"
                            @if($hasDigitalSignature) autofocus @endif
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
                    <div class="mt-2 flex items-center gap-2 text-xs text-zinc-600">
                        <flux:icon icon="lock-closed" class="size-4 text-zinc-500" />
                        <span>{{ __('Se usa exclusivamente para firmar documentos oficiales') }}</span>
                    </div>
                    @error('password')
                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                    @error('generation')
                        <div class="mt-3 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg">
                            <div class="flex items-start gap-3">
                                <flux:icon icon="x-circle" variant="solid" class="size-5 text-red-600 flex-shrink-0 mt-0.5" />
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-red-800">{{ __('Error al generar el informe') }}</p>
                                    <p class="text-sm text-red-700 mt-1">{{ $message }}</p>
                                </div>
                            </div>
                        </div>
                    @enderror
                </div>

                <div class="flex justify-end space-x-3">
                    <button
                        wire:click="closeSummaryModal"
                        class="px-4 py-2 bg-zinc-200 text-zinc-700 rounded-lg hover:bg-zinc-300 transition-colors"
                    >
                        {{ __('Cancelar') }}
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
                            <flux:icon wire:loading wire:target="generateBatchReports" icon="arrow-path" class="animate-spin size-4" />
                            <span wire:loading.remove wire:target="generateBatchReports">{{ __('Generar') }} {{ $totalBatches }} {{ __('Informes') }}</span>
                            <span wire:loading wire:target="generateBatchReports">{{ __('Generando...') }}</span>
                        </button>

                        {{-- Opción alternativa: generar uno solo --}}
                        <button
                            wire:click="forceGenerateSingle"
                            wire:loading.attr="disabled"
                            class="px-4 py-2 bg-zinc-500 text-white rounded-lg hover:bg-zinc-600 transition-colors text-sm"
                            :title="__('Generar un solo informe (puede tardar más tiempo)')"
                        >
                            {{ __('Un solo informe') }}
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
                            <flux:icon wire:loading wire:target="confirmAndGenerateReport" icon="arrow-path" class="animate-spin size-4" />
                            <span wire:loading.remove wire:target="confirmAndGenerateReport">{{ __('Firmar y Generar') }}</span>
                            <span wire:loading wire:target="confirmAndGenerateReport">{{ __('Generando...') }}</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
