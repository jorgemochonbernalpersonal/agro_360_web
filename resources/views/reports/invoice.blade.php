<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $invoice->invoice_type === 'grape_purchase' ? 'Liquidación de Vendimia' : 'Factura' }} {{ $invoice->invoice_number ?? '' }}</title>
    <style>
        @page {
            margin: 18mm 18mm 22mm 18mm;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            color: #1f2937;
            line-height: 1.6;
        }


        /* ── LOGO / CABECERA SUPERIOR ── */
        .header-top {
            text-align: center;
            margin-bottom: 18pt;
            padding-bottom: 12pt;
            border-bottom: 2.5pt solid #15803d;
        }
        .header-top.grape {
            border-bottom-color: #92400e;
        }
        .header-logo img {
            height: 44pt;
            width: auto;
        }
        .header-brand {
            font-size: 8.5pt;
            color: #6b7280;
            letter-spacing: 0.4pt;
            margin-top: 3pt;
        }

        /* ── TÍTULO DEL DOCUMENTO ── */
        .doc-title-row {
            display: table;
            width: 100%;
            margin-bottom: 18pt;
        }
        .doc-title-left  { display: table-cell; width: 55%; vertical-align: top; }
        .doc-title-right { display: table-cell; width: 45%; vertical-align: top; text-align: right; }

        .doc-title {
            font-size: 20pt;
            font-weight: bold;
            color: #111827;
            letter-spacing: -0.5pt;
        }
        .doc-number {
            font-size: 11pt;
            font-weight: bold;
            color: #15803d;
            margin-top: 3pt;
        }
        .doc-number.grape { color: #92400e; }

        .doc-meta {
            font-size: 8pt;
            color: #6b7280;
            margin-top: 6pt;
            line-height: 1.75;
        }
        .doc-meta strong { color: #374151; }

        /* ── BADGES ── */
        .badge {
            display: inline-block;
            padding: 2pt 8pt;
            border-radius: 20pt;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
            margin-top: 5pt;
        }
        .badge-sent      { background: #dbeafe; color: #1e40af; border: 1pt solid #93c5fd; }
        .badge-paid      { background: #dcfce7; color: #166534; border: 1pt solid #86efac; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; border: 1pt solid #fca5a5; }

        /* ── CABECERA DE PARTES (A: / De:) ── */
        .parties {
            display: table;
            width: 100%;
            margin-bottom: 18pt;
        }
        .party-box {
            display: table-cell;
            width: 48%;
            padding: 12pt 14pt;
            border: 1pt solid #e5e7eb;
            vertical-align: top;
        }
        .party-gap { display: table-cell; width: 4%; }

        .party-role {
            font-size: 7pt;
            font-weight: bold;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.6pt;
            margin-bottom: 4pt;
            padding-bottom: 4pt;
            border-bottom: 0.5pt solid #f3f4f6;
        }
        .party-name   { font-size: 10pt; font-weight: bold; color: #111827; margin-bottom: 2pt; }
        .party-detail { font-size: 8pt; color: #4b5563; line-height: 1.65; }

        /* ── TABLA ÍTEMS ── */
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18pt;
            font-size: 8.5pt;
        }
        table.items thead tr {
            background: #15803d;
            color: white;
        }
        table.items thead tr.grape-head { background: #92400e; }
        table.items thead th {
            padding: 7pt 9pt;
            text-align: left;
            font-weight: bold;
            font-size: 7.5pt;
            text-transform: uppercase;
            letter-spacing: 0.3pt;
        }
        table.items thead th.right  { text-align: right; }
        table.items thead th.center { text-align: center; }

        table.items tbody tr.row-even { background: #f9fafb; }

        table.items tbody td {
            padding: 7pt 9pt;
            border-bottom: 0.5pt solid #e5e7eb;
            vertical-align: top;
        }
        table.items tbody td.right  { text-align: right; white-space: nowrap; }
        table.items tbody td.center { text-align: center; }

        .item-name { font-weight: bold; color: #111827; }
        .item-sub  { font-size: 7.5pt; color: #6b7280; margin-top: 1pt; }
        .wine-tag {
            font-size: 7pt;
            color: #166534;
            background: #f0fdf4;
            border: 0.5pt solid #bbf7d0;
            padding: 0pt 3pt;
            margin-right: 2pt;
        }

        /* ── TOTALES ── */
        .totals-section {
            display: table;
            width: 100%;
            margin-bottom: 18pt;
        }
        .totals-left  { display: table-cell; width: 52%; vertical-align: top; }
        .totals-right { display: table-cell; width: 48%; vertical-align: top; }

        table.totals {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
        }
        table.totals td {
            padding: 5pt 10pt;
        }
        table.totals td:first-child { color: #6b7280; }
        table.totals td:last-child  { text-align: right; font-weight: bold; color: #374151; }
        table.totals tr.sep td      { border-top: 0.5pt solid #e5e7eb; padding-top: 5pt; }
        table.totals tr.discount td:first-child { color: #dc2626; }
        table.totals tr.discount td:last-child  { color: #dc2626; }
        table.totals tr.total-row { background: #15803d; }
        table.totals tr.total-row.grape-total { background: #92400e; }
        table.totals tr.total-row td {
            font-size: 10pt;
            font-weight: bold;
            padding: 6pt 8pt;
            color: white;
        }

        /* ── DESGLOSE IVA POR TIPO ── */
        table.tax-groups {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-top: 8pt;
        }
        table.tax-groups thead tr { background: #f3f4f6; }
        table.tax-groups thead th {
            padding: 4pt 6pt;
            font-weight: bold;
            font-size: 7pt;
            text-transform: uppercase;
            color: #6b7280;
            text-align: right;
        }
        table.tax-groups thead th:first-child { text-align: left; }
        table.tax-groups tbody td {
            padding: 3pt 6pt;
            text-align: right;
            border-bottom: 0.5pt solid #f3f4f6;
            color: #374151;
        }
        table.tax-groups tbody td:first-child { text-align: left; }

        /* ── OBSERVACIONES ── */
        .obs-box {
            background: #fffbeb;
            border-left: 3pt solid #f59e0b;
            padding: 10pt 14pt;
            font-size: 8pt;
            color: #374151;
            margin-bottom: 18pt;
        }
        .obs-box .obs-title { font-weight: bold; color: #b45309; margin-bottom: 3pt; font-size: 8.5pt; }

        /* ── NOTA IRPF ── */
        .irpf-note {
            font-size: 7.5pt;
            color: #6b7280;
            margin-top: 6pt;
            line-height: 1.65;
        }

        /* ── FIRMAS ── */
        .signatures {
            display: table;
            width: 100%;
            margin-top: 20pt;
        }
        .sig-cell {
            display: table-cell;
            width: 45%;
            border: 1pt solid #d1d5db;
            padding: 10pt 14pt;
            text-align: center;
            vertical-align: bottom;
        }
        .sig-gap  { display: table-cell; width: 10%; }
        .sig-line { border-top: 1pt solid #9ca3af; margin: 52pt 8pt 6pt 8pt; }
        .sig-label { font-size: 7.5pt; color: #9ca3af; }
        .sig-name  { font-size: 8.5pt; font-weight: bold; color: #374151; margin-top: 2pt; }

        /* ── PIE ── */
        .footer-legal {
            font-size: 7pt;
            color: #9ca3af;
            text-align: center;
            border-top: 0.5pt solid #e5e7eb;
            padding-top: 7pt;
            margin-top: 14pt;
            line-height: 1.6;
        }

        /* ── SELLO PAGADO ── */
        .paid-stamp {
            position: absolute;
            top: 65pt;
            right: 20mm;
            border: 3pt solid #16a34a;
            color: #16a34a;
            padding: 4pt 14pt;
            font-size: 18pt;
            font-weight: bold;
            letter-spacing: 2pt;
            opacity: 0.25;
            text-transform: uppercase;
            transform: rotate(-20deg);
        }
    </style>
</head>
<body>

@php
    $profile  = $user->profile;
    $isGrape  = $invoice->invoice_type === 'grape_purchase';
    $isPaid   = $invoice->payment_status === 'paid';

    $accentGreen = '#15803d';
    $accentAmber = '#92400e';
    $accent      = $isGrape ? $accentAmber : $accentGreen;

    // Logo base64
    $logoPath = public_path('images/logo.png');
    $logoSrc  = file_exists($logoPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
        : null;

    // Nombres para la sección "A:"
    $billingName = trim(implode(' ', array_filter([
        $invoice->billing_first_name ?? '',
        $invoice->billing_last_name  ?? '',
    ])));
    if (!$billingName) {
        $billingName = $invoice->billing_company_name ?? '—';
    }

    // Emisor (De:)
    $issuerCity = trim(implode(' ', array_filter([
        $profile->postal_code ?? '',
        $profile->city        ?? '',
    ])));

    // Título y número
    $docTitle  = $isGrape ? 'LIQUIDACIÓN DE VENDIMIA' : 'FACTURA';
    $docNumber = $invoice->invoice_number ?? '—';

    // Agrupación por tasa impositiva
    $taxGroups = $invoice->items
        ->groupBy(fn($i) => number_format((float) $i->tax_rate, 2))
        ->map(fn($group, $rate) => [
            'rate'   => (float) $rate,
            'base'   => $group->sum(fn($i) => (float) $i->tax_base),
            'amount' => $group->sum(fn($i) => (float) $i->tax_amount),
        ])
        ->sortBy('rate')
        ->values();
    $multipleRates = $taxGroups->count() > 1;

    // Wine lots
    $hasWineLots = $invoice->items->filter(fn($i) => $i->wineLot)->isNotEmpty();

    // Forma de pago
    $paymentLabels = [
        'transfer' => 'Transferencia bancaria',
        'cash'     => 'Efectivo',
        'card'     => 'Tarjeta',
        'check'    => 'Cheque',
        'other'    => 'Otro',
    ];
    $paymentLabel = $paymentLabels[$invoice->payment_type] ?? ($invoice->payment_type ? ucfirst($invoice->payment_type) : null);

    // Estado
    $statusLabels = [
        'sent'      => 'Enviada',
        'paid'      => 'Pagada',
        'cancelled' => 'Cancelada',
    ];
    $statusClasses = [
        'sent'      => 'badge-sent',
        'paid'      => 'badge-paid',
        'cancelled' => 'badge-cancelled',
    ];
@endphp

{{-- SELLO PAGADO --}}
@if($isPaid)
    <div class="paid-stamp">PAGADA</div>
@endif

{{-- ═══ LOGO CENTRADO ═══ --}}
<div class="header-top {{ $isGrape ? 'grape' : '' }}">
    @if($logoSrc)
        <div class="header-logo">
            <img src="{{ $logoSrc }}" alt="Agro360"/>
        </div>
    @endif
    <div class="header-brand">Agro360 &middot; Plataforma de gestión vitivinícola</div>
</div>

{{-- ═══ TÍTULO + DATOS DEL DOCUMENTO ═══ --}}
<div class="doc-title-row">
    <div class="doc-title-left">
        {{-- Emisor resumido en la zona izquierda del encabezado --}}
        <div style="font-size:7.5pt; font-weight:bold; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5pt; margin-bottom:3pt;">Emitido por</div>
        <div style="font-size:11pt; font-weight:bold; color:#111827;">{{ $user->name }}</div>
        <div style="font-size:8pt; color:#4b5563; margin-top:3pt; line-height:1.65;">
            @if($profile?->address){{ $profile->address }}<br>@endif
            @if($issuerCity){{ $issuerCity }}@if($profile?->province?->name) &mdash; {{ $profile->province->name }}@endif<br>@endif
            @if($profile?->phone)Tel.: {{ $profile->phone }}<br>@endif
            {{ $user->email }}
        </div>
    </div>
    <div class="doc-title-right">
        <div class="doc-title">{{ $docTitle }}</div>
        <div class="doc-number {{ $isGrape ? 'grape' : '' }}">{{ $docNumber }}</div>
        <div class="doc-meta">
            <strong>Fecha:</strong> {{ $invoice->invoice_date?->format('d/m/Y') ?? now()->format('d/m/Y') }}<br>
            @if($invoice->delivery_note_date)
                <strong>F. albarán:</strong> {{ $invoice->delivery_note_date->format('d/m/Y') }}<br>
            @endif
            @if($invoice->delivery_note_code)
                <strong>Ref. albarán:</strong> {{ $invoice->delivery_note_code }}<br>
            @endif
            @if($paymentLabel)
                <strong>Forma de pago:</strong> {{ $paymentLabel }}<br>
            @endif
        </div>
        @if(isset($statusLabels[$invoice->status]))
        <div class="badge {{ $statusClasses[$invoice->status] }}">
            {{ $statusLabels[$invoice->status] }}
        </div>
        @endif
    </div>
</div>

{{-- ═══ PARTES: A: / De: ═══ --}}
<div class="parties">
    {{-- A: (Destinatario / Viticultor) --}}
    <div class="party-box">
        <div class="party-role">A:</div>
        @if($isGrape && $invoice->viticulturist)
            <div class="party-name">{{ $invoice->viticulturist->name }}</div>
            <div class="party-detail">
                {{ $invoice->viticulturist->email }}<br>
                @if($invoice->billing_company_document)NIF: {{ $invoice->billing_company_document }}<br>@endif
                @if($invoice->billing_address){{ $invoice->billing_address }}<br>@endif
                @if($invoice->billing_postal_code || $invoice->billing_city)
                    {{ trim(implode(' ', array_filter([$invoice->billing_postal_code ?? '', $invoice->billing_city ?? '']))) }}<br>
                @endif
            </div>
        @else
            <div class="party-name">{{ $billingName }}</div>
            <div class="party-detail">
                @if($invoice->billing_company_name && $invoice->billing_company_name !== $billingName)
                    {{ $invoice->billing_company_name }}<br>
                @endif
                @if($invoice->billing_company_document)NIF/CIF: {{ $invoice->billing_company_document }}<br>@endif
                @if($invoice->billing_email){{ $invoice->billing_email }}<br>@endif
                @if($invoice->billing_phone)Tel.: {{ $invoice->billing_phone }}<br>@endif
                @if($invoice->billing_address){{ $invoice->billing_address }}<br>@endif
                @if($invoice->billing_postal_code || $invoice->billing_city)
                    {{ trim(implode(' ', array_filter([$invoice->billing_postal_code ?? '', $invoice->billing_city ?? '']))) }}
                    @if($invoice->billing_state) &mdash; {{ $invoice->billing_state }}@endif
                    <br>
                @endif
            </div>
        @endif
    </div>

    <div class="party-gap"></div>

    {{-- De: (Emisor / Bodega) --}}
    <div class="party-box">
        <div class="party-role">De:</div>
        <div class="party-name">{{ $user->name }}</div>
        <div class="party-detail">
            {{ $user->email }}<br>
            @if($profile?->phone)Tel.: {{ $profile->phone }}<br>@endif
            @if($profile?->address){{ $profile->address }}<br>@endif
            @if($issuerCity){{ $issuerCity }}@if($profile?->province?->name) &mdash; {{ $profile->province->name }}@endif<br>@endif
        </div>
    </div>
</div>

{{-- ═══ TABLA DE ÍTEMS ═══ --}}
@if($isGrape)
    {{-- LIQUIDACIÓN DE VENDIMIA: columnas uva --}}
    <table class="items">
        <thead>
            <tr class="grape-head">
                <th style="width:35%">Descripción / Recepción</th>
                <th class="right" style="width:13%">Kg</th>
                <th class="right" style="width:12%">€/kg</th>
                <th class="right" style="width:13%">Base</th>
                <th class="right" style="width:12%">IRPF %</th>
                <th class="right" style="width:13%">Retención</th>
                <th class="right" style="width:12%">A pagar</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $idx => $item)
                @php
                    $base   = (float) $item->tax_base;
                    $irpf   = (float) $item->tax_amount;
                    $aPagar = $base - $irpf;
                @endphp
                <tr class="{{ $idx % 2 !== 0 ? 'row-even' : '' }}">
                    <td>
                        <div class="item-name">{{ $item->name }}</div>
                        @if($item->description && $item->description !== $item->name)
                            <div class="item-sub">{{ $item->description }}</div>
                        @endif
                        @if($item->sku)
                            <div class="item-sub">Ref: {{ $item->sku }}</div>
                        @endif
                    </td>
                    <td class="right">{{ number_format((float) $item->quantity, 3, ',', '.') }}</td>
                    <td class="right">{{ number_format((float) $item->unit_price, 4, ',', '.') }} €</td>
                    <td class="right">{{ number_format($base, 2, ',', '.') }} €</td>
                    <td class="right">{{ number_format((float) $item->tax_rate, 2, ',', '.') }}%</td>
                    <td class="right" style="color:#92400e;">{{ number_format($irpf, 2, ',', '.') }} €</td>
                    <td class="right" style="font-weight:bold;">{{ number_format($aPagar, 2, ',', '.') }} €</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center; color:#9ca3af; padding:18pt;">Sin líneas</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@else
    {{-- FACTURA VENTA DE VINO --}}
    <table class="items">
        <thead>
            <tr>
                <th style="width:{{ $hasWineLots ? '26%' : '38%' }}">Producto</th>
                @if($hasWineLots)
                    <th style="width:16%">SKU / Lote</th>
                @else
                    <th style="width:10%">SKU</th>
                @endif
                <th class="right" style="width:7%">Cant.</th>
                <th class="right" style="width:11%">Precio unit.</th>
                <th class="right" style="width:7%">Dto.%</th>
                <th class="right" style="width:8%">IVA%</th>
                <th class="right" style="width:12%">Base imp.</th>
                <th class="right" style="width:10%">Cuota IVA</th>
                <th class="right" style="width:9%">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $idx => $item)
                @php
                    $lot = $item->wineLot;
                @endphp
                <tr class="{{ $idx % 2 !== 0 ? 'row-even' : '' }}">
                    <td>
                        <div class="item-name">{{ $item->name }}</div>
                        @if($item->description && $item->description !== $item->name)
                            <div class="item-sub">{{ $item->description }}</div>
                        @endif
                        @if($lot)
                            <div class="item-sub">
                                @if($lot->vintage)<span class="wine-tag">{{ $lot->vintage }}</span>@endif
                                @if($lot->wine_type)<span class="wine-tag">{{ ucfirst($lot->wine_type) }}</span>@endif
                                @if($lot->alcohol)<span class="wine-tag">{{ $lot->alcohol }}%</span>@endif
                                @if($lot->aging_type)<span class="wine-tag">{{ ucfirst($lot->aging_type) }}</span>@endif
                                @if($lot->bottle_format)<span class="wine-tag">{{ number_format((float)$lot->bottle_format * 1000) }} ml</span>@endif
                            </div>
                        @endif
                    </td>
                    @if($hasWineLots)
                        <td>
                            @if($item->sku)<div class="item-name" style="font-size:8pt;">{{ $item->sku }}</div>@endif
                            @if($lot?->sku && $lot->sku !== $item->sku)
                                <div class="item-sub">Lote: {{ $lot->sku }}</div>
                            @elseif($lot?->name)
                                <div class="item-sub">{{ $lot->name }}</div>
                            @endif
                        </td>
                    @else
                        <td>
                            @if($item->sku)<span style="font-size:8pt; color:#6b7280;">{{ $item->sku }}</span>@endif
                        </td>
                    @endif
                    <td class="right">{{ number_format((float) $item->quantity, 2, ',', '.') }}</td>
                    <td class="right">{{ number_format((float) $item->unit_price, 2, ',', '.') }} €</td>
                    <td class="right">
                        @if((float) $item->discount_percentage > 0)
                            {{ number_format((float) $item->discount_percentage, 0) }}%
                        @else
                            &mdash;
                        @endif
                    </td>
                    <td class="right">{{ number_format((float) $item->tax_rate, 0) }}%</td>
                    <td class="right">{{ number_format((float) $item->tax_base, 2, ',', '.') }} €</td>
                    <td class="right">{{ number_format((float) $item->tax_amount, 2, ',', '.') }} €</td>
                    <td class="right" style="font-weight:bold;">{{ number_format((float) $item->total, 2, ',', '.') }} €</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $hasWineLots ? 9 : 9 }}" style="text-align:center; color:#9ca3af; padding:18pt;">Sin líneas</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endif

{{-- ═══ TOTALES ═══ --}}
<div class="totals-section">
    <div class="totals-left">

        @if($isGrape)
            <div class="irpf-note">
                <strong style="color:#92400e;">Nota:</strong> El IRPF (retención) se descuenta de la base imponible.<br>
                El importe «A pagar» es el neto a abonar al viticultor/proveedor.
            </div>
        @else
            @if($multipleRates)
                <div style="font-size:7.5pt; font-weight:bold; color:#6b7280; text-transform:uppercase; margin-bottom:4pt;">Desglose por tipo de IVA</div>
                <table class="tax-groups">
                    <thead>
                        <tr>
                            <th>Tipo IVA</th>
                            <th>Base imp.</th>
                            <th>Cuota IVA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($taxGroups as $group)
                            <tr>
                                <td>{{ number_format($group['rate'], 0) }}%</td>
                                <td>{{ number_format($group['base'], 2, ',', '.') }} €</td>
                                <td>{{ number_format($group['amount'], 2, ',', '.') }} €</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @endif

    </div>

    <div class="totals-right">
        <table class="totals">
            @if($isGrape)
                <tr>
                    <td>Base imponible</td>
                    <td>{{ number_format((float) $invoice->tax_base, 2, ',', '.') }} €</td>
                </tr>
                <tr>
                    <td>Retención IRPF</td>
                    <td style="color:#92400e;">&minus; {{ number_format((float) $invoice->tax_amount, 2, ',', '.') }} €</td>
                </tr>
                <tr class="total-row grape-total">
                    <td>A PAGAR</td>
                    <td>{{ number_format((float) $invoice->total_amount, 2, ',', '.') }} €</td>
                </tr>
            @else
                <tr>
                    <td>Subtotal</td>
                    <td>{{ number_format((float) $invoice->subtotal, 2, ',', '.') }} €</td>
                </tr>
                @if((float) ($invoice->discount_amount ?? 0) > 0)
                    <tr class="discount">
                        <td>Descuento</td>
                        <td>&minus; {{ number_format((float) $invoice->discount_amount, 2, ',', '.') }} €</td>
                    </tr>
                @endif
                <tr class="sep">
                    <td>Base imponible</td>
                    <td>{{ number_format((float) $invoice->tax_base, 2, ',', '.') }} €</td>
                </tr>
                <tr>
                    <td>
                        IVA
                        @if(!$multipleRates && $taxGroups->count() === 1)
                            ({{ number_format($taxGroups->first()['rate'], 0) }}%)
                        @endif
                    </td>
                    <td>{{ number_format((float) $invoice->tax_amount, 2, ',', '.') }} €</td>
                </tr>
                <tr class="total-row">
                    <td>TOTAL</td>
                    <td>{{ number_format((float) $invoice->total_amount, 2, ',', '.') }} €</td>
                </tr>
            @endif
        </table>
    </div>
</div>

{{-- ═══ OBSERVACIONES ═══ --}}
@if($invoice->observations || $invoice->observations_invoice)
    <div class="obs-box">
        <div class="obs-title">Observaciones</div>
        @if($invoice->observations)
            <div>{{ $invoice->observations }}</div>
        @endif
        @if($invoice->observations_invoice && $invoice->observations_invoice !== $invoice->observations)
            <div style="margin-top:3pt;">{{ $invoice->observations_invoice }}</div>
        @endif
    </div>
@endif

{{-- ═══ FIRMAS ═══ --}}
<div class="signatures">
    <div class="sig-cell">
        <div class="sig-line"></div>
        <div class="sig-label">Firma y sello del {{ $isGrape ? 'viticultor / proveedor' : 'cliente' }}</div>
        <div class="sig-name">
            {{ $isGrape ? ($invoice->viticulturist?->name ?? $billingName) : $billingName }}
        </div>
        @if(!$isGrape)
            <div style="font-size:7pt; color:#9ca3af; margin-top:2pt;">Fecha de recepción: _______________</div>
        @endif
    </div>
    <div class="sig-gap"></div>
    <div class="sig-cell">
        <div class="sig-line"></div>
        <div class="sig-label">Firma y sello de la bodega</div>
        <div class="sig-name">{{ $user->name }}</div>
    </div>
</div>

{{-- ═══ PIE LEGAL ═══ --}}
<div class="footer-legal">
    Documento generado el {{ now()->format('d/m/Y \a \l\a\s H:i') }}
    &middot; Agro360 &middot; Plataforma de gestión vitivinícola
    @if($invoice->invoice_number)
        &middot; {{ $isGrape ? 'Liquidación' : 'Factura' }} {{ $invoice->invoice_number }}
    @endif
    &middot; Página <span class="pagenum"></span>
</div>

</body>
</html>
