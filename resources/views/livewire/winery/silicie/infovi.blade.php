<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="INFOVI — Declaración de Mercados Vitivinícolas"
        description="Cuadros de existencias, producción y ventas para la declaración obligatoria AICA (Real Decreto 739/2015). Año {{ $year }}."
        icon="chart-bar"
    >
        <x-slot:actions>
            <flux:button
                :href="route('winery.silicie.dashboard')"
                variant="ghost"
                icon="arrow-left"
                size="sm"
            >
                Volver a SILICIE
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <flux:callout variant="info" icon="information-circle">
        <flux:callout.heading>Declaración INFOVI</flux:callout.heading>
        <flux:callout.text>
            Los datos de esta pantalla corresponden a las declaraciones obligatorias ante AICA
            (Agencia de Información y Control Alimentarios). La declaración mensual debe realizarse
            antes del día 15 del mes siguiente en <strong>mapa.gob.es/infovi</strong>.
            Los datos se muestran en <strong>hectolitros (HL)</strong>.
        </flux:callout.text>
    </flux:callout>

    {{-- Filtros ─────────────────────────────────────────────────────────── --}}
    <x-agro.filter-bar>
        <x-agro.filter-select wire:model.live="filterYear" label="Año">
            @foreach(range(now()->year, now()->year - 5) as $y)
                <option value="{{ $y }}">{{ $y }}</option>
            @endforeach
        </x-agro.filter-select>

        <x-agro.filter-select wire:model.live="filterMonth" label="Mes (existencias)">
            @foreach(range(1, 12) as $m)
                <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
            @endforeach
        </x-agro.filter-select>
    </x-agro.filter-bar>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- CUADRO 1 — ENTRADAS (uva, mosto, vino a granel)                   --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-agro-100">
                    <flux:icon icon="archive-box-arrow-down" class="size-4 text-agro-600" />
                </div>
                <span class="font-semibold text-zinc-800">Cuadro 1 — Entradas de materia prima ({{ $year }})</span>
            </div>
        </x-slot:header>

        <div class="grid grid-cols-2 gap-4 p-4">
            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-center">
                <p class="text-xs text-zinc-400 mb-1">Uva propia vendimiada</p>
                <p class="text-2xl font-bold text-zinc-800">{{ number_format($entradas['kg_propia'], 0, ',', '.') }}</p>
                <p class="text-xs text-zinc-500 mt-0.5">kg</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-center">
                <p class="text-xs text-zinc-400 mb-1">Uva comprada</p>
                <p class="text-2xl font-bold text-zinc-800">{{ number_format($entradas['kg_comprada'], 0, ',', '.') }}</p>
                <p class="text-xs text-zinc-500 mt-0.5">kg</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-center">
                <p class="text-xs text-zinc-400 mb-1">Mosto comprado</p>
                <p class="text-2xl font-bold text-zinc-800">{{ number_format($entradas['kg_mosto'] / 100, 3, ',', '.') }}</p>
                <p class="text-xs text-zinc-500 mt-0.5">HL (aprox.)</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-center">
                <p class="text-xs text-zinc-400 mb-1">Vino a granel comprado</p>
                <p class="text-2xl font-bold text-zinc-800">{{ number_format($entradas['kg_vino_granel'] / 100, 3, ',', '.') }}</p>
                <p class="text-xs text-zinc-500 mt-0.5">HL (aprox.)</p>
            </div>
        </div>
    </x-agro.card>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- CUADRO 2 — PRODUCCIÓN (A15 vinos obtenidos)                       --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-violet-100">
                    <flux:icon icon="beaker" class="size-4 text-violet-600" />
                </div>
                <span class="font-semibold text-zinc-800">Cuadro 2 — Producción obtenida añada {{ $year }}</span>
                <span class="ml-auto text-sm font-semibold text-violet-600">
                    Total: {{ number_format($produccion['total_hl'], 3, ',', '.') }} HL
                </span>
            </div>
        </x-slot:header>

        <x-agro.data-table :headers="['Tipo de vino', 'Nº vinos', 'Hectolitros (HL)']">
            @foreach($produccion['rows'] as $row)
                @if($row['hl'] > 0 || true)
                    <x-agro.table-row>
                        <x-agro.table-cell>
                            <span class="{{ $row['hl'] > 0 ? 'text-zinc-800 font-medium' : 'text-zinc-300' }}">
                                {{ $row['label'] }}
                            </span>
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            <span class="{{ $row['hl'] > 0 ? 'text-zinc-600' : 'text-zinc-300' }}">
                                {{ $row['wine_count'] ?: '—' }}
                            </span>
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($row['hl'] > 0)
                                <span class="font-semibold text-violet-700">{{ number_format($row['hl'], 3, ',', '.') }}</span>
                                <span class="text-zinc-400 text-xs ml-1">HL</span>
                            @else
                                <span class="text-zinc-300">0,000</span>
                            @endif
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endif
            @endforeach
        </x-agro.data-table>
    </x-agro.card>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- CUADRO 3 — EXISTENCIAS (stock a fin de mes)                       --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-blue-100">
                    <flux:icon icon="squares-2x2" class="size-4 text-blue-600" />
                </div>
                <div>
                    <span class="font-semibold text-zinc-800">
                        Cuadro 3 — Existencias a fin de {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }} de {{ $year }}
                    </span>
                    @if($existencias['snapshot_date'])
                        <span class="ml-2 text-xs text-blue-500">
                            (instantánea del {{ \Carbon\Carbon::parse($existencias['snapshot_date'])->translatedFormat('d M Y') }})
                        </span>
                    @else
                        <span class="ml-2 text-xs text-amber-500">(stock en tiempo real — sin instantánea para este mes)</span>
                    @endif
                </div>
                <span class="ml-auto text-sm font-semibold text-blue-600">
                    Total: {{ number_format($existencias['total_hl'], 3, ',', '.') }} HL
                </span>
            </div>
        </x-slot:header>

        <x-agro.data-table :headers="['Tipo de vino', 'Nº vinos', 'Hectolitros (HL)']">
            @foreach($existencias['rows'] as $row)
                <x-agro.table-row>
                    <x-agro.table-cell>
                        <span class="{{ $row['hl'] > 0 ? 'text-zinc-800 font-medium' : 'text-zinc-300' }}">
                            {{ $row['label'] }}
                        </span>
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
                </x-agro.table-row>
            @endforeach
        </x-agro.data-table>

        @if(!$existencias['snapshot_date'])
            <div class="px-4 pb-4">
                <flux:callout variant="warning" icon="exclamation-triangle">
                    <flux:callout.text>
                        No hay instantánea (snapshot) para {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }} de {{ $year }}.
                        Para declarar correctamente, registra una instantánea desde el panel SILICIE al cierre del mes.
                    </flux:callout.text>
                </flux:callout>
            </div>
        @endif
    </x-agro.card>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- CUADRO 4 — SALIDAS / VENTAS                                       --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-amber-100">
                    <flux:icon icon="arrow-up-tray" class="size-4 text-amber-600" />
                </div>
                <span class="font-semibold text-zinc-800">Cuadro 4 — Salidas / Ventas {{ $year }}</span>
                <span class="ml-auto text-sm font-semibold text-amber-600">
                    {{ $ventas['count'] }} facturas · {{ number_format($ventas['total_amount'], 2, ',', '.') }} €
                </span>
            </div>
        </x-slot:header>

        @if($ventas['count'] === 0)
            <x-agro.empty-state
                icon="arrow-up-tray"
                title="Sin ventas facturadas"
                description="No hay facturas enviadas o cobradas en {{ $year }}."
            />
        @else
            <flux:callout variant="info" icon="information-circle" class="mx-4 mt-4">
                <flux:callout.text class="text-xs">{{ $ventas['note'] }}</flux:callout.text>
            </flux:callout>

            <x-agro.data-table :headers="['Fecha', 'Nº Factura', 'Cliente', 'Importe (€)']">
                @foreach($ventas['invoices'] as $inv)
                    <x-agro.table-row>
                        <x-agro.table-cell>
                            {{ $inv->invoice_date ? \Carbon\Carbon::parse($inv->invoice_date)->translatedFormat('d M Y') : '—' }}
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            <span class="font-mono text-xs">{{ $inv->invoice_number ?? ('FAC-' . $inv->id) }}</span>
                        </x-agro.table-cell>
                        <x-agro.table-cell>{{ $inv->client_name ?? '—' }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            <span class="font-medium">{{ number_format($inv->total_amount, 2, ',', '.') }}</span>
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach
            </x-agro.data-table>
        @endif
    </x-agro.card>

</div>
