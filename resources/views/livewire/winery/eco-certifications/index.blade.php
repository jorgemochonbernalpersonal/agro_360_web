<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="{{ __('Certificaciones Ecológicas') }}"
        :description="__('Gestión de certificaciones de producción ecológica, biodinámica y sostenible')"
        icon="sparkles"
    >
        <x-slot:actions>
            <flux:button variant="primary" icon="plus" href="{{ roleRoute('eco-certifications.create') }}" wire:navigate>
                Nueva certificación
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs --}}
    <x-agro.stats-section key="eco-certifications">
        <x-agro.stat-card
            :label="__('Total certificaciones')"
            :value="$stats['total']"
            icon="sparkles"
            color="zinc"
        />
        <x-agro.stat-card
            :label="__('Activas')"
            :value="$stats['active']"
            icon="check-circle"
            color="agro"
        />
        <x-agro.stat-card
            :label="__('Próximas a vencer')"
            :value="$stats['expiring']"
            icon="clock"
            color="amber"
        />
        <x-agro.stat-card
            :label="__('Pendientes')"
            :value="$stats['pending']"
            icon="queue-list"
            color="zinc"
        />
    </x-agro.stats-section>

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">
        <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar por nombre, organismo...')" />

        <flux:select wire:model.live="typeFilter" class="w-44">
            <flux:select.option value="">{{ __('Todos los tipos') }}</flux:select.option>
            @foreach($types as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="statusFilter" class="w-40">
            <flux:select.option value="">{{ __('Todos los estados') }}</flux:select.option>
            @foreach($statuses as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>

        @if($search || $typeFilter || $statusFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">{{ __('Limpiar') }}</flux:button>
        @endif
    </div>

    {{-- Loading skeleton --}}
    <x-agro.loading-grid target="search, typeFilter, statusFilter, clearFilters, nextPage, previousPage" :count="6" :cols="3" />

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
                            <x-agro.card-item-header
                                icon="sparkles"
                                :title="$cert->name"
                                :subtitle="$cert->certifying_body ?? null"
                                :iconBg="$statusConfig['bg']"
                                :iconColor="$statusConfig['icon']"
                                size="md"
                                radius="xl"
                            >
                                <flux:badge color="{{ $statusConfig['badge'] }}" size="sm">
                                    {{ $cert->status_label }}
                                </flux:badge>
                            </x-agro.card-item-header>
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
                                        <flux:badge color="amber" size="sm">{{ __('Próximo') }}</flux:badge>
                                    @endif
                                </div>
                            @else
                                <div class="flex items-center gap-2 text-xs text-zinc-400">
                                    <flux:icon icon="clock" class="size-3.5" />
                                    <span>{{ __('Sin fecha de vencimiento') }}</span>
                                </div>
                            @endif
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5">
                                <x-agro.action-button variant="edit" href="{{ roleRoute('eco-certifications.edit', $cert) }}" wire:navigate title="{{ __('Editar certificación') }}" />
                                <x-agro.action-button variant="delete" wire:click="delete({{ $cert->id }})" wire:confirm="{{ __('¿Eliminar esta certificación?') }}" wire:loading.attr="disabled" title="{{ __('Eliminar certificación') }}" />
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <x-agro-pagination :paginator="$certifications" />
        @else
            <x-agro.empty-state
                icon="sparkles"
                title="{{ $search || $typeFilter || $statusFilter ? 'Ninguna certificación coincide con los filtros' : 'Sin certificaciones registradas' }}"
                description="{{ $search || $typeFilter || $statusFilter ? 'Prueba a cambiar o limpiar los filtros aplicados.' : 'Añade tus certificaciones ecológicas, biodinámicas o de sostenibilidad.' }}"
            >
                @if($search || $typeFilter || $statusFilter)
                    <x-slot:action>
                        <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">{{ __('Limpiar filtros') }}</flux:button>
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

