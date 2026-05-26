<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="{{ __('Enólogos') }}"
        :description="__('Gestiona los técnicos enológicos de tu bodega')"
    />

    {{-- Tabs --}}
    <x-agro.tabs
        :tabs="[
            'active'   => ['label' => 'Activos',   'count' => $stats['active']],
            'inactive' => ['label' => 'Inactivos', 'count' => $stats['inactive']],
        ]"
        :active="$currentTab"
        wireMethod="switchTab"
    />

    {{-- Toolbar --}}
    <div class="space-y-3">
        <div class="flex items-center gap-3">

            <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar por nombre, email o nº colegiado...')" />

            <flux:button href="{{ roleRoute('oenologists.create') }}" wire:navigate variant="primary" icon="plus">
                Nuevo Enólogo
            </flux:button>

        </div>

        {{-- Active filter chips --}}
        @if($search)
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs text-zinc-400">{{ __('Filtros activos:') }}</span>
                <x-agro.filter-chip icon="magnifying-glass" :label="'&quot;' . $search . '&quot;'" wireRemove="$set('search', '')" />
                <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 underline">{{ __('Limpiar todo') }}</button>
            </div>
        @endif
    </div>

    {{-- Card grid --}}
    @if($oenologists->count() > 0)
        <div
            class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="search, clearFilters, switchTab"
        >
            @foreach($oenologists as $i => $oenologist)

                <x-agro.card
                    wire:key="oenologist-{{ $oenologist->id }}"
                    class="animate-fade-in-up hover:-translate-y-1 {{ !$oenologist->active ? 'opacity-70' : '' }}"
                    style="animation-delay: {{ min($i * 50, 400) }}ms"
                >
                    <x-slot:header>
                        <x-agro.card-item-header
                            icon="beaker"
                            :title="$oenologist->full_name"
                            :subtitle="$oenologist->license_number ? 'Colegiado: ' . $oenologist->license_number : null"
                            iconBg="bg-violet-50"
                            iconColor="text-violet-500"
                            size="sm"
                            radius="full"
                        >
                            @if(!$oenologist->active)
                                <flux:badge color="zinc" size="sm">{{ __('Inactivo') }}</flux:badge>
                            @endif
                        </x-agro.card-item-header>
                    </x-slot:header>

                    {{-- Contacto --}}
                    <div class="space-y-1.5 mb-3">
                        @if($oenologist->email)
                            <div class="flex items-center gap-2">
                                <flux:icon icon="envelope" class="size-3.5 text-zinc-400 shrink-0" />
                                <span class="text-xs text-zinc-600 truncate">{{ $oenologist->email }}</span>
                            </div>
                        @endif
                        @if($oenologist->phone)
                            <div class="flex items-center gap-2">
                                <flux:icon icon="phone" class="size-3.5 text-zinc-400 shrink-0" />
                                <span class="text-xs text-zinc-600">{{ $oenologist->phone }}</span>
                            </div>
                        @endif
                        @if(!$oenologist->email && !$oenologist->phone)
                            <p class="text-xs text-zinc-400 italic">{{ __('Sin datos de contacto') }}</p>
                        @endif
                    </div>

                    @if($oenologist->notes)
                        <p class="text-xs text-zinc-500 line-clamp-2">{{ $oenologist->notes }}</p>
                    @endif

                    <x-slot:footer>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1">
                                <x-agro.action-button variant="edit" href="{{ roleRoute('oenologists.edit', $oenologist->id) }}" wire:navigate title="{{ __('Editar') }}" />
                            </div>
                            <div class="flex items-center gap-1">
                                @if($oenologist->active)
                                    <x-agro.action-button variant="deactivate" wire:click="toggleActive({{ $oenologist->id }})" wire:loading.attr="disabled" title="{{ __('Desactivar') }}" />
                                @else
                                    <x-agro.action-button variant="activate" wire:click="toggleActive({{ $oenologist->id }})" wire:loading.attr="disabled" title="{{ __('Activar') }}" />
                                @endif
                                <x-agro.action-button variant="delete" wire:click="delete({{ $oenologist->id }})" wire:loading.attr="disabled" wire:confirm="{{ __('¿Seguro que quieres eliminar este enólogo?') }}" title="{{ __('Eliminar') }}" />
                            </div>
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        <x-agro-pagination :paginator="$oenologists" />

    @else
        <x-agro.empty-state
            icon="beaker"
            message="{{ $currentTab === 'active' ? 'No hay enólogos activos' : 'No hay enólogos inactivos' }}"
            description="{{ $search ? 'Ningún enólogo coincide con la búsqueda.' : ($currentTab === 'active' ? 'Añade el técnico enológico responsable de tu bodega.' : 'Los enólogos desactivados aparecerán aquí.') }}"
        >
            @if($search)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">{{ __('Limpiar búsqueda') }}</flux:button>
                </x-slot:action>
            @elseif($currentTab === 'active')
                <x-slot:action>
                    <flux:button href="{{ roleRoute('oenologists.create') }}" wire:navigate variant="primary" icon="plus">
                        Nuevo Enólogo
                    </flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @endif

</div>
