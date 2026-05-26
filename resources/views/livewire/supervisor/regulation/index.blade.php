<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="{{ __('Normativa DO') }}"
        :description="__('Autorizaciones de plantación, certificaciones ecológicas y documentos regulatorios.')"
    />

    {{-- Tabs --}}
    <div class="border-b border-zinc-200">
        <nav class="-mb-px flex gap-1 overflow-x-auto">
            @foreach($tabs as $key => $tab)
                <button wire:click="switchTab('{{ $key }}')"
                    class="whitespace-nowrap px-4 py-2.5 text-sm font-medium border-b-2 transition
                        {{ $currentTab === $key
                            ? 'border-indigo-500 text-indigo-600'
                            : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' }}">
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </nav>
    </div>

    {{-- ── TAB: Autorizaciones de plantación ─────────────────────────────── --}}
    @if($currentTab === 'autorizaciones')

        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
            <x-agro.stat-card :label="__('Total autorizaciones')" :value="$stats['total']" icon="check-circle" color="indigo" />
            <x-agro.stat-card :label="__('Nueva plantación')"    :value="$stats['nueva']"         icon="sparkles"   color="emerald" />
            <x-agro.stat-card :label="__('Replantación')"        :value="$stats['replantacion']"   icon="arrow-path" color="blue" />
            <x-agro.stat-card :label="__('Conversión')"          :value="$stats['conversion']"     icon="arrows-right-left" color="amber" />
            <x-agro.stat-card :label="__('Transferencia')"       :value="$stats['transferencia']"  icon="arrow-right-circle" color="violet" />
        </div>

        {{-- Filters --}}
        <div class="flex flex-wrap items-center gap-3">
            <flux:select wire:model.live="filterVit">
                <flux:select.option value="">{{ __('Todos los viticultores') }}</flux:select.option>
                @foreach($viticulturists as $v)
                    <flux:select.option value="{{ $v->id }}">{{ $v->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="filterRightType">
                <flux:select.option value="">{{ __('Todos los tipos de derecho') }}</flux:select.option>
                <flux:select.option value="nueva">{{ __('Nueva plantación') }}</flux:select.option>
                <flux:select.option value="replantacion">{{ __('Replantación') }}</flux:select.option>
                <flux:select.option value="conversion">{{ __('Conversión') }}</flux:select.option>
                <flux:select.option value="transferencia">{{ __('Transferencia') }}</flux:select.option>
            </flux:select>

            <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar autorización o parcela...')" />
        </div>

        {{-- Skeleton durante carga --}}
        <x-agro.loading-grid target="search, switchTab, filterVit, filterRightType, nextPage, previousPage" />

        {{-- Card grid --}}
        <div wire:loading.remove wire:target="search, switchTab, filterVit, filterRightType, nextPage, previousPage">
            @if($items->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($items as $planting)
                        @php
                            $delay = min($loop->index * 50, 300);
                            $rightLabels = ['nueva' => 'Nueva', 'replantacion' => 'Replantación', 'conversion' => 'Conversión', 'transferencia' => 'Transferencia'];
                            $rightColors = ['nueva' => 'emerald', 'replantacion' => 'blue', 'conversion' => 'amber', 'transferencia' => 'violet'];
                            $rc = $rightColors[$planting->right_type] ?? 'zinc';
                            $rightBg = [
                                'nueva'          => 'bg-emerald-100',
                                'replantacion'   => 'bg-blue-100',
                                'conversion'     => 'bg-amber-100',
                                'transferencia'  => 'bg-violet-100',
                            ];
                            $rightIconColor = [
                                'nueva'          => 'text-emerald-600',
                                'replantacion'   => 'text-blue-600',
                                'conversion'     => 'text-amber-600',
                                'transferencia'  => 'text-violet-600',
                            ];
                            $rightIcons = [
                                'nueva'          => 'sparkles',
                                'replantacion'   => 'arrow-path',
                                'conversion'     => 'arrows-right-left',
                                'transferencia'  => 'arrow-right-circle',
                            ];
                            $bgClass = $rightBg[$planting->right_type] ?? 'bg-zinc-100';
                            $iconColorClass = $rightIconColor[$planting->right_type] ?? 'text-zinc-500';
                            $iconName = $rightIcons[$planting->right_type] ?? 'document-check';
                        @endphp
                        <x-agro.card
                            class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                            style="animation-delay: {{ $delay }}ms;"
                            wire:key="planting-{{ $planting->id }}"
                        >
                            <x-slot:header>
                                <x-agro.card-item-header
                                    :icon="$iconName"
                                    :title="$planting->plot?->name ?? '—'"
                                    :subtitle="$planting->plot?->viticulturist?->name ?? '—'"
                                    :iconBg="$bgClass"
                                    :iconColor="$iconColorClass"
                                    size="md"
                                    radius="xl"
                                >
                                    @if($planting->right_type)
                                        <flux:badge color="{{ $rc }}" size="sm">{{ $rightLabels[$planting->right_type] ?? $planting->right_type }}</flux:badge>
                                    @endif
                                </x-agro.card-item-header>
                            </x-slot:header>

                            <div class="flex-1 space-y-4">
                                <div class="bg-agro-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">{{ __('Nº autorización') }}</p>
                                    <p class="text-sm font-bold text-agro-700 leading-none font-mono">{{ $planting->planting_authorization }}</p>
                                </div>

                                <div class="space-y-2 text-sm">
                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-400">{{ __('Variedad') }}</span>
                                        <span class="text-zinc-700 font-medium">{{ $planting->grapeVariety?->name ?? '—' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-400">{{ __('Fecha autorización') }}</span>
                                        <span class="text-zinc-700 font-medium">{{ $planting->authorization_date ? $planting->authorization_date->format('d/m/Y') : '—' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-400">{{ __('Arranque') }}</span>
                                        <span class="text-zinc-700 font-medium">{{ $planting->uprooting_date ? $planting->uprooting_date->format('d/m/Y') : '—' }}</span>
                                    </div>
                                </div>
                            </div>
                        </x-agro.card>
                    @endforeach
                </div>
                <x-agro-pagination :paginator="$items" />
            @else
                <x-agro.empty-state icon="document-check" title="{{ __('Sin autorizaciones') }}" :description="__('No hay plantaciones con autorización registrada.')" />
            @endif
        </div>

    {{-- ── TAB: Certificaciones ecológicas ────────────────────────────────── --}}
    @elseif($currentTab === 'certificaciones')

        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <x-agro.stat-card :label="__('Total certificaciones')" :value="$stats['total']"   icon="check-badge"        color="indigo" />
            <x-agro.stat-card :label="__('Vigentes')"              :value="$stats['active']"  icon="check-circle"       color="emerald" />
            <x-agro.stat-card :label="__('Por vencer (60 días)')"  :value="$stats['expiring']" icon="clock"             color="amber" />
            <x-agro.stat-card :label="__('Caducadas')"             :value="$stats['expired']" icon="exclamation-circle" color="red" />
        </div>

        {{-- Filters --}}
        <div class="flex flex-wrap items-center gap-3">
            <flux:select wire:model.live="filterVit">
                <flux:select.option value="">{{ __('Todos los viticultores') }}</flux:select.option>
                @foreach($viticulturists as $v)
                    <flux:select.option value="{{ $v->id }}">{{ $v->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="filterStatus">
                <flux:select.option value="">{{ __('Todos los estados') }}</flux:select.option>
                <flux:select.option value="active">{{ __('Vigentes') }}</flux:select.option>
                <flux:select.option value="expiring">{{ __('Por vencer') }}</flux:select.option>
                <flux:select.option value="expired">{{ __('Caducadas') }}</flux:select.option>
            </flux:select>

            <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar nº certificado u organismo...')" />
        </div>

        {{-- Skeleton durante carga --}}
        <x-agro.loading-grid target="search, switchTab, filterVit, filterStatus, nextPage, previousPage" />

        {{-- Card grid --}}
        <div wire:loading.remove wire:target="search, switchTab, filterVit, filterStatus, nextPage, previousPage">
            @if($items->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($items as $cert)
                        @php
                            $delay = min($loop->index * 50, 300);
                            $isExpired  = $cert->is_expired;
                            $isExpiring = $cert->is_expiring_soon;
                            $statusColor = $isExpired ? 'red' : ($isExpiring ? 'amber' : 'emerald');
                            $statusLabel = $isExpired ? 'Caducada' : ($isExpiring ? 'Por vencer' : 'Vigente');
                            $iconBg = $isExpired ? 'bg-red-100' : ($isExpiring ? 'bg-amber-100' : 'bg-emerald-100');
                            $iconText = $isExpired ? 'text-red-600' : ($isExpiring ? 'text-amber-600' : 'text-emerald-600');
                        @endphp
                        <x-agro.card
                            class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                            style="animation-delay: {{ $delay }}ms;"
                            wire:key="cert-{{ $cert->id }}"
                        >
                            <x-slot:header>
                                <x-agro.card-item-header
                                    icon="check-badge"
                                    :title="$cert->viticulturist?->name ?? '—'"
                                    :subtitle="$cert->certifying_body ?? '—'"
                                    :iconBg="$iconBg"
                                    :iconColor="$iconText"
                                    size="md"
                                    radius="xl"
                                >
                                    @if($cert->active)
                                        <flux:badge color="{{ $statusColor }}" size="sm">{{ $statusLabel }}</flux:badge>
                                    @else
                                        <flux:badge color="zinc" size="sm">{{ __('Inactiva') }}</flux:badge>
                                    @endif
                                </x-agro.card-item-header>
                            </x-slot:header>

                            <div class="flex-1 space-y-4">
                                <div class="bg-agro-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">{{ __('Nº certificado') }}</p>
                                    <p class="text-sm font-bold text-agro-700 leading-none font-mono">{{ $cert->certificate_number ?? '—' }}</p>
                                </div>

                                <div class="space-y-2 text-sm">
                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-400">{{ __('Emisión') }}</span>
                                        <span class="text-zinc-700 font-medium">{{ $cert->issue_date ? $cert->issue_date->format('d/m/Y') : '—' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-400">{{ __('Caducidad') }}</span>
                                        <span class="{{ $isExpired ? 'text-red-600 font-semibold' : ($isExpiring ? 'text-amber-600 font-semibold' : 'text-zinc-700 font-medium') }}">
                                            {{ $cert->expiry_date ? $cert->expiry_date->format('d/m/Y') : '—' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </x-agro.card>
                    @endforeach
                </div>
                <x-agro-pagination :paginator="$items" />
            @else
                <x-agro.empty-state icon="check-badge" title="{{ __('Sin certificaciones') }}" :description="__('No hay certificaciones ecológicas registradas.')" />
            @endif
        </div>

    {{-- ── TAB: Pliego de condiciones / Reglamento interno ─────────────────── --}}
    @elseif(in_array($currentTab, ['pliego', 'reglamento']))

        @php
            $typeLabel = $currentTab === 'pliego' ? 'pliego de condiciones' : 'reglamento interno';
            $typeIcon  = $currentTab === 'pliego' ? 'document-text' : 'clipboard-document-list';
        @endphp

        {{-- Stats + link to management --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="grid grid-cols-3 gap-3 flex-1">
                <x-agro.stat-card :label="__('Borradores')" :value="$stats['draft']"    icon="pencil"       color="zinc" />
                <x-agro.stat-card :label="__('Vigentes')"   :value="$stats['active']"   icon="check-circle" color="emerald" />
                <x-agro.stat-card :label="__('Archivados')" :value="$stats['archived']" icon="archive-box"  color="amber" />
            </div>
            <a href="{{ route('supervisor.documents.index') }}"
                class="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-800 transition shrink-0">
                Gestionar documentos
                <flux:icon icon="arrow-top-right-on-square" class="w-4 h-4" />
            </a>
        </div>

        {{-- Filter --}}
        <div class="flex items-center gap-3">
            <flux:select wire:model.live="filterStatus">
                <flux:select.option value="">{{ __('Todos los estados') }}</flux:select.option>
                <flux:select.option value="draft">{{ __('Borradores') }}</flux:select.option>
                <flux:select.option value="active">{{ __('Vigentes') }}</flux:select.option>
                <flux:select.option value="archived">{{ __('Archivados') }}</flux:select.option>
            </flux:select>
        </div>

        {{-- Document list (read-only) --}}
        @if($items->isEmpty())
            <x-agro.empty-state
                :icon="$typeIcon"
                :title="__('Sin documentos de :type', ['type' => $typeLabel])"
                :description="'Accede a Gestionar documentos para crear el primero.'"
            />
        @else
            <div class="space-y-3">
                @foreach($items as $doc)
                    @php
                        $statusColors = ['draft' => 'zinc', 'active' => 'emerald', 'archived' => 'amber'];
                        $sc = $statusColors[$doc->status] ?? 'zinc';
                    @endphp
                    <x-agro.card class="hover:shadow-sm transition">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <flux:icon icon="{{ $typeIcon }}" class="w-4 h-4 text-indigo-500" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-zinc-800">{{ $doc->title }}</p>
                                <div class="flex flex-wrap items-center gap-3 mt-1">
                                    @if($doc->version)
                                        <span class="text-xs text-zinc-400">v{{ $doc->version }}</span>
                                    @endif
                                    @if($doc->effective_date)
                                        <span class="text-xs text-zinc-400">Vigente desde {{ $doc->effective_date->format('d/m/Y') }}</span>
                                    @endif
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $sc }}-100 text-{{ $sc }}-700">
                                        {{ \App\Models\DoDocument::STATUS_LABELS[$doc->status] }}
                                    </span>
                                </div>
                                @if($doc->content)
                                    <p class="text-xs text-zinc-500 mt-2 line-clamp-2">{{ $doc->content }}</p>
                                @endif
                            </div>
                        </div>
                    </x-agro.card>
                @endforeach
            </div>
            <x-agro-pagination :paginator="$items" />
        @endif

    @endif

</div>
