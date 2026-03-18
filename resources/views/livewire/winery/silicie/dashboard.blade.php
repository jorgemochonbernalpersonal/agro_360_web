<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="SILICIE — Libro de Registro"
        description="Registro oficial de entradas, elaboración, existencias y salidas. Campaña {{ $vintage }}."
        icon="document-chart-bar"
    />

    {{-- Filtro y acciones ───────────────────────────────────────────────── --}}
    <x-agro.filter-bar>
        <x-agro.filter-select wire:model.live="filterVintage" label="Añada">
            @foreach($vintages as $v)
                <option value="{{ $v }}">{{ $v }}</option>
            @endforeach
        </x-agro.filter-select>

        <div class="flex-1"></div>

        <flux:button
            wire:click="takeSnapshot"
            wire:confirm="¿Registrar instantánea de existencias para hoy?"
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
        >
            Exportar CSV SILICIE
        </flux:button>
    </x-agro.filter-bar>

    {{-- KPIs ────────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-agro.stat-card
            label="Kg recibidos (añada {{ $vintage }})"
            :value="number_format($stats['kgRecibidos'], 0, ',', '.') . ' kg'"
            icon="archive-box-arrow-down"
            color="agro"
        />
        <x-agro.stat-card
            label="Litros en bodega (hoy)"
            :value="number_format($stats['litrosBodega'], 0, ',', '.') . ' L'"
            icon="beaker"
            color="blue"
        />
        <x-agro.stat-card
            label="Vinos activos (añada {{ $vintage }})"
            :value="$stats['vinosActivos']"
            icon="funnel"
            color="violet"
        />
        <x-agro.stat-card
            label="Expediciones facturadas ({{ $vintage }})"
            :value="$stats['salidas']"
            icon="arrow-up-tray"
            color="amber"
        />
    </div>

    {{-- Tabs ────────────────────────────────────────────────────────────── --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex flex-wrap gap-1">
                @foreach([
                    'entradas'    => ['label' => 'Libro I — Entradas',      'icon' => 'archive-box-arrow-down'],
                    'elaboracion' => ['label' => 'Libro II — Elaboración',   'icon' => 'beaker'],
                    'existencias' => ['label' => 'Libro III — Existencias',  'icon' => 'squares-2x2'],
                    'salidas'     => ['label' => 'Libro IV — Salidas',       'icon' => 'arrow-up-tray'],
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
        @if($currentTab === 'entradas')
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
                <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wide">A02 — Recepciones de vendimia propia</p>
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
                <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wide">A02/A04/A06 — Uva, mosto y vino a granel comprado</p>
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
        @if($currentTab === 'elaboracion')
            @php $steps = $tabData['steps']; $losses = $tabData['losses']; @endphp

            {{-- Proceso enológico --}}
            <div class="px-4 pt-4 pb-2">
                <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wide mb-3">Operaciones enológicas</p>
            </div>

            @if($steps->isEmpty())
                <x-agro.empty-state
                    icon="beaker"
                    title="Sin operaciones registradas"
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
                    <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wide mb-3">Pérdidas registradas</p>
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
        @if($currentTab === 'existencias')
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
                    title="Sin existencias registradas"
                    description="No hay contenedores con vino activo en este momento."
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
        @if($currentTab === 'salidas')
            @php
                $ventas       = $tabData['ventas'];
                $perdidas     = $tabData['perdidas'];
                $subproductos = $tabData['subproductos'];
                $totals       = $tabData['totals'];
            @endphp

            {{-- Resumen salidas --}}
            <div class="px-4 py-3 grid grid-cols-3 gap-4 border-b border-zinc-100 bg-zinc-50/50">
                <div>
                    <p class="text-xs text-zinc-400">Ventas facturadas</p>
                    <p class="text-lg font-semibold text-zinc-800">{{ $totals['ventas_count'] }} facturas</p>
                    <p class="text-xs text-zinc-500">{{ number_format($totals['ventas_amount'], 2, ',', '.') }} €</p>
                </div>
                <div>
                    <p class="text-xs text-zinc-400">Pérdidas registradas</p>
                    <p class="text-lg font-semibold text-zinc-800">{{ number_format($totals['perdidas_qty'], 2, ',', '.') }} L</p>
                </div>
                <div>
                    <p class="text-xs text-zinc-400">Subproductos gestionados</p>
                    <p class="text-lg font-semibold text-zinc-800">{{ number_format($totals['subproductos_qty'], 2, ',', '.') }}</p>
                </div>
            </div>

            {{-- Ventas --}}
            <div class="px-4 pt-4 pb-2">
                <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wide">Ventas y expediciones</p>
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
                    <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wide">Subproductos (orujo · lías · vinaza)</p>
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
                    <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wide">Pérdidas y mermas</p>
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

    </x-agro.card>

</div>
