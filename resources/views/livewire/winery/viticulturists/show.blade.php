<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="{{ $viticulturist->name }}"
        description="Perfil del viticultor vinculado a tu bodega"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturists.index') }}" variant="ghost" icon="arrow-left">
                Volver
            </flux:button>
            @if($isOwn)
                <flux:button
                    href="{{ roleRoute('plots.create', ['viticulturist_id' => $viticulturist->id]) }}"
                    variant="ghost"
                    icon="map"
                >
                    Añadir parcela
                </flux:button>
                <flux:button href="{{ roleRoute('viticulturists.edit', $viticulturist->id) }}" variant="primary" icon="pencil-square">
                    Editar
                </flux:button>
            @endif
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-4">
        <x-agro.stat-card
            label="Parcelas"
            :value="$plots->count()"
            icon="map"
        />
        <x-agro.stat-card
            label="Hectáreas totales"
            :value="number_format($totalHa, 2) . ' ha'"
            icon="chart-bar"
        />
        <x-agro.stat-card
            label="Plantaciones"
            :value="$totalPlantings"
            icon="sparkles"
        />
        <x-agro.stat-card
            label="Límite kg total"
            :value="$totalKgLimit > 0 ? number_format($totalKgLimit, 0) . ' kg' : '—'"
            icon="scale"
        />
    </div>

    {{-- Datos del viticultor --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-agro-50">
                    <flux:icon icon="user" class="size-4 text-agro-600" />
                </div>
                <span class="font-semibold text-zinc-900 text-sm">Datos del Viticultor</span>
            </div>
        </x-slot:header>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <p class="text-sm text-zinc-500">Nombre</p>
                <p class="font-semibold text-zinc-900">{{ $viticulturist->name }}</p>
            </div>
            @if($viticulturist->email && !str_starts_with($viticulturist->email, 'viticultores.'))
                <div>
                    <p class="text-sm text-zinc-500">Email</p>
                    <p class="font-semibold text-zinc-900">{{ $viticulturist->email }}</p>
                </div>
            @endif
            @if($viticulturist->dni)
                <div>
                    <p class="text-sm text-zinc-500">DNI</p>
                    <p class="font-semibold text-zinc-900">{{ $viticulturist->dni }}</p>
                </div>
            @endif
            @if($viticulturist->profile?->phone)
                <div>
                    <p class="text-sm text-zinc-500">Teléfono</p>
                    <p class="font-semibold text-zinc-900">{{ $viticulturist->profile->phone }}</p>
                </div>
            @endif
            <div>
                <p class="text-sm text-zinc-500">Origen</p>
                @php
                    $sourceLabels = [
                        'own'          => ['label' => 'Ghost propio',   'color' => 'blue'],
                        'supervisor'   => ['label' => 'Asignado por DO','color' => 'purple'],
                        'viticulturist'=> ['label' => 'Viticultor',     'color' => 'zinc'],
                        'self'         => ['label' => 'Autoregistro',   'color' => 'green'],
                    ];
                    $src = $sourceLabels[$relation->source] ?? ['label' => $relation->source, 'color' => 'zinc'];
                @endphp
                <flux:badge :color="$src['color']" size="sm">{{ $src['label'] }}</flux:badge>
            </div>
            <div>
                <p class="text-sm text-zinc-500">Acceso al sistema</p>
                <flux:badge
                    :color="$viticulturist->can_login ? 'green' : null"
                    :icon="$viticulturist->can_login ? 'check' : 'x-mark'"
                    size="sm"
                >
                    {{ $viticulturist->can_login ? 'Con acceso' : 'Sin acceso (ghost)' }}
                </flux:badge>
            </div>
        </div>

        @if($isOwn && !$viticulturist->can_login)
            <div class="mt-4 space-y-3">
                {{-- Panel de invitación --}}
                <div class="p-4 rounded-lg border {{ $viticulturist->invitation_token ? 'bg-amber-50 border-amber-200' : 'bg-zinc-50 border-zinc-200' }}">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <flux:icon
                                icon="{{ $viticulturist->invitation_token ? 'paper-airplane' : 'envelope' }}"
                                class="size-5 {{ $viticulturist->invitation_token ? 'text-amber-600' : 'text-zinc-400' }} shrink-0"
                            />
                            <div>
                                @if($viticulturist->invitation_token)
                                    <p class="text-sm font-medium text-amber-800">Invitación pendiente de aceptar</p>
                                    <p class="text-xs text-amber-600">
                                        Enviada el {{ $viticulturist->invitation_sent_at?->format('d/m/Y H:i') ?? '—' }}
                                        a {{ $hasRealEmail ? $viticulturist->email : '—' }}
                                        @if($viticulturist->invitation_expires_at)
                                            · Caduca el {{ $viticulturist->invitation_expires_at->format('d/m/Y') }}
                                            @if($viticulturist->invitation_expires_at->isPast())
                                                <span class="text-red-600 font-medium">(Caducada)</span>
                                            @endif
                                        @endif
                                    </p>
                                @else
                                    <p class="text-sm font-medium text-zinc-700">Sin acceso al sistema</p>
                                    <p class="text-xs text-zinc-500">Invítale para que pueda usar el cuaderno de campo digital.</p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            @if($viticulturist->invitation_token)
                                {{-- Reenviar --}}
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="arrow-path"
                                    wire:click="$set('showEmailField', true)"
                                >
                                    Reenviar
                                </flux:button>
                                {{-- Revocar --}}
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="x-mark"
                                    wire:click="revokeInvitation"
                                    wire:confirm="¿Revocar la invitación? El enlace actual quedará inválido."
                                >
                                    Revocar
                                </flux:button>
                            @else
                                <flux:button
                                    size="sm"
                                    variant="primary"
                                    icon="paper-airplane"
                                    wire:click="$set('showEmailField', true)"
                                >
                                    Enviar invitación
                                </flux:button>
                            @endif
                        </div>
                    </div>

                    {{-- Formulario inline de email --}}
                    @if($showEmailField)
                        <div class="mt-3 pt-3 border-t {{ $viticulturist->invitation_token ? 'border-amber-200' : 'border-zinc-200' }}">
                            <div class="flex items-end gap-3">
                                <flux:field class="flex-1">
                                    <flux:label>Email del viticultor</flux:label>
                                    <flux:input
                                        wire:model="inviteEmail"
                                        type="email"
                                        placeholder="email@ejemplo.com"
                                        autofocus
                                    />
                                    <flux:error name="inviteEmail" />
                                </flux:field>
                                <flux:button
                                    variant="primary"
                                    icon="paper-airplane"
                                    wire:click="sendInvitation"
                                    wire:loading.attr="disabled"
                                >
                                    Enviar
                                </flux:button>
                                <flux:button
                                    variant="ghost"
                                    wire:click="$set('showEmailField', false)"
                                >
                                    Cancelar
                                </flux:button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @elseif($isOwn && $viticulturist->can_login)
            <div class="mt-4">
                <flux:callout variant="info" icon="check-circle">
                    Viticultor activo — gestiona sus datos desde su propio panel.
                    Ambos trabajáis sobre los mismos datos de parcelas y plantaciones.
                </flux:callout>
            </div>
        @elseif($relation->source === 'supervisor')
            <div class="mt-4">
                <flux:callout variant="info" icon="information-circle">
                    Este viticultor fue asignado por tu Denominación de Origen. Solo puedes visualizar sus datos.
                </flux:callout>
            </div>
        @endif
    </x-agro.card>

    {{-- Cuaderno de campo --}}
    @if($viticulturist->can_login)
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-purple-50">
                    <flux:icon icon="book-open" class="size-4 text-purple-600" />
                </div>
                <span class="font-semibold text-zinc-900 text-sm">Cuaderno de campo digital</span>
            </div>
        </x-slot:header>

        <div class="flex items-center justify-between gap-4">
            <div class="flex items-start gap-3">
                @if($relation->notebook_access)
                    <div class="w-9 h-9 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
                        <flux:icon icon="check-circle" class="size-5 text-green-600" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-zinc-900">Acceso concedido</p>
                        <p class="text-xs text-zinc-400">
                            Desde {{ $relation->notebook_granted_at?->format('d/m/Y') ?? '—' }}
                            · Puedes ver las actividades del viticultor en el cuaderno de campo.
                        </p>
                    </div>
                @elseif($accessRequest?->isPending())
                    <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                        <flux:icon icon="clock" class="size-5 text-amber-500" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-amber-800">Solicitud pendiente</p>
                        <p class="text-xs text-zinc-400">
                            Enviada el {{ $accessRequest->requested_at->format('d/m/Y') }}
                            · Esperando que el viticultor apruebe el acceso.
                        </p>
                    </div>
                @elseif($accessRequest?->isRejected())
                    <div class="w-9 h-9 rounded-xl bg-red-50 flex items-center justify-center shrink-0">
                        <flux:icon icon="x-circle" class="size-5 text-red-400" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-zinc-700">Solicitud rechazada</p>
                        <p class="text-xs text-zinc-400">
                            El viticultor no concedió el acceso.
                            @if($accessRequest->responded_at)
                                Respondida el {{ $accessRequest->responded_at->format('d/m/Y') }}.
                            @endif
                        </p>
                    </div>
                @else
                    <div class="w-9 h-9 rounded-xl bg-zinc-100 flex items-center justify-center shrink-0">
                        <flux:icon icon="lock-closed" class="size-5 text-zinc-400" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-zinc-700">Sin acceso al cuaderno</p>
                        <p class="text-xs text-zinc-400">
                            El viticultor no ha concedido acceso a sus actividades de campo.
                            @if($relation->notebook_revoked_at)
                                Revocado el {{ $relation->notebook_revoked_at->format('d/m/Y') }}.
                            @endif
                        </p>
                    </div>
                @endif
            </div>

            @if($isOwn)
            <div class="shrink-0">
                @if($relation->notebook_access)
                    {{-- Access granted: viticulturist controls revocation --}}
                @elseif($accessRequest?->isPending())
                    <flux:button
                        size="sm"
                        variant="ghost"
                        icon="x-mark"
                        wire:click="cancelAccessRequest"
                        wire:confirm="¿Cancelar la solicitud de acceso al cuaderno?"
                    >
                        Cancelar solicitud
                    </flux:button>
                @else
                    <flux:button
                        size="sm"
                        variant="primary"
                        icon="book-open"
                        wire:click="requestNotebookAccess"
                    >
                        {{ $accessRequest?->isRejected() ? 'Solicitar de nuevo' : 'Solicitar acceso' }}
                    </flux:button>
                @endif
            </div>
            @endif
        </div>

        @if($relation->notebook_access)
            <div class="mt-3 pt-3 border-t border-zinc-100">
                <p class="text-xs text-zinc-400">
                    <flux:icon icon="information-circle" class="size-3.5 inline-block mr-1" />
                    El viticultor puede revocar este acceso en cualquier momento desde su panel.
                </p>
            </div>
        @elseif(!$isOwn)
            <div class="mt-3 pt-3 border-t border-zinc-100">
                <p class="text-xs text-zinc-400">
                    <flux:icon icon="information-circle" class="size-3.5 inline-block mr-1" />
                    El acceso al cuaderno lo gestiona el propio viticultor desde su perfil.
                </p>
            </div>
        @endif
    </x-agro.card>
    @endif

    {{-- Parcelas y plantaciones --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-agro-50">
                    <flux:icon icon="map" class="size-4 text-agro-600" />
                </div>
                <span class="font-semibold text-zinc-900 text-sm">Parcelas y Plantaciones</span>
            </div>
        </x-slot:header>

        @if($plots->isEmpty())
            <p class="text-sm text-zinc-400 py-4 text-center">Este viticultor no tiene parcelas registradas.</p>
        @else
            <div class="space-y-6">
                @foreach($plots as $plot)
                    <div class="border border-zinc-200 rounded-lg overflow-hidden">
                        {{-- Cabecera de la parcela --}}
                        <div class="bg-zinc-50 px-4 py-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <flux:icon icon="map-pin" class="size-4 text-agro-600 flex-shrink-0" />
                                <div>
                                    <p class="font-semibold text-sm text-zinc-900">{{ $plot->name }}</p>
                                    @if($plot->municipality)
                                        <p class="text-xs text-zinc-500">{{ $plot->municipality->name }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-4 text-sm text-zinc-600">
                                <span>{{ number_format($plot->area, 2) }} ha</span>
                                <span>{{ $plot->plantings->count() }} {{ Str::plural('plantación', $plot->plantings->count()) }}</span>
                                <a href="{{ roleRoute('plots.show', $plot->id) }}"
                                   class="text-agro-700 hover:underline text-xs font-medium">
                                    Ver parcela →
                                </a>
                            </div>
                        </div>

                        {{-- Plantaciones de la parcela --}}
                        @if($plot->plantings->isNotEmpty())
                            <x-agro.data-table
                                :headers="['Variedad', 'Ha plantadas', 'Año', 'Límite kg/ha', 'Estado']"
                                empty-message="Sin plantaciones"
                            >
                                @foreach($plot->plantings as $planting)
                                    <x-agro.table-row>
                                        <x-agro.table-cell>
                                            <span class="font-medium text-zinc-900">
                                                {{ $planting->grapeVariety?->name ?? $planting->name ?? '—' }}
                                            </span>
                                        </x-agro.table-cell>
                                        <x-agro.table-cell>
                                            {{ $planting->area_planted ? number_format($planting->area_planted, 2) . ' ha' : '—' }}
                                        </x-agro.table-cell>
                                        <x-agro.table-cell>
                                            {{ $planting->planting_year ?? '—' }}
                                        </x-agro.table-cell>
                                        <x-agro.table-cell>
                                            {{ $planting->harvest_limit_kg ? number_format($planting->harvest_limit_kg, 0) . ' kg' : '—' }}
                                        </x-agro.table-cell>
                                        <x-agro.table-cell>
                                            <flux:badge
                                                :color="$planting->status === 'active' ? 'green' : 'zinc'"
                                                size="sm"
                                            >
                                                {{ match($planting->status) {
                                                    'active'     => 'Activa',
                                                    'replanting' => 'Replantación',
                                                    'uprooted'   => 'Arrancada',
                                                    default      => $planting->status ?? '—',
                                                } }}
                                            </flux:badge>
                                        </x-agro.table-cell>
                                    </x-agro.table-row>
                                @endforeach
                            </x-agro.data-table>
                        @else
                            <p class="px-4 py-3 text-sm text-zinc-400">Sin plantaciones registradas.</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </x-agro.card>
</div>
