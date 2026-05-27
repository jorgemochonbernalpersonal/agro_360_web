<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="{{ __('SILICIE — Libro de Registro') }}"
        description="Registro oficial de entradas, elaboración, existencias y salidas. Campaña {{ $vintage }}."
        icon="document-chart-bar"
    >
        <x-slot:actions>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200"
                  title="{{ __('Conforme a Orden HAC/1505/2024 — SILICIE 2.0 (TIPO_DOCUMENTO + PERIODO_FISCAL)') }}">
                <flux:icon icon="check-circle" class="size-3.5" />
                SILICIE 2.0 · HAC/1505/2024
            </span>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Filtro y acciones ───────────────────────────────────────────────── --}}
    <x-agro.filter-bar>
        <x-agro.filter-select wire:model.live="filterVintage" :label="__('Añada')">
            @foreach($vintages as $v)
                <option value="{{ $v }}">{{ $v }}</option>
            @endforeach
        </x-agro.filter-select>

        <div class="flex-1"></div>

        <flux:button
            wire:click="takeSnapshot"
            wire:confirm="{{ __('¿Registrar instantánea de existencias para hoy?') }}"
            variant="ghost"
            icon="camera"
            size="sm"
        >
            Instantánea de existencias
        </flux:button>

        <flux:button
            wire:click="exportCsv"
            variant="primary"
            icon="arrow-down-tray"
            size="sm"
        >{{ __('Exportar CSV SILICIE') }}</flux:button>
    </x-agro.filter-bar>

    {{-- Guía post-exportación ───────────────────────────────────────────── --}}
    @if($showExportGuide)
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4" x-data>
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center shrink-0 mt-0.5">
                        <flux:icon icon="check-circle" class="size-5 text-blue-600" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-blue-900 mb-1">{{ __('CSV descargado — ¿Qué hago ahora?') }}</p>
                        <ol class="text-xs text-blue-800 space-y-1 list-decimal list-inside leading-relaxed">
                            <li>{{ __('Accede al portal oficial de SILICIE') }}</li>
                            <li>Ve a <strong>{{ __('Importar movimientos') }}</strong> → selecciona el fichero CSV descargado</li>
                            <li>{{ __('Verifica que todos los movimientos se han importado correctamente') }}</li>
                            <li>{{ __('Confirma la presentación desde el portal') }}</li>
                        </ol>
                        <a
                            href="https://silicie.es"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1.5 mt-2 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-colors"
                        >
                            <flux:icon icon="arrow-top-right-on-square" class="size-3.5" />
                            Ir a silicie.es
                        </a>
                    </div>
                </div>
                <button
                    wire:click="dismissExportGuide"
                    class="text-blue-400 hover:text-blue-600 transition-colors shrink-0"
                    title="{{ __('Cerrar') }}"
                >
                    <flux:icon icon="x-mark" class="size-4" />
                </button>
            </div>
        </div>
    @endif

    {{-- KPIs ────────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 gap-4">
        <x-agro.stat-card
            label="Kg recibidos (añada {{ $vintage }})"
            :value="number_format($stats['kgReceived'], 0, ',', '.') . ' kg'"
            icon="archive-box-arrow-down"
            color="agro"
        />
        <x-agro.stat-card
            :label="__('Litros en bodega (hoy)')"
            :value="number_format($stats['wineryLiters'], 0, ',', '.') . ' L'"
            icon="beaker"
            color="blue"
        />
        <x-agro.stat-card
            label="Vinos activos (añada {{ $vintage }})"
            :value="$stats['activeWines']"
            icon="funnel"
            color="violet"
        />
        <x-agro.stat-card
            label="Expediciones facturadas ({{ $vintage }})"
            :value="$stats['outputs']"
            icon="arrow-up-tray"
            color="amber"
        />
    </div>

    {{-- Tabs ────────────────────────────────────────────────────────────── --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex flex-wrap gap-1">
                @foreach([
                    'entries'    => ['label' => 'Libro I — Entradas',      'icon' => 'archive-box-arrow-down'],
                    'elaboration' => ['label' => 'Libro II — Elaboración',   'icon' => 'beaker'],
                    'inventory'  => ['label' => 'Libro III — Existencias',  'icon' => 'squares-2x2'],
                    'outputs'    => ['label' => 'Libro IV — Salidas',       'icon' => 'arrow-up-tray'],
                    'opening'    => ['label' => 'Apertura ejercicio',        'icon' => 'calendar-days'],
                ] as $tab => $meta)
                    <button
                        wire:click="switchTab('{{ $tab }}')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors
                            {{ $currentTab === $tab
                                ? 'bg-agro-600 text-white shadow-sm'
                                : 'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700' }}"
                    >
                        <flux:icon icon="{{ $meta['icon'] }}" class="size-3.5" />
                        {{ $meta['label'] }}
                    </button>
                @endforeach
            </div>
        </x-slot:header>

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- LIBRO I — ENTRADAS                                            --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        @if($currentTab === 'entries')
            @php $recepciones = $tabData['recepciones']; $externas = $tabData['externas']; $totals = $tabData['totals']; @endphp

            <div class="px-4 py-3 flex flex-wrap items-center gap-x-6 gap-y-1 border-b border-zinc-100 bg-zinc-50/50">
                <div class="text-sm text-zinc-500">
                    <span class="font-semibold text-zinc-800">{{ $totals['recepciones'] }}</span> recepciones propias ·
                    <span class="font-semibold text-zinc-800">{{ number_format($totals['kg_total'], 0, ',', '.') }}</span> kg
                </div>
                <div class="text-sm text-zinc-500">
                    <span class="font-semibold text-zinc-800">{{ $totals['externas_count'] }}</span> entradas externas ·
                    <span class="font-semibold text-zinc-800">{{ number_format($totals['externas_kg'], 0, ',', '.') }}</span> kg
                </div>
            </div>

            {{-- A02: Uva propia --}}
            <div class="px-4 pt-4 pb-2">
                <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wide">{{ __('A02 — Recepciones de vendimia propia') }}</p>
            </div>
            @if($recepciones->isEmpty())
                <p class="px-4 pb-4 text-sm text-zinc-400">Sin recepciones de uva propia para la añada {{ $vintage }}.</p>
            @else
                <x-agro.data-table
                    :headers="['Fecha', 'Viticultor', 'Variedad', 'Kg recibidos', 'Estado sanitario', '°Baumé']"
                >
                    @foreach($recepciones as $r)
                        <x-agro.table-row>
                            <x-agro.table-cell>
                                {{ $r->harvest_start_date
                                    ? \Carbon\Carbon::parse($r->harvest_start_date)->translatedFormat('d M Y')
                                    : '—' }}
                            </x-agro.table-cell>
                            <x-agro.table-cell>{{ $r->viticulturist_name ?? '—' }}</x-agro.table-cell>
                            <x-agro.table-cell>{{ $r->variety_name ?? '—' }}</x-agro.table-cell>
                            <x-agro.table-cell>
                                @if($r->total_weight)
                                    <span class="font-medium">{{ number_format($r->total_weight, 0, ',', '.') }}</span>
                                    <span class="text-zinc-400 text-xs">kg</span>
                                @else
                                    <span class="text-zinc-300">—</span>
                                @endif
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                @if($r->health_status)
                                    <x-agro.status-badge :status="$r->health_status" />
                                @else
                                    <span class="text-zinc-300">—</span>
                                @endif
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                {{ $r->baume_degree ? number_format($r->baume_degree, 1, ',', '') . '°' : '—' }}
                            </x-agro.table-cell>
                        </x-agro.table-row>
                    @endforeach
                </x-agro.data-table>
            @endif

            {{-- A02/A04/A06: Compras externas --}}
            <div class="px-4 pt-6 pb-2 border-t border-zinc-100 mt-2">
                <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wide">{{ __('A02/A04/A06 — Uva, mosto y vino a granel comprado') }}</p>
            </div>
            @if($externas->isEmpty())
                <p class="px-4 pb-4 text-sm text-zinc-400">Sin entradas externas para la añada {{ $vintage }}.</p>
            @else
                <x-agro.data-table
                    :headers="['Fecha', 'Tipo', 'Proveedor', 'Variedad / DO', 'Peso (kg)']"
                >
                    @foreach($externas as $eg)
                        <x-agro.table-row>
                            <x-agro.table-cell>
                                {{ $eg->entry_date ? \Carbon\Carbon::parse($eg->entry_date)->translatedFormat('d M Y') : '—' }}
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                @php
                                    $typeLabels = ['grapes' => 'Uva', 'must' => 'Mosto', 'bulk_wine' => 'Vino granel'];
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-agro-50 text-agro-700 border border-agro-200">
                                    {{ $typeLabels[$eg->grape_type] ?? $eg->grape_type }}
                                </span>
                            </x-agro.table-cell>
                            <x-agro.table-cell>{{ $eg->supplier_name ?? '—' }}</x-agro.table-cell>
                            <x-agro.table-cell>
                                <span class="text-zinc-800">{{ $eg->variety_name ?? '—' }}</span>
                                @if($eg->protection_level)
                                    <span class="text-zinc-400 text-xs ml-1">{{ $eg->protection_level }}</span>
                                @endif
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                <span class="font-medium">{{ number_format($eg->total_weight_kg, 0, ',', '.') }}</span>
                                <span class="text-zinc-400 text-xs">kg</span>
                            </x-agro.table-cell>
                        </x-agro.table-row>
                    @endforeach
                </x-agro.data-table>
            @endif
        @endif

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- LIBRO II — ELABORACIÓN                                        --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        @if($currentTab === 'elaboration')
            @php $steps = $tabData['steps']; $losses = $tabData['losses']; @endphp

            {{-- Proceso enológico --}}
            <div class="px-4 pt-4 pb-2">
                <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wide mb-3">{{ __('Operaciones enológicas') }}</p>
            </div>

            @if($steps->isEmpty())
                <x-agro.empty-state
                    icon="beaker"
                    title="{{ __('Sin operaciones registradas') }}"
                    description="No hay pasos de elaboración para la añada {{ $vintage }}."
                />
            @else
                <x-agro.data-table
                    :headers="['Vino', 'Operación', 'Inicio', 'Fin', 'Contenedor', 'Observaciones']"
                >
                    @foreach($steps as $s)
                        <x-agro.table-row>
                            <x-agro.table-cell>
                                <div>
                                    <p class="font-medium text-zinc-800 text-sm">{{ $s->wine_name }}</p>
                                    <p class="text-xs text-zinc-400">{{ \App\Models\Wine::WINE_TYPES[$s->wine_type] ?? $s->wine_type }}</p>
                                </div>
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-violet-50 text-violet-700 border border-violet-200">
                                    {{ \App\Models\WineProcessDetail::PROCESS_TYPES[$s->process_type] ?? $s->process_type }}
                                </span>
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                {{ $s->start_date ? \Carbon\Carbon::parse($s->start_date)->translatedFormat('d M Y') : '—' }}
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                {{ $s->end_date ? \Carbon\Carbon::parse($s->end_date)->translatedFormat('d M Y') : '—' }}
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                {{ $s->container_name ?? '—' }}
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                <span class="text-zinc-500 text-xs line-clamp-1">{{ $s->observations ?? '—' }}</span>
                            </x-agro.table-cell>
                        </x-agro.table-row>
                    @endforeach
                </x-agro.data-table>
            @endif

            {{-- Pérdidas durante elaboración --}}
            @if($losses->isNotEmpty())
                <div class="px-4 pt-6 pb-2 border-t border-zinc-100 mt-2">
                    <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wide mb-3">{{ __('Pérdidas registradas') }}</p>
                </div>
                <x-agro.data-table
                    :headers="['Fecha', 'Vino', 'Tipo pérdida', 'Autorización', 'Cantidad', 'Notas']"
                >
                    @foreach($losses as $l)
                        <x-agro.table-row>
                            <x-agro.table-cell>
                                {{ $l->loss_date ? \Carbon\Carbon::parse($l->loss_date)->translatedFormat('d M Y') : '—' }}
                            </x-agro.table-cell>
                            <x-agro.table-cell>{{ $l->wine_name ?? '—' }}</x-agro.table-cell>
                            <x-agro.table-cell>
                                {{ \App\Models\WineLoss::LOSS_TYPES[$l->loss_type] ?? $l->loss_type }}
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                @if($l->loss_authorization)
                                    <span class="text-xs text-zinc-500">
                                        {{ \App\Models\WineLoss::LOSS_AUTHORIZATIONS[$l->loss_authorization] ?? $l->loss_authorization }}
                                    </span>
                                @else
                                    <span class="text-zinc-300">—</span>
                                @endif
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                <span class="font-medium">{{ number_format($l->quantity, 2, ',', '.') }}</span>
                                <span class="text-zinc-400 text-xs">L</span>
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                <span class="text-zinc-500 text-xs line-clamp-1">{{ $l->notes ?? '—' }}</span>
                            </x-agro.table-cell>
                        </x-agro.table-row>
                    @endforeach
                </x-agro.data-table>
            @endif
        @endif

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- LIBRO III — EXISTENCIAS                                       --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        @if($currentTab === 'inventory')
            @php $byWine = $tabData['byWine']; $totals = $tabData['totals']; $lastSnapshot = $tabData['lastSnapshot']; @endphp

            <div class="px-4 py-3 flex items-center justify-between border-b border-zinc-100 bg-zinc-50/50">
                <div class="text-sm text-zinc-500">
                    <span class="font-semibold text-zinc-800">{{ number_format($totals['total_liters'], 0, ',', '.') }}</span> L en bodega ·
                    <span class="font-semibold text-zinc-800">{{ $totals['wine_count'] }}</span> vinos ·
                    <span class="font-semibold text-zinc-800">{{ $totals['container_count'] }}</span> contenedores ocupados
                </div>
                @if($lastSnapshot)
                    <span class="text-xs text-zinc-400">
                        Última instantánea: {{ \Carbon\Carbon::parse($lastSnapshot)->translatedFormat('d M Y') }}
                    </span>
                @endif
            </div>

            @if($byWine->isEmpty())
                <x-agro.empty-state
                    icon="squares-2x2"
                    title="{{ __('Sin existencias registradas') }}"
                    :description="__('No hay contenedores con vino activo en este momento.')"
                />
            @else
                <x-agro.data-table
                    :headers="['Vino', 'Tipo', 'Añada', 'Estado', 'Litros', 'Contenedores']"
                >
                    @foreach($byWine as $row)
                        <x-agro.table-row>
                            <x-agro.table-cell>
                                <span class="font-medium text-zinc-800">{{ $row['wine_name'] ?? '—' }}</span>
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                {{ \App\Models\Wine::WINE_TYPES[$row['wine_type']] ?? ($row['wine_type'] ?? '—') }}
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                {{ $row['vintage'] ?? '—' }}
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                @php $statusLabels = \App\Models\Wine::STATUSES; @endphp
                                <x-agro.status-badge
                                    :status="$row['wine_status']"
                                    :label="$statusLabels[$row['wine_status']] ?? $row['wine_status']"
                                />
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                <span class="font-semibold text-zinc-800">{{ number_format($row['total_liters'], 0, ',', '.') }}</span>
                                <span class="text-zinc-400 text-xs">L</span>
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                {{ $row['containers'] }}
                            </x-agro.table-cell>
                        </x-agro.table-row>
                    @endforeach
                </x-agro.data-table>
            @endif
        @endif

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- LIBRO IV — SALIDAS                                            --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        @if($currentTab === 'outputs')
            @php
                $ventas       = $tabData['ventas'];
                $perdidas     = $tabData['perdidas'];
                $subproductos = $tabData['subproductos'];
                $totals       = $tabData['totals'];
            @endphp

            {{-- Resumen salidas --}}
            <div class="px-4 py-3 grid grid-cols-3 gap-4 border-b border-zinc-100 bg-zinc-50/50">
                <div>
                    <p class="text-xs text-zinc-400">{{ __('Ventas facturadas') }}</p>
                    <p class="text-lg font-semibold text-zinc-800">{{ $totals['ventas_count'] }} facturas</p>
                    <p class="text-xs text-zinc-500">{{ number_format($totals['ventas_amount'], 2, ',', '.') }} €</p>
                </div>
                <div>
                    <p class="text-xs text-zinc-400">{{ __('Pérdidas registradas') }}</p>
                    <p class="text-lg font-semibold text-zinc-800">{{ number_format($totals['perdidas_qty'], 2, ',', '.') }} L</p>
                </div>
                <div>
                    <p class="text-xs text-zinc-400">{{ __('Subproductos gestionados') }}</p>
                    <p class="text-lg font-semibold text-zinc-800">{{ number_format($totals['subproductos_qty'], 2, ',', '.') }}</p>
                </div>
            </div>

            {{-- Ventas --}}
            <div class="px-4 pt-4 pb-2">
                <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wide">{{ __('Ventas y expediciones') }}</p>
            </div>
            @if($ventas->isEmpty())
                <p class="px-4 pb-4 text-sm text-zinc-400">Sin ventas en {{ $vintage }}.</p>
            @else
                <x-agro.data-table :headers="['Fecha', 'Nº Factura', 'Cliente', 'Importe', 'Estado']">
                    @foreach($ventas as $v)
                        <x-agro.table-row>
                            <x-agro.table-cell>
                                {{ $v->invoice_date ? \Carbon\Carbon::parse($v->invoice_date)->translatedFormat('d M Y') : '—' }}
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                <span class="font-mono text-xs">{{ $v->invoice_number ?? '—' }}</span>
                            </x-agro.table-cell>
                            <x-agro.table-cell>{{ $v->client_name ?? '—' }}</x-agro.table-cell>
                            <x-agro.table-cell>
                                <span class="font-medium">{{ number_format($v->total_amount, 2, ',', '.') }}</span>
                                <span class="text-zinc-400 text-xs">€</span>
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                <x-agro.status-badge :status="$v->status" />
                            </x-agro.table-cell>
                        </x-agro.table-row>
                    @endforeach
                </x-agro.data-table>
            @endif

            {{-- Subproductos --}}
            @if($subproductos->isNotEmpty())
                <div class="px-4 pt-6 pb-2 border-t border-zinc-100 mt-2">
                    <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wide">{{ __('Subproductos (orujo · lías · vinaza)') }}</p>
                </div>
                <x-agro.data-table :headers="['Fecha', 'Vino', 'Tipo', 'Destino', 'Cantidad', 'Lote']">
                    @foreach($subproductos as $s)
                        <x-agro.table-row>
                            <x-agro.table-cell>
                                {{ $s->subproduct_date ? \Carbon\Carbon::parse($s->subproduct_date)->translatedFormat('d M Y') : '—' }}
                            </x-agro.table-cell>
                            <x-agro.table-cell>{{ $s->wine_name ?? '—' }}</x-agro.table-cell>
                            <x-agro.table-cell>
                                {{ \App\Models\WineSubproduct::TYPES[$s->type] ?? $s->type }}
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                <span class="text-xs text-zinc-500">{{ \App\Models\WineSubproduct::DESTINATIONS[$s->destination] ?? ($s->destination ?? '—') }}</span>
                                @if($s->destination_name)
                                    <br><span class="text-xs text-zinc-400">{{ $s->destination_name }}</span>
                                @endif
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                <span class="font-medium">{{ number_format($s->quantity, 2, ',', '.') }}</span>
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                <span class="font-mono text-xs text-zinc-400">{{ $s->lot_number ?? '—' }}</span>
                            </x-agro.table-cell>
                        </x-agro.table-row>
                    @endforeach
                </x-agro.data-table>
            @endif

            {{-- Pérdidas en Salidas --}}
            @if($perdidas->isNotEmpty())
                <div class="px-4 pt-6 pb-2 border-t border-zinc-100 mt-2">
                    <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wide">{{ __('Pérdidas y mermas') }}</p>
                </div>
                <x-agro.data-table :headers="['Fecha', 'Vino', 'Tipo', 'Autorización', 'Litros', 'Notas']">
                    @foreach($perdidas as $p)
                        <x-agro.table-row>
                            <x-agro.table-cell>
                                {{ $p->loss_date ? \Carbon\Carbon::parse($p->loss_date)->translatedFormat('d M Y') : '—' }}
                            </x-agro.table-cell>
                            <x-agro.table-cell>{{ $p->wine_name ?? '—' }}</x-agro.table-cell>
                            <x-agro.table-cell>
                                {{ \App\Models\WineLoss::LOSS_TYPES[$p->loss_type] ?? $p->loss_type }}
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                <span class="text-xs text-zinc-500">
                                    {{ \App\Models\WineLoss::LOSS_AUTHORIZATIONS[$p->loss_authorization] ?? '—' }}
                                </span>
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                <span class="font-medium">{{ number_format($p->quantity, 2, ',', '.') }}</span>
                                <span class="text-zinc-400 text-xs">L</span>
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                <span class="text-zinc-500 text-xs line-clamp-1">{{ $p->notes ?? '—' }}</span>
                            </x-agro.table-cell>
                        </x-agro.table-row>
                    @endforeach
                </x-agro.data-table>
            @endif
        @endif

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- APERTURA EJERCICIO — A22 SILICIE 2.0                          --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        @if($currentTab === 'opening')
            @php $ab = $tabData; @endphp

            <div class="px-4 py-3 flex flex-wrap items-center gap-4 border-b border-zinc-100 bg-zinc-50/50">
                <div class="flex items-center gap-2">
                    <label class="text-sm text-zinc-500">{{ __('Ejercicio fiscal') }}</label>
                    <select
                        wire:model.live="filterFiscalYear"
                        class="text-sm border border-zinc-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-300"
                    >
                        @foreach($fiscalYears as $fy)
                            <option value="{{ $fy }}">{{ $fy }}</option>
                        @endforeach
                    </select>
                </div>
                @if($ab['snapshot_date'])
                    <span class="text-xs text-zinc-400">
                        Instantánea de referencia: {{ \Carbon\Carbon::parse($ab['snapshot_date'])->translatedFormat('d M Y') }}
                    </span>
                @endif
                @if(!empty($ab['rows']))
                    <span class="text-xs font-medium text-blue-600">
                        Total existencias apertura: {{ number_format($ab['total_hl'], 3, ',', '.') }} HL
                    </span>
                @endif
            </div>

            @if(!$ab['snapshot_date'])
                <div class="p-4">
                    <flux:callout variant="warning" icon="exclamation-triangle">
                        <flux:callout.heading>{{ __('Sin instantánea de existencias disponible') }}</flux:callout.heading>
                        <flux:callout.text>
                            {{ __('No se encontró ninguna instantánea de existencias anterior al 1 de enero de') }} {{ $ab['fiscal_year'] }}.
                            {{ __('Registra una instantánea desde el panel SILICIE al cierre del ejercicio anterior para poder generar los movimientos de apertura A22.') }}
                        </flux:callout.text>
                    </flux:callout>
                </div>
            @else
                <div class="px-4 pt-4 pb-2">
                    <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wide">
                        A22 — Existencias en apertura · 1 enero {{ $ab['fiscal_year'] }}
                    </p>
                    <p class="text-xs text-zinc-400 mt-0.5">
                        Estos datos corresponden al movimiento SILICIE de tipo A22 (Entrada almacén auxiliar)
                        que debe declararse al abrir el ejercicio {{ $ab['fiscal_year'] }} en la sede electrónica de la AEAT.
                    </p>
                </div>

                <x-agro.data-table :headers="['Producto', 'Tipo', 'Nº vinos', 'HL apertura', 'SILICIE A22']">
                    @foreach($ab['rows'] as $row)
                        <x-agro.table-row>
                            <x-agro.table-cell>
                                <span class="{{ $row['hl'] > 0 ? 'text-zinc-800 font-medium' : 'text-zinc-300' }}">
                                    {{ $row['label'] }}
                                </span>
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                @if($row['is_must'])
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-amber-50 text-amber-700 border border-amber-200">{{ __('Mosto') }}</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-violet-50 text-violet-700 border border-violet-200">{{ __('Vino') }}</span>
                                @endif
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                <span class="{{ $row['hl'] > 0 ? 'text-zinc-600' : 'text-zinc-300' }}">
                                    {{ $row['wine_count'] ?: '—' }}
                                </span>
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                @if($row['hl'] > 0)
                                    <span class="font-semibold text-blue-700">{{ number_format($row['hl'], 3, ',', '.') }}</span>
                                    <span class="text-zinc-400 text-xs ml-1">HL</span>
                                @else
                                    <span class="text-zinc-300">0,000</span>
                                @endif
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                <code class="text-xs text-zinc-500 bg-zinc-100 px-1.5 py-0.5 rounded">
                                    A22;{{ $row['silicie_row']['FECHA_OPERACION'] }};OTR;{{ $row['silicie_row']['PERIODO_FISCAL'] }};{{ $row['silicie_row']['CODIGO_NC'] }};{{ number_format($row['hl'], 3, ',', '') }};HL
                                </code>
                            </x-agro.table-cell>
                        </x-agro.table-row>
                    @endforeach
                </x-agro.data-table>

                <div class="p-4 border-t border-zinc-100 mt-2">
                    <flux:callout variant="info" icon="information-circle">
                        <flux:callout.text class="text-xs">
                            <strong>{{ __('SILICIE 2.0 (Orden HAC/1505/2024)') }}</strong> —
                            Los campos
                            <code>TIPO_DOCUMENTO</code>
                            <x-agro.help-tip title="TIPO_DOCUMENTO" text="Tipo de documento que ampara el movimiento: FAC (factura), ALB (albarán de entrega), OTR (otro documento). Obligatorio desde enero 2025. En movimientos internos o de elaboración se usa OTR." />
                            y <code>PERIODO_FISCAL</code>
                            <x-agro.help-tip title="PERIODO_FISCAL" text="Mes y año del movimiento en formato AAAA-MM (ej. 2025-03). Obligatorio desde enero 2025. Corresponde al mes en que se realiza la operación, no a la fecha de factura." />
                            son obligatorios desde el 1 de enero de 2025.
                            El movimiento A22 (entrada almacén auxiliar) se utiliza para las existencias de apertura de ejercicio.
                            Exporta el CSV SILICIE desde este panel para obtener el fichero con todos los campos requeridos.
                        </flux:callout.text>
                    </flux:callout>
                </div>
            @endif
        @endif

    </x-agro.card>

</div>
