<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="Asesores Técnicos"
        description="Registro de asesores fitosanitarios y técnicos (obligatorio RD 1311/2012)"
        icon="user-group"
    >
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.advisory-memberships.create') }}" variant="primary" icon="plus">
                Nuevo Asesor
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <flux:callout variant="info" icon="information-circle">
        El RD 1311/2012 exige contar con un asesor fitosanitario cualificado para la gestión integrada de plagas (GIP). Su nº de colegiado debe figurar en el cuaderno de campo.
    </flux:callout>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-agro.stat-card
            label="Total asesores"
            :value="$stats['total']"
            icon="user-group"
            color="zinc"
        />
        <x-agro.stat-card
            label="Permanentes"
            :value="$stats['permanent']"
            icon="check-circle"
            color="agro"
        />
        <x-agro.stat-card
            label="Esta campaña"
            :value="$stats['this_year']"
            icon="calendar-days"
            color="agro"
        />
    </div>

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">
        <flux:select wire:model.live="filterSpecialty" class="w-52">
            <flux:select.option value="">Todas las especialidades</flux:select.option>
            @foreach($specialties as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
        @if($filterSpecialty)
            <flux:button wire:click="$set('filterSpecialty', '')" variant="ghost" size="sm" icon="x-mark">
                Limpiar
            </flux:button>
        @endif
    </div>

    {{-- Grid de cards --}}
    @if($entries->isEmpty())
        <x-agro.empty-state
            icon="user-group"
            title="{{ $filterSpecialty ? 'Ningún asesor coincide con el filtro' : 'Sin asesores registrados' }}"
            description="{{ $filterSpecialty ? 'Prueba a cambiar o limpiar el filtro.' : 'Añade tus asesores técnicos y fitosanitarios para cumplir con la normativa.' }}"
        >
            <x-slot:action>
                @if($filterSpecialty)
                    <flux:button wire:click="$set('filterSpecialty', '')" variant="outline" icon="x-mark">Limpiar filtro</flux:button>
                @else
                    <flux:button href="{{ route('viticulturist.advisory-memberships.create') }}" variant="primary" icon="plus">
                        Nuevo Asesor
                    </flux:button>
                @endif
            </x-slot:action>
        </x-agro.empty-state>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($entries as $entry)
                @php $delay = min($loop->index * 50, 300); @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="advisor-{{ $entry->id }}"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-agro-100">
                                <flux:icon icon="user" class="size-5 text-agro-600" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-zinc-900 truncate">{{ $entry->advisor_name }}</h3>
                                @if($entry->company_name)
                                    <p class="text-xs text-zinc-400 truncate">{{ $entry->company_name }}</p>
                                @endif
                            </div>
                            <flux:badge color="blue" size="sm" class="shrink-0">{{ $entry->specialty_label }}</flux:badge>
                        </div>
                    </x-slot:header>

                    <div class="flex-1 space-y-3">
                        <div class="bg-zinc-50 rounded-xl p-3">
                            <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">Nº Colegiado</p>
                            <p class="text-base font-bold text-zinc-700 font-mono leading-none">{{ $entry->license_number }}</p>
                        </div>

                        @if($entry->phone || $entry->email)
                            <div class="space-y-1">
                                @if($entry->phone)
                                    <div class="flex items-center gap-2 text-xs text-zinc-600">
                                        <flux:icon icon="phone" class="size-3.5 text-zinc-400 shrink-0" />
                                        <span>{{ $entry->phone }}</span>
                                    </div>
                                @endif
                                @if($entry->email)
                                    <div class="flex items-center gap-2 text-xs text-zinc-500">
                                        <flux:icon icon="envelope" class="size-3.5 text-zinc-400 shrink-0" />
                                        <span class="truncate">{{ $entry->email }}</span>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div class="flex items-center gap-2 text-xs text-zinc-500">
                            <flux:icon icon="calendar-days" class="size-3.5 text-zinc-400 shrink-0" />
                            <span>{{ $entry->campaign?->name ?? 'Permanente' }}</span>
                        </div>
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-end gap-0.5">
                            <a href="{{ route('viticulturist.advisory-memberships.edit', $entry) }}"
                               title="Editar"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                <flux:icon icon="pencil-square" class="size-4" />
                            </a>
                            <button
                                wire:click="deactivate({{ $entry->id }})"
                                wire:confirm="¿Desactivar este asesor?"
                                title="Desactivar"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-amber-600 hover:bg-amber-50 transition-colors">
                                <flux:icon icon="user-minus" class="size-4" />
                            </button>
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        @if($entries->hasPages())
            <div class="mt-6">{{ $entries->links() }}</div>
        @endif
    @endif

</div>
