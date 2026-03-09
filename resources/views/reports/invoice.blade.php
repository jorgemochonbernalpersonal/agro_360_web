<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $invoice->invoice_type === 'grape_purchase' ? 'Liquidación' : 'Factura' }} {{ $invoice->invoice_number ?? 'Borrador' }}</title>
    <style>
        @page {
            margin: 15mm 15mm 20mm 15mm;
            @bottom-center {
                content: "Página " counter(page) " de " counter(pages);
                font-size: 7.5pt;
                color: #9ca3af;
            }
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9pt;
            color: #1f2937;
            line-height: 1.5;
        }

        /* ── CABECERA ── */
        .header-top {
            text-align: center;
            margin-bottom: 14pt;
            padding-bottom: 12pt;
            border-bottom: 2.5pt solid #15803d;
        }
        .header-logo {
            margin-bottom: 4pt;
        }
        .header-logo img {
            height: 42pt;
            width: auto;
        }
        .header-brand {
            font-size: 9pt;
            color: #6b7280;
            letter-spacing: 0.5pt;
        }

        /* ── DOC INFO ── */
        .doc-row {
            display: table;
            width: 100%;
            margin-bottom: 14pt;
        }
        .doc-row-left  { display: table-cell; width: 55%; vertical-align: top; }
        .doc-row-right { display: table-cell; width: 45%; vertical-align: top; text-align: right; }

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
            margin-top: 2pt;
        }
        .doc-number.grape { color: #b45309; }
        .doc-meta {
            font-size: 8pt;
            color: #6b7280;
            margin-top: 5pt;
            line-height: 1.7;
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
        .badge-draft     { background: #fef9c3; color: #854d0e; border: 1pt solid #fde047; }
        .badge-sent      { background: #dbeafe; color: #1e40af; border: 1pt solid #93c5fd; }
        .badge-paid      { background: #dcfce7; color: #166534; border: 1pt solid #86efac; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; border: 1pt solid #fca5a5; }

        /* ── PARTIES ── */
        .parties {
            display: table;
            width: 100%;
            margin-bottom: 14pt;
        }
        .party-box {
            display: table-cell;
            width: 48%;
            padding: 9pt 11pt;
            border: 1pt solid #e5e7eb;
            border-radius: 4pt;
            vertical-align: top;
        }
        .party-gap { display: table-cell; width: 4%; }
        .party-label {
            font-size: 7pt;
            font-weight: bold;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.6pt;
            margin-bottom: 5pt;
            border-bottom: 0.5pt solid #f3f4f6;
            padding-bottom: 4pt;
        }
        .party-name   { font-size: 10pt; font-weight: bold; color: #111827; margin-bottom: 2pt; }
        .party-detail { font-size: 8pt; color: #4b5563; line-height: 1.65; }

        /* ── TABLA DE ÍTEMS ── */
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12pt;
            font-size: 8.5pt;
        }
        table.items thead tr {
            background: #15803d;
            color: white;
        }
        table.items thead tr.grape-head { background: #92400e; }
        table.items thead th {
            padding: 6pt 7pt;
            text-align: left;
            font-weight: bold;
            font-size: 7.5pt;
            text-transform: uppercase;
            letter-spacing: 0.3pt;
        }
        table.items thead th.right  { text-align: right; }
        table.items thead th.center { text-align: center; }

        table.items tbody tr:nth-child(even) { background: #f9fafb; }

        table.items tbody td {
            padding: 6pt 7pt;
            border-bottom: 0.5pt solid #e5e7eb;
            vertical-align: top;
        }
        table.items tbody td.right  { text-align: right; white-space: nowrap; }
        table.items tbody td.center { text-align: center; }

        .item-name { font-weight: bold; color: #111827; }
        .item-sub  { font-size: 7.5pt; color: #6b7280; margin-top: 1pt; }
        .item-wine {
            font-size: 7.5pt;
            color: #374151;
            margin-top: 2pt;
            line-height: 1.5;
        }
        .wine-tag {
            display: inline-block;
            background: #f0fdf4;
            border: 0.5pt solid #bbf7d0;
            border-radius: 2pt;
            padding: 0pt 3pt;
            font-size: 7pt;
            color: #166534;
            margin-right: 2pt;
            margin-top: 1pt;
        }

        /* ── TOTALES ── */
        .totals-section {
            display: table;
            width: 100%;
            margin-bottom: 12pt;
        }
        .totals-left  { display: table-cell; width: 52%; vertical-align: top; }
        .totals-right { display: table-cell; width: 48%; vertical-align: top; }

        table.totals {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
        }
        table.totals td {
            padding: 3.5pt 8pt;
        }
        table.totals td:first-child { color: #6b7280; }
        table.totals td:last-child  { text-align: right; font-weight: bold; color: #374151; }

        table.totals tr.sep td    { border-top: 0.5pt solid #e5e7eb; padding-top: 5pt; }
        table.totals tr.discount  td:first-child { color: #dc2626; }
        table.totals tr.discount  td:last-child  { color: #dc2626; }
        table.totals tr.total-row {
            background: #15803d;
            color: white;
        }
        table.totals tr.total-row.grape-total { background: #92400e; }
        table.totals tr.total-row td {
            font-size: 10pt;
            font-weight: bold;
            padding: 6pt 8pt;
            color: white !important;
        }

        /* Tax group subtable */
        table.tax-groups {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 6pt;
        }
        table.tax-groups thead tr { background: #f3f4f6; }
        table.tax-groups thead th {
            padding: 4pt 6pt;
            text-align: right;
            font-weight: bold;
            font-size: 7pt;
            text-transform: uppercase;
            color: #6b7280;
        }
        table.tax-groups thead th:first-child { text-align: left; }
        table.tax-groups tbody td {
            padding: 3pt 6pt;
            text-align: right;
            border-bottom: 0.5pt solid #f3f4f6;
        }
        table.tax-groups tbody td:first-child { text-align: left; color: #374151; }

        /* ── DATOS BANCARIOS ── */
        .bank-box {
            background: #f0fdf4;
            border: 1pt solid #bbf7d0;
            border-radius: 4pt;
            padding: 8pt 11pt;
            font-size: 8pt;
            line-height: 1.7;
            margin-bottom: 8pt;
        }
        .bank-box .bank-title {
            font-weight: bold;
            color: #15803d;
            font-size: 8.5pt;
            margin-bottom: 4pt;
        }
        .bank-box .bank-row { color: #374151; }
        .bank-box .bank-row strong { color: #111827; }

        /* ── OBSERVACIONES ── */
        .obs-box {
            background: #fffbeb;
            border-left: 3pt solid #f59e0b;
            padding: 7pt 11pt;
            font-size: 8pt;
            color: #374151;
            margin-bottom: 12pt;
            border-radius: 0 4pt 4pt 0;
        }
        .obs-box .obs-title { font-weight: bold; color: #b45309; margin-bottom: 3pt; font-size: 8.5pt; }

        /* ── SELLO PAGADO ── */
        .paid-stamp {
            position: absolute;
            top: 75pt;
            right: 15mm;
            transform: rotate(-20deg);
            border: 3pt solid #16a34a;
            color: #16a34a;
            padding: 4pt 14pt;
            font-size: 18pt;
            font-weight: bold;
            letter-spacing: 2pt;
            opacity: 0.3;
            text-transform: uppercase;
        }

        /* ── FIRMAS ── */
        .signatures {
            display: table;
            width: 100%;
            margin-top: 12pt;
        }
        .sig-cell {
            display: table-cell;
            width: 45%;
            border: 1pt solid #d1d5db;
            border-radius: 4pt;
            padding: 8pt 12pt;
            text-align: center;
            vertical-align: bottom;
        }
        .sig-gap  { display: table-cell; width: 10%; }
        .sig-line { border-top: 1pt solid #9ca3af; margin: 44pt 8pt 5pt 8pt; }
        .sig-label { font-size: 7.5pt; color: #9ca3af; }
        .sig-name  { font-size: 8.5pt; font-weight: bold; color: #374151; margin-top: 2pt; }

        /* ── PIE ── */
        .footer-legal {
            font-size: 7pt;
            color: #9ca3af;
            text-align: center;
            border-top: 0.5pt solid #e5e7eb;
            padding-top: 7pt;
            margin-top: 12pt;
            line-height: 1.6;
        }
    </style>
</head>
<body>

@php
    $profile     = $user->profile;
    $isGrape     = $invoice->invoice_type === 'grape_purchase';
    $accentColor = $isGrape ? '#92400e' : '#15803d';

    $statusLabels = [
        'draft'      => 'Borrador',
        'sent'       => 'Enviada',
        'paid'       => 'Pagada',
        'cancelled'  => 'Cancelada',
        'corrective' => 'Rectificativa',
    ];
    $statusClasses = [
        'draft'      => 'badge-draft',
        'sent'       => 'badge-sent',
        'paid'       => 'badge-paid',
        'cancelled'  => 'badge-cancelled',
    ];

    // Logo base64
    $logoPath = public_path('images/logo.png');
    $logoSrc  = file_exists($logoPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
        : null;

    // Billing name
    $billingName = implode(' ', array_filter([
        $invoice->billing_first_name,
        $invoice->billing_last_name,
    ])) ?: $invoice->client?->full_name ?? '—';

    // Detect if items have wine lots (to show wine columns)
    $hasWineLots = $invoice->items->filter(fn($i) => $i->wineLot)->isNotEmpty();

    // Group items by tax rate for summary
    $taxGroups = $invoice->items
        ->groupBy(fn($i) => (float) $i->tax_rate)
        ->map(fn($group, $rate) => [
            'rate'   => $rate,
            'base'   => $group->sum(fn($i) => (float) $i->tax_base),
            'amount' => $group->sum(fn($i) => (float) $i->tax_amount),
        ])
        ->sortBy('rate')
        ->values();

    $multipleRates = $taxGroups->count() > 1;

    // Volume total for wine lots (units × bottle format in liters)
    $totalVolumeLiters = null;
    if ($hasWineLots) {
        $vol = 0;
        foreach ($invoice->items as $item) {
            $lot = $item->wineLot;
            if ($lot) {
                // bottle_format in liters (e.g. 0.75)
                $fmt = (float) ($lot->bottle_format ?? 0.75);
                $vol += (float) $item->quantity * $fmt;
            }
        }
        $totalVolumeLiters = $vol;
    }

    // Payment method label
    $paymentLabels = [
        'transfer' => 'Transferencia bancaria',
        'cash'     => 'Efectivo',
        'card'     => 'Tarjeta',
        'check'    => 'Cheque',
        'other'    => 'Otro',
    ];
    $paymentLabel = $paymentLabels[$invoice->payment_type] ?? ($invoice->payment_type ? ucfirst($invoice->payment_type) : null);

    $docTitle  = $isGrape ? 'LIQUIDACIÓN' : 'FACTURA';
    $docNumber = $invoice->invoice_number ?? '—';
@endphp

{{-- SELLO PAGADO --}}
@if($invoice->payment_status === 'paid')
    <div class="paid-stamp">PAGADA</div>
@endif

{{-- ═══ CABECERA CON LOGO ═══ --}}
<div class="header-top">
    @if($logoSrc)
        <div class="header-logo">
            <img src="{{ $logoSrc }}" alt="Agro365" />
        </div>
    @endif
    <div class="header-brand">Agro365 · Plataforma de gestión vitivinícola</div>
</div>

{{-- ═══ DOC INFO ═══ --}}
<div class="doc-row">
    <div class="doc-row-left">
        {{-- Datos del emisor --}}
        <div style="font-size:8pt; font-weight:bold; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5pt; margin-bottom:4pt;">Emisor</div>
        <div style="font-size:11pt; font-weight:bold; color:#111827;">{{ $user->name }}</div>
        <div style="font-size:8pt; color:#4b5563; margin-top:3pt; line-height:1.65;">
            @if($profile?->address){{ $profile->address }}<br>@endif
            @if($profile?->postal_code || $profile?->city)
                {{ implode(' ', array_filter([$profile->postal_code ?? '', $profile->city ?? ''])) }}
                @if($profile?->province) — {{ $profile->province->name }}@endif
                <br>
            @endif
            @if($profile?->phone)Tel.: {{ $profile->phone }}<br>@endif
            @if($profile?->nif)NIF/CIF: {{ $profile->nif }}<br>@endif
            {{ $user->email }}
        </div>
    </div>
    <div class="doc-row-right">
        <div class="doc-title">{{ $docTitle }}</div>
        <div class="doc-number {{ $isGrape ? 'grape' : '' }}">{{ $docNumber }}</div>
        <div class="doc-meta">
            <strong>Fecha:</strong> {{ $invoice->invoice_date?->format('d/m/Y') ?? now()->format('d/m/Y') }}<br>
            @if($invoice->delivery_note_code)
                <strong>Ref. albarán:</strong> {{ $invoice->delivery_note_code }}<br>
            @endif
            @if($invoice->order_date)
                <strong>F. pedido:</strong> {{ $invoice->order_date->format('d/m/Y') }}<br>
            @endif
            @if($invoice->payment_date)
                <strong>F. pago:</strong> {{ $invoice->payment_date->format('d/m/Y') }}<br>
            @endif
            @if($paymentLabel)
                <strong>Forma de pago:</strong> {{ $paymentLabel }}<br>
            @endif
        </div>
        <div class="badge {{ $statusClasses[$invoice->status] ?? 'badge-draft' }}">
            {{ $statusLabels[$invoice->status] ?? $invoice->status }}
        </div>
    </div>
</div>

{{-- ═══ PARTES: FACTURAR A / EMISOR ═══ --}}
<div class="parties">
    <div class="party-box">
        <div class="party-label">
            {{ $isGrape ? 'Viticultor / Proveedor' : 'Facturar a' }}
        </div>
        @if($isGrape && $invoice->viticulturist)
            <div class="party-name">{{ $invoice->viticulturist->name }}</div>
            <div class="party-detail">
                {{ $invoice->viticulturist->email }}<br>
                @if($invoice->billing_company_document)NIF: {{ $invoice->billing_company_document }}<br>@endif
                @if($invoice->billing_address){{ $invoice->billing_address }}<br>@endif
                @if($invoice->billing_postal_code || $invoice->billing_city)
                    {{ implode(' ', array_filter([$invoice->billing_postal_code ?? '', $invoice->billing_city ?? ''])) }}<br>
                @endif
            </div>
        @else
            <div class="party-name">{{ $billingName }}</div>
            <div class="party-detail">
                @if($invoice->billing_company_name){{ $invoice->billing_company_name }}<br>@endif
                @if($invoice->billing_company_document)NIF/CIF: {{ $invoice->billing_company_document }}<br>@endif
                @if($invoice->billing_address){{ $invoice->billing_address }}<br>@endif
                @if($invoice->billing_postal_code || $invoice->billing_city)
                    {{ implode(' ', array_filter([$invoice->billing_postal_code ?? '', $invoice->billing_city ?? ''])) }}
                    @if($invoice->billing_state) — {{ $invoice->billing_state }}@endif
                    <br>
                @endif
                @if($invoice->billing_email){{ $invoice->billing_email }}<br>@endif
                @if($invoice->billing_phone){{ $invoice->billing_phone }}@endif
            </div>
        @endif
    </div>
    <div class="party-gap"></div>
    <div class="party-box">
        <div class="party-label">{{ $isGrape ? 'Bodega / Pagador' : 'Datos del emisor' }}</div>
        <div class="party-name">{{ $user->name }}</div>
        <div class="party-detail">
            {{ $user->email }}<br>
            @if($profile?->phone)Tel.: {{ $profile->phone }}<br>@endif
            @if($profile?->nif)NIF/CIF: {{ $profile->nif }}<br>@endif
            @if($profile?->address){{ $profile->address }}<br>@endif
            @if($profile?->city){{ implode(' ', array_filter([$profile->postal_code ?? '', $profile->city])) }}@endif
        </div>
    </div>
</div>

{{-- ═══ TABLA DE ÍTEMS ═══ --}}
@if($isGrape)
    {{-- LIQUIDACIÓN: columnas específicas para uva --}}
    <table class="items">
        <thead>
            <tr class="grape-head">
                <th style="width:38%">Concepto / Recepción</th>
                <th class="right" style="width:13%">Kg</th>
                <th class="right" style="width:13%">€/kg</th>
                <th class="right" style="width:12%">Base</th>
                <th class="right" style="width:12%">IRPF %</th>
                <th class="right" style="width:12%">A pagar</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $item)
                @php
                    $base     = (float) $item->tax_base;
                    $irpf     = (float) $item->tax_amount;
                    $aPagar   = $base - $irpf;
                @endphp
                <tr>
                    <td>
                        <div class="item-name">{{ $item->name }}</div>
                        @if($item->description)
                            <div class="item-sub">{{ $item->description }}</div>
                        @endif
                        @if($item->harvest?->plotPlanting?->grapeVariety)
                            <div class="item-sub">Variedad: {{ $item->harvest->plotPlanting->grapeVariety->name }}</div>
                        @elseif($item->harvest?->variety)
                            <div class="item-sub">Variedad: {{ $item->harvest->variety }}</div>
                        @endif
                        @if($item->harvest?->harvest_date)
                            <div class="item-sub">Recepción: {{ $item->harvest->harvest_date->format('d/m/Y') }}</div>
                        @endif
                    </td>
                    <td class="right">{{ number_format((float)$item->quantity, 3, ',', '.') }}</td>
                    <td class="right">{{ number_format((float)$item->unit_price, 4, ',', '.') }} €</td>
                    <td class="right">{{ number_format($base, 2, ',', '.') }} €</td>
                    <td class="right">{{ number_format((float)$item->tax_rate, 2) }}%</td>
                    <td class="right" style="font-weight:bold;">{{ number_format($aPagar, 2, ',', '.') }} €</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center; color:#9ca3af; padding:18pt;">Sin ítems</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@else
    {{-- FACTURA ESTÁNDAR / WINE_SALE --}}
    <table class="items">
        <thead>
            <tr>
                <th style="width:{{ $hasWineLots ? '30%' : '40%' }}">Concepto</th>
                @if($hasWineLots)
                    <th style="width:18%">Vino / Lote</th>
                @endif
                <th class="right" style="width:8%">Cant.</th>
                <th class="right" style="width:11%">P. unit.</th>
                <th class="right" style="width:7%">Dto.</th>
                <th class="right" style="width:12%">Base imp.</th>
                <th class="right" style="width:7%">IVA</th>
                <th class="right" style="width:{{ $hasWineLots ? '7%' : '15%' }}">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $item)
                <tr>
                    <td>
                        <div class="item-name">{{ $item->name }}</div>
                        @if($item->description && $item->description !== $item->name)
                            <div class="item-sub">{{ $item->description }}</div>
                        @endif
                        @if($item->sku)
                            <div class="item-sub">Ref: {{ $item->sku }}</div>
                        @endif
                        @if($item->harvest?->plotPlanting?->grapeVariety)
                            <div class="item-sub">Variedad: {{ $item->harvest->plotPlanting->grapeVariety->name }}</div>
                        @endif
                    </td>
                    @if($hasWineLots)
                        <td>
                            @if($item->wineLot)
                                @php $lot = $item->wineLot; @endphp
                                <div class="item-wine">
                                    @if($lot->wine_type)
                                        <span class="wine-tag">{{ ucfirst($lot->wine_type) }}</span>
                                    @endif
                                    @if($lot->vintage)
                                        <span class="wine-tag">{{ $lot->vintage }}</span>
                                    @endif
                                    @if($lot->alcohol)
                                        <span class="wine-tag">{{ $lot->alcohol }}%</span>
                                    @endif
                                    @if($lot->aging_type)
                                        <br><span style="font-size:7pt; color:#6b7280;">{{ ucfirst($lot->aging_type) }}</span>
                                    @endif
                                    @if($lot->bottle_format)
                                        <br><span style="font-size:7pt; color:#6b7280;">{{ number_format((float)$lot->bottle_format * 1000) }} ml</span>
                                    @endif
                                </div>
                            @else
                                <div class="item-sub">—</div>
                            @endif
                        </td>
                    @endif
                    <td class="right">{{ number_format((float)$item->quantity, 2) }}</td>
                    <td class="right">{{ number_format((float)$item->unit_price, 2, ',', '.') }} €</td>
                    <td class="right">
                        @if((float)$item->discount_percentage > 0)
                            {{ number_format((float)$item->discount_percentage, 0) }}%
                        @else
                            —
                        @endif
                    </td>
                    <td class="right">{{ number_format((float)$item->tax_base, 2, ',', '.') }} €</td>
                    <td class="right">{{ number_format((float)$item->tax_rate, 0) }}%</td>
                    <td class="right" style="font-weight:bold;">{{ number_format((float)$item->total, 2, ',', '.') }} €</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $hasWineLots ? 8 : 7 }}" style="text-align:center; color:#9ca3af; padding:18pt;">
                        Sin ítems
                    </td>
                </tr>
            @endforelse

            {{-- Fila volumen total vino --}}
            @if($hasWineLots && $totalVolumeLiters > 0)
                <tr style="background:#f0fdf4;">
                    <td colspan="{{ $hasWineLots ? 5 : 4 }}" style="text-align:right; color:#15803d; font-weight:bold; font-size:8pt; padding:5pt 7pt;">
                        Volumen total:
                    </td>
                    <td colspan="3" style="color:#15803d; font-weight:bold; font-size:8pt; padding:5pt 7pt;">
                        {{ number_format($totalVolumeLiters, 2, ',', '.') }} L
                        ({{ number_format($totalVolumeLiters / 0.75, 0) }} botellas est.)
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
@endif

{{-- ═══ TOTALES + BANCO ═══ --}}
<div class="totals-section">
    <div class="totals-left">

        {{-- Datos bancarios --}}
        @if($invoice->bank_account_number)
            <div class="bank-box">
                <div class="bank-title">Datos para transferencia</div>
                @if($invoice->bank_name)
                    <div class="bank-row"><strong>Banco:</strong> {{ $invoice->bank_name }}</div>
                @endif
                @if($invoice->bank_account_name)
                    <div class="bank-row"><strong>Titular:</strong> {{ $invoice->bank_account_name }}</div>
                @endif
                <div class="bank-row"><strong>IBAN:</strong> {{ $invoice->bank_account_number }}</div>
                @if($invoice->bank_routing_number)
                    <div class="bank-row"><strong>BIC/SWIFT:</strong> {{ $invoice->bank_routing_number }}</div>
                @endif
            </div>
        @endif

        {{-- Desglose por tipo impositivo (si hay varios) --}}
        @if($multipleRates && !$isGrape)
            <div style="font-size:7.5pt; font-weight:bold; color:#6b7280; text-transform:uppercase; margin-bottom:4pt;">Desglose IVA</div>
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

        @if($isGrape)
            <div style="font-size:7.5pt; color:#6b7280; margin-top:6pt; line-height:1.65;">
                <strong style="color:#92400e;">Nota:</strong> La retención IRPF se descuenta de la base imponible.<br>
                El importe "A pagar" es el neto a abonar al viticultor.
            </div>
        @endif

    </div>

    <div class="totals-right">
        <table class="totals">
            @if($isGrape)
                <tr>
                    <td>Base imponible</td>
                    <td>{{ number_format((float)$invoice->tax_base, 2, ',', '.') }} €</td>
                </tr>
                <tr>
                    <td>Retención IRPF</td>
                    <td>− {{ number_format((float)$invoice->tax_amount, 2, ',', '.') }} €</td>
                </tr>
                <tr class="total-row grape-total">
                    <td>A PAGAR</td>
                    <td>{{ number_format((float)$invoice->total_amount, 2, ',', '.') }} €</td>
                </tr>
            @else
                <tr>
                    <td>Subtotal</td>
                    <td>{{ number_format((float)$invoice->subtotal, 2, ',', '.') }} €</td>
                </tr>
                @if((float)($invoice->discount_amount ?? 0) > 0)
                    <tr class="discount">
                        <td>Descuento</td>
                        <td>− {{ number_format((float)$invoice->discount_amount, 2, ',', '.') }} €</td>
                    </tr>
                @endif
                <tr class="sep">
                    <td>Base imponible</td>
                    <td>{{ number_format((float)$invoice->tax_base, 2, ',', '.') }} €</td>
                </tr>
                <tr>
                    <td>
                        IVA
                        @if(!$multipleRates && $taxGroups->count() === 1)
                            ({{ number_format($taxGroups->first()['rate'], 0) }}%)
                        @endif
                    </td>
                    <td>{{ number_format((float)$invoice->tax_amount, 2, ',', '.') }} €</td>
                </tr>
                <tr class="total-row">
                    <td>TOTAL</td>
                    <td>{{ number_format((float)$invoice->total_amount, 2, ',', '.') }} €</td>
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
        <div class="sig-label">Firma y sello del {{ $isGrape ? 'viticultor' : 'cliente' }}</div>
        <div class="sig-name">{{ $isGrape ? ($invoice->viticulturist?->name ?? $billingName) : $billingName }}</div>
        @if(!$isGrape)
            <div style="font-size:7pt; color:#9ca3af; margin-top:2pt;">Fecha de recepción: _______________</div>
        @endif
    </div>
    <div class="sig-gap"></div>
    <div class="sig-cell">
        <div class="sig-line"></div>
        <div class="sig-label">Firma y sello del emisor</div>
        <div class="sig-name">{{ $user->name }}</div>
    </div>
</div>

{{-- ═══ PIE LEGAL ═══ --}}
<div class="footer-legal">
    Documento generado el {{ now()->format('d/m/Y \a \l\a\s H:i') }} · Agro365 · Plataforma de gestión vitivinícola
    @if($invoice->invoice_number) · {{ $isGrape ? 'Liquidación' : 'Factura' }} {{ $invoice->invoice_number }}@endif
</div>

</body>
</html>
