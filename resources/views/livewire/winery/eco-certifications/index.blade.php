<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="Certificaciones Ecológicas"
        description="Gestión de certificaciones de producción ecológica, biodinámica y sostenible"
        icon="sparkles"
    >
        <x-slot:actions>
            <flux:button variant="primary" icon="plus" href="{{ roleRoute('eco-certifications.create') }}" wire:navigate>
                Nueva certificación
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs --}}
    <div x-data="{
        open: localStorage.getItem('eco-certifications-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('eco-certifications-stats-open', String(this.open));
        }
    }">
        <button
            @click="toggle()"
            class="flex items-center gap-1.5 text-[11px] font-semibold text-zinc-400 uppercase tracking-widest hover:text-zinc-600 transition-colors mb-3"
        >
            <span>Estadísticas</span>
            <flux:icon icon="chevron-up" class="size-3.5 transition-transform duration-200" ::class="{ 'rotate-180': !open }" />
        </button>
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
        >
        <div class="grid grid-cols-2 gap-4">
            <x-agro.stat-card
                label="Total certificaciones"
                :value="$stats['total']"
                icon="sparkles"
                color="zinc"
            />
            <x-agro.stat-card
                label="Activas"
                :value="$stats['active']"
                icon="check-circle"
                color="agro"
            />
            <x-agro.stat-card
                label="Próximas a vencer"
                :value="$stats['expiring']"
                icon="clock"
                color="amber"
            />
            <x-agro.stat-card
                label="Pendientes"
                :value="$stats['pending']"
                icon="hourglass"
                color="zinc"
            />
        </div>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">
        <div class="flex-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Buscar por nombre, organismo..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
            />
        </div>

        <flux:select wire:model.live="typeFilter" class="w-44">
            <flux:select.option value="">Todos los tipos</flux:select.option>
            @foreach($types as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="statusFilter" class="w-40">
            <flux:select.option value="">Todos los estados</flux:select.option>
            @foreach($statuses as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>

        @if($search || $typeFilter || $statusFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                Limpiar
            </flux:button>
        @endif
    </div>

    {{-- Loading skeleton --}}
    <div wire:loading wire:target="search, typeFilter, statusFilter, clearFilters, nextPage, previousPage">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @for($i = 0; $i < 6; $i++)
                <x-agro.skeleton-card />
            @endfor
        </div>
    </div>

    {{-- Grid de cards --}}
    <div wire:loading.remove wire:target="search, typeFilter, statusFilter, clearFilters, nextPage, previousPage">
        @if($certifications->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($certifications as $cert)
                    @php
                        $delay = min($loop->index * 50, 300);
                        $statusConfig = match($cert->status) {
                            'active'    => ['badge' => 'green',  'bg' => 'bg-agro-100',   'icon' => 'text-agro-600'],
                            'expired'   => ['badge' => 'red',    'bg' => 'bg-red-100',     'icon' => 'text-red-600'],
                            'pending'   => ['badge' => 'yellow', 'bg' => 'bg-yellow-100',  'icon' => 'text-yellow-600'],
                            'suspended' => ['badge' => 'zinc',   'bg' => 'bg-zinc-100',    'icon' => 'text-zinc-400'],
                            default     => ['badge' => 'zinc',   'bg' => 'bg-zinc-100',    'icon' => 'text-zinc-400'],
                        };
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="cert-{{ $cert->id }}"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $statusConfig['bg'] }}">
                                    <flux:icon icon="sparkles" class="size-5 {{ $statusConfig['icon'] }}" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">{{ $cert->name }}</h3>
                                    @if($cert->certifying_body)
                                        <p class="text-xs text-zinc-400 truncate">{{ $cert->certifying_body }}</p>
                                    @endif
                                </div>
                                <flux:badge color="{{ $statusConfig['badge'] }}" size="sm" class="shrink-0">
                                    {{ $cert->status_label }}
                                </flux:badge>
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-3">
                            {{-- Tipo --}}
                            <flux:badge color="green" size="sm">{{ $cert->type_label }}</flux:badge>

                            {{-- Nº certificado --}}
                            @if($cert->certificate_number)
                                <div class="flex items-center gap-2 text-sm text-zinc-600">
                                    <flux:icon icon="hashtag" class="size-4 text-zinc-400 shrink-0" />
                                    <span class="text-xs font-mono">{{ $cert->certificate_number }}</span>
                                </div>
                            @endif

                            {{-- Válido hasta --}}
                            @if($cert->valid_until)
                                <div class="flex items-center gap-2 text-xs {{ $cert->isExpiringSoon() ? 'text-amber-600 font-medium' : 'text-zinc-500' }}">
                                    <flux:icon icon="clock" class="size-3.5 {{ $cert->isExpiringSoon() ? 'text-amber-500' : 'text-zinc-400' }}" />
                                    <span>Válido hasta: {{ $cert->valid_until->format('d/m/Y') }}</span>
                                    @if($cert->isExpiringSoon())
                                        <flux:badge color="amber" size="sm">Próximo</flux:badge>
                                    @endif
                                </div>
                            @else
                                <div class="flex items-center gap-2 text-xs text-zinc-400">
                                    <flux:icon icon="clock" class="size-3.5" />
                                    <span>Sin fecha de vencimiento</span>
                                </div>
                            @endif
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5">
                                <a href="{{ roleRoute('eco-certifications.edit', $cert) }}"
                                   wire:navigate
                                   title="Editar certificación"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                    <flux:icon icon="pencil-square" class="size-4" />
                                </a>
                                <button
                                    wire:click="delete({{ $cert->id }})"
                                    wire:confirm="¿Eliminar esta certificación?"
                                    wire:loading.attr="disabled"
                                    title="Eliminar certificación"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                                    <flux:icon icon="trash" class="size-4" />
                                </button>
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $certifications->links() }}
            </div>
        @else
            <x-agro.empty-state
                icon="sparkles"
                title="{{ $search || $typeFilter || $statusFilter ? 'Ninguna certificación coincide con los filtros' : 'Sin certificaciones registradas' }}"
                description="{{ $search || $typeFilter || $statusFilter ? 'Prueba a cambiar o limpiar los filtros aplicados.' : 'Añade tus certificaciones ecológicas, biodinámicas o de sostenibilidad.' }}"
            >
                @if($search || $typeFilter || $statusFilter)
                    <x-slot:action>
                        <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                    </x-slot:action>
                @else
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('eco-certifications.create') }}" wire:navigate variant="primary" icon="plus">
                            Nueva certificación
                        </flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @endif
    </div>

</div>

