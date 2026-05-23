<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Acceso al Cuaderno de Campo"
        description="Gestiona las solicitudes de acceso al cuaderno de campo de los viticultores de la DO"
    >
        <x-slot:actions>
            <flux:button wire:click="openRequestModal" variant="primary" icon="plus">
                Solicitar acceso
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Estadísticas --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <x-agro.stat-card label="Total solicitudes" :value="$stats['total']"    icon="document-text"   color="blue"   />
        <x-agro.stat-card label="Pendientes"         :value="$stats['pending']"  icon="clock"           color="yellow" />
        <x-agro.stat-card label="Aprobadas"          :value="$stats['approved']" icon="check-circle"    color="agro"   />
        <x-agro.stat-card label="Rechazadas"         :value="$stats['rejected']" icon="x-circle"        color="red"    />
    </div>

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live="search"
            placeholder="Buscar por viticultor o email..."
        />
        <x-agro.filter-select wire:model.live="filterStatus">
            <option value="all">Todos los estados</option>
            <option value="pending">Pendientes</option>
            <option value="approved">Aprobadas</option>
            <option value="rejected">Rechazadas</option>
        </x-agro.filter-select>
    </x-agro.filter-bar>

    {{-- Card Grid --}}
    @if($requests->count() > 0)
        @php
            $statusMap = [
                'pending'  => ['label' => 'Pendiente',  'color' => 'yellow', 'iconBg' => 'bg-yellow-100', 'iconText' => 'text-yellow-600'],
                'approved' => ['label' => 'Aprobada',   'color' => 'green',  'iconBg' => 'bg-emerald-100', 'iconText' => 'text-emerald-600'],
                'rejected' => ['label' => 'Rechazada',  'color' => 'red',    'iconBg' => 'bg-red-100',    'iconText' => 'text-red-600'],
            ];
        @endphp
        <div
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="search, filterStatus"
        >
            @foreach($requests as $req)
                @php
                    $s = $statusMap[$req->status] ?? ['label' => $req->status, 'color' => 'zinc', 'iconBg' => 'bg-zinc-100', 'iconText' => 'text-zinc-600'];
                    $delay = min($loop->index * 50, 300);
                @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="request-{{ $req->id }}"
                >
                    <x-slot:header>
                        <x-agro.card-item-header
                            icon="book-open"
                            :title="$req->viticulturist?->name ?? '—'"
                            :subtitle="$req->viticulturist?->email"
                            :iconBg="$s['iconBg']"
                            :iconColor="$s['iconText']"
                            size="md"
                            radius="xl"
                        >
                            <flux:badge :color="$s['color']" size="sm">{{ $s['label'] }}</flux:badge>
                        </x-agro.card-item-header>
                    </x-slot:header>

                    <div class="flex-1 space-y-3">
                        {{-- Dates --}}
                        <div class="grid grid-cols-2 gap-2">
                            <div class="bg-zinc-50 rounded-lg p-2 text-center">
                                <p class="text-[9px] text-zinc-400 uppercase tracking-wide mb-0.5">Solicitado</p>
                                <p class="text-sm font-bold text-zinc-700">
                                    @if($req->requested_at)
                                        {{ $req->requested_at->format('d/m/Y') }}
                                    @else
                                        —
                                    @endif
                                </p>
                                @if($req->requested_at)
                                    <p class="text-[9px] text-zinc-400">{{ $req->requested_at->diffForHumans() }}</p>
                                @endif
                            </div>
                            <div class="bg-zinc-50 rounded-lg p-2 text-center">
                                <p class="text-[9px] text-zinc-400 uppercase tracking-wide mb-0.5">Respondido</p>
                                <p class="text-sm font-bold text-zinc-700">
                                    @if($req->responded_at)
                                        {{ $req->responded_at->format('d/m/Y') }}
                                    @else
                                        —
                                    @endif
                                </p>
                                @if($req->responded_at)
                                    <p class="text-[9px] text-zinc-400">{{ $req->responded_at->diffForHumans() }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($req->status !== 'rejected')
                        <x-slot:footer>
                            <div class="flex items-center justify-end">
                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    icon="trash"
                                    class="text-red-400 hover:text-red-600"
                                    wire:click="revokeAccess({{ $req->id }})"
                                    wire:confirm="¿Revocar la solicitud de acceso al cuaderno de {{ $req->viticulturist?->name }}?"
                                    tooltip="Revocar acceso"
                                />
                            </div>
                        </x-slot:footer>
                    @endif
                </x-agro.card>
            @endforeach
        </div>

        <x-agro-pagination :paginator="$requests" />
    @else
        <x-agro.empty-state
            icon="book-open"
            title="No hay solicitudes de acceso"
            description="Solicita acceso al cuaderno de campo de tus viticultores."
        />
    @endif

    {{-- Modal: Nueva solicitud --}}
    <flux:modal wire:model="showRequestModal" class="w-full max-w-lg">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2 rounded-lg bg-indigo-50">
                    <flux:icon icon="book-open" class="size-5 text-indigo-600" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-zinc-900">Solicitar acceso al cuaderno</h3>
                    <p class="text-xs text-zinc-500">El viticultor deberá aprobar tu solicitud</p>
                </div>
            </div>

            @if($availableForRequest->count() > 0)
                <div class="space-y-1 max-h-64 overflow-y-auto border border-zinc-200 rounded-lg p-1">
                    @foreach($availableForRequest as $v)
                        <label class="flex items-center gap-3 px-3 py-2.5 rounded-md cursor-pointer hover:bg-zinc-50 transition-colors {{ (string)$targetViticulturistId === (string)$v->id ? 'bg-indigo-50' : '' }}">
                            <input
                                type="radio"
                                wire:model.live="targetViticulturistId"
                                value="{{ $v->id }}"
                                class="text-indigo-600 focus:ring-indigo-500"
                            />
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-zinc-900 truncate">{{ $v->name }}</p>
                                <p class="text-xs text-zinc-400 truncate">{{ $v->email }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-zinc-400">
                    <flux:icon icon="check-circle" class="size-10 mx-auto mb-2 text-green-300" />
                    <p class="text-sm">Todos los viticultores ya tienen solicitud activa</p>
                </div>
            @endif

            @error('targetViticulturistId')
                <p class="text-xs text-red-500 mt-2">{{ $message }}</p>
            @enderror

            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-zinc-100">
                <flux:button variant="ghost" wire:click="closeRequestModal">Cancelar</flux:button>
                <flux:button
                    variant="primary"
                    wire:click="requestAccess"
                    :disabled="!$targetViticulturistId"
                >
                    Enviar solicitud
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
