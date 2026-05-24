<div>
    {{-- Welcome Modal --}}
    <div x-data="{ show: @entangle('showModal') }"
         x-show="show"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title"
         role="dialog"
         aria-modal="true">

        {{-- Backdrop --}}
        <div x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-zinc-500 bg-opacity-75 transition-opacity"></div>

        {{-- Modal Content --}}
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="show"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-2xl bg-white shadow-2xl transition-all sm:w-full sm:max-w-2xl">

                {{-- Header with Logo --}}
                <div class="bg-gradient-to-br from-green-500 to-emerald-600 px-8 py-8 text-center">
                    <div class="mx-auto w-32 h-32 mb-4 bg-white rounded-2xl shadow-lg flex items-center justify-center p-4">
                        <img src="{{ asset('images/logo.png') }}" alt="Agro365" class="w-full h-full object-contain">
                    </div>
                    <h2 class="text-2xl font-bold text-white mb-2">
                        {{ __('¡Bienvenido a tu viñedo digital!') }}
                    </h2>
                    <p class="text-green-50 text-base">
                        {{ __('Gestiona tu explotación vitícola de forma profesional') }}
                    </p>
                </div>

                {{-- Content --}}
                <div class="px-8 py-6">
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                                <flux:icon icon="calendar-days" class="size-6 text-green-600" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-zinc-900 text-sm">{{ __('Campañas') }}</h3>
                                <p class="text-xs text-zinc-600">{{ __('Organiza por año vitícola') }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                <flux:icon icon="map" class="size-6 text-blue-600" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-zinc-900 text-sm">{{ __('Parcelas') }}</h3>
                                <p class="text-xs text-zinc-600">{{ __('Con datos SIGPAC y plantaciones') }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                                <span class="text-xl">🧪</span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-zinc-900 text-sm">{{ __('Productos fitosanitarios') }}</h3>
                                <p class="text-xs text-zinc-600">{{ __('Catálogo con números ROPO') }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                                <flux:icon icon="book-open" class="size-6 text-amber-600" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-zinc-900 text-sm">{{ __('Cuaderno digital') }}</h3>
                                <p class="text-xs text-zinc-600">{{ __('Tratamientos, riegos y labores') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <flux:icon icon="information-circle" class="size-5 text-green-600" />
                            </div>
                            <div>
                                <p class="text-sm text-zinc-700 font-medium">
                                    {{ __('4 pasos para empezar a trabajar') }}
                                </p>
                                <p class="text-xs text-zinc-600 mt-1">
                                    {{ __('Campaña → Parcelas → Primera actividad → Productos fitosanitarios') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="bg-zinc-50 px-8 py-4 flex items-center justify-between gap-4">
                    <button wire:click="skipTour"
                            type="button"
                            class="text-sm text-zinc-600 hover:text-zinc-800 transition-colors font-medium">
                        {{ __('Saltar introducción') }}
                    </button>
                    <button wire:click="startTour"
                            type="button"
                            class="px-6 py-2.5 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                        {{ __('Comenzar') }}
                        <flux:icon icon="arrow-right" class="size-4" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
