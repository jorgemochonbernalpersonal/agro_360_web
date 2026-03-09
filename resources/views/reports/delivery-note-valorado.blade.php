<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Albarán Valorado {{ $invoice->delivery_note_code ?? $invoice->invoice_number ?? $invoice->id }}</title>
    <style>
        @page {
            margin: 15mm 15mm 20mm 15mm;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            color: #1f2937;
            line-height: 1.5;
            padding: 15mm 15mm 20mm 15mm;
        }

        /* ── LOGO / CABECERA SUPERIOR ── */
        .header-top {
            text-align: center;
            margin-bottom: 12pt;
            padding-bottom: 10pt;
            border-bottom: 2.5pt solid #7c3aed;
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

        /* ── AVISO SIN VALOR FISCAL ── */
        .no-fiscal-notice {
            background: #faf5ff;
            border: 1pt solid #ddd6fe;
            padding: 5pt 10pt;
            font-size: 8pt;
            color: #6d28d9;
            margin-bottom: 10pt;
            text-align: center;
        }

        /* ── DATOS DEL DOCUMENTO ── */
        .doc-info-row {
            display: table;
            width: 100%;
            margin-bottom: 12pt;
        }
        .doc-info-left  { display: table-cell; width: 55%; vertical-align: top; }
        .doc-info-right { display: table-cell; width: 45%; vertical-align: top; text-align: right; }

        .doc-title {
            font-size: 18pt;
            font-weight: bold;
            color: #111827;
            letter-spacing: -0.5pt;
        }
        .doc-subtitle {
            font-size: 9pt;
            color: #7c3aed;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
            margin-top: 2pt;
        }
        .doc-code {
            font-size: 11pt;
            font-weight: bold;
            color: #7c3aed;
            margin-top: 3pt;
        }
        .doc-meta {
            font-size: 8pt;
            color: #6b7280;
            margin-top: 6pt;
            line-height: 1.75;
        }
        .doc-meta strong { color: #374151; }

        /* ── PARTES (A: / De:) ── */
        .parties {
            display: table;
            width: 100%;
            margin-bottom: 12pt;
        }
        .party-box {
            display: table-cell;
            width: 48%;
            padding: 9pt 11pt;
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

        /* ── TABLA ÍTEMS CON PRECIOS ── */
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12pt;
            font-size: 8.5pt;
        }
        table.items thead tr {
            background: #7c3aed;
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

        table.items tbody tr.row-even { background: #f9fafb; }

        table.items tbody td {
            padding: 5pt 7pt;
            border-bottom: 0.5pt solid #e5e7eb;
            vertical-align: top;
        }
        table.items tbody td.right  { text-align: right; white-space: nowrap; }
        table.items tbody td.center { text-align: center; }

        .item-name { font-weight: bold; color: #111827; }
        .item-sub  { font-size: 7.5pt; color: #6b7280; margin-top: 1pt; }
        .wine-tag {
            font-size: 7pt;
            color: #6d28d9;
            background: #faf5ff;
            border: 0.5pt solid #ddd6fe;
            padding: 0pt 3pt;
            margin-right: 2pt;
        }
        .wine-tag-grape {
            font-size: 7pt;
            color: #92400e;
            background: #fff7ed;
            border: 0.5pt solid #fed7aa;
            padding: 0pt 3pt;
            margin-right: 2pt;
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
        table.totals tr.sep td      { border-top: 0.5pt solid #e5e7eb; padding-top: 5pt; }
        table.totals tr.discount td:first-child { color: #dc2626; }
        table.totals tr.discount td:last-child  { color: #dc2626; }
        table.totals tr.total-row { background: #7c3aed; }
        table.totals tr.total-row.grape-total { background: #92400e; }
        table.totals tr.total-row td {
            font-size: 10pt;
            font-weight: bold;
            padding: 6pt 8pt;
            color: white;
        }

        /* ── NOTA ── */
        .info-note {
            font-size: 7.5pt;
            color: #9ca3af;
            text-align: center;
            margin-top: 5pt;
        }

        /* ── OBSERVACIONES ── */
        .obs-box {
            background: #fffbeb;
            border-left: 3pt solid #f59e0b;
            padding: 7pt 11pt;
            font-size: 8pt;
            color: #374151;
            margin-bottom: 12pt;
        }
        .obs-box .obs-title { font-weight: bold; color: #b45309; margin-bottom: 3pt; font-size: 8.5pt; }

        /* ── FIRMAS ── */
        .signatures {
            display: table;
            width: 100%;
            margin-top: 14pt;
        }
        .sig-cell {
            display: table-cell;
            width: 45%;
            border: 1pt solid #d1d5db;
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
            margin-top: 14pt;
            line-height: 1.6;
        }
    </style>
</head>
<body>

@php
    $profile = $user->profile;
    $code    = $invoice->delivery_note_code ?? $invoice->invoice_number ?? ('ALB-' . $invoice->id);
    $isGrape = $invoice->invoice_type === 'grape_purchase';

    // Logo base64
    $logoPath = public_path('images/logo.png');
    $logoSrc  = file_exists($logoPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
        : null;

    // Nombres para A:
    $billingName = trim(implode(' ', array_filter([
        $invoice->billing_first_name ?? '',
        $invoice->billing_last_name  ?? '',
    ])));
    if (!$billingName) {
        $billingName = $isGrape
            ? ($invoice->viticulturist?->name ?? '—')
            : ($invoice->billing_company_name ?? '—');
    }

    // Emisor ciudad
    $issuerCity = trim(implode(' ', array_filter([
        $profile->postal_code ?? '',
        $profile->city        ?? '',
    ])));

    // Detectar wine lots
    $hasWineLots = $invoice->items->filter(fn($i) => $i->wineLot)->isNotEmpty();

    // Cálculos orientativos para totales
    $subtotalOrientativo = $invoice->items->sum(fn($i) => abs((float) $i->quantity) * (float) $i->unit_price);
    $discountTotal       = $invoice->items->sum(fn($i) => (float) $i->discount_amount);
    $taxBaseTotal        = $invoice->items->sum(fn($i) => (float) $i->tax_base);
    $taxAmountTotal      = $invoice->items->sum(fn($i) => (float) $i->tax_amount);
    $totalOrientativo    = $isGrape
        ? ($taxBaseTotal - $taxAmountTotal)
        : ($taxBaseTotal + $taxAmountTotal);
@endphp

{{-- ═══ LOGO CENTRADO ═══ --}}
<div class="header-top">
    @if($logoSrc)
        <div class="header-logo">
            <img src="{{ $logoSrc }}" alt="Agro360"/>
        </div>
    @endif
    <div class="header-brand">Agro360 &middot; Plataforma de gestión vitivinícola</div>
</div>

{{-- ═══ AVISO SIN VALOR FISCAL ═══ --}}
<div class="no-fiscal-notice">
    Documento informativo con precios orientativos &mdash; No tiene valor fiscal ni sustituye a la factura
</div>

{{-- ═══ DATOS DEL DOCUMENTO ═══ --}}
<div class="doc-info-row">
    <div class="doc-info-left">
        <div style="font-size:7.5pt; font-weight:bold; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5pt; margin-bottom:3pt;">Emitido por</div>
        <div style="font-size:11pt; font-weight:bold; color:#111827;">{{ $user->name }}</div>
        <div style="font-size:8pt; color:#4b5563; margin-top:3pt; line-height:1.65;">
            @if($profile?->address){{ $profile->address }}<br>@endif
            @if($issuerCity){{ $issuerCity }}@if($profile?->province?->name) &mdash; {{ $profile->province->name }}@endif<br>@endif
            @if($profile?->phone)Tel.: {{ $profile->phone }}<br>@endif
            {{ $user->email }}
        </div>
    </div>
    <div class="doc-info-right">
        <div class="doc-title">ALBARÁN</div>
        <div class="doc-subtitle">Valorado</div>
        <div class="doc-code">{{ $code }}</div>
        <div class="doc-meta">
            <strong>Fecha:</strong> {{ ($invoice->delivery_note_date ?? $invoice->invoice_date)?->format('d/m/Y') ?? now()->format('d/m/Y') }}<br>
            @if($invoice->invoice_number && $invoice->delivery_note_code)
                <strong>Ref. factura:</strong> {{ $invoice->invoice_number }}<br>
            @endif
        </div>
    </div>
</div>

{{-- ═══ PARTES (A: / De:) ═══ --}}
<div class="parties">
    {{-- A: --}}
    <div class="party-box">
        <div class="party-role">A:</div>
        @if($isGrape && $invoice->viticulturist)
            <div class="party-name">{{ $invoice->viticulturist->name }}</div>
            <div class="party-detail">
                {{ $invoice->viticulturist->email }}<br>
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

    {{-- De: --}}
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

{{-- ═══ TABLA DE ÍTEMS CON PRECIOS ═══ --}}
@if($isGrape)
    {{-- ALBARÁN VALORADO para compra de uva --}}
    <table class="items">
        <thead>
            <tr class="grape-head">
                <th style="width:35%">Descripción</th>
                <th class="right" style="width:14%">Kg</th>
                <th class="right" style="width:12%">€/kg</th>
                <th class="right" style="width:13%">Base</th>
                <th class="right" style="width:12%">IRPF%</th>
                <th class="right" style="width:14%">A pagar</th>
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
                    <td class="right">{{ number_format(abs((float) $item->quantity), 3, ',', '.') }}</td>
                    <td class="right">{{ number_format((float) $item->unit_price, 4, ',', '.') }} €</td>
                    <td class="right">{{ number_format($base, 2, ',', '.') }} €</td>
                    <td class="right">{{ number_format((float) $item->tax_rate, 2, ',', '.') }}%</td>
                    <td class="right" style="font-weight:bold;">{{ number_format($aPagar, 2, ',', '.') }} €</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center; color:#9ca3af; padding:18pt;">Sin líneas</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@else
    {{-- ALBARÁN VALORADO para venta de vino --}}
    <table class="items">
        <thead>
            <tr>
                <th style="width:{{ $hasWineLots ? '28%' : '38%' }}">Producto</th>
                @if($hasWineLots)
                    <th style="width:14%">Lote / Vino</th>
                @endif
                <th class="right" style="width:8%">Cant.</th>
                <th class="right" style="width:11%">Precio unit.</th>
                <th class="right" style="width:7%">Dto.%</th>
                <th class="right" style="width:12%">Base</th>
                <th class="right" style="width:7%">IVA%</th>
                <th class="right" style="width:13%">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $idx => $item)
                @php $lot = $item->wineLot; @endphp
                <tr class="{{ $idx % 2 !== 0 ? 'row-even' : '' }}">
                    <td>
                        <div class="item-name">{{ $item->name }}</div>
                        @if($item->description && $item->description !== $item->name)
                            <div class="item-sub">{{ $item->description }}</div>
                        @endif
                        @if($item->sku)
                            <div class="item-sub">Ref: {{ $item->sku }}</div>
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
                        <td style="font-size:8pt;">
                            @if($lot?->name)<div style="font-weight:bold; color:#6d28d9;">{{ $lot->name }}</div>@endif
                            @if($lot?->sku)<div class="item-sub">SKU: {{ $lot->sku }}</div>@endif
                        </td>
                    @endif
                    <td class="right">{{ number_format(abs((float) $item->quantity), 2, ',', '.') }}</td>
                    <td class="right">{{ number_format((float) $item->unit_price, 2, ',', '.') }} €</td>
                    <td class="right">
                        @if((float) $item->discount_percentage > 0)
                            {{ number_format((float) $item->discount_percentage, 0) }}%
                        @else
                            &mdash;
                        @endif
                    </td>
                    <td class="right">{{ number_format((float) $item->tax_base, 2, ',', '.') }} €</td>
                    <td class="right">{{ number_format((float) $item->tax_rate, 0) }}%</td>
                    <td class="right" style="font-weight:bold;">{{ number_format((float) $item->total, 2, ',', '.') }} €</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $hasWineLots ? 8 : 7 }}" style="text-align:center; color:#9ca3af; padding:18pt;">Sin líneas</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endif

{{-- ═══ TOTALES ═══ --}}
<div class="totals-section">
    <div class="totals-left">
        @if($isGrape)
            <div style="font-size:7.5pt; color:#6b7280; line-height:1.65;">
                <strong style="color:#92400e;">Nota:</strong> La retención IRPF se descuenta de la base imponible.<br>
                El importe «A pagar» es el neto a abonar al viticultor.<br>
                Documento orientativo sin valor fiscal.
            </div>
        @else
            <div style="font-size:7.5pt; color:#6b7280; line-height:1.65;">
                Importes orientativos calculados a partir de las líneas del albarán.<br>
                Consulte la factura correspondiente para los datos fiscales definitivos.
            </div>
        @endif
    </div>

    <div class="totals-right">
        <table class="totals">
            @if($isGrape)
                <tr>
                    <td>Base bruta</td>
                    <td>{{ number_format($taxBaseTotal, 2, ',', '.') }} €</td>
                </tr>
                <tr>
                    <td>Retención IRPF</td>
                    <td style="color:#92400e;">&minus; {{ number_format($taxAmountTotal, 2, ',', '.') }} €</td>
                </tr>
                <tr class="total-row grape-total">
                    <td>A PAGAR</td>
                    <td>{{ number_format($totalOrientativo, 2, ',', '.') }} €</td>
                </tr>
            @else
                <tr>
                    <td>Subtotal</td>
                    <td>{{ number_format($subtotalOrientativo, 2, ',', '.') }} €</td>
                </tr>
                @if($discountTotal > 0)
                    <tr class="discount">
                        <td>Descuento</td>
                        <td>&minus; {{ number_format($discountTotal, 2, ',', '.') }} €</td>
                    </tr>
                @endif
                <tr class="sep">
                    <td>Base imponible</td>
                    <td>{{ number_format($taxBaseTotal, 2, ',', '.') }} €</td>
                </tr>
                <tr>
                    <td>IVA (estimado)</td>
                    <td>{{ number_format($taxAmountTotal, 2, ',', '.') }} €</td>
                </tr>
                <tr class="total-row">
                    <td>TOTAL ORIENTATIVO</td>
                    <td>{{ number_format($totalOrientativo, 2, ',', '.') }} €</td>
                </tr>
            @endif
        </table>
        <div class="info-note">Importes orientativos. Sin valor fiscal.</div>
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
        <div style="font-size:7pt; color:#9ca3af; margin-top:2pt;">Fecha de recepción: _______________</div>
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
    &middot; Agro360 &middot; Albarán valorado {{ $code }}
    &middot; Documento informativo sin valor fiscal
    &middot; Página <span class="pagenum"></span>
</div>

</body>
</html>
