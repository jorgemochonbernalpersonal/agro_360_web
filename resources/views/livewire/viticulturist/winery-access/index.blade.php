<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        :title="__('Acceso al Cuaderno de Campo')"
        :description="__('Controla qué bodegas y denominaciones de origen pueden ver tus actividades de campo. Solo tú decides quién tiene acceso.')"
    />

    {{-- Pending requests --}}
    @if($pending->isNotEmpty())
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-amber-50">
                    <flux:icon icon="clock" class="size-4 text-amber-500" />
                </div>
                <span class="font-semibold text-zinc-900 text-sm">{{ __('Solicitudes pendientes') }}</span>
                <flux:badge color="amber" size="sm">{{ $pending->count() }}</flux:badge>
            </div>
        </x-slot:header>

        <div class="divide-y divide-zinc-100">
            @foreach($pending as $request)
                @php
                    $isSupervisor = $request->isFromSupervisor();
                    $requester    = $isSupervisor ? $request->supervisor : $request->winery;
                    $label        = $isSupervisor ? __('Denominación de Origen') : __('Bodega');
                    $iconBg       = $isSupervisor ? 'bg-indigo-100' : 'bg-zinc-100';
                    $iconColor    = $isSupervisor ? 'text-indigo-600' : 'text-zinc-500';
                @endphp
                <div class="flex items-center justify-between gap-4 py-4 first:pt-0 last:pb-0">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full {{ $iconBg }} flex items-center justify-center shrink-0">
                            <flux:icon icon="{{ $isSupervisor ? 'building-library' : 'building-office-2' }}" class="size-5 {{ $iconColor }}" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-zinc-900">{{ $requester?->name }}</p>
                            <p class="text-xs text-zinc-400">
                                {{ $label }} · {{ __('Solicitud recibida el') }} {{ $request->requested_at->format('d/m/Y') }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <flux:button
                            size="sm"
                            variant="ghost"
                            icon="x-mark"
                            wire:click="reject({{ $request->id }})"
                            wire:confirm="{{ __('¿Rechazar el acceso al cuaderno para :name?', ['name' => $requester?->name]) }}"
                        >
                            {{ __('Rechazar') }}
                        </flux:button>
                        <flux:button
                            size="sm"
                            variant="primary"
                            icon="check"
                            wire:click="approve({{ $request->id }})"
                        >
                            {{ __('Aprobar') }}
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
                <span class="font-semibold text-zinc-900 text-sm">{{ __('Accesos activos') }}</span>
            </div>
        </x-slot:header>

        @if($granted->isEmpty())
            <div class="py-8 text-center">
                <flux:icon icon="lock-closed" class="size-8 text-zinc-300 mx-auto mb-2" />
                <p class="text-sm text-zinc-400">{{ __('Ninguna bodega ni denominación tiene acceso a tu cuaderno ahora mismo.') }}</p>
            </div>
        @else
            <div class="divide-y divide-zinc-100">
                @foreach($granted as $item)
                    @php
                        $isSupervisor = $item->type === 'supervisor';
                        $iconBg       = $isSupervisor ? 'bg-indigo-50' : 'bg-green-50';
                        $iconColor    = $isSupervisor ? 'text-indigo-600' : 'text-green-600';
                        $label        = $isSupervisor ? __('Denominación de Origen') : __('Bodega');
                    @endphp
                    <div class="flex items-center justify-between gap-4 py-4 first:pt-0 last:pb-0">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full {{ $iconBg }} flex items-center justify-center shrink-0">
                                <flux:icon icon="{{ $isSupervisor ? 'building-library' : 'building-office-2' }}" class="size-5 {{ $iconColor }}" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-900">{{ $item->name }}</p>
                                <p class="text-xs text-zinc-400">
                                    {{ $label }} · {{ __('Acceso desde') }} {{ $item->granted_at?->format('d/m/Y') ?? '—' }}
                                </p>
                            </div>
                        </div>
                        <flux:button
                            size="sm"
                            variant="ghost"
                            icon="lock-closed"
                            wire:click="revoke({{ $item->id }}, '{{ $item->type }}')"
                            wire:confirm="{{ __('¿Revocar el acceso al cuaderno para :name? Dejarán de ver tus actividades de campo.', ['name' => $item->name]) }}"
                        >
                            {{ __('Revocar') }}
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
                <span class="font-semibold text-zinc-900 text-sm">{{ __('Solicitudes rechazadas') }}</span>
            </div>
        </x-slot:header>

        <div class="divide-y divide-zinc-100">
            @foreach($rejected as $request)
                @php
                    $requester = $request->requester();
                    $label     = $request->isFromSupervisor() ? __('Denominación de Origen') : __('Bodega');
                @endphp
                <div class="flex items-center justify-between gap-4 py-4 first:pt-0 last:pb-0">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-zinc-100 flex items-center justify-center shrink-0">
                            <flux:icon icon="building-office-2" class="size-5 text-zinc-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-zinc-500">{{ $requester?->name }}</p>
                            <p class="text-xs text-zinc-400">
                                {{ $label }} · {{ __('Rechazada el') }} {{ $request->responded_at?->format('d/m/Y') ?? '—' }}
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
        <flux:callout.heading>{{ __('Tu cuaderno es privado por defecto') }}</flux:callout.heading>
        <flux:callout.text>
            {{ __('Solo las bodegas y denominaciones de origen a las que hayas aprobado el acceso pueden ver tus actividades de campo. Puedes revocar el acceso en cualquier momento.') }}
        </flux:callout.text>
    </flux:callout>
</div>
