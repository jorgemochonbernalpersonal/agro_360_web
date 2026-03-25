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
    <div x-data="{
        open: localStorage.getItem('supervisor-growers-stats-open') !== 'false',
        toggle() { this.open = !this.open; localStorage.setItem('supervisor-growers-stats-open', String(this.open)); }
    }">
        <button @click="toggle()"
            class="flex items-center gap-1.5 text-[11px] font-semibold text-zinc-400 uppercase tracking-widest hover:text-zinc-600 transition-colors mb-3">
            <span>Estadísticas</span>
            <flux:icon icon="chevron-up" class="size-3.5 transition-transform duration-200" ::class="{ 'rotate-180': !open }" />
        </button>
        <div x-show="open" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <x-agro.stat-card label="Viticultores DO" :value="$totalGrowerCount" icon="users" color="agro" />
                <x-agro.stat-card label="Total parcelas activas" :value="$plotStatsByVit->sum('plot_count')" icon="map" color="blue" />
                <x-agro.stat-card label="Superficie total (ha)" :value="number_format($plotStatsByVit->sum('total_area'), 2)" icon="square-3-stack-3d" color="yellow" />
            </div>
        </div>
    </div>

    {{-- Search --}}
    <div class="flex items-center gap-2">
        <div class="relative">
            <flux:icon icon="magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400 pointer-events-none" />
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar viticultor..."
                class="pl-9 pr-3 py-1.5 text-sm border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-300 w-52" />
        </div>
        @if($search)
            <button wire:click="clearSearch" class="text-xs text-zinc-400 hover:text-zinc-600 transition px-2 py-1.5">Limpiar</button>
        @endif
    </div>

    {{-- Table --}}
    <x-agro.data-table
        :headers="['Viticultor', 'Estado', 'Bodegas asignadas', 'Parcelas / Plantaciones', '']"
        emptyMessage="No hay viticultores adscritos a esta denominación."
    >
        @foreach($growers as $grower)
            @php
                $plots     = $plotStatsByVit[$grower->id]       ?? null;
                $plantings = $activePlantingsByVit[$grower->id] ?? null;
                $isGhost   = !$grower->can_login;
                $hasPendingInvite = $isGhost && $grower->invitation_token && $grower->invitation_expires_at?->isFuture();
            @endphp
            <tr class="hover:bg-zinc-50 transition">
                <td class="px-6 py-3 text-sm">
                    <div class="font-medium text-zinc-800">{{ $grower->name }}</div>
                    @if(!str_starts_with($grower->email, 'viticultores.'))
                        <div class="text-xs text-zinc-400">{{ $grower->email }}</div>
                    @else
                        <div class="text-xs text-zinc-300 italic">Sin email registrado</div>
                    @endif
                </td>
                <td class="px-6 py-3 text-sm">
                    @if(!$isGhost)
                        <flux:badge color="green" size="sm">Activo</flux:badge>
                    @elseif($hasPendingInvite)
                        <flux:badge color="amber" size="sm">Invitación enviada</flux:badge>
                    @else
                        <flux:badge color="zinc" size="sm">Ghost</flux:badge>
                    @endif
                </td>
                <td class="px-6 py-3 text-sm text-zinc-500">
                    {{ $wineryNamesByVit[$grower->id] ?? '—' }}
                </td>
                <td class="px-6 py-3 text-sm text-zinc-500">
                    {{ $plots?->plot_count ?? 0 }} parcelas
                    @if($plantings?->planting_count)
                        · {{ $plantings->planting_count }} plantac.
                    @endif
                </td>
                <td class="px-6 py-3 text-sm text-right">
                    @if($isGhost)
                        @if($hasPendingInvite)
                            <flux:button
                                size="xs"
                                variant="ghost"
                                icon="x-mark"
                                wire:click="revokeInvitation({{ $grower->id }})"
                                wire:confirm="¿Revocar la invitación de {{ $grower->name }}?"
                            >
                                Revocar invitación
                            </flux:button>
                        @else
                            <flux:button
                                size="xs"
                                variant="ghost"
                                icon="envelope"
                                wire:click="openInviteModal({{ $grower->id }})"
                            >
                                Invitar
                            </flux:button>
                        @endif
                    @endif
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $growers->links() }}
        </x-slot>
    </x-agro.data-table>

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
