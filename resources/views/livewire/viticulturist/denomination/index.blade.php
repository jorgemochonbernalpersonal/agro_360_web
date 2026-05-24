<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        :title="__('Mi Denominación de Origen')"
        :description="__('Información sobre tu denominación de origen y tus asignaciones.')"
        icon="building-office-2"
    />

    @if(! $supervisor)
        <x-agro.empty-state
            icon="building-office-2"
            :title="__('Sin denominación asignada')"
            :description="__('No estás adscrito a ninguna denominación de origen. Si crees que es un error, contacta con tu DO.')"
        />
    @else

        {{-- DO info card --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <flux:icon icon="building-office-2" class="size-4 text-indigo-500" />
                    <span>{{ __('Denominación de Origen') }}</span>
                </div>
            </x-slot:header>

            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center flex-shrink-0">
                    <flux:icon icon="building-office-2" class="size-6 text-indigo-600" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-base font-semibold text-zinc-900">{{ $supervisor->name }}</p>
                    <p class="text-sm text-zinc-500">{{ $supervisor->email }}</p>
                    @if($supervisorJoined)
                        <p class="text-xs text-zinc-400 mt-1">{{ __('Adscrito desde') }} {{ $supervisorJoined->format('d/m/Y') }}</p>
                    @endif
                </div>
            </div>
        </x-agro.card>

        {{-- Notebook access card --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <flux:icon icon="book-open" class="size-4 text-agro-500" />
                    <span>{{ __('Cuaderno de campo') }}</span>
                </div>
            </x-slot:header>

            @if($notebookGranted)
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center shrink-0">
                        <flux:icon icon="check-circle" class="size-5 text-green-600" />
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-zinc-900">{{ __('Acceso concedido') }}</p>
                        <p class="text-xs text-zinc-500 mt-0.5">{{ __('Tu denominación de origen puede consultar las anotaciones de tu cuaderno de campo.') }}</p>
                    </div>
                    <button
                        wire:click="revokeNotebookAccess"
                        wire:confirm="{{ __('¿Revocar el acceso al cuaderno? Tu DO dejará de poder ver tus anotaciones.') }}"
                        class="shrink-0 text-xs text-red-500 hover:text-red-700 hover:underline transition"
                    >
                        {{ __('Revocar acceso') }}
                    </button>
                </div>
            @elseif($pendingRequest)
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                        <flux:icon icon="clock" class="size-5 text-amber-500" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-zinc-900">{{ __('Solicitud pendiente') }}</p>
                        <p class="text-xs text-zinc-500 mt-0.5">
                            {{ __('Tu denominación ha solicitado acceso a tu cuaderno el') }}
                            {{ $pendingRequest->requested_at->format('d/m/Y') }}.
                            {{ __('Puedes aprobarla o rechazarla desde las notificaciones.') }}
                        </p>
                    </div>
                </div>
            @else
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-zinc-100 flex items-center justify-center shrink-0">
                        <flux:icon icon="lock-closed" class="size-5 text-zinc-400" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-zinc-900">{{ __('Sin acceso al cuaderno') }}</p>
                        <p class="text-xs text-zinc-500 mt-0.5">{{ __('Tu denominación no tiene acceso a tu cuaderno de campo. El cuaderno es privado por defecto.') }}</p>
                    </div>
                </div>
            @endif
        </x-agro.card>

        {{-- Bodegas asignadas por la DO --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <flux:icon icon="home-modern" class="size-4 text-blue-500" />
                    <span>{{ __('Bodegas asignadas por la DO') }}</span>
                    @if($supervisorWineries->count() > 0)
                        <span class="px-1.5 py-0.5 text-[10px] font-bold bg-zinc-100 text-zinc-500 rounded-full">{{ $supervisorWineries->count() }}</span>
                    @endif
                </div>
            </x-slot:header>

            @forelse($supervisorWineries as $winery)
                <div class="flex items-center gap-3 py-3 border-b border-zinc-100 last:border-0">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <flux:icon icon="home-modern" class="size-4 text-blue-600" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-zinc-800">{{ $winery->name }}</p>
                        <p class="text-xs text-zinc-400">{{ $winery->email }}</p>
                    </div>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-violet-50 text-violet-600 border border-violet-200">
                        {{ __('Asignada por DO') }}
                    </span>
                </div>
            @empty
                <div class="py-6 text-center">
                    <flux:icon icon="home-modern" class="size-8 mx-auto text-zinc-300 mb-2" />
                    <p class="text-sm text-zinc-400">{{ __('Aún no tienes bodegas asignadas por tu denominación.') }}</p>
                </div>
            @endforelse
        </x-agro.card>

        {{-- DO Documents (pliego, reglamento) --}}
        @if($doDocuments->count() > 0)
            <div>
                <h2 class="text-sm font-semibold text-zinc-700 mb-3">{{ __('Documentos de la denominación') }}</h2>
                <div class="space-y-3">
                    @foreach($doDocuments as $doc)
                        @php
                            $typeLabel = $doc->type === 'pliego' ? __('Pliego de condiciones') : __('Reglamento');
                            $typeColor = $doc->type === 'pliego'
                                ? 'bg-indigo-100 text-indigo-600'
                                : 'bg-violet-100 text-violet-600';
                        @endphp
                        <x-agro.card>
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-xl {{ $typeColor }} flex items-center justify-center shrink-0">
                                    <flux:icon icon="document-text" class="size-5" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="text-sm font-medium text-zinc-900">{{ $doc->title }}</p>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-zinc-100 text-zinc-500 font-medium">
                                            {{ $typeLabel }}
                                        </span>
                                        @if($doc->version)
                                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-blue-50 text-blue-600 font-medium">
                                                v{{ $doc->version }}
                                            </span>
                                        @endif
                                    </div>
                                    @if($doc->effective_date)
                                        <p class="text-xs text-zinc-400 mt-0.5">{{ __('Vigente desde') }} {{ $doc->effective_date->format('d/m/Y') }}</p>
                                    @endif
                                </div>
                            </div>
                        </x-agro.card>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Info --}}
        <flux:callout variant="info" icon="information-circle">
            <flux:callout.text>
                {{ __('Las parcelas vinculadas a bodegas asignadas por la DO son de solo lectura. Para cualquier cambio en tu adscripción, contacta directamente con tu denominación de origen.') }}
            </flux:callout.text>
        </flux:callout>

    @endif

</div>
