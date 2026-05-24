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
                            <h3 class="text-sm font-bold text-zinc-900">{{ __('Primeros pasos en Agro365') }}</h3>
                            <div class="flex items-center gap-2 mt-0.5">
                                <div class="w-32 bg-zinc-200 rounded-full h-1.5">
                                    <div class="bg-green-500 h-1.5 rounded-full transition-all duration-500"
                                         style="width: {{ $progressPercentage }}%"></div>
                                </div>
                                <span class="text-xs font-medium text-green-600">{{ $progressPercentage }}%</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-zinc-500">
                            {{ count(array_filter($steps, fn($s) => $s['completed'])) }}/{{ count($steps) }}
                        </span>
                        <flux:icon icon="chevron-down" class="size-5 text-zinc-400 transition-transform" ::class="{ 'rotate-180': expanded }" />
                    </div>
                </div>
            </div>

            {{-- Expanded Content --}}
            <div x-show="expanded"
                 x-collapse
                 class="mt-2 bg-white rounded-lg border border-zinc-200 p-4">

                {{-- Steps List --}}
                <div class="space-y-2 mb-3">
                    @foreach($steps as $step)
                        <div class="flex items-center gap-2 p-2 rounded hover:bg-zinc-50 transition-colors">
                            {{-- Checkbox --}}
                            <div class="flex-shrink-0">
                                @if($step['completed'])
                                    <div class="w-5 h-5 rounded-full bg-green-500 flex items-center justify-center">
                                        <flux:icon icon="check" class="size-3 text-white" />
                                    </div>
                                @else
                                    <div class="w-5 h-5 rounded-full border-2 border-zinc-300"></div>
                                @endif
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-zinc-900 {{ $step['completed'] ? 'line-through text-zinc-500' : '' }}">
                                    {{ $step['icon'] }} {{ $step['title'] }}
                                </p>
                            </div>

                            {{-- Action --}}
                            @if(!$step['completed'])
                                <a href="{{ $step['route'] }}"
                                   wire:navigate
                                   class="flex-shrink-0 px-2 py-1 bg-green-500 hover:bg-green-600 text-white text-xs font-medium rounded transition-colors">
                                    {{ __('Ir') }}
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-between pt-3 border-t border-zinc-200">
                    <button wire:click="skipAll"
                            class="text-xs text-zinc-500 hover:text-zinc-700 transition-colors">
                        {{ __('Saltar tour') }}
                    </button>

                    @if($progressPercentage === 100)
                        <div class="flex items-center gap-1 text-green-600">
                            <flux:icon icon="check-circle" class="size-4" />
                            <span class="text-xs font-semibold">{{ __('¡Completado!') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Success Message --}}
        @if(session('onboarding_complete'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-3 py-2 rounded-lg mb-4 text-sm" role="alert">
                <div class="flex items-center gap-2">
                    <flux:icon icon="check-circle" class="size-4" />
                    <span class="font-semibold">{{ __('¡Enhorabuena! Has completado el onboarding.') }}</span>
                </div>
            </div>
        @endif
    @else
        {{-- Botón discreto para reactivar onboarding cuando está oculto --}}
        <div class="mb-4">
            <button wire:click="resetOnboarding"
                    class="text-xs text-zinc-500 hover:text-green-600 transition-colors flex items-center gap-1">
                <flux:icon icon="arrow-path" class="size-3" />
                {{ __('Volver a ver el tour de bienvenida') }}
            </button>
        </div>
    @endif
</div>
