<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        :title="__('Gestión de Viticultores')"
        :description="__('Administra los viticultores que has creado para tus cuadrillas y parcelas')"
        icon="users"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.viticulturists.create') }}" variant="primary" icon="plus" wire:navigate>
                {{ __('Nuevo Viticultor') }}
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        <x-agro.stat-card :label="__('Total viticultores')" :value="$stats['total']" icon="users" color="zinc" />
        <x-agro.stat-card :label="__('En cuadrilla')" :value="$stats['with_crew']" icon="user-group" color="agro" />
        <x-agro.stat-card :label="__('Con acceso')" :value="$stats['with_access']" icon="key" color="blue" />
    </div>

    {{-- Toolbar --}}
    <div class="flex items-center gap-3 flex-wrap">
        <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar por nombre o email...')" />
        @if($search)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">{{ __('Limpiar') }}</flux:button>
        @endif
    </div>

    {{-- Cards --}}
    @if($viticulturists->isEmpty())
        <x-agro.empty-state
            icon="users"
            :title="$search ? __('Ningún viticultor coincide con la búsqueda') : __('Sin viticultores registrados')"
            :description="$search ? __('Prueba a cambiar los términos de búsqueda.') : __('Comienza creando tu primer viticultor para gestionarlo en el sistema.')"
        >
            @if($search)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">{{ __('Limpiar búsqueda') }}</flux:button>
                </x-slot:action>
            @else
                <x-slot:action>
                    <flux:button href="{{ roleRoute('viticulturist.viticulturists.create') }}" variant="primary" icon="plus" wire:navigate>
                        {{ __('Nuevo Viticultor') }}
                    </flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($viticulturists as $v)
                @php $delay = min($loop->index * 50, 300); @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="vit-{{ $v->id }}"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-agro-100">
                                <flux:icon icon="user" class="size-5 text-agro-600" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-zinc-900 truncate">
                                    {{ $v->name }}
                                    @if($v->id === auth()->id())
                                        <span class="text-xs font-normal text-agro-600">({{ __('Yo') }})</span>
                                    @endif
                                </h3>
                                <p class="text-xs text-zinc-400 truncate">{{ $v->email }}</p>
                            </div>
                        </div>
                    </x-slot:header>

                    <div class="flex-1 space-y-3">
                        @php $member = $membersByViticulturist->get($v->id); @endphp

                        <div class="flex flex-wrap gap-2">
                            @if($member && $member->crew)
                                <flux:badge color="green" size="sm" icon="user-group">{{ $member->crew->name }}</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">{{ __('Sin cuadrilla') }}</flux:badge>
                            @endif

                            @php $hasAccess = $v->can_login; @endphp
                            <flux:badge :color="$hasAccess ? 'blue' : null" :icon="$hasAccess ? 'check' : 'x-mark'" size="sm">
                                {{ $hasAccess ? __('Con acceso') : __('Sin acceso') }}
                            </flux:badge>
                        </div>

                        {{-- Asignar a cuadrilla --}}
                        @if($crews->count() > 0)
                            <div x-data="{ open: false }" class="relative">
                                <button
                                    @click="open = !open"
                                    class="w-full flex items-center justify-center gap-2 px-3 py-2 text-xs font-medium text-agro-700 bg-agro-50 hover:bg-agro-100 border border-agro-200 rounded-lg transition-colors"
                                >
                                    <flux:icon icon="user-group" class="size-3.5" />
                                    {{ __('Asignar a cuadrilla') }}
                                </button>
                                <div
                                    x-show="open"
                                    @click.away="open = false"
                                    x-transition
                                    class="absolute left-0 right-0 mt-1 bg-white rounded-lg shadow-xl z-10 border border-zinc-200 p-3"
                                >
                                    <p class="text-xs font-semibold text-zinc-600 mb-2">{{ __('Seleccionar cuadrilla') }}</p>
                                    <select
                                        wire:model="assignToCrewId"
                                        class="w-full px-2 py-1.5 text-sm border border-zinc-300 rounded-lg mb-2"
                                    >
                                        <option value="">{{ __('Selecciona una cuadrilla') }}</option>
                                        @foreach($crews as $crew)
                                            <option value="{{ $crew->id }}">{{ $crew->name }}</option>
                                        @endforeach
                                    </select>
                                    <flux:button
                                        wire:click="assignToCrew({{ $v->id }})"
                                        x-on:click="open = false"
                                        variant="primary"
                                        size="sm"
                                        class="w-full"
                                    >
                                        {{ __('Asignar') }}
                                    </flux:button>
                                </div>
                            </div>
                        @endif
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-end">
                            <x-agro.action-button
                                variant="delete"
                                wire:click="delete({{ $v->id }})"
                                wire:confirm="{{ __('¿Estás seguro de eliminar este viticultor? Esta acción no se puede deshacer.') }}"
                                :title="__('Eliminar')"
                            />
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        <x-agro-pagination :paginator="$viticulturists" />
    @endif

</div>
