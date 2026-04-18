<div class="space-y-6 animate-fade-in" @if($hasPendingReports) wire:poll.5s @endif>

    <x-agro.page-header
        title="Informes Oficiales"
        description="Gestiona tus informes firmados electrónicamente para administración y certificaciones"
    />

    {{-- Tabs --}}
    <x-agro.tabs
        :tabs="[
            'all'     => ['label' => 'Todos',       'count' => $totalCount],
            'valid'   => ['label' => 'Válidos',     'count' => $validCount],
            'invalid' => ['label' => 'Invalidados', 'count' => $invalidCount],
        ]"
        :active="$statusFilter"
        wireMethod="switchTab"
    />

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">

        <x-agro.search-input wire:model.live.debounce.300ms="search" placeholder="Buscar por código de verificación o tipo..." />

        @if($search)
            <flux:button wire:click="resetFilters" variant="ghost" icon="x-mark" size="sm">Limpiar</flux:button>
        @endif

        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        <flux:button href="{{ roleRoute('viticulturist.official-reports.create') }}" wire:navigate variant="primary" icon="plus">
            Generar Informe
        </flux:button>

    </div>

    {{-- Card grid --}}
    @if($reports->count() > 0)
        @php
            $btnBase = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors';
            $btnRed  = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors';
        @endphp

        <div
            class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="search, statusFilter, switchTab, resetFilters"
        >
            @foreach($reports as $i => $report)
                <x-agro.card
                    wire:key="report-{{ $report->id }}"
                    class="animate-fade-in-up hover:-translate-y-1"
                    style="animation-delay: {{ min($i * 50, 400) }}ms"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-blue-50 rounded-full flex items-center justify-center shrink-0 text-lg leading-none">
                                {{ $report->report_icon }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-zinc-900 text-sm truncate leading-tight">{{ $report->report_type_name }}</p>
                                <p class="text-xs text-zinc-400 leading-tight mt-0.5 font-mono">{{ substr($report->verification_code, 0, 14) }}…</p>
                            </div>
                            <div class="flex flex-col items-end gap-1 shrink-0">
                                <x-agro.status-badge :active="$report->isValid()" active-text="Válido" inactive-text="Invalidado" />
                                @if($report->processing_status === 'pending')
                                    <flux:badge color="yellow" size="sm">Pendiente</flux:badge>
                                @elseif($report->processing_status === 'processing')
                                    <flux:badge color="blue" size="sm">Procesando…</flux:badge>
                                @elseif($report->processing_status === 'failed')
                                    <flux:badge color="red" size="sm">Error</flux:badge>
                                @endif
                            </div>
                        </div>
                    </x-slot:header>

                    {{-- Detalles --}}
                    <div class="bg-zinc-50 rounded-xl p-2.5 mb-3 space-y-1.5">
                        <div class="flex items-center gap-2">
                            <flux:icon icon="calendar-days" class="size-3.5 text-zinc-400 shrink-0" />
                            <span class="text-xs text-zinc-600">
                                {{ $report->period_start->format('d/m/Y') }} — {{ $report->period_end->format('d/m/Y') }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <flux:icon icon="clock" class="size-3.5 text-zinc-400 shrink-0" />
                            <span class="text-xs text-zinc-600">
                                Generado {{ $report->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>
                        @if($report->formatted_pdf_size)
                            <div class="flex items-center gap-2">
                                <flux:icon icon="document" class="size-3.5 text-zinc-400 shrink-0" />
                                <span class="text-xs text-zinc-600">{{ $report->formatted_pdf_size }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- PAC compliance badge --}}
                    @if(isset($report->report_metadata['pac_compliance']))
                        @php
                            $pac = $report->report_metadata['pac_compliance'];
                            $pacPct = $report->report_metadata['compliance_percentage'] ?? 0;
                        @endphp
                        <div class="mb-3">
                            @if($pac['is_compliant'])
                                <flux:badge color="green" size="sm" icon="check-circle">PAC OK ({{ number_format($pacPct, 1) }}%)</flux:badge>
                            @elseif($pac['has_warnings'] && empty($pac['errors']))
                                <flux:badge color="yellow" size="sm" icon="exclamation-triangle">Revisar ({{ number_format($pacPct, 1) }}%)</flux:badge>
                            @else
                                <flux:badge color="red" size="sm" icon="x-circle">No Cumple ({{ number_format($pacPct, 1) }}%)</flux:badge>
                            @endif
                        </div>
                    @endif

                    <x-slot:footer>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1">
                                {{-- Preview --}}
                                <button wire:click="openPreviewModal({{ $report->id }})" class="{{ $btnBase }}" title="Vista previa">
                                    <flux:icon icon="eye" class="size-4" />
                                </button>

                                {{-- Verify --}}
                                <a href="{{ route('reports.verify', ['code' => $report->verification_code]) }}" target="_blank" class="{{ $btnBase }}" title="Verificar autenticidad">
                                    <flux:icon icon="shield-check" class="size-4" />
                                </a>

                                {{-- Share --}}
                                <button wire:click="openShareModal({{ $report->id }})" class="{{ $btnBase }}" title="Compartir por email">
                                    <flux:icon icon="envelope" class="size-4" />
                                </button>
                            </div>

                            <div class="flex items-center gap-1">
                                {{-- Download dropdown --}}
                                @if($report->processing_status === 'completed')
                                    <div x-data="{ open: false }" @click.away="open = false" class="relative">
                                        <button @click="open = !open" class="{{ $btnBase }}" title="Descargar">
                                            <flux:icon icon="arrow-down-tray" class="size-4" />
                                        </button>
                                        <div
                                            x-show="open"
                                            x-transition
                                            class="absolute right-0 bottom-10 w-36 rounded-xl shadow-xl bg-white ring-1 ring-black/5 z-50 py-1"
                                            style="display: none;"
                                        >
                                            <a href="{{ roleRoute('viticulturist.official-reports.download', $report) }}" class="flex items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-agro-50 rounded-lg mx-1">
                                                <flux:icon icon="document" class="size-4 text-red-500" /> PDF
                                            </a>
                                            @if($report->csv_path)
                                                <button wire:click="downloadInFormat({{ $report->id }}, 'csv')" @click="open = false" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-agro-50 rounded-lg mx-1">
                                                    <flux:icon icon="document" class="size-4 text-emerald-500" /> CSV
                                                </button>
                                            @endif
                                            @if($report->xml_path)
                                                <button wire:click="downloadInFormat({{ $report->id }}, 'xml')" @click="open = false" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-agro-50 rounded-lg mx-1">
                                                    <flux:icon icon="document" class="size-4 text-orange-500" /> XML
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- Invalidate --}}
                                @if($report->isValid())
                                    <button wire:click="openInvalidateModal({{ $report->id }})" class="{{ $btnRed }}" title="Invalidar informe">
                                        <flux:icon icon="no-symbol" class="size-4" />
                                    </button>
                                @endif
                            </div>
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        @if($reports->hasPages())
            <div class="flex justify-center">{{ $reports->links() }}</div>
        @endif

    @else
        <x-agro.empty-state
            icon="document-text"
            message="No hay informes generados"
            :description="$search ? 'No se encontraron informes con ese código de verificación.' : 'Comienza generando tu primer informe oficial.'"
        />
    @endif

    {{-- Modals --}}
    @include('livewire.viticulturist.official-reports.partials._share-modal')
    @include('livewire.viticulturist.official-reports.partials._invalidate-modal')
    @include('livewire.viticulturist.official-reports.partials._preview-modal')

</div>
