<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="{{ __('INFOVI — Declaración de Mercados Vitivinícolas') }}"
        :description="__('Cuadros de existencias, producción y ventas para la declaración obligatoria AICA (Real Decreto 739/2015). Datos en hectolitros (HL).')"
        icon="chart-bar"
    >
        <x-slot:actions>
            <flux:button
                :href="roleRoute('silicie.dashboard')"
                variant="ghost"
                icon="arrow-left"
                size="sm"
            >{{ __('Volver a SILICIE') }}</flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Identificación REOVI + Tipo productor ────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="relative">
            <x-agro.stat-card
                :label="__('Número REOVI')"
                :value="$org?->reovi_number ?: '—'"
                icon="identification"
                color="blue"
                :description="$org?->reovi_number ? 'Registro operadores AICA' : 'No configurado — ve a Configuración › INFOVI'"
            />
            <span class="absolute top-3 right-3">
                <x-agro.help-tip
                    title="{{ __('¿Qué es el REOVI?') }}"
                    text="Número de Registro de Operadores Vitivinícolas Industriales. Lo asigna el MAPA/AICA al registrarte como operador. Es obligatorio para enviar la declaración INFOVI en mapa.gob.es/infovi."
                />
            </span>
        </div>
        <div class="relative">
            <x-agro.stat-card
                :label="__('NIDPB (instalación)')"
                :value="$org?->nidpb ?: '—'"
                icon="building-office"
                color="blue"
                :description="$org?->nidpb ? 'Código de instalación' : 'No configurado — ve a Configuración › INFOVI'"
            />
            <span class="absolute top-3 right-3">
                <x-agro.help-tip
                    title="{{ __('¿Qué es el NIDPB?') }}"
                    text="Número de Identificación de la Destilería/Planta/Bodega. Identifica la instalación física donde se elabora o almacena el vino. Obligatorio en la declaración SILICIE."
                />
            </span>
        </div>
        <div class="relative">
            <x-agro.stat-card
                :label="__('Tipo de productor')"
                :value="$threshold['is_large'] ? 'Gran productor' : 'Pequeño productor'"
                icon="scale"
                :color="$threshold['is_large'] ? 'amber' : 'agro'"
                :description="'Media ' . number_format($threshold['avg_hl'], 1, ',', '.') . ' HL/campaña' . ($threshold['campaigns'] > 0 ? ' (' . $threshold['campaigns'] . ' añadas)' : '')"
            />
            <span class="absolute top-3 right-3">
                <x-agro.help-tip
                    title="{{ __('Gran vs. pequeño productor') }}"
                    text="Se calcula como media de las últimas 4 campañas. Más de 1.000 HL de media = gran productor (declaración mensual, antes del día 19). Hasta 1.000 HL = pequeño productor (declaración semestral: enero y agosto)."
                />
            </span>
        </div>
    </div>

    {{-- Próximos plazos ───────────────────────────────────────────────────── --}}
    @if(!empty($threshold['next_deadlines']))
        <div class="flex flex-wrap gap-3">
            @foreach($threshold['next_deadlines'] as $dl)
                @php
                    $days = now()->diffInDays(\Carbon\Carbon::parse($dl['date']), false);
                    $urgent = $days <= 7;
                @endphp
                <div class="flex items-center gap-2 px-3 py-2 rounded-lg border
                    {{ $urgent ? 'border-red-200 bg-red-50 text-red-700' : 'border-amber-200 bg-amber-50 text-amber-700' }}
                    text-sm">
                    <flux:icon icon="clock" class="size-4 flex-shrink-0" />
                    <span class="font-medium">{{ $dl['label'] }}</span>
                    <span class="text-xs opacity-75">
                        · {{ \Carbon\Carbon::parse($dl['date'])->translatedFormat('d M Y') }}
                        @if($days >= 0) ({{ $days }} días) @else (vencido) @endif
                    </span>
                </div>
            @endforeach
            <a
                href="{{ roleRoute('settings', ['tab' => 'infovi']) }}"
                class="flex items-center gap-1.5 px-3 py-2 rounded-lg border border-zinc-200 bg-white text-zinc-500 text-sm hover:bg-zinc-50 transition"
            >
                <flux:icon icon="cog-6-tooth" class="size-4" />
                Configurar REOVI
            </a>
        </div>
    @endif

    {{-- Callout informativo ──────────────────────────────────────────────── --}}
    <flux:callout variant="info" icon="information-circle">
        <flux:callout.heading>Campaña vitivinícola {{ $campaign }}/{{ $campaign + 1 }}</flux:callout.heading>
        <flux:callout.text>
            Los datos corresponden al período
            <strong>{{ \Carbon\Carbon::parse($campaignStart)->translatedFormat('d M Y') }}</strong>
            al
            <strong>{{ \Carbon\Carbon::parse($campaignEnd)->translatedFormat('d M Y') }}</strong>.
            La declaración {{ $threshold['is_large'] ? 'mensual' : 'semestral' }} debe realizarse en
            <strong>{{ __('mapa.gob.es/infovi') }}</strong>
            antes del día 19 del mes siguiente{{ $threshold['is_large'] ? '' : ' (agosto y diciembre)' }}.
            <x-agro.help-tip
                title="{{ __('Plazos de declaración INFOVI') }}"
                text="{{ $threshold['is_large'] ? 'Gran productor (>1.000 HL): declaración mensual. Antes del día 19 del mes siguiente al declarado. Obligatorio todos los meses aunque no haya movimientos.' : 'Pequeño productor (≤1.000 HL): declaración semestral. Plazo 1.º: antes del 31 de agosto (período agosto–julio del año anterior). Plazo 2.º: antes del 31 de enero (período agosto–diciembre).' }}"
            />
            Los datos se muestran en <strong>{{ __('hectolitros (HL)') }}</strong>.
        </flux:callout.text>
    </flux:callout>

    {{-- Selector de campaña + toggle desglose DO/IGP ──────────────────── --}}
    <x-agro.filter-bar>
        <div class="flex items-center gap-2">
            <label class="text-sm font-medium text-zinc-600">{{ __('Campaña') }}</label>
            <select
                wire:model.live="filterCampaign"
                class="text-sm border border-zinc-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-300"
            >
                @foreach($campaigns as $c)
                    <option value="{{ $c }}">{{ $c }}/{{ $c + 1 }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1"></div>
        <a
            href="{{ roleRoute('silicie.infovi.pdf', ['campaign' => $campaign]) }}"
            target="_blank"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-200 bg-white text-zinc-600 text-sm font-medium hover:bg-zinc-50 transition-colors shadow-sm"
            title="{{ __('Exportar cuadros INFOVI en PDF') }}"
        >
            <flux:icon icon="document-arrow-down" class="size-4 text-red-500" />
            PDF
        </a>
        <flux:button
            wire:click="toggleCategoryBreakdown"
            variant="ghost"
            icon="tag"
            size="sm"
        >
            {{ $showCategoryBreakdown ? 'Ocultar desglose DO/IGP' : 'Mostrar desglose DO/IGP' }}
        </flux:button>
    </x-agro.filter-bar>

    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- CUADRO 1 — ENTRADAS                                                 --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-agro-100">
                    <flux:icon icon="archive-box-arrow-down" class="size-4 text-agro-600" />
                </div>
                <span class="font-semibold text-zinc-800">{{ __('Cuadro 1 — Entradas de materia prima') }}</span>
                <span class="ml-1 text-xs text-zinc-400">Campaña {{ $campaign }}/{{ $campaign + 1 }}</span>
            </div>
        </x-slot:header>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4">
            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-center">
                <p class="text-xs text-zinc-400 mb-1">{{ __('Uva propia vendimiada') }}</p>
                <p class="text-2xl font-bold text-zinc-800">{{ number_format($entradas['kg_propia'], 0, ',', '.') }}</p>
                <p class="text-xs text-zinc-500 mt-0.5">kg</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-center">
                <p class="text-xs text-zinc-400 mb-1">{{ __('Uva comprada') }}</p>
                <p class="text-2xl font-bold text-zinc-800">{{ number_format($entradas['kg_comprada'], 0, ',', '.') }}</p>
                <p class="text-xs text-zinc-500 mt-0.5">kg</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-center">
                <p class="text-xs text-zinc-400 mb-1">{{ __('Mosto comprado') }}</p>
                <p class="text-2xl font-bold text-zinc-800">{{ number_format($entradas['hl_mosto'], 3, ',', '.') }}</p>
                <p class="text-xs text-zinc-500 mt-0.5">HL</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-center">
                <p class="text-xs text-zinc-400 mb-1">{{ __('Vino a granel comprado') }}</p>
                <p class="text-2xl font-bold text-zinc-800">{{ number_format($entradas['hl_vino_granel'], 3, ',', '.') }}</p>
                <p class="text-xs text-zinc-500 mt-0.5">HL</p>
            </div>
        </div>
    </x-agro.card>

    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- CUADRO 2 — PRODUCCIÓN                                               --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-violet-100">
                    <flux:icon icon="beaker" class="size-4 text-violet-600" />
                </div>
                <span class="font-semibold text-zinc-800">Cuadro 2 — Producción obtenida añada {{ $campaign }}</span>
                <span class="text-xs text-zinc-400 ml-1">{{ __('(vino — excluye mosto)') }}</span>
                <span class="ml-auto text-sm font-semibold text-violet-600">
                    Total: {{ number_format($produccion['total_hl'], 3, ',', '.') }} HL
                </span>
            </div>
        </x-slot:header>

        <x-agro.data-table :headers="$showCategoryBreakdown ? ['Tipo de vino', 'Nº vinos', 'DO/DOC', 'IGP', 'Mesa/Sin IG', 'Total HL'] : ['Tipo de vino', 'Nº vinos', 'Hectolitros (HL)']">
            @foreach($produccion['rows'] as $row)
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
                    @if($showCategoryBreakdown)
                        @foreach(['DO', 'IGP', 'VdM'] as $lvl)
                            <x-agro.table-cell>
                                @if(isset($row['categories'][$lvl]) && $row['categories'][$lvl]['hl'] > 0)
                                    <span class="text-violet-700 font-medium text-xs">{{ number_format($row['categories'][$lvl]['hl'], 3, ',', '.') }}</span>
                                @else
                                    <span class="text-zinc-300 text-xs">—</span>
                                @endif
                            </x-agro.table-cell>
                        @endforeach
                    @endif
                    <x-agro.table-cell>
                        @if($row['hl'] > 0)
                            <span class="font-semibold text-violet-700">{{ number_format($row['hl'], 3, ',', '.') }}</span>
                            <span class="text-zinc-400 text-xs ml-1">HL</span>
                        @else
                            <span class="text-zinc-300">0,000</span>
                        @endif
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach
        </x-agro.data-table>
    </x-agro.card>

    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- CUADRO BALANCE — Hoja de conciliación                               --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-teal-100">
                    <flux:icon icon="scale" class="size-4 text-teal-600" />
                </div>
                <div>
                    <span class="font-semibold text-zinc-800">{{ __('Balance de campaña — Conciliación INFOVI') }}</span>
                    <span class="ml-2 text-xs text-zinc-400">{{ __('Apertura + Producción + Compras − Ventas − Pérdidas = Cierre') }}</span>
                </div>
                @if($balanceSheet['closing_snapshot'])
                    <span class="ml-auto text-xs text-teal-600">
                        Cierre real: {{ \Carbon\Carbon::parse($balanceSheet['closing_snapshot'])->translatedFormat('d M Y') }}
                    </span>
                @else
                    <span class="ml-auto text-xs text-amber-500">{{ __('Sin instantánea de cierre') }}</span>
                @endif
            </div>
        </x-slot:header>

        @if(!$balanceSheet['opening_snapshot'] && !$balanceSheet['closing_snapshot'])
            <div class="p-4">
                <flux:callout variant="warning" icon="exclamation-triangle">
                    <flux:callout.text>
                        No hay instantáneas de existencias para esta campaña. Registra instantáneas al inicio y al final
                        de la campaña desde el panel SILICIE para poder verificar el balance de HL.
                    </flux:callout.text>
                </flux:callout>
            </div>
        @else
            @if($balanceSheet['opening_snapshot'])
                <div class="px-4 pt-3 pb-1">
                    <span class="text-xs text-zinc-400">
                        Apertura tomada de la instantánea del {{ \Carbon\Carbon::parse($balanceSheet['opening_snapshot'])->translatedFormat('d M Y') }}
                    </span>
                </div>
            @endif

            {{-- Note: bulk wine purchased (comprado) is not splittable by wine type --}}
            {{-- It appears in the TOTAL row only --}}
            @if($balanceSheet['comprado_total'] > 0)
                <div class="px-4 pt-3 pb-1">
                    <span class="inline-flex items-center gap-1 text-xs text-blue-600 bg-blue-50 border border-blue-100 rounded px-2 py-0.5">
                        <flux:icon icon="information-circle" class="size-3.5" />
                        Vino a granel comprado: <strong>+{{ number_format($balanceSheet['comprado_total'], 3, ',', '.') }} HL</strong>
                        · No asignable por tipo de vino — incluido en el total
                    </span>
                </div>
            @endif

            <x-agro.data-table :headers="['Tipo de vino', 'Apertura', '+Producido', '−Vendido', '−Perdido', '=Cierre calc.', 'Cierre real', 'Δ']">
                @foreach($balanceSheet['rows'] as $row)
                    @php
                        $hasActivity = $row['apertura'] + $row['producido'] + $row['vendido'] + $row['perdido'] > 0;
                        $deltaClass  = $row['delta'] === null ? 'text-zinc-300'
                            : (abs($row['delta']) < 0.1 ? 'text-teal-600 font-medium'
                            : (abs($row['delta']) < 1 ? 'text-amber-600 font-medium'
                            : 'text-red-600 font-bold'));
                    @endphp
                    @if($hasActivity || $row['apertura'] > 0)
                        <x-agro.table-row>
                            <x-agro.table-cell>
                                <span class="text-zinc-800 font-medium text-sm">{{ $row['label'] }}</span>
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                <span class="text-zinc-600 text-xs">{{ number_format($row['apertura'], 3, ',', '.') }}</span>
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                @if($row['producido'] > 0)
                                    <span class="text-violet-600 text-xs font-medium">+{{ number_format($row['producido'], 3, ',', '.') }}</span>
                                @else
                                    <span class="text-zinc-300 text-xs">—</span>
                                @endif
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                @if($row['vendido'] > 0)
                                    <span class="text-amber-600 text-xs font-medium">−{{ number_format($row['vendido'], 3, ',', '.') }}</span>
                                @else
                                    <span class="text-zinc-300 text-xs">—</span>
                                @endif
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                @if($row['perdido'] > 0)
                                    <span class="text-red-500 text-xs font-medium">−{{ number_format($row['perdido'], 3, ',', '.') }}</span>
                                @else
                                    <span class="text-zinc-300 text-xs">—</span>
                                @endif
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                <span class="text-teal-700 font-semibold text-xs">{{ number_format($row['cierre_calc'], 3, ',', '.') }}</span>
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                @if($row['cierre_real'] !== null)
                                    <span class="text-zinc-700 text-xs font-medium">{{ number_format($row['cierre_real'], 3, ',', '.') }}</span>
                                @else
                                    <span class="text-zinc-300 text-xs">—</span>
                                @endif
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                @if($row['delta'] !== null)
                                    <span class="{{ $deltaClass }} text-xs">
                                        {{ $row['delta'] >= 0 ? '+' : '' }}{{ number_format($row['delta'], 3, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-zinc-300 text-xs">—</span>
                                @endif
                            </x-agro.table-cell>
                        </x-agro.table-row>
                    @endif
                @endforeach

                {{-- Totals row --}}
                @php $t = $balanceSheet['totals']; @endphp
                <x-agro.table-row class="bg-zinc-50 font-semibold border-t-2 border-zinc-200">
                    <x-agro.table-cell>
                        <span class="text-zinc-700 font-bold text-xs uppercase tracking-wide">{{ __('Total') }}</span>
                    </x-agro.table-cell>
                    <x-agro.table-cell><span class="text-zinc-700 text-xs font-bold">{{ number_format($t['apertura'], 3, ',', '.') }}</span></x-agro.table-cell>
                    <x-agro.table-cell>
                        <span class="text-violet-700 text-xs font-bold">+{{ number_format($t['producido'], 3, ',', '.') }}</span>
                        @if($balanceSheet['comprado_total'] > 0)
                            <span class="text-blue-600 text-xs ml-1">(+{{ number_format($balanceSheet['comprado_total'], 3, ',', '.') }} granel)</span>
                        @endif
                    </x-agro.table-cell>
                    <x-agro.table-cell><span class="text-amber-700 text-xs font-bold">−{{ number_format($t['vendido'], 3, ',', '.') }}</span></x-agro.table-cell>
                    <x-agro.table-cell><span class="text-red-600 text-xs font-bold">−{{ number_format($t['perdido'], 3, ',', '.') }}</span></x-agro.table-cell>
                    <x-agro.table-cell><span class="text-teal-700 text-xs font-bold">{{ number_format($t['cierre_calc'], 3, ',', '.') }}</span></x-agro.table-cell>
                    <x-agro.table-cell>
                        @if($t['cierre_real'] !== null)
                            <span class="text-zinc-700 text-xs font-bold">{{ number_format($t['cierre_real'], 3, ',', '.') }}</span>
                        @else
                            <span class="text-zinc-300 text-xs">—</span>
                        @endif
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        @if($t['cierre_real'] !== null)
                            @php $totalDelta = round($t['cierre_real'] - $t['cierre_calc'], 3); @endphp
                            <span class="{{ abs($totalDelta) < 0.1 ? 'text-teal-600' : (abs($totalDelta) < 1 ? 'text-amber-600' : 'text-red-600') }} text-xs font-bold">
                                {{ $totalDelta >= 0 ? '+' : '' }}{{ number_format($totalDelta, 3, ',', '.') }}
                            </span>
                        @else
                            <span class="text-zinc-300 text-xs">—</span>
                        @endif
                    </x-agro.table-cell>
                </x-agro.table-row>
            </x-agro.data-table>

            <div class="px-4 pb-3 pt-2">
                <p class="text-xs text-zinc-400">
                    <strong>{{ __('Δ (delta)') }}</strong>: diferencia entre el cierre calculado y el cierre real registrado en la instantánea.
                    Verde (&lt;0,1 HL) = correcto · Ámbar (0,1–1 HL) = revisar · Rojo (&gt;1 HL) = discrepancia significativa.
                </p>
            </div>
        @endif
    </x-agro.card>

    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- CUADRO 3 — EXISTENCIAS                                              --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-blue-100">
                    <flux:icon icon="squares-2x2" class="size-4 text-blue-600" />
                </div>
                <div>
                    <span class="font-semibold text-zinc-800">{{ __('Cuadro 3 — Existencias') }}</span>
                    <span class="text-xs text-zinc-400 ml-1">{{ __('(vino — excluye mosto)') }}</span>
                    @if($existencias['snapshot_date'])
                        <span class="ml-2 text-xs text-blue-500">
                            (instantánea del {{ \Carbon\Carbon::parse($existencias['snapshot_date'])->translatedFormat('d M Y') }})
                        </span>
                    @else
                        <span class="ml-2 text-xs text-amber-500">{{ __('(stock en tiempo real — sin instantánea de campaña)') }}</span>
                    @endif
                </div>
                <span class="ml-auto text-sm font-semibold text-blue-600">
                    Total: {{ number_format($existencias['total_hl'], 3, ',', '.') }} HL
                </span>
            </div>
        </x-slot:header>

        <x-agro.data-table :headers="$showCategoryBreakdown ? ['Tipo de vino', 'Nº vinos', 'DO/DOC', 'IGP', 'Mesa/Sin IG', 'Total HL'] : ['Tipo de vino', 'Nº vinos', 'Hectolitros (HL)']">
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
                    @if($showCategoryBreakdown)
                        @foreach(['DO', 'IGP', 'VdM'] as $lvl)
                            <x-agro.table-cell>
                                @if(isset($row['categories'][$lvl]) && $row['categories'][$lvl]['hl'] > 0)
                                    <span class="text-blue-700 font-medium text-xs">{{ number_format($row['categories'][$lvl]['hl'], 3, ',', '.') }}</span>
                                @else
                                    <span class="text-zinc-300 text-xs">—</span>
                                @endif
                            </x-agro.table-cell>
                        @endforeach
                    @endif
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
                        No hay instantánea de existencias para la campaña {{ $campaign }}/{{ $campaign + 1 }}.
                        Para declarar correctamente, registra una instantánea desde el panel SILICIE al cierre de cada mes.
                    </flux:callout.text>
                </flux:callout>
            </div>
        @endif
    </x-agro.card>

    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- CUADRO 4 — VENTAS / SALIDAS                                         --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-amber-100">
                    <flux:icon icon="arrow-up-tray" class="size-4 text-amber-600" />
                </div>
                <span class="font-semibold text-zinc-800">{{ __('Cuadro 4 — Salidas / Ventas') }}</span>
                <span class="text-xs text-zinc-400 ml-1">{{ __('(vino — excluye mosto)') }}</span>
                <span class="ml-auto flex items-center gap-3 text-sm">
                    <span class="font-semibold text-amber-600">{{ number_format($ventas['total_hl'], 3, ',', '.') }} HL</span>
                    <span class="text-zinc-400">·</span>
                    <span class="text-zinc-500">{{ $ventas['total_invoices'] }} facturas · {{ number_format($ventas['total_amount'], 2, ',', '.') }} €</span>
                </span>
            </div>
        </x-slot:header>

        @if($ventas['total_invoices'] === 0)
            <x-agro.empty-state
                icon="arrow-up-tray"
                title="{{ __('Sin ventas facturadas') }}"
                description="No hay facturas enviadas o cobradas en la campaña {{ $campaign }}/{{ $campaign + 1 }}."
            />
        @else
            @if($ventas['unknown_hl_items'] > 0)
                <div class="px-4 pt-4">
                    <flux:callout variant="warning" icon="exclamation-triangle">
                        <flux:callout.text class="text-xs">
                            {{ $ventas['unknown_hl_items'] }} línea(s) de factura no tienen un lote de vino asociado
                            (liquidaciones de uva u otros conceptos) y no se incluyen en el cálculo de HL.
                            Para declarar estas ventas en INFOVI, asigna los productos a lotes de vino desde
                            <strong>{{ __('Lotes de producto') }}</strong>.
                        </flux:callout.text>
                    </flux:callout>
                </div>
            @endif

            <x-agro.data-table :headers="['Tipo de vino', 'Botellas', 'Hectolitros (HL)']" class="mt-4">
                @foreach($ventas['rows'] as $row)
                    <x-agro.table-row>
                        <x-agro.table-cell>
                            <span class="{{ $row['hl'] > 0 ? 'text-zinc-800 font-medium' : 'text-zinc-300' }}">
                                {{ $row['label'] }}
                            </span>
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            <span class="{{ $row['bottles'] > 0 ? 'text-zinc-600' : 'text-zinc-300' }}">
                                {{ $row['bottles'] > 0 ? number_format($row['bottles'], 0, ',', '.') : '—' }}
                            </span>
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($row['hl'] > 0)
                                <span class="font-semibold text-amber-700">{{ number_format($row['hl'], 3, ',', '.') }}</span>
                                <span class="text-zinc-400 text-xs ml-1">HL</span>
                            @else
                                <span class="text-zinc-300">0,000</span>
                            @endif
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach
            </x-agro.data-table>
        @endif
    </x-agro.card>

    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- MOSTO — Declaración separada INFOVI                                 --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    @if($mosto['has_data'])
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-orange-100">
                        <flux:icon icon="beaker" class="size-4 text-orange-600" />
                    </div>
                    <div>
                        <span class="font-semibold text-zinc-800">{{ __('Mosto — Declaración separada INFOVI') }}</span>
                        <span class="ml-2 text-xs text-zinc-400">{{ __('El mosto se declara como sección independiente en AICA') }}</span>
                    </div>
                </div>
            </x-slot:header>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 p-4">
                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-center">
                    <p class="text-xs text-zinc-400 mb-1">{{ __('Apertura campaña') }}</p>
                    <p class="text-xl font-bold text-zinc-700">{{ number_format($mosto['apertura'], 3, ',', '.') }}</p>
                    <p class="text-xs text-zinc-400 mt-0.5">HL</p>
                    @if($mosto['opening_snapshot'])
                        <p class="text-xs text-zinc-300 mt-0.5">{{ \Carbon\Carbon::parse($mosto['opening_snapshot'])->translatedFormat('d M Y') }}</p>
                    @endif
                </div>
                <div class="rounded-xl border border-violet-100 bg-violet-50 p-4 text-center">
                    <p class="text-xs text-violet-500 mb-1">{{ __('+ Producido') }}</p>
                    <p class="text-xl font-bold text-violet-700">{{ number_format($mosto['producido'], 3, ',', '.') }}</p>
                    <p class="text-xs text-violet-400 mt-0.5">HL</p>
                </div>
                <div class="rounded-xl border border-blue-100 bg-blue-50 p-4 text-center">
                    <p class="text-xs text-blue-500 mb-1">{{ __('+ Comprado') }}</p>
                    <p class="text-xl font-bold text-blue-700">{{ number_format($mosto['comprado'], 3, ',', '.') }}</p>
                    <p class="text-xs text-blue-400 mt-0.5">HL</p>
                </div>
                <div class="rounded-xl border border-amber-100 bg-amber-50 p-4 text-center">
                    <p class="text-xs text-amber-500 mb-1">{{ __('− Vendido') }}</p>
                    <p class="text-xl font-bold text-amber-700">{{ number_format($mosto['vendido'], 3, ',', '.') }}</p>
                    <p class="text-xs text-amber-400 mt-0.5">HL</p>
                </div>
                <div class="rounded-xl border border-red-100 bg-red-50 p-4 text-center">
                    <p class="text-xs text-red-500 mb-1">{{ __('− Perdido') }}</p>
                    <p class="text-xl font-bold text-red-700">{{ number_format($mosto['perdido'], 3, ',', '.') }}</p>
                    <p class="text-xs text-red-400 mt-0.5">HL</p>
                </div>
                <div class="rounded-xl border border-teal-200 bg-teal-50 p-4 text-center">
                    <p class="text-xs text-teal-500 mb-1">{{ __('= Cierre calculado') }}</p>
                    <p class="text-xl font-bold text-teal-700">{{ number_format($mosto['cierre_calc'], 3, ',', '.') }}</p>
                    <p class="text-xs text-teal-400 mt-0.5">HL</p>
                </div>
            </div>

            @if($mosto['cierre_real'] !== null || $mosto['delta'] !== null)
                <div class="px-4 pb-4 flex items-center gap-4 border-t border-zinc-100 pt-3">
                    @if($mosto['cierre_real'] !== null)
                        <span class="text-sm text-zinc-600">
                            Cierre real (instantánea):
                            <strong>{{ number_format($mosto['cierre_real'], 3, ',', '.') }} HL</strong>
                        </span>
                    @endif
                    @if($mosto['delta'] !== null)
                        @php
                            $dc = abs($mosto['delta']) < 0.1 ? 'text-teal-600' : (abs($mosto['delta']) < 1 ? 'text-amber-600' : 'text-red-600');
                        @endphp
                        <span class="text-sm {{ $dc }} font-medium">
                            Δ {{ $mosto['delta'] >= 0 ? '+' : '' }}{{ number_format($mosto['delta'], 3, ',', '.') }} HL
                        </span>
                    @endif
                </div>
            @endif
        </x-agro.card>
    @endif

</div>
