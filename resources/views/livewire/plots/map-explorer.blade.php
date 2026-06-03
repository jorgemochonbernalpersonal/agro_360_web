<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        :title="__('Explorador de Mapas')"
        :description="__('Filtra por territorio para ver todas tus parcelas en el mapa')"
    >
        <x-slot:actions>
            <flux:button href="{{ route('plots.index') }}" variant="ghost" icon="arrow-left">
                {{ __('Parcelas') }}
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Explicación del flujo --}}
    <div class="max-w-xl rounded-xl border border-blue-200 bg-blue-50 p-4 flex gap-3">
        <flux:icon icon="information-circle" class="size-5 text-blue-500 shrink-0 mt-0.5" />
        <div class="text-sm text-blue-800 space-y-1">
            <p class="font-semibold">{{ __('¿Cómo funciona?') }}</p>
            <p class="text-blue-700">
                {{ __('Selecciona comunidad autónoma, provincia y municipio para ver en un único mapa todas las parcelas que tienes registradas en ese municipio.') }}
            </p>
            <p class="text-blue-600 text-xs">
                {{ __('Solo aparecen los territorios donde ya tienes mapas generados.') }}
            </p>
        </div>
    </div>

    <x-agro.card class="max-w-xl">
        <div class="space-y-5">

            {{-- Comunidad autónoma --}}
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">
                    {{ __('Comunidad autónoma') }}
                </label>
                <flux:select wire:model.live="communityId" placeholder="{{ __('Selecciona comunidad...') }}">
                    @foreach($communities as $community)
                        <flux:select.option value="{{ $community->id }}">{{ $community->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                @if($communities->isEmpty())
                    <p class="mt-1 text-xs text-zinc-400">{{ __('No tienes parcelas con mapas generados.') }}</p>
                @endif
            </div>

            {{-- Provincia --}}
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">
                    {{ __('Provincia') }}
                </label>
                <flux:select wire:model.live="provinceId"
                             placeholder="{{ __('Selecciona provincia...') }}"
                             :disabled="!$communityId">
                    @foreach($provinces as $province)
                        <flux:select.option value="{{ $province->id }}">{{ $province->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            {{-- Municipio --}}
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">
                    {{ __('Municipio') }}
                </label>
                <flux:select wire:model.live="municipalityId"
                             placeholder="{{ __('Selecciona municipio...') }}"
                             :disabled="!$provinceId">
                    @foreach($municipalities as $municipality)
                        <flux:select.option value="{{ $municipality->id }}">{{ $municipality->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            {{-- Preview del municipio seleccionado --}}
            @if($municipalityId && $geometryCount !== null)
                <div class="rounded-lg border border-agro-200 bg-agro-50 p-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-agro-100 flex items-center justify-center shrink-0">
                        <flux:icon icon="map" class="size-5 text-agro-600" />
                    </div>
                    <div class="flex-1">
                        @if($geometryCount > 0)
                            <p class="text-sm font-semibold text-agro-800">
                                {{ $geometryCount }} {{ __('recinto(s) con mapa generado') }}
                                @if($plotCount)
                                    · {{ $plotCount }} {{ __('parcela(s)') }}
                                @endif
                            </p>
                            <p class="text-xs text-agro-600 mt-0.5">{{ __('Listo para visualizar en el mapa') }}</p>
                        @else
                            <p class="text-sm font-semibold text-amber-700">{{ __('Sin mapas generados') }}</p>
                            <p class="text-xs text-amber-600 mt-0.5">
                                {{ __('Genera los mapas desde la lista de parcelas antes de visualizarlos.') }}
                            </p>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Botón --}}
            <flux:button
                wire:click="viewMap"
                variant="primary"
                icon="map"
                class="w-full"
                :disabled="!$municipalityId || $geometryCount === 0"
            >
                {{ __('Ver mapa del municipio') }}
            </flux:button>

        </div>
    </x-agro.card>

</div>
