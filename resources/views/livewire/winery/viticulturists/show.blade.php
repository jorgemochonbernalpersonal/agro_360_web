<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="{{ $viticulturist->name }}"
        description="Perfil del viticultor vinculado a tu bodega"
    >
        <x-slot:actions>
            <flux:button href="{{ route('winery.viticulturists.index') }}" variant="ghost" icon="arrow-left">
                Volver
            </flux:button>
            @if($isOwn)
                <flux:button href="{{ route('winery.viticulturists.edit', $viticulturist->id) }}" variant="primary" icon="pencil-square">
                    Editar
                </flux:button>
            @endif
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
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
            @if($viticulturist->email)
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
            <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg flex items-start gap-2">
                <flux:icon icon="information-circle" class="size-4 text-amber-500 flex-shrink-0 mt-0.5" />
                <p class="text-xs text-amber-700">
                    Viticultor sin acceso al sistema. Puedes gestionar sus datos y parcelas desde aquí.
                    Si quieres que acceda a su propio panel, invítale desde el listado de viticultores.
                </p>
            </div>
        @elseif($isOwn && $viticulturist->can_login)
            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg flex items-start gap-2">
                <flux:icon icon="information-circle" class="size-4 text-blue-500 flex-shrink-0 mt-0.5" />
                <p class="text-xs text-blue-700">
                    Este viticultor también gestiona sus datos desde su propio panel.
                    Ambos trabajáis sobre los mismos datos de parcelas y plantaciones.
                    El cuaderno de campo es privado — necesita autorización explícita para compartirlo.
                </p>
            </div>
        @elseif($relation->source === 'supervisor')
            <div class="mt-4 p-3 bg-purple-50 border border-purple-200 rounded-lg flex items-start gap-2">
                <flux:icon icon="information-circle" class="size-4 text-purple-500 flex-shrink-0 mt-0.5" />
                <p class="text-xs text-purple-700">
                    Este viticultor fue asignado por tu Denominación de Origen. Solo puedes visualizar sus datos.
                </p>
            </div>
        @endif
    </x-agro.card>

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
                                <a href="{{ route('winery.plots.show', $plot->id) }}"
                                   class="text-agro-700 hover:underline text-xs font-medium">
                                    Ver parcela →
                                </a>
                            </div>
                        </div>

                        {{-- Plantaciones de la parcela --}}
                        @if($plot->plantings->isNotEmpty())
                            <table class="w-full text-sm">
                                <thead class="bg-white border-b border-zinc-100">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 uppercase">Variedad</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 uppercase">Ha plantadas</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 uppercase">Año</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 uppercase">Límite kg/ha</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 uppercase">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-100">
                                    @foreach($plot->plantings as $planting)
                                        <tr class="hover:bg-zinc-50">
                                            <td class="px-4 py-2 font-medium text-zinc-900">
                                                {{ $planting->grapeVariety?->name ?? $planting->name ?? '—' }}
                                            </td>
                                            <td class="px-4 py-2 text-zinc-600">
                                                {{ $planting->area_planted ? number_format($planting->area_planted, 2) . ' ha' : '—' }}
                                            </td>
                                            <td class="px-4 py-2 text-zinc-600">
                                                {{ $planting->planting_year ?? '—' }}
                                            </td>
                                            <td class="px-4 py-2 text-zinc-600">
                                                {{ $planting->harvest_limit_kg ? number_format($planting->harvest_limit_kg, 0) . ' kg' : '—' }}
                                            </td>
                                            <td class="px-4 py-2">
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
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="px-4 py-3 text-sm text-zinc-400">Sin plantaciones registradas.</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </x-agro.card>
</div>
