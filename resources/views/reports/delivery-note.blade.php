<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Albarán {{ $invoice->delivery_note_code ?? $invoice->invoice_number ?? $invoice->id }}</title>
    <style>
        @page {
            margin: 18mm 15mm 22mm 15mm;
            @bottom-center {
                content: "Página " counter(page) " de " counter(pages);
                font-size: 8pt;
                color: #6b7280;
            }
            @bottom-right {
                content: "Agro365";
                font-size: 8pt;
                color: #9ca3af;
            }
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9.5pt;
            color: #1f2937;
            line-height: 1.5;
        }

        /* ── CABECERA ── */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 18pt;
            border-bottom: 2.5pt solid #0891b2;
            padding-bottom: 12pt;
        }
        .header-left  { display: table-cell; width: 55%; vertical-align: top; }
        .header-right { display: table-cell; width: 45%; vertical-align: top; text-align: right; }

        .issuer-name {
            font-size: 15pt;
            font-weight: bold;
            color: #0e7490;
            margin-bottom: 3pt;
        }
        .issuer-detail {
            font-size: 8.5pt;
            color: #4b5563;
            line-height: 1.6;
        }

        .doc-title   { font-size: 22pt; font-weight: bold; color: #111827; letter-spacing: -0.5pt; }
        .doc-code    { font-size: 11pt; font-weight: bold; color: #0891b2; margin-top: 2pt; }
        .doc-meta    { font-size: 8.5pt; color: #6b7280; margin-top: 6pt; line-height: 1.7; }
        .doc-meta strong { color: #374151; }

        /* ── BLOQUE DIRECCIONES ── */
        .parties {
            display: table;
            width: 100%;
            margin-bottom: 16pt;
        }
        .party-box {
            display: table-cell;
            width: 48%;
            padding: 10pt 12pt;
            border: 1pt solid #e5e7eb;
            border-radius: 4pt;
            vertical-align: top;
        }
        .party-gap  { display: table-cell; width: 4%; }
        .party-label {
            font-size: 7.5pt;
            font-weight: bold;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
            margin-bottom: 5pt;
            border-bottom: 1pt solid #f3f4f6;
            padding-bottom: 4pt;
        }
        .party-name   { font-size: 10.5pt; font-weight: bold; color: #111827; margin-bottom: 2pt; }
        .party-detail { font-size: 8.5pt; color: #4b5563; line-height: 1.6; }

        /* ── TABLA DE ÍTEMS (sin precios) ── */
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20pt;
            font-size: 9pt;
        }
        table.items thead tr {
            background: #0891b2;
            color: white;
        }
        table.items thead th {
            padding: 7pt 8pt;
            text-align: left;
            font-weight: bold;
            font-size: 8.5pt;
            text-transform: uppercase;
            letter-spacing: 0.3pt;
        }
        table.items thead th.right  { text-align: right; }
        table.items thead th.center { text-align: center; }

        table.items tbody tr:nth-child(even) { background: #f9fafb; }

        table.items tbody td {
            padding: 7pt 8pt;
            border-bottom: 0.5pt solid #e5e7eb;
            vertical-align: middle;
        }
        table.items tbody td.right  { text-align: right; }
        table.items tbody td.center { text-align: center; }

        .item-name { font-weight: bold; color: #111827; }
        .item-desc { font-size: 8pt; color: #6b7280; margin-top: 1pt; }

        /* ── CAJA DE INFORMACIÓN ── */
        .info-box {
            background: #f0f9ff;
            border: 1pt solid #bae6fd;
            border-radius: 4pt;
            padding: 9pt 12pt;
            font-size: 8.5pt;
            line-height: 1.7;
            margin-bottom: 16pt;
        }
        .info-box .info-title { font-weight: bold; color: #0369a1; font-size: 9pt; margin-bottom: 5pt; }
        .info-box .info-row   { color: #374151; }
        .info-box .info-row strong { color: #111827; }

        /* ── OBSERVACIONES ── */
        .obs-box {
            background: #fffbeb;
            border-left: 3pt solid #f59e0b;
            padding: 8pt 12pt;
            font-size: 8.5pt;
            color: #374151;
            margin-bottom: 16pt;
            border-radius: 0 4pt 4pt 0;
        }
        .obs-box .obs-title { font-weight: bold; color: #b45309; margin-bottom: 4pt; }

        /* ── SECCIÓN FIRMA ── */
        .signatures {
            display: table;
            width: 100%;
            margin-top: 10pt;
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
        .sig-gap { display: table-cell; width: 10%; }
        .sig-line {
            border-top: 1pt solid #374151;
            margin: 50pt 10pt 6pt 10pt;
        }
        .sig-label { font-size: 8pt; color: #6b7280; }
        .sig-name  { font-size: 9pt; font-weight: bold; color: #111827; margin-top: 2pt; }
    </style>
</head>
<body>

@php
    $profile = $user->profile;
    $code    = $invoice->delivery_note_code ?? $invoice->invoice_number ?? ('ALB-' . $invoice->id);
@endphp

{{-- ═══ CABECERA ═══ --}}
<div class="header">
    <div class="header-left">
        <div class="issuer-name">{{ $user->name }}</div>
        <div class="issuer-detail">
            @if($profile)
                @if($profile->address){{ $profile->address }}<br>@endif
                @if($profile->postal_code || $profile->city)
                    {{ implode(' ', array_filter([$profile->postal_code, $profile->city])) }}
                    @if($profile->province) — {{ $profile->province->name }}@endif
                    <br>
                @endif
                @if($profile->phone)Tel.: {{ $profile->phone }}<br>@endif
            @endif
            {{ $user->email }}
        </div>
    </div>
    <div class="header-right">
        <div class="doc-title">ALBARÁN</div>
        <div class="doc-code">{{ $code }}</div>
        <div class="doc-meta">
            <strong>Fecha:</strong> {{ ($invoice->delivery_note_date ?? $invoice->invoice_date)?->format('d/m/Y') ?? now()->format('d/m/Y') }}<br>
            @if($invoice->invoice_number)
                <strong>Factura:</strong> {{ $invoice->invoice_number }}<br>
            @endif
            @if($invoice->tracking_code)
                <strong>Seguimiento:</strong> {{ $invoice->tracking_code }}<br>
            @endif
        </div>
    </div>
</div>

{{-- ═══ REMITENTE / DESTINATARIO ═══ --}}
<div class="parties">
    <div class="party-box">
        <div class="party-label">Remitente</div>
        <div class="party-name">{{ $user->name }}</div>
        <div class="party-detail">
            {{ $user->email }}
            @if($profile?->phone)<br>{{ $profile->phone }}@endif
            @if($profile?->address)<br>{{ $profile->address }}@endif
            @if($profile?->city)<br>{{ implode(' ', array_filter([$profile->postal_code, $profile->city])) }}@endif
        </div>
    </div>
    <div class="party-gap"></div>
    <div class="party-box">
        <div class="party-label">Destinatario</div>
        @php
            $billingName = implode(' ', array_filter([
                $invoice->billing_first_name,
                $invoice->billing_last_name,
            ])) ?: $invoice->client?->full_name;
        @endphp
        <div class="party-name">{{ $billingName }}</div>
        <div class="party-detail">
            @if($invoice->billing_company_name){{ $invoice->billing_company_name }}<br>@endif
            @if($invoice->billing_address){{ $invoice->billing_address }}<br>@endif
            @if($invoice->billing_postal_code || $invoice->billing_city)
                {{ implode(' ', array_filter([$invoice->billing_postal_code, $invoice->billing_city])) }}
                @if($invoice->billing_state) — {{ $invoice->billing_state }}@endif
                <br>
            @endif
            @if($invoice->billing_email){{ $invoice->billing_email }}<br>@endif
            @if($invoice->billing_phone){{ $invoice->billing_phone }}@endif
        </div>
    </div>
</div>

{{-- ═══ TABLA DE BULTOS / ÍTEMS ═══ --}}
<table class="items">
    <thead>
        <tr>
            <th style="width:5%">#</th>
            <th style="width:60%">Descripción</th>
            <th class="right" style="width:20%">Cantidad</th>
            <th class="right" style="width:15%">Kg / Und.</th>
        </tr>
    </thead>
    <tbody>
        @forelse($invoice->items as $i => $item)
            <tr>
                <td class="center" style="color:#9ca3af">{{ $i + 1 }}</td>
                <td>
                    <div class="item-name">{{ $item->name }}</div>
                    @if($item->description)
                        <div class="item-desc">{{ $item->description }}</div>
                    @endif
                    @if($item->harvest?->plotPlanting?->grapeVariety)
                        <div class="item-desc">
                            Variedad: {{ $item->harvest->plotPlanting->grapeVariety->name }}
                        </div>
                    @endif
                </td>
                <td class="right">{{ number_format($item->quantity, 3) }}</td>
                <td class="right" style="color:#6b7280">kg</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" style="text-align:center; color:#9ca3af; padding:20pt;">
                    Sin ítems
                </td>
            </tr>
        @endforelse

        {{-- Fila de totales de bultos --}}
        @if($invoice->items->count() > 0)
            <tr style="background:#e0f2fe; font-weight:bold;">
                <td colspan="2" style="text-align:right; color:#0369a1; padding:6pt 8pt;">
                    Total
                </td>
                <td class="right" style="color:#0369a1; padding:6pt 8pt;">
                    {{ number_format($invoice->items->sum('quantity'), 3) }}
                </td>
                <td class="right" style="color:#6b7280; padding:6pt 8pt;">kg</td>
            </tr>
        @endif
    </tbody>
</table>

{{-- ═══ INFO DE ENTREGA ═══ --}}
@if($invoice->delivery_status || $invoice->tracking_code)
    <div class="info-box">
        <div class="info-title">Información de entrega</div>
        @if($invoice->delivery_status)
            @php
                $deliveryLabels = [
                    'pending'    => 'Pendiente',
                    'in_transit' => 'En tránsito',
                    'delivered'  => 'Entregado',
                    'cancelled'  => 'Cancelado',
                ];
            @endphp
            <div class="info-row">
                <strong>Estado:</strong> {{ $deliveryLabels[$invoice->delivery_status] ?? $invoice->delivery_status }}
            </div>
        @endif
        @if($invoice->tracking_code)
            <div class="info-row"><strong>Código seguimiento:</strong> {{ $invoice->tracking_code }}</div>
        @endif
        @if($invoice->delivery_note_date)
            <div class="info-row"><strong>Fecha albarán:</strong> {{ $invoice->delivery_note_date->format('d/m/Y') }}</div>
        @endif
    </div>
@endif

{{-- ═══ OBSERVACIONES ═══ --}}
@if($invoice->observations)
    <div class="obs-box">
        <div class="obs-title">Observaciones</div>
        {{ $invoice->observations }}
    </div>
@endif

{{-- ═══ FIRMAS ═══ --}}
<div class="signatures">
    <div class="sig-cell">
        <div class="sig-line"></div>
        <div class="sig-label">Firma y sello del remitente</div>
        <div class="sig-name">{{ $user->name }}</div>
    </div>
    <div class="sig-gap"></div>
    <div class="sig-cell">
        <div class="sig-line"></div>
        <div class="sig-label">Firma y sello del destinatario</div>
        <div class="sig-name">{{ $billingName }}</div>
        <div style="font-size:7.5pt; color:#9ca3af; margin-top:3pt;">Fecha de recepción: _______________</div>
    </div>
</div>

{{-- ═══ PIE ═══ --}}
<div style="font-size:7.5pt; color:#9ca3af; text-align:center; border-top:0.5pt solid #e5e7eb; padding-top:8pt; margin-top:14pt;">
    Documento generado el {{ now()->format('d/m/Y H:i') }} · Agro365
    · Albarán {{ $code }}
</div>

</body>
</html>
