<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100" 
     @if($hasPendingReports) wire:poll.5s @endif>
    {{-- Auto-refresh every 5 seconds if there are pending/processing reports --}}
    {{-- Header --}}
    @php
        $reportIcon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';
    @endphp
    <x-page-header 
        :icon="$reportIcon"
        title="Informes Oficiales" 
        description="Gestiona tus informes firmados electrónicamente para administración y certificaciones"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    >
        <x-slot:actionButton>
            <a href="{{ route('viticulturist.official-reports.create') }}" wire:navigate class="group">
                <button class="flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold">
                    <svg class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Generar Nuevo Informe
                </button>
            </a>
        </x-slot:actionButton>
    </x-page-header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Panel de Estadísticas Compacto --}}
        <div class="bg-white rounded-2xl shadow-xl p-6 mb-8 border border-gray-200">
            <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2 mb-4">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Resumen
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg border border-blue-200">
                    <p class="text-2xl font-bold text-blue-900">{{ $totalCount }}</p>
                    <p class="text-xs text-blue-700 mt-1">Total Informes</p>
                </div>
                <div class="text-center p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-lg border border-green-200">
                    <p class="text-2xl font-bold text-green-900">{{ $validCount }}</p>
                    <p class="text-xs text-green-700 mt-1">Válidos</p>
                </div>
                <div class="text-center p-4 bg-gradient-to-br from-red-50 to-red-100 rounded-lg border border-red-200">
                    <p class="text-2xl font-bold text-red-900">{{ $invalidCount }}</p>
                    <p class="text-xs text-red-700 mt-1">Invalidados</p>
                </div>
                <div class="text-center p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg border border-purple-200">
                    <p class="text-lg font-bold text-purple-900">{{ $lastReportDate ?? '—' }}</p>
                    <p class="text-xs text-purple-700 mt-1">Último Generado</p>
                </div>
            </div>
        </div>


        {{-- Filtros --}}
        <x-filter-section title="Filtros de Búsqueda" color="green">
            <x-filter-input 
                wire:model.live="search" 
                placeholder="Buscar por código de verificación..."
            />
            <x-slot:actions>
                @if($search || $statusFilter !== 'all')
                    <x-button wire:click="resetFilters" variant="ghost" size="sm">
                        Limpiar Filtros
                    </x-button>
                @endif
            </x-slot:actions>
        </x-filter-section>

        {{-- Tabs de Estado --}}
        <div class="bg-white rounded-t-2xl shadow-xl border-x border-t border-gray-200 px-6 pt-4">
            <div class="border-b border-gray-200">
                <div class="flex space-x-2">
                    <button 
                        wire:click="$set('statusFilter', 'all')"
                        class="px-6 py-3 font-semibold border-b-2 transition-colors
                               {{ $statusFilter === 'all' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
                    >
                        Todos ({{ $totalCount }})
                    </button>
                    <button 
                        wire:click="$set('statusFilter', 'valid')"
                        class="px-6 py-3 font-semibold border-b-2 transition-colors
                               {{ $statusFilter === 'valid' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
                    >
                        ✓ Válidos ({{ $validCount }})
                    </button>
                    <button 
                        wire:click="$set('statusFilter', 'invalid')"
                        class="px-6 py-3 font-semibold border-b-2 transition-colors
                               {{ $statusFilter === 'invalid' ? 'border-red-600 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
                    >
                        ✗ Invalidados ({{ $invalidCount }})
                    </button>
                </div>
            </div>
        </div>

        {{-- Tabla de Informes --}}
        @php
            $headers = [
                ['label' => 'Estado', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'],
                ['label' => 'Tipo', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>'],
                ['label' => 'Periodo', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'],
                ['label' => 'Generado', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'],
                ['label' => 'Tamaño', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>'],
                'Acciones',
            ];
        @endphp

        <x-data-table 
            :headers="$headers" 
            empty-message="No hay informes generados"
            empty-description="Comienza generando tu primer informe oficial"
            class="rounded-t-none border-t-0"
        >
            @if($reports->count() > 0)
                @foreach($reports as $report)
                    <x-table-row>
                        {{-- Estado --}}
                        <x-table-cell>
                            <div class="flex flex-col gap-1">
                                <x-status-badge :active="$report->isValid()" 
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
                                        <span 
                                            class="px-2 py-0.5 text-xs bg-green-100 text-green-800 rounded-full flex items-center gap-1 cursor-help"
                                            title="Cumplimiento PAC: {{ number_format($compliancePercentage, 1) }}% - {{ $pacCompliance['stats']['with_valid_sigpac'] }}/{{ $pacCompliance['stats']['total_activities'] }} actividades con SIGPAC válido"
                                        >
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            PAC OK
                                        </span>
                                    @elseif($pacCompliance['has_warnings'] && empty($pacCompliance['errors']))
                                        <span 
                                            class="px-2 py-0.5 text-xs bg-yellow-100 text-yellow-800 rounded-full flex items-center gap-1 cursor-help"
                                            title="Cumplimiento PAC: {{ number_format($compliancePercentage, 1) }}% - {{ count($pacCompliance['warnings']) }} advertencias encontradas"
                                        >
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            ⚠ Revisar
                                        </span>
                                    @else
                                        <span 
                                            class="px-2 py-0.5 text-xs bg-red-100 text-red-800 rounded-full flex items-center gap-1 cursor-help"
                                            title="Cumplimiento PAC: {{ number_format($compliancePercentage, 1) }}% - {{ count($pacCompliance['errors']) }} errores críticos"
                                        >
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                            ✗ No Cumple
                                        </span>
                                    @endif
                                @endif
                                
                                @if($report->processing_status === 'pending')
                                    <span class="px-2 py-0.5 text-xs bg-yellow-100 text-yellow-800 rounded-full inline-flex items-center gap-1">
                                        <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Pendiente
                                    </span>
                                @elseif($report->processing_status === 'processing')
                                    <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-800 rounded-full inline-flex items-center gap-1">
                                        <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Procesando...
                                    </span>
                                @elseif($report->processing_status === 'failed')
                                    <span class="px-2 py-0.5 text-xs bg-red-100 text-red-800 rounded-full inline-flex items-center gap-1">
                                        ❌ Error
                                    </span>
                                @endif
                            </div>
                        </x-table-cell>

                        {{-- Tipo --}}
                        <x-table-cell>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center">
                                    <span class="text-xl">{{ $report->report_icon }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">{{ $report->report_type_name }}</p>
                                    <p class="text-xs text-gray-500">{{ substr($report->verification_code, 0, 12) }}...</p>
                                </div>
                            </div>
                        </x-table-cell>

                        {{-- Periodo --}}
                        <x-table-cell>
                            <p class="text-sm text-gray-900">{{ $report->period_start->format('d/m/Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $report->period_end->format('d/m/Y') }}</p>
                        </x-table-cell>

                        {{-- Generado --}}
                        <x-table-cell>
                            <p class="text-sm text-gray-900">{{ $report->created_at->format('d/m/Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $report->created_at->format('H:i') }}</p>
                        </x-table-cell>

                        {{-- Tamaño --}}
                        <x-table-cell>
                            <span class="text-sm text-gray-600">{{ $report->formatted_pdf_size }}</span>
                        </x-table-cell>

                        {{-- Acciones --}}
                        <x-table-actions align="right">
                            {{-- Ver Preview --}}
                            <button 
                                wire:click="openPreviewModal({{ $report->id }})"
                                class="p-2 rounded-lg transition-all duration-200 group/btn text-gray-600 hover:bg-gray-100"
                                title="Vista previa"
                            >
                                <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>

                            {{-- Descargar (Dropdown para formatos múltiples) --}}
                            @if($report->processing_status === 'completed')
                                <div x-data="{ open: false }" @click.away="open = false" class="relative">
                                    <button
                                        @click="open = !open"
                                        class="p-2 rounded-lg transition-all duration-200 group/btn text-green-600 hover:bg-green-50"
                                        title="Descargar informe"
                                    >
                                        <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0  24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                    </button>

                                    <!-- Dropdown Menu -->
                                    <div
                                        x-show="open"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="transform opacity-0 scale-95"
                                        x-transition:enter-end="transform opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="transform opacity-100 scale-100"
                                        x-transition:leave-end="transform opacity-0 scale-95"
                                        class="absolute right-0 mt-2 w-48 rounded-lg shadow-xl bg-white ring-1 ring-black ring-opacity-5 z-50"
                                        style="display: none;"
                                    >
                                        <div class="py-1">
                                            <!-- PDF -->
                                            <a
                                                href="{{ route('viticulturist.official-reports.download', $report) }}"
                                                class="group flex items-center justify-between px-4 py-2 text-sm text-gray-700 hover:bg-green-50 transition-colors"
                                            >
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/>
                                                        <path fill="white" d="M14 2v6h6"/>
                                                    </svg>
                                                    <span class="font-medium">PDF</span>
                                                </div>
                                                <span class="text-xs text-green-600">✓</span>
                                            </a>

                                            <!-- CSV -->
                                            @if($report->csv_path)
                                                <button
                                                    wire:click="downloadInFormat({{ $report->id }}, 'csv')"
                                                    @click="open = false"
                                                    class="group w-full flex items-center justify-between px-4 py-2 text-sm text-gray-700 hover:bg-green-50 transition-colors"
                                                >
                                                    <div class="flex items-center gap-2">
                                                        <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/>
                                                            <path fill="white" d="M14 2v6h6"/>
                                                        </svg>
                                                        <span class="font-medium">CSV</span>
                                                    </div>
                                                    <span class="text-xs text-green-600">✓</span>
                                                </button>
                                            @else
                                                <div class="flex items-center justify-between px-4 py-2 text-sm text-gray-400 cursor-not-allowed">
                                                    <div class="flex items-center gap-2">
                                                        <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/>
                                                        </svg>
                                                        <span>CSV</span>
                                                    </div>
                                                    <span class="text-xs">N/A</span>
                                                </div>
                                            @endif

                                            <!-- XML -->
                                            @if($report->xml_path)
                                                <button
                                                    wire:click="downloadInFormat({{ $report->id }}, 'xml')"
                                                    @click="open = false"
                                                    class="group w-full flex items-center justify-between px-4 py-2 text-sm text-gray-700 hover:bg-green-50 transition-colors"
                                                >
                                                    <div class="flex items-center gap-2">
                                                        <svg class="w-4 h-4 text-orange-500" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/>
                                                            <path fill="white" d="M14 2v6h6"/>
                                                        </svg>
                                                        <span class="font-medium">XML</span>
                                                    </div>
                                                    <span class="text-xs text-green-600">✓</span>
                                                </button>
                                            @else
                                                <div class="flex items-center justify-between px-4 py-2 text-sm text-gray-400 cursor-not-allowed">
                                                    <div class="flex items-center gap-2">
                                                        <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/>
                                                        </svg>
                                                        <span>XML</span>
                                                    </div>
                                                    <span class="text-xs">N/A</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Verificar --}}
                            <a 
                                href="{{ route('reports.verify', ['code' => $report->verification_code]) }}" 
                                target="_blank"
                                class="p-2 rounded-lg transition-all duration-200 group/btn text-blue-600 hover:bg-blue-50"
                                title="Verificar autenticidad"
                            >
                                <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </a>

                            {{-- Compartir --}}
                            <button 
                                wire:click="openShareModal({{ $report->id }})"
                                class="p-2 rounded-lg transition-all duration-200 group/btn text-indigo-600 hover:bg-indigo-50"
                                title="Compartir por email"
                            >
                                <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </button>

                            {{-- Invalidar --}}
                            @if($report->isValid())
                                <button 
                                    wire:click="openInvalidateModal({{ $report->id }})"
                                    class="p-2 rounded-lg transition-all duration-200 group/btn text-red-600 hover:bg-red-50"
                                    title="Invalidar informe"
                                >
                                    <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                </button>
                            @endif
                        </x-table-actions>
                    </x-table-row>
                @endforeach

                <x-slot name="pagination">
                    {{ $reports->links() }}
                </x-slot>
            @else
                <x-slot name="emptyAction">
                    <a href="{{ route('viticulturist.official-reports.create') }}" wire:navigate>
                        <x-button variant="primary">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Generar Primer Informe
                        </x-button>
                    </a>
                </x-slot>
            @endif
        </x-data-table>
    </div>

    {{-- Include modals --}}
    @include('livewire.viticulturist.official-reports.partials._share-modal')
    @include('livewire.viticulturist.official-reports.partials._invalidate-modal')
    @include('livewire.viticulturist.official-reports.partials._preview-modal')
</div>
