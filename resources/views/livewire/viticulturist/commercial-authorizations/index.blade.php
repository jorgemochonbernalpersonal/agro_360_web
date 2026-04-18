<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Autorizaciones Comerciales"
        subtitle="DO, certificaciones ecológicas, derechos de plantación y replantación"
        icon="shield-check"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.commercial-authorizations.create') }}" variant="primary" icon="plus">
                Nueva Autorización
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats --}}
    <x-agro.stats-section key="commercial-auth">
        <div class="grid grid-cols-2 gap-4">
            <x-agro.stat-card
                label="Total activas"
                :value="$stats['total']"
                description="'Autorizaciones vigentes'"
                icon="shield-check"
                color="agro"
            />
            <x-agro.stat-card
                label="Tipos distintos"
                :value="$stats['types']"
                description="'Categorías de autorización'"
                icon="squares-2x2"
                color="blue"
            />
            <x-agro.stat-card
                label="Próximas a vencer"
                :value="$stats['expiring']"
                description="'En los próximos 60 días'"
                icon="exclamation-triangle"
                color="orange"
            />
            <x-agro.stat-card
                label="Vencidas"
                :value="$stats['expired']"
                description="$stats['expired'] > 0 ? 'Requieren renovación' : 'Todas vigentes'"
                icon="x-circle"
                color="red"
            />
        </div>
    </x-agro.stats-section>
        @if($expiring > 0)
        <flux:callout variant="warning" icon="exclamation-triangle">
            Tienes <strong>{{ $expiring }}</strong> {{ $expiring === 1 ? 'autorización que vence' : 'autorizaciones que vencen' }} en los próximos 60 días. Revisa y renuévalas a tiempo.
        </flux:callout>
    @endif

    <x-agro.filter-bar>
        <x-agro.filter-select wire:model.live="filterType" label="Tipo">
            <option value="">Todos</option>
            @foreach($authTypes as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>
    </x-agro.filter-bar>

    <x-agro.loading-grid target="filterType" />
    <div wire:loading.remove wire:target="filterType">
        @if($entries->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($entries as $entry)
                    @php
                        $delay = min($loop->index * 50, 300);
                        $statusColor = $entry->isExpired() ? 'red' : ($entry->isExpiringSoon() ? 'amber' : 'green');
                        $statusLabel = $entry->isExpired() ? 'Vencida' : ($entry->isExpiringSoon() ? 'Por vencer' : 'Vigente');
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="entry-{{ $entry->id }}"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center shrink-0">
                                    <flux:icon icon="shield-check" class="size-5 text-indigo-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">{{ $entry->authorization_type_label }}</h3>
                                    <p class="text-xs text-zinc-500 truncate">{{ $entry->authorization_code ?? 'Sin código' }}</p>
                                </div>
                                <flux:badge color="{{ $statusColor }}" size="sm" class="shrink-0">{{ $statusLabel }}</flux:badge>
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">
                            <div class="grid grid-cols-2 gap-2">
                                <div class="bg-agro-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">Emisión</p>
                                    <p class="text-sm font-bold text-agro-700 leading-none">{{ $entry->issue_date->format('d/m/Y') }}</p>
                                </div>
                                <div class="bg-agro-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">Caducidad</p>
                                    <p class="text-sm font-bold leading-none {{ $entry->isExpired() ? 'text-red-600' : ($entry->isExpiringSoon() ? 'text-amber-600' : 'text-agro-700') }}">
                                        {{ $entry->expiry_date?->format('d/m/Y') ?? 'Indefinida' }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">Organismo</span>
                                    <span class="text-zinc-700 font-medium truncate ml-2">{{ $entry->issuing_body ?? '—' }}</span>
                                </div>
                                @if($entry->description)
                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-400">Descripción</span>
                                        <span class="text-zinc-700 font-medium truncate ml-2">{{ Str::limit($entry->description, 30) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <x-slot:footer>
                            @php $btnBase = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors'; @endphp
                            <div class="flex items-center justify-end gap-0.5">
                                <a href="{{ roleRoute('viticulturist.commercial-authorizations.edit', $entry) }}"
                                   class="{{ $btnBase }}" title="Editar">
                                    <flux:icon icon="pencil-square" class="size-4" />
                                </a>
                                <button
                                    wire:click="deactivate({{ $entry->id }})"
                                    wire:confirm="¿Archivar esta autorización?"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-amber-600 hover:bg-amber-50 transition-colors"
                                    title="Archivar">
                                    <flux:icon icon="archive-box" class="size-4" />
                                </button>
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>
            @if($entries->hasPages())
                <div class="mt-6">{{ $entries->links() }}</div>
            @endif
        @else
            <x-agro.empty-state
                icon="shield-check"
                title="Sin autorizaciones registradas"
                description="Registra tus inscripciones en Denominaciones de Origen, certificaciones ecológicas y derechos de plantación."
            >
                <x-slot:action>
                    <flux:button href="{{ roleRoute('viticulturist.commercial-authorizations.create') }}" variant="primary" icon="plus">
                        Nueva Autorización
                    </flux:button>
                </x-slot:action>
            </x-agro.empty-state>
        @endif
    </div>

</div>
