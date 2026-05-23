<div class="max-w-lg mx-auto space-y-6 animate-fade-in">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        @if($step > 1)
            <flux:button wire:click="back" variant="ghost" icon="arrow-left" />
        @endif
        <div>
            <h1 class="text-xl font-bold text-zinc-900">Entrada rápida</h1>
            <p class="text-sm text-zinc-400">
                @if($step === 1) Elige el tipo de actividad
                @elseif($step === 2) Selecciona parcela y fecha
                @endif
            </p>
        </div>
        {{-- Step indicator --}}
        <div class="ml-auto flex items-center gap-1.5">
            <div class="w-2 h-2 rounded-full {{ $step >= 1 ? 'bg-agro-600' : 'bg-zinc-200' }}"></div>
            <div class="w-2 h-2 rounded-full {{ $step >= 2 ? 'bg-agro-600' : 'bg-zinc-200' }}"></div>
        </div>
    </div>

    {{-- Paso 1: Tipo de actividad --}}
    @if($step === 1)
        <div class="grid grid-cols-2 gap-3">
            @foreach($activityTypes as $type => $info)
                <button
                    wire:click="selectType('{{ $type }}')"
                    class="flex items-center gap-3 p-4 border-2 rounded-2xl transition-all hover:shadow-md active:scale-95 {{ $info['color'] }}"
                >
                    <span class="text-2xl leading-none flex-shrink-0">{{ $info['icon'] }}</span>
                    <span class="text-sm font-semibold text-left leading-tight">{{ $info['label'] }}</span>
                </button>
            @endforeach
        </div>
    @endif

    {{-- Paso 2: Parcela, fecha y notas --}}
    @if($step === 2)
        <x-agro.card>
            <div class="space-y-5">

                {{-- Actividad seleccionada --}}
                @php
                    $selectedType = $activityTypes[$activityType] ?? null;
                @endphp
                @if($selectedType)
                    <div class="flex items-center gap-3 p-3 bg-agro-50 rounded-xl border border-agro-200">
                        <span class="text-2xl">{{ $selectedType['icon'] }}</span>
                        <div>
                            <p class="text-sm font-semibold text-agro-800">{{ $selectedType['label'] }}</p>
                            <p class="text-xs text-agro-600">Actividad seleccionada</p>
                        </div>
                    </div>
                @endif

                {{-- Parcela --}}
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1.5">Parcela *</label>
                    @if($plots->count() > 0)
                        <div class="space-y-1 max-h-48 overflow-y-auto border border-zinc-200 rounded-xl p-1">
                            @foreach($plots as $plot)
                                <label class="flex items-center gap-3 px-3 py-2.5 rounded-lg cursor-pointer hover:bg-zinc-50 transition-colors {{ (string)$plotId === (string)$plot->id ? 'bg-agro-50 border border-agro-200' : '' }}">
                                    <input
                                        type="radio"
                                        wire:model.live="plotId"
                                        value="{{ $plot->id }}"
                                        class="text-agro-600 focus:ring-agro-500"
                                    />
                                    <span class="text-sm font-medium text-zinc-900">{{ $plot->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="py-4 text-center space-y-3">
                            <p class="text-sm text-zinc-500">Aún no tienes parcelas registradas.</p>
                            <a href="{{ route('plots.create') }}" wire:navigate
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-agro-600 text-white text-sm font-medium rounded-xl hover:bg-agro-700 transition-colors">
                                <flux:icon icon="plus" class="size-4" />
                                Crear mi primera parcela
                            </a>
                        </div>
                    @endif
                    @error('plotId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Fecha --}}
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1.5">Fecha *</label>
                    <flux:input
                        type="date"
                        wire:model="activityDate"
                        max="{{ now()->toDateString() }}"
                    />
                    @error('activityDate') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Notas --}}
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1.5">
                        Notas <span class="font-normal text-zinc-400">(opcional)</span>
                    </label>
                    <flux:textarea
                        wire:model="notes"
                        rows="3"
                        placeholder="Observaciones rápidas..."
                    />
                </div>

                {{-- Guardar --}}
                <flux:button
                    wire:click="save"
                    variant="primary"
                    icon="check"
                    class="w-full"
                    :disabled="!$plotId"
                >
                    Registrar actividad
                </flux:button>

            </div>
        </x-agro.card>
    @endif

    {{-- Enlace al cuaderno completo --}}
    <div class="text-center">
        <a href="{{ roleRoute('viticulturist.digital-notebook') }}" wire:navigate
            class="text-xs text-zinc-400 hover:text-agro-600 transition-colors">
            Ir al cuaderno de campo completo →
        </a>
    </div>

</div>
