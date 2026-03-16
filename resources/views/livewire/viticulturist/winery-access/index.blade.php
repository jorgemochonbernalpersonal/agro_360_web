<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Acceso al Cuaderno de Campo"
        description="Controla qué bodegas pueden ver tus actividades de campo. Solo tú decides quién tiene acceso."
    />

    {{-- Pending requests --}}
    @if($pending->isNotEmpty())
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-amber-50">
                    <flux:icon icon="clock" class="size-4 text-amber-500" />
                </div>
                <span class="font-semibold text-zinc-900 text-sm">Solicitudes pendientes</span>
                <flux:badge color="amber" size="sm">{{ $pending->count() }}</flux:badge>
            </div>
        </x-slot:header>

        <div class="divide-y divide-zinc-100">
            @foreach($pending as $request)
                <div class="flex items-center justify-between gap-4 py-4 first:pt-0 last:pb-0">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-zinc-100 flex items-center justify-center shrink-0">
                            <flux:icon icon="building-office-2" class="size-5 text-zinc-500" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-zinc-900">{{ $request->winery->name }}</p>
                            <p class="text-xs text-zinc-400">
                                Solicitud recibida el {{ $request->requested_at->format('d/m/Y') }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <flux:button
                            size="sm"
                            variant="ghost"
                            icon="x-mark"
                            wire:click="reject({{ $request->id }})"
                            wire:confirm="¿Rechazar el acceso al cuaderno para {{ $request->winery->name }}?"
                        >
                            Rechazar
                        </flux:button>
                        <flux:button
                            size="sm"
                            variant="primary"
                            icon="check"
                            wire:click="approve({{ $request->id }})"
                        >
                            Aprobar
                        </flux:button>
                    </div>
                </div>
            @endforeach
        </div>
    </x-agro.card>
    @endif

    {{-- Granted accesses --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-green-50">
                    <flux:icon icon="book-open" class="size-4 text-green-600" />
                </div>
                <span class="font-semibold text-zinc-900 text-sm">Accesos activos</span>
            </div>
        </x-slot:header>

        @if($granted->isEmpty())
            <div class="py-8 text-center">
                <flux:icon icon="lock-closed" class="size-8 text-zinc-300 mx-auto mb-2" />
                <p class="text-sm text-zinc-400">Ninguna bodega tiene acceso a tu cuaderno ahora mismo.</p>
            </div>
        @else
            <div class="divide-y divide-zinc-100">
                @foreach($granted as $relation)
                    <div class="flex items-center justify-between gap-4 py-4 first:pt-0 last:pb-0">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-green-50 flex items-center justify-center shrink-0">
                                <flux:icon icon="building-office-2" class="size-5 text-green-600" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-900">{{ $relation->winery->name }}</p>
                                <p class="text-xs text-zinc-400">
                                    Acceso desde {{ $relation->cuaderno_granted_at?->format('d/m/Y') ?? '—' }}
                                </p>
                            </div>
                        </div>
                        <flux:button
                            size="sm"
                            variant="ghost"
                            icon="lock-closed"
                            wire:click="revoke({{ $relation->winery_id }})"
                            wire:confirm="¿Revocar el acceso al cuaderno para {{ $relation->winery->name }}? Dejarán de ver tus actividades de campo."
                        >
                            Revocar
                        </flux:button>
                    </div>
                @endforeach
            </div>
        @endif
    </x-agro.card>

    {{-- Rejected --}}
    @if($rejected->isNotEmpty())
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-zinc-100">
                    <flux:icon icon="x-circle" class="size-4 text-zinc-400" />
                </div>
                <span class="font-semibold text-zinc-900 text-sm">Solicitudes rechazadas</span>
            </div>
        </x-slot:header>

        <div class="divide-y divide-zinc-100">
            @foreach($rejected as $request)
                <div class="flex items-center justify-between gap-4 py-4 first:pt-0 last:pb-0">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-zinc-100 flex items-center justify-center shrink-0">
                            <flux:icon icon="building-office-2" class="size-5 text-zinc-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-zinc-500">{{ $request->winery->name }}</p>
                            <p class="text-xs text-zinc-400">
                                Rechazada el {{ $request->responded_at?->format('d/m/Y') ?? '—' }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-agro.card>
    @endif

    {{-- Info box --}}
    <flux:callout variant="info" icon="shield-check">
        <flux:callout.heading>Tu cuaderno es privado por defecto</flux:callout.heading>
        <flux:callout.text>
            Solo las bodegas a las que hayas aprobado el acceso pueden ver tus actividades de campo.
            Puedes revocar el acceso en cualquier momento.
        </flux:callout.text>
    </flux:callout>
</div>
