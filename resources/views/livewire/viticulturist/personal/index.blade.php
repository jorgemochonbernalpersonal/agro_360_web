<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="Gestión de Cuadrillas"
        description="Administra tus equipos de trabajo y personal"
        icon="user-group"
    >
        <x-slot:actions>
            @can('create', \App\Models\Crew::class)
                <flux:button href="{{ roleRoute('viticulturist.personal.create') }}" variant="primary" icon="plus">
                    Nueva Cuadrilla
                </flux:button>
            @endcan
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-agro.stat-card
            label="Total cuadrillas"
            :value="$stats['total']"
            icon="user-group"
            color="zinc"
        />
        <x-agro.stat-card
            label="Total miembros"
            :value="$stats['members']"
            icon="users"
            color="agro"
        />
        <x-agro.stat-card
            label="Con actividades"
            :value="$stats['with_activities']"
            icon="clipboard-document-list"
            color="agro"
        />
    </div>

    {{-- Toolbar --}}
    <div class="flex items-center gap-3 flex-wrap">
        <x-agro.search-input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre o descripción..." />
        @if($wineries->count() > 1)
            <flux:select wire:model.live="wineryFilter" class="w-44">
                <flux:select.option value="">Todas las bodegas</flux:select.option>
                @foreach($wineries as $winery)
                    <flux:select.option value="{{ $winery->id }}">{{ $winery->name }}</flux:select.option>
                @endforeach
            </flux:select>
        @endif
        @if($search || $wineryFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">Limpiar</flux:button>
        @endif
    </div>

    {{-- Grid de cards --}}
    @if($crews->count() === 0)
        <x-agro.empty-state
            icon="user-group"
            title="{{ $search || $wineryFilter ? 'Ninguna cuadrilla coincide con los filtros' : 'Sin cuadrillas registradas' }}"
            description="{{ $search || $wineryFilter ? 'Prueba a cambiar o limpiar los filtros.' : 'Crea tu primera cuadrilla para comenzar a gestionar tu equipo de trabajo.' }}"
        >
            <x-slot:action>
                @if($search || $wineryFilter)
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                @else
                    @can('create', \App\Models\Crew::class)
                        <flux:button href="{{ roleRoute('viticulturist.personal.create') }}" variant="primary" icon="plus">
                            Crear Primera Cuadrilla
                        </flux:button>
                    @endcan
                @endif
            </x-slot:action>
        </x-agro.empty-state>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($crews as $crew)
                @php $delay = min($loop->index * 50, 300); @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="crew-{{ $crew->id }}"
                >
                    <x-slot:header>
                        <x-agro.card-item-header
                            icon="user-group"
                            :title="$crew->name"
                            :subtitle="$crew->winery?->name ?? null"
                            iconBg="bg-agro-100"
                            iconColor="text-agro-600"
                            size="md"
                            radius="xl"
                        />
                    </x-slot:header>

                    <div class="flex-1 space-y-3">
                        @if($crew->description)
                            <p class="text-xs text-zinc-500 line-clamp-2">{{ $crew->description }}</p>
                        @endif

                        <div class="grid grid-cols-2 gap-2">
                            <div class="bg-zinc-50 rounded-xl p-3">
                                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">Miembros</p>
                                <p class="text-xl font-bold text-zinc-700 leading-none">{{ $crew->members_count }}</p>
                            </div>
                            <div class="bg-zinc-50 rounded-xl p-3">
                                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">Actividades</p>
                                <p class="text-xl font-bold text-zinc-700 leading-none">{{ $crew->activities_count }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 text-xs text-zinc-400">
                            <flux:icon icon="calendar-days" class="size-3.5 shrink-0" />
                            <span>Creada el {{ $crew->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-end gap-0.5">
                            <x-agro.action-button
                                variant="view"
                                href="{{ roleRoute('viticulturist.personal.show', $crew) }}#miembros"
                                title="Ver cuadrilla"
                            />
                            @can('update', $crew)
                                <x-agro.action-button
                                    variant="edit"
                                    href="{{ roleRoute('viticulturist.personal.edit', $crew) }}"
                                    title="Editar"
                                />
                            @endcan
                            @can('delete', $crew)
                                <x-agro.action-button
                                    variant="delete"
                                    wire:click="delete({{ $crew->id }})"
                                    wire:confirm="¿Estás seguro de eliminar esta cuadrilla?"
                                    title="Eliminar"
                                />
                            @endcan
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        <div class="mt-6">{{ $crews->links() }}</div>
    @endif

</div>
