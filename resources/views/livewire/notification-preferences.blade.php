<div class="space-y-6">
    {{-- Delivery mode --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-3">
                <flux:icon icon="clock" class="size-5 text-agro-600" />
                <div>
                    <p class="font-bold text-zinc-900">{{ __('Modo de entrega') }}</p>
                    <p class="text-sm text-zinc-500">{{ __('Elige si recibir emails al instante o un resumen diario') }}</p>
                </div>
            </div>
        </x-slot:header>

        <div class="flex gap-4">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" wire:model="delivery" value="instant"
                       class="text-agro-600 focus:ring-agro-500">
                <span class="text-sm text-zinc-700">{{ __('Instantáneo') }}</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" wire:model="delivery" value="digest"
                       class="text-agro-600 focus:ring-agro-500">
                <span class="text-sm text-zinc-700">{{ __('Resumen diario (08:00)') }}</span>
            </label>
        </div>
    </x-agro.card>

    {{-- Categories by section --}}
    @foreach($categories as $sectionKey => $section)
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-3">
                    <flux:icon icon="bell" class="size-5 text-amber-600" />
                    <div>
                        <p class="font-bold text-zinc-900">{{ $section['label'] }}</p>
                    </div>
                </div>
            </x-slot:header>

            <div class="divide-y divide-zinc-100">
                @foreach($section['items'] as $catKey => $cat)
                    <div class="py-3 flex items-center justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-zinc-900">{{ $cat['label'] }}</p>
                            <p class="text-xs text-zinc-500">{{ $cat['description'] }}</p>
                        </div>

                        <div class="flex items-center gap-3 flex-shrink-0">
                            {{-- Database (always on, shown as info) --}}
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-zinc-100 text-zinc-500 text-xs" title="{{ __('Siempre activo en la app') }}">
                                <flux:icon icon="bell" class="size-3.5" />
                                App
                            </span>

                            {{-- Mail toggle --}}
                            @if(in_array('mail', $cat['available_channels']))
                                <button
                                    wire:click="toggleChannel('{{ $catKey }}', 'mail')"
                                    class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs transition-colors
                                        {{ in_array('mail', $channels[$catKey] ?? [])
                                            ? 'bg-agro-100 text-agro-700 ring-1 ring-agro-300'
                                            : 'bg-zinc-100 text-zinc-400 hover:bg-zinc-200' }}"
                                    title="{{ in_array('mail', $channels[$catKey] ?? []) ? __('Desactivar email') : __('Activar email') }}"
                                >
                                    <flux:icon icon="envelope" class="size-3.5" />
                                    Email
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </x-agro.card>
    @endforeach

    {{-- Save button --}}
    <div class="flex justify-end">
        <flux:button wire:click="save" variant="primary">{{ __('Guardar preferencias') }}</flux:button>
    </div>
</div>
