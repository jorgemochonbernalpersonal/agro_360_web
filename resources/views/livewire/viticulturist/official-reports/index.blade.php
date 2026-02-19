<div class="space-y-6 animate-fade-in" @if($hasPendingReports) wire:poll.5s @endif>
    {{-- Auto-refresh every 5 seconds if there are pending/processing reports --}}
    {{-- Header --}}
    <x-agro.page-header
        title="Informes Oficiales"
        description="Gestiona tus informes firmados electrónicamente para administración y certificaciones"
    >
        <x-slot:actions>
            <a href="{{ route('viticulturist.official-reports.create') }}" wire:navigate>
                <flux:button variant="primary" icon="plus">
                    Generar Nuevo Informe
                </flux:button>
            </a>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Panel de Estadísticas --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <x-agro.stat-card label="Total Informes" :value="$totalCount" icon="document-text" color="blue" />
        <x-agro.stat-card label="Válidos" :value="$validCount" icon="check-circle" color="green" />
        <x-agro.stat-card label="Invalidados" :value="$invalidCount" icon="x-circle" color="red" />
        <x-agro.stat-card label="Último Generado" :value="$lastReportDate ?? '—'" icon="clock" color="agro" />
    </div>

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live="search"
            placeholder="Buscar por código de verificación..."
        />
        @if($search || $statusFilter !== 'all')
            <flux:button wire:click="resetFilters" variant="ghost" size="sm" icon="x-mark">Limpiar</flux:button>
        @endif
    </x-agro.filter-bar>

    {{-- Tabs de Estado --}}
    <x-agro.card :padding="false">
        <div class="px-6 pt-4">
            <div class="border-b border-zinc-200">
                <div class="flex space-x-2">
                    <button
                        wire:click="$set('statusFilter', 'all')"
                        class="px-6 py-3 font-semibold border-b-2 transition-colors
                               {{ $statusFilter === 'all' ? 'border-green-600 text-green-600' : 'border-transparent text-zinc-500 hover:text-zinc-700' }}"
                    >
                        Todos ({{ $totalCount }})
                    </button>
                    <button
                        wire:click="$set('statusFilter', 'valid')"
                        class="px-6 py-3 font-semibold border-b-2 transition-colors
                               {{ $statusFilter === 'valid' ? 'border-green-600 text-green-600' : 'border-transparent text-zinc-500 hover:text-zinc-700' }}"
                    >
                        Válidos ({{ $validCount }})
                    </button>
                    <button
                        wire:click="$set('statusFilter', 'invalid')"
                        class="px-6 py-3 font-semibold border-b-2 transition-colors
                               {{ $statusFilter === 'invalid' ? 'border-red-600 text-red-600' : 'border-transparent text-zinc-500 hover:text-zinc-700' }}"
                    >
                        Invalidados ({{ $invalidCount }})
                    </button>
                </div>
            </div>
        </div>
    </x-agro.card>

    {{-- Tabla de Informes --}}
    <x-agro.data-table
        :headers="['Estado', 'Tipo', 'Periodo', 'Generado', 'Tamaño', 'Acciones']"
        empty-message="No hay informes generados"
        empty-description="Comienza generando tu primer informe oficial"
    >
            @if($reports->count() > 0)
                @foreach($reports as $report)
                    <x-agro.table-row>
                        {{-- Estado --}}
                        <x-agro.table-cell>
                            <div class="flex flex-col gap-1">
                                <x-agro.status-badge :active="$report->isValid()"
                                    active-text="Válido"
                                    inactive-text="Invalidado"
                                />

                                {{-- PAC Compliance Badge --}}
                                @if(isset($report->report_metadata['pac_compliance']))
                                    @php
                                        $pacCompliance = $report->report_metadata['pac_compliance'];
                                        $compliancePercentage = $report->report_metadata['compliance_percentage'] ?? 0;
                                    @endphp

                                    @if($pacCompliance['is_compliant'])
                                        <flux:badge color="green" size="sm" icon="check-circle"
                                            title="Cumplimiento PAC: {{ number_format($compliancePercentage, 1) }}%"
                                        >PAC OK</flux:badge>
                                    @elseif($pacCompliance['has_warnings'] && empty($pacCompliance['errors']))
                                        <flux:badge color="yellow" size="sm" icon="exclamation-triangle"
                                            title="Cumplimiento PAC: {{ number_format($compliancePercentage, 1) }}%"
                                        >Revisar</flux:badge>
                                    @else
                                        <flux:badge color="red" size="sm" icon="x-circle"
                                            title="Cumplimiento PAC: {{ number_format($compliancePercentage, 1) }}%"
                                        >No Cumple</flux:badge>
                                    @endif
                                @endif

                                @if($report->processing_status === 'pending')
                                    <flux:badge color="yellow" size="sm">Pendiente</flux:badge>
                                @elseif($report->processing_status === 'processing')
                                    <flux:badge color="blue" size="sm">Procesando...</flux:badge>
                                @elseif($report->processing_status === 'failed')
                                    <flux:badge color="red" size="sm">Error</flux:badge>
                                @endif
                            </div>
                        </x-agro.table-cell>

                        {{-- Tipo --}}
                        <x-agro.table-cell>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                                    <span class="text-xl">{{ $report->report_icon }}</span>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-zinc-900">{{ $report->report_type_name }}</div>
                                    <div class="text-xs text-zinc-500">{{ substr($report->verification_code, 0, 12) }}...</div>
                                </div>
                            </div>
                        </x-agro.table-cell>

                        {{-- Periodo --}}
                        <x-agro.table-cell>
                            <p class="text-sm text-zinc-900">{{ $report->period_start->format('d/m/Y') }}</p>
                            <p class="text-xs text-zinc-500">{{ $report->period_end->format('d/m/Y') }}</p>
                        </x-agro.table-cell>

                        {{-- Generado --}}
                        <x-agro.table-cell>
                            <p class="text-sm text-zinc-900">{{ $report->created_at->format('d/m/Y') }}</p>
                            <p class="text-xs text-zinc-500">{{ $report->created_at->format('H:i') }}</p>
                        </x-agro.table-cell>

                        {{-- Tamaño --}}
                        <x-agro.table-cell>
                            <span class="text-sm text-zinc-600">{{ $report->formatted_pdf_size }}</span>
                        </x-agro.table-cell>

                        {{-- Acciones --}}
                        <x-agro.table-cell align="right">
                            <div class="flex items-center justify-end gap-1">
                                {{-- Ver Preview --}}
                                <flux:button wire:click="openPreviewModal({{ $report->id }})" variant="ghost" size="sm" icon="eye" title="Vista previa" />

                                {{-- Descargar --}}
                                @if($report->processing_status === 'completed')
                                    <div x-data="{ open: false }" @click.away="open = false" class="relative">
                                        <flux:button @click="open = !open" variant="ghost" size="sm" icon="arrow-down-tray" title="Descargar informe" />
                                        <div
                                            x-show="open"
                                            x-transition
                                            class="absolute right-0 mt-2 w-40 rounded-lg shadow-xl bg-white ring-1 ring-black ring-opacity-5 z-50 py-1"
                                            style="display: none;"
                                        >
                                            <a href="{{ route('viticulturist.official-reports.download', $report) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-zinc-700 hover:bg-agro-50">
                                                <flux:icon icon="document" class="size-4 text-red-500" /> PDF
                                            </a>
                                            @if($report->csv_path)
                                                <button wire:click="downloadInFormat({{ $report->id }}, 'csv')" @click="open = false" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-zinc-700 hover:bg-agro-50">
                                                    <flux:icon icon="document" class="size-4 text-emerald-500" /> CSV
                                                </button>
                                            @endif
                                            @if($report->xml_path)
                                                <button wire:click="downloadInFormat({{ $report->id }}, 'xml')" @click="open = false" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-zinc-700 hover:bg-agro-50">
                                                    <flux:icon icon="document" class="size-4 text-orange-500" /> XML
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- Verificar --}}
                                <flux:button href="{{ route('reports.verify', ['code' => $report->verification_code]) }}" target="_blank" variant="ghost" size="sm" icon="shield-check" title="Verificar autenticidad" />

                                {{-- Compartir --}}
                                <flux:button wire:click="openShareModal({{ $report->id }})" variant="ghost" size="sm" icon="envelope" title="Compartir por email" />

                                {{-- Invalidar --}}
                                @if($report->isValid())
                                    <flux:button wire:click="openInvalidateModal({{ $report->id }})" variant="ghost" size="sm" icon="no-symbol" class="text-red-600 hover:bg-red-50" title="Invalidar informe" />
                                @endif
                            </div>
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach

                <x-slot name="pagination">
                    {{ $reports->links() }}
                </x-slot>
            @else
                <x-slot name="emptyAction">
                    <a href="{{ route('viticulturist.official-reports.create') }}" wire:navigate>
                        <flux:button variant="primary" icon="plus">
                            Generar Primer Informe
                        </flux:button>
                    </a>
                </x-slot>
            @endif
    </x-agro.data-table>

    {{-- Include modals --}}
    @include('livewire.viticulturist.official-reports.partials._share-modal')
    @include('livewire.viticulturist.official-reports.partials._invalidate-modal')
    @include('livewire.viticulturist.official-reports.partials._preview-modal')
</div>
