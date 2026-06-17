<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        :title="__('Certificaciones y Sellos')"
        :description="__('Registro de certificaciones oficiales: ecológico, PI, GlobalG.A.P., DO/IGP (Reglamento UE 2018/848)')"
        icon="shield-check"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.certifications.create') }}" variant="primary" icon="plus">
                {{ __('Nueva Certificación') }}
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats (colapsables) --}}
    <x-agro.stats-section key="certifications">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <x-agro.stat-card
                :label="__('Certificaciones vigentes')"
                :value="$stats['active']"
                icon="shield-check"
                color="agro"
            />
            <x-agro.stat-card
                :label="__('Próximas a vencer')"
                :value="$stats['expiring_soon']"
                :description="__('En los próximos 60 días')"
                icon="clock"
                color="amber"
            />
            <x-agro.stat-card
                :label="__('Vencidas')"
                :value="$stats['expired']"
                :description="__('Requieren renovación')"
                icon="x-circle"
                color="red"
            />
            <x-agro.stat-card
                :label="__('Archivadas')"
                :value="$stats['archived']"
                icon="archive-box"
                color="zinc"
            />
        </div>
    </x-agro.stats-section>

    {{-- Tabs --}}
    <x-agro.tabs :tabs="[
        'active'   => ['label' => __('Vigentes'),   'count' => $stats['active']],
        'archived' => ['label' => __('Archivadas'), 'count' => $stats['archived']],
    ]" :active="$currentTab" wireMethod="switchTab" />

    {{-- Toolbar --}}
    @php
        $filterCount = (int) !empty($filterCertificationType);
    @endphp
    <div class="flex items-center gap-3">

        {{-- Search --}}
        <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar por organismo, número o alcance...')" />

        {{-- Filtros --}}
        <x-agro.filter-button modal="certifications-filters" :count="$filterCount" />

        {{-- Separador --}}
        <x-agro.divider-vertical />

        {{-- Nueva Certificación --}}
        <flux:button href="{{ roleRoute('viticulturist.certifications.create') }}" variant="primary" icon="plus">
            {{ __('Nueva') }}
        </flux:button>

    </div>

    {{-- Chip de filtro activo --}}
    @if ($filterCertificationType)
        <div class="flex flex-wrap items-center gap-2">
            <x-agro.filter-chip icon="shield-check" :label="$certificationTypes[$filterCertificationType] ?? $filterCertificationType" wireRemove="$set('filterCertificationType', '')" />
            <button
                wire:click="$set('filterCertificationType', '')"
                class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors"
            >
                {{ __('Limpiar todo') }}
            </button>
        </div>
    @endif

    {{-- Skeleton durante carga --}}
    <x-agro.loading-grid target="switchTab, search, filterCertificationType, nextPage, previousPage, gotoPage" />

    {{-- Grid de cards --}}
    <div
        wire:loading.remove
        wire:target="switchTab, search, filterCertificationType, nextPage, previousPage, gotoPage"
    >
        @if ($entries->isEmpty())
            <x-agro.empty-state
                icon="shield-check"
                :title="$currentTab === 'active' ? __('Sin certificaciones vigentes') : __('Sin certificaciones archivadas')"
                :description="$currentTab === 'active' ? __('Registra tus certificaciones oficiales: agricultura ecológica, producción integrada, GlobalG.A.P., DO/IGP y más.') : __('Las certificaciones archivadas aparecerán aquí.')"
            >
                @if ($currentTab === 'active')
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('viticulturist.certifications.create') }}" variant="primary" icon="plus">
                            {{ __('Nueva Certificación') }}
                        </flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($entries as $entry)
                    @php
                        $delay = min($loop->index * 50, 300);
                        $iconClasses = match($entry->certification_type) {
                            'ecologico'             => 'bg-green-100 text-green-600',
                            'produccion_integrada'  => 'bg-teal-100 text-teal-600',
                            'globalgap'             => 'bg-blue-100 text-blue-600',
                            'rainforest'            => 'bg-emerald-100 text-emerald-600',
                            'denominacion_origen'   => 'bg-agro-100 text-agro-600',
                            'indicacion_geografica' => 'bg-violet-100 text-violet-600',
                            default                 => 'bg-zinc-100 text-zinc-400',
                        };
                        $daysLeft = $entry->expiry_date ? (int) now()->diffInDays($entry->expiry_date, false) : null;
                    @endphp

                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="cert-{{ $entry->id }}"
                    >
                        {{-- Header --}}
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $iconClasses }}">
                                    <flux:icon icon="shield-check" class="size-5" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">{{ $entry->certifying_body }}</h3>
                                    @if ($entry->certificate_number)
                                        <p class="text-xs text-zinc-400 font-mono truncate">{{ $entry->certificate_number }}</p>
                                    @endif
                                </div>
                                <flux:badge color="{{ $entry->type_color }}" size="sm" class="shrink-0">
                                    {{ $entry->certification_type_label }}
                                </flux:badge>
                            </div>
                        </x-slot:header>

                        {{-- Body --}}
                        <div class="flex-1 space-y-3">

                            {{-- Fechas vigencia --}}
                            <div class="bg-zinc-50 rounded-xl p-3 space-y-1">
                                <div class="flex items-center gap-2 text-xs text-zinc-600">
                                    <flux:icon icon="calendar-days" class="size-3.5 text-zinc-400 shrink-0" />
                                    <span class="text-zinc-400">{{ __('Emisión') }}:</span>
                                    <span class="font-medium">{{ $entry->issue_date->format('d/m/Y') }}</span>
                                </div>
                                @if ($entry->expiry_date)
                                    <div class="flex items-center gap-2 text-xs">
                                        <flux:icon icon="calendar-days" class="size-3.5 text-zinc-400 shrink-0" />
                                        <span class="text-zinc-400">{{ __('Vencimiento') }}:</span>
                                        <span class="font-medium {{ $entry->is_expired ? 'text-red-600' : ($entry->is_expiring_soon ? 'text-amber-600' : 'text-zinc-600') }}">
                                            {{ $entry->expiry_date->format('d/m/Y') }}
                                        </span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-2 text-xs text-zinc-400">
                                        <flux:icon icon="calendar-days" class="size-3.5 shrink-0" />
                                        <span>{{ __('Sin fecha de vencimiento') }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Badges de estado --}}
                            @if ($entry->is_expired)
                                <div class="flex items-center gap-1.5">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                        <flux:icon icon="x-circle" class="size-3" />
                                        {{ __('Vencida') }}
                                    </span>
                                </div>
                            @elseif ($entry->is_expiring_soon && $daysLeft !== null && $daysLeft >= 0)
                                <div class="flex items-center gap-1.5">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                        <flux:icon icon="clock" class="size-3" />
                                        {{ __('Vence en') }} {{ $daysLeft }} {{ __('días') }}
                                    </span>
                                </div>
                            @endif

                            {{-- Próxima auditoría --}}
                            @if ($entry->audit_date)
                                <div class="flex items-center gap-2 text-xs text-zinc-600">
                                    <flux:icon icon="clipboard-document-check" class="size-3.5 text-zinc-400 shrink-0" />
                                    <span class="text-zinc-400">{{ __('Auditoría') }}:</span>
                                    <span class="font-medium">{{ $entry->audit_date->format('d/m/Y') }}</span>
                                </div>
                            @endif

                            {{-- Alcance --}}
                            @if ($entry->scope)
                                <p class="text-xs text-zinc-500 truncate" title="{{ $entry->scope }}">
                                    {{ Str::limit($entry->scope, 80) }}
                                </p>
                            @endif

                        </div>

                        {{-- Footer acciones --}}
                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5">
                                <x-agro.action-button
                                    variant="edit"
                                    href="{{ roleRoute('viticulturist.certifications.edit', $entry) }}"
                                    :title="__('Editar')"
                                />
                                @if ($currentTab === 'active')
                                    <x-agro.action-button
                                        variant="archive"
                                        wire:click="archive({{ $entry->id }})"
                                        wire:confirm="{{ __('¿Archivar esta certificación?') }}"
                                        :title="__('Archivar')"
                                    />
                                @else
                                    <x-agro.action-button
                                        variant="restore"
                                        icon="arrow-path"
                                        wire:click="unarchive({{ $entry->id }})"
                                        wire:confirm="{{ __('¿Restaurar esta certificación?') }}"
                                        :title="__('Restaurar')"
                                    />
                                @endif
                                <x-agro.action-button
                                    variant="delete"
                                    wire:click="delete({{ $entry->id }})"
                                    wire:confirm="{{ __('¿Eliminar esta certificación? Esta acción no se puede deshacer.') }}"
                                    :title="__('Eliminar')"
                                />
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <x-agro.pagination :paginator="$entries" />
        @endif
    </div>

    {{-- Modal: Filtros --}}
    <x-agro.filter-modal
        name="certifications-filters"
        :hasActiveFilters="(bool) $filterCertificationType"
        clearAction="$set('filterCertificationType', '')"
    >
        <div>
            <x-agro.field-label>{{ __('Tipo de Certificación') }}</x-agro.field-label>
            <flux:select wire:model.live="filterCertificationType">
                <option value="">{{ __('Todos los tipos') }}</option>
                @foreach ($certificationTypes as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </flux:select>
        </div>
    </x-agro.filter-modal>

</div>
