<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="{{ __('Invitar Viticultor Existente') }}"
        :description="__('Busca un viticultor registrado en el sistema y vincúlalo a tu bodega')"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturists.index') }}" variant="ghost" icon="arrow-left">
                {{ __('Volver') }}
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-agro-50">
                    <flux:icon icon="magnifying-glass" class="size-4 text-agro-600" />
                </div>
                <span class="font-semibold text-zinc-900 text-sm">{{ __('Buscar Viticultor') }}</span>
            </div>
        </x-slot:header>

        <div class="space-y-5">
            <flux:field>
                <flux:label>{{ __('Nombre, email o DNI') }}</flux:label>
                <flux:input
                    wire:model.live.debounce.400ms="search"
                    placeholder="{{ __('Escribe al menos 3 caracteres...') }}"
                    icon="magnifying-glass"
                    autofocus
                />
                <flux:description>{{ __('Solo se muestran viticultores con acceso al sistema que aún no están vinculados a tu bodega.') }}</flux:description>
            </flux:field>

            {{-- Sin búsqueda todavía --}}
            @if(mb_strlen(trim($search)) < 3)
                <div class="flex flex-col items-center justify-center py-10 text-zinc-400 gap-2">
                    <flux:icon icon="magnifying-glass" class="size-10" />
                    <p class="text-sm">{{ __('Introduce al menos 3 caracteres para buscar') }}</p>
                </div>

            {{-- Sin resultados --}}
            @elseif(empty($results))
                <div class="flex flex-col items-center justify-center py-10 text-zinc-400 gap-2">
                    <flux:icon icon="user-minus" class="size-10" />
                    <p class="text-sm font-medium">{{ __('No se encontraron viticultores') }}</p>
                    <p class="text-xs text-center max-w-xs">
                        Si el viticultor aún no tiene cuenta, puedes
                        <a href="{{ roleRoute('viticulturists.create') }}" class="text-agro-600 hover:underline font-medium">crearlo como ghost</a>.
                    </p>
                </div>

            {{-- Resultados --}}
            @else
                <div class="divide-y divide-zinc-100 border border-zinc-200 rounded-lg overflow-hidden">
                    @foreach($results as $result)
                        <div class="flex items-center justify-between px-4 py-3 bg-white hover:bg-zinc-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-agro-50 flex items-center justify-center flex-shrink-0">
                                    <span class="text-sm font-bold text-agro-700">
                                        {{ strtoupper(substr($result['name'], 0, 1)) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-zinc-900">{{ $result['name'] }}</p>
                                    <p class="text-xs text-zinc-500">
                                        {{ $result['email'] ?? '—' }}
                                        @if($result['dni'])
                                            · DNI: {{ $result['dni'] }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                @if($confirmingId === $result['id'])
                                    <span class="text-xs text-zinc-500">{{ __('¿Confirmar?') }}</span>
                                    <flux:button
                                        wire:click="link({{ $result['id'] }})"
                                        variant="primary"
                                        size="sm"
                                        icon="check"
                                    >
                                        Sí, vincular
                                    </flux:button>
                                    <flux:button
                                        wire:click="cancelConfirmation"
                                        variant="ghost"
                                        size="sm"
                                        icon="x-mark"
                                    >{{ __('Cancelar') }}</flux:button>
                                @else
                                    <flux:button
                                        wire:click="confirm({{ $result['id'] }})"
                                        variant="filled"
                                        size="sm"
                                        icon="link"
                                    >
                                        Vincular
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <p class="text-xs text-zinc-400 text-center">
                    {{ count($results) === 10 ? 'Mostrando los primeros 10 resultados. Refina la búsqueda para más precisión.' : count($results) . ' resultado(s) encontrado(s).' }}
                </p>
            @endif
        </div>
    </x-agro.card>
</div>
