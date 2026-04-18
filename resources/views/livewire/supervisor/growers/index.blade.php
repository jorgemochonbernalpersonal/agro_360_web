<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Viticultores DO"
        description="Viticultores gestionados directamente por la denominación de origen."
    >
        <x-slot:actions>
            <flux:button wire:click="openCreateModal" variant="primary" icon="plus">
                Nuevo viticultor
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats --}}
    <x-agro.stats-section key="supervisor-growers" columns="3">
        <x-agro.stat-card label="Viticultores DO" :value="$totalGrowerCount" icon="users" color="agro" />
        <x-agro.stat-card label="Total parcelas activas" :value="$plotStatsByVit->sum('plot_count')" icon="map" color="blue" />
        <x-agro.stat-card label="Superficie total (ha)" :value="number_format($plotStatsByVit->sum('total_area'), 2)" icon="square-3-stack-3d" color="yellow" />
    </x-agro.stats-section>

    {{-- Search --}}
    <div class="flex items-center gap-2">
        <x-agro.search-input wire:model.live.debounce.300ms="search" placeholder="Buscar viticultor..." />
        @if($search)
            <button wire:click="clearSearch" class="text-xs text-zinc-400 hover:text-zinc-600 transition px-2 py-1.5">Limpiar</button>
        @endif
    </div>

    {{-- Loading skeleton --}}
    <x-agro.loading-grid target="search, nextPage, previousPage" />

    {{-- Card grid --}}
    <div wire:loading.remove wire:target="search, nextPage, previousPage">
        @if($growers->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($growers as $grower)
                    @php
                        $delay     = min($loop->index * 50, 300);
                        $plots     = $plotStatsByVit[$grower->id]       ?? null;
                        $plantings = $activePlantingsByVit[$grower->id] ?? null;
                        $isGhost   = !$grower->can_login;
                        $hasPendingInvite = $isGhost && $grower->invitation_token && $grower->invitation_expires_at?->isFuture();
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="grower-{{ $grower->id }}"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-agro-100 flex items-center justify-center shrink-0">
                                    <flux:icon icon="user" class="size-5 text-agro-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">{{ $grower->name }}</h3>
                                    @if(!str_starts_with($grower->email, 'viticultores.'))
                                        <p class="text-xs text-zinc-500 truncate">{{ $grower->email }}</p>
                                    @else
                                        <p class="text-xs text-zinc-300 italic">Sin email registrado</p>
                                    @endif
                                </div>
                                @if(!$isGhost)
                                    <flux:badge color="green" size="sm" class="shrink-0">Activo</flux:badge>
                                @elseif($hasPendingInvite)
                                    <flux:badge color="amber" size="sm" class="shrink-0">Invitación enviada</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm" class="shrink-0">Ghost</flux:badge>
                                @endif
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">
                            <div class="grid grid-cols-2 gap-2">
                                <div class="bg-blue-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-blue-400 uppercase tracking-widest mb-0.5">Parcelas</p>
                                    <p class="text-2xl font-bold text-blue-700 leading-none">{{ $plots?->plot_count ?? 0 }}</p>
                                </div>
                                <div class="bg-agro-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">Plantaciones</p>
                                    <p class="text-2xl font-bold text-agro-700 leading-none">{{ $plantings?->planting_count ?? 0 }}</p>
                                </div>
                            </div>

                            <div class="space-y-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">Bodegas</span>
                                    <span class="text-zinc-700 font-medium truncate ml-2 max-w-[60%] text-right">{{ $wineryNamesByVit[$grower->id] ?? '---' }}</span>
                                </div>
                            </div>
                        </div>

                        <x-slot:footer>
                            @php
                                $btnBase = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors';
                                $btnDanger = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors';
                            @endphp
                            <div class="flex items-center justify-end gap-0.5">
                                @if($isGhost)
                                    @if($hasPendingInvite)
                                        <button
                                            wire:click="revokeInvitation({{ $grower->id }})"
                                            wire:confirm="¿Revocar la invitación de {{ $grower->name }}?"
                                            class="{{ $btnDanger }}"
                                            title="Revocar invitación"
                                        >
                                            <flux:icon icon="x-mark" class="size-4" />
                                        </button>
                                    @else
                                        <button
                                            wire:click="openInviteModal({{ $grower->id }})"
                                            class="{{ $btnBase }}"
                                            title="Invitar"
                                        >
                                            <flux:icon icon="envelope" class="size-4" />
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <div class="mt-6">{{ $growers->links() }}</div>
        @else
            <x-agro.empty-state icon="users" title="No hay viticultores" description="No hay viticultores adscritos a esta denominación." />
        @endif
    </div>

    {{-- Modal: Crear viticultor ghost --}}
    <flux:modal wire:model="showCreateModal" class="w-full max-w-lg">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2 rounded-lg bg-agro-50">
                    <flux:icon icon="user-plus" class="size-5 text-agro-600" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-zinc-900">Nuevo viticultor</h3>
                    <p class="text-xs text-zinc-500">Puedes invitarle a registrarse más adelante</p>
                </div>
            </div>

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Nombre completo <span class="text-red-400">*</span></flux:label>
                    <flux:input wire:model="createName" placeholder="Juan García López" autofocus />
                    <flux:error name="createName" />
                </flux:field>

                <flux:field>
                    <flux:label>Email <span class="text-zinc-400 font-normal text-xs">(opcional)</span></flux:label>
                    <flux:input wire:model="createEmail" type="email" placeholder="viticultor@ejemplo.com" />
                    <flux:description class="text-xs text-zinc-400">Si lo introduces podrás enviarle una invitación directamente.</flux:description>
                    <flux:error name="createEmail" />
                </flux:field>

                <div class="grid grid-cols-2 gap-3">
                    <flux:field>
                        <flux:label>DNI / NIF <span class="text-zinc-400 font-normal text-xs">(opcional)</span></flux:label>
                        <flux:input wire:model="createDni" placeholder="12345678A" maxlength="20" />
                        <flux:error name="createDni" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Teléfono <span class="text-zinc-400 font-normal text-xs">(opcional)</span></flux:label>
                        <flux:input wire:model="createPhone" placeholder="600 000 000" maxlength="20" />
                    </flux:field>
                </div>

                <flux:callout variant="info" icon="information-circle" class="text-xs">
                    <flux:callout.text>
                        El viticultor se crea sin acceso al sistema. Podrás invitarle a registrarse cuando quieras para que use el plan gratuito.
                    </flux:callout.text>
                </flux:callout>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-zinc-100">
                <flux:button variant="ghost" wire:click="closeCreateModal">Cancelar</flux:button>
                <flux:button variant="primary" wire:click="createGrower" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="createGrower">Crear viticultor</span>
                    <span wire:loading wire:target="createGrower">Creando...</span>
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal: Enviar invitación --}}
    <flux:modal wire:model="showInviteModal" class="w-full max-w-md">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2 rounded-lg bg-blue-50">
                    <flux:icon icon="envelope" class="size-5 text-blue-600" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-zinc-900">Enviar invitación</h3>
                    <p class="text-xs text-zinc-500">El viticultor recibirá un enlace de activación (7 días)</p>
                </div>
            </div>

            <flux:field>
                <flux:label>Email del viticultor</flux:label>
                <flux:input wire:model="inviteEmail" type="email" placeholder="viticultor@ejemplo.com" autofocus />
                <flux:description class="text-xs text-zinc-400">
                    Al activar la cuenta, el viticultor tendrá acceso gratuito al cuaderno de campo.
                </flux:description>
                <flux:error name="inviteEmail" />
            </flux:field>

            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-zinc-100">
                <flux:button variant="ghost" wire:click="closeInviteModal">Cancelar</flux:button>
                <flux:button variant="primary" wire:click="sendInvitation" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="sendInvitation">Enviar invitación</span>
                    <span wire:loading wire:target="sendInvitation">Enviando...</span>
                </flux:button>
            </div>
        </div>
    </flux:modal>

</div>
