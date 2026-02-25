<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Firma y Cierre de Campaña"
        subtitle="Validación intermedia y cierre definitivo del cuaderno de campo"
        icon="shield-check"
    />

    @if(!$campaign)
        <flux:callout variant="warning" icon="exclamation-triangle">
            No se ha encontrado ninguna campaña activa. Crea o activa una campaña antes de proceder.
        </flux:callout>
    @else
        {{-- Info de campaña --}}
        <x-agro.card>
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900">{{ $campaign->name }}</h3>
                    <p class="text-sm text-zinc-500 mt-1">
                        {{ $campaign->start_date?->format('d/m/Y') }} — {{ $campaign->end_date?->format('d/m/Y') }}
                    </p>
                </div>
                @if($campaign->locked_at)
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-red-100 text-red-800 text-sm font-medium">
                        <flux:icon icon="lock-closed" class="w-4 h-4" /> Campaña cerrada y bloqueada
                    </span>
                @elseif($campaign->final_validation_signed)
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-100 text-emerald-800 text-sm font-medium">
                        <flux:icon icon="check-circle" class="w-4 h-4" /> Validación final firmada
                    </span>
                @elseif($campaign->mid_validation_signed)
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-100 text-amber-800 text-sm font-medium">
                        <flux:icon icon="clock" class="w-4 h-4" /> Validación intermedia firmada
                    </span>
                @else
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-zinc-100 text-zinc-600 text-sm font-medium">
                        <flux:icon icon="pencil" class="w-4 h-4" /> En curso
                    </span>
                @endif
            </div>
        </x-agro.card>

        {{-- Paso 1: Validación Intermedia --}}
        <x-agro.card>
            <div x-data="{ open: {{ !$campaign->mid_validation_signed ? 'true' : 'false' }} }">
                <button @click="open = !open" class="w-full flex items-center justify-between text-left">
                    <div class="flex items-center gap-3">
                        @if($campaign->mid_validation_signed)
                            <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center">
                                <flux:icon icon="check" class="w-5 h-5 text-emerald-600" />
                            </div>
                        @else
                            <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center">
                                <span class="text-amber-700 font-bold text-sm">1</span>
                            </div>
                        @endif
                        <div>
                            <h3 class="font-semibold text-zinc-900">Validación Intermedia (Mitad de campaña)</h3>
                            @if($campaign->mid_validation_signed)
                                <p class="text-sm text-emerald-600">
                                    Firmado el {{ $campaign->mid_validation_date?->format('d/m/Y H:i') }}
                                </p>
                            @else
                                <p class="text-sm text-zinc-500">Revisión de todas las actividades hasta la fecha</p>
                            @endif
                        </div>
                    </div>
                    <flux:icon icon="chevron-down" class="w-5 h-5 text-zinc-400 transition-transform" :class="{ 'rotate-180': open }" />
                </button>

                <div x-show="open" x-transition class="mt-4 pt-4 border-t border-zinc-100">
                    @if($campaign->mid_validation_signed)
                        <flux:callout variant="success" icon="check-circle">
                            Validación intermedia firmada correctamente el {{ $campaign->mid_validation_date?->format('d/m/Y \a \l\a\s H:i') }}.
                        </flux:callout>
                    @else
                        <flux:callout variant="warning" icon="exclamation-triangle" class="mb-4">
                            Al firmar la validación intermedia declaras que todos los registros del cuaderno de campo hasta la fecha son correctos y completos.
                        </flux:callout>

                        <div class="space-y-4">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <flux:checkbox wire:model="confirmMid" class="mt-0.5" />
                                <span class="text-sm text-zinc-700">
                                    Confirmo que he revisado todos los registros de actividades agrícolas, tratamientos fitosanitarios, fertilizaciones, riegos y cosechas hasta la fecha, y que son correctos y completos conforme a la normativa vigente.
                                </span>
                            </label>
                            @error('confirmMid') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                            <flux:button
                                variant="primary"
                                icon="shield-check"
                                wire:click="signMidValidation"
                                wire:confirm="¿Firmar la validación intermedia? Esta acción quedará registrada con tu usuario y fecha."
                                :disabled="!$campaign->mid_validation_signed && !$confirmMid"
                            >
                                Firmar Validación Intermedia
                            </flux:button>
                        </div>
                    @endif
                </div>
            </div>
        </x-agro.card>

        {{-- Paso 2: Cierre Final --}}
        <x-agro.card>
            <div x-data="{ open: {{ $campaign->mid_validation_signed && !$campaign->final_validation_signed ? 'true' : 'false' }} }">
                <button @click="open = !open" class="w-full flex items-center justify-between text-left">
                    <div class="flex items-center gap-3">
                        @if($campaign->final_validation_signed)
                            <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center">
                                <flux:icon icon="check" class="w-5 h-5 text-emerald-600" />
                            </div>
                        @elseif($campaign->mid_validation_signed)
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                <span class="text-blue-700 font-bold text-sm">2</span>
                            </div>
                        @else
                            <div class="w-8 h-8 rounded-full bg-zinc-100 flex items-center justify-center">
                                <span class="text-zinc-400 font-bold text-sm">2</span>
                            </div>
                        @endif
                        <div>
                            <h3 class="font-semibold text-zinc-900 {{ !$campaign->mid_validation_signed ? 'text-zinc-400' : '' }}">
                                Cierre y Firma Final (Fin de campaña)
                            </h3>
                            @if($campaign->final_validation_signed)
                                <p class="text-sm text-emerald-600">
                                    Campaña cerrada el {{ $campaign->final_validation_date?->format('d/m/Y H:i') }}
                                </p>
                            @elseif(!$campaign->mid_validation_signed)
                                <p class="text-sm text-zinc-400">Requiere la validación intermedia primero</p>
                            @else
                                <p class="text-sm text-zinc-500">Cierre definitivo del cuaderno de campaña</p>
                            @endif
                        </div>
                    </div>
                    <flux:icon icon="chevron-down" class="w-5 h-5 text-zinc-400 transition-transform" :class="{ 'rotate-180': open }" />
                </button>

                <div x-show="open" x-transition class="mt-4 pt-4 border-t border-zinc-100">
                    @if(!$campaign->mid_validation_signed)
                        <flux:callout variant="info" icon="information-circle">
                            Debes completar la validación intermedia (Paso 1) antes de cerrar la campaña.
                        </flux:callout>
                    @elseif($campaign->final_validation_signed)
                        <flux:callout variant="success" icon="lock-closed">
                            Campaña cerrada y bloqueada el {{ $campaign->locked_at?->format('d/m/Y \a \l\a\s H:i') }}.
                            No se pueden realizar más modificaciones.
                        </flux:callout>
                    @else
                        <flux:callout variant="danger" icon="exclamation-triangle" class="mb-4">
                            <strong>Atención:</strong> El cierre de campaña es irreversible. Una vez firmado, el cuaderno quedará bloqueado y no podrás añadir ni modificar registros.
                        </flux:callout>

                        <div class="space-y-4">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <flux:checkbox wire:model="confirmFinal" class="mt-0.5" />
                                <span class="text-sm text-zinc-700">
                                    Confirmo que el cuaderno de campo de la campaña <strong>{{ $campaign->name }}</strong> está completo, es correcto y cumple con todos los requisitos legales. Autorizo el cierre definitivo y el bloqueo de edición.
                                </span>
                            </label>
                            @error('confirmFinal') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                            <flux:button
                                variant="danger"
                                icon="lock-closed"
                                wire:click="signFinalValidation"
                                wire:confirm="¿Cerrar definitivamente la campaña? Esta acción BLOQUEARÁ el cuaderno de campo y no se podrán hacer más cambios."
                                :disabled="!$confirmFinal"
                            >
                                Cerrar y Firmar Campaña Definitivamente
                            </flux:button>
                        </div>
                    @endif
                </div>
            </div>
        </x-agro.card>
    @endif
</div>
