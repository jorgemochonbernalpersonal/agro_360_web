<div>
    @if($show)
        <div x-data="{ expanded: false }" class="mb-4">
            {{-- Compact Header (Always Visible) --}}
            <div @click="expanded = !expanded" 
                 class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border border-green-200 p-3 cursor-pointer hover:shadow-md transition-all">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center text-white text-lg">
                            🚀
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Primeros pasos en Agro365</h3>
                            <div class="flex items-center gap-2 mt-0.5">
                                <div class="w-32 bg-gray-200 rounded-full h-1.5">
                                    <div class="bg-green-500 h-1.5 rounded-full transition-all duration-500" 
                                         style="width: {{ $progressPercentage }}%"></div>
                                </div>
                                <span class="text-xs font-medium text-green-600">{{ $progressPercentage }}%</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-500">
                            {{ count(array_filter($steps, fn($s) => $s['completed'])) }}/{{ count($steps) }}
                        </span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" 
                             :class="{ 'rotate-180': expanded }"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Expanded Content --}}
            <div x-show="expanded" 
                 x-collapse
                 class="mt-2 bg-white rounded-lg border border-gray-200 p-4">
                
                {{-- Steps List --}}
                <div class="space-y-2 mb-3">
                    @foreach($steps as $step)
                        <div class="flex items-center gap-2 p-2 rounded hover:bg-gray-50 transition-colors">
                            {{-- Checkbox --}}
                            <div class="flex-shrink-0">
                                @if($step['completed'])
                                    <div class="w-5 h-5 rounded-full bg-green-500 flex items-center justify-center">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-5 h-5 rounded-full border-2 border-gray-300"></div>
                                @endif
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 {{ $step['completed'] ? 'line-through text-gray-500' : '' }}">
                                    {{ $step['icon'] }} {{ $step['title'] }}
                                </p>
                            </div>

                            {{-- Action --}}
                            @if(!$step['completed'])
                                <a href="{{ $step['route'] }}" 
                                   wire:navigate
                                   class="flex-shrink-0 px-2 py-1 bg-green-500 hover:bg-green-600 text-white text-xs font-medium rounded transition-colors">
                                    Ir
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                    <button wire:click="skipAll" 
                            class="text-xs text-gray-500 hover:text-gray-700 transition-colors">
                        Saltar tour
                    </button>
                    
                    @if($progressPercentage === 100)
                        <div class="flex items-center gap-1 text-green-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-xs font-semibold">¡Completado!</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Success Message --}}
        @if(session('onboarding_complete'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-3 py-2 rounded-lg mb-4 text-sm" role="alert">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-semibold">¡Enhorabuena! Has completado el onboarding.</span>
                </div>
            </div>
        @endif
    @else
        {{-- Botón discreto para reactivar onboarding cuando está oculto --}}
        <div class="mb-4">
            <button wire:click="resetOnboarding" 
                    class="text-xs text-gray-500 hover:text-green-600 transition-colors flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Volver a ver el tour de bienvenida
            </button>
        </div>
    @endif
</div>
