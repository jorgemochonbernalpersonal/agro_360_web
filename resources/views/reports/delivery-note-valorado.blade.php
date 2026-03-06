<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Albarán Valorado {{ $invoice->delivery_note_code ?? $invoice->invoice_number ?? $invoice->id }}</title>
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
            border-bottom: 2.5pt solid #7c3aed;
            padding-bottom: 12pt;
        }
        .header-left  { display: table-cell; width: 55%; vertical-align: top; }
        .header-right { display: table-cell; width: 45%; vertical-align: top; text-align: right; }

        .issuer-name   { font-size: 15pt; font-weight: bold; color: #6d28d9; margin-bottom: 3pt; }
        .issuer-detail { font-size: 8.5pt; color: #4b5563; line-height: 1.6; }

        .doc-title { font-size: 18pt; font-weight: bold; color: #111827; letter-spacing: -0.5pt; }
        .doc-sub   { font-size: 9pt; color: #7c3aed; font-weight: bold; margin-top: 1pt; letter-spacing: 0.3pt; text-transform: uppercase; }
        .doc-code  { font-size: 11pt; font-weight: bold; color: #7c3aed; margin-top: 4pt; }
        .doc-meta  { font-size: 8.5pt; color: #6b7280; margin-top: 6pt; line-height: 1.7; }
        .doc-meta strong { color: #374151; }

        /* ── AVISO sin valor fiscal ── */
        .no-fiscal-notice {
            background: #faf5ff;
            border: 1pt solid #ddd6fe;
            border-radius: 4pt;
            padding: 6pt 10pt;
            font-size: 8pt;
            color: #6d28d9;
            margin-bottom: 14pt;
            text-align: center;
        }

        /* ── BLOQUE DIRECCIONES ── */
        .parties      { display: table; width: 100%; margin-bottom: 16pt; }
        .party-box    { display: table-cell; width: 48%; padding: 10pt 12pt; border: 1pt solid #e5e7eb; border-radius: 4pt; vertical-align: top; }
        .party-gap    { display: table-cell; width: 4%; }
        .party-label  { font-size: 7.5pt; font-weight: bold; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5pt; margin-bottom: 5pt; border-bottom: 1pt solid #f3f4f6; padding-bottom: 4pt; }
        .party-name   { font-size: 10.5pt; font-weight: bold; color: #111827; margin-bottom: 2pt; }
        .party-detail { font-size: 8.5pt; color: #4b5563; line-height: 1.6; }

        /* ── TABLA DE ÍTEMS CON PRECIOS ── */
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16pt;
            font-size: 9pt;
        }
        table.items thead tr { background: #7c3aed; color: white; }
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

        /* ── RESUMEN IMPORTE ── */
        .totals-box {
            display: table;
            width: 100%;
            margin-bottom: 16pt;
        }
        .totals-spacer { display: table-cell; width: 55%; }
        .totals-data   { display: table-cell; width: 45%; }
        .totals-table  { width: 100%; border-collapse: collapse; font-size: 9pt; }
        .totals-table td { padding: 5pt 8pt; }
        .totals-table .total-row {
            background: #7c3aed;
            color: white;
            font-weight: bold;
            font-size: 10.5pt;
        }
        .totals-table .total-row td { padding: 7pt 8pt; }

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
    </style>
</head>
<body>

@php
    $profile  = $user->profile;
    $code     = $invoice->delivery_note_code ?? $invoice->invoice_number ?? ('ALB-' . $invoice->id);
    $subtotal = $invoice->items->sum(fn($i) => abs((float)$i->quantity) * (float)$i->unit_price);
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
        <div class="doc-sub">Valorado</div>
        <div class="doc-code">{{ $code }}</div>
        <div class="doc-meta">
            <strong>Fecha:</strong> {{ ($invoice->delivery_note_date ?? $invoice->invoice_date)?->format('d/m/Y') ?? now()->format('d/m/Y') }}<br>
            @if($invoice->invoice_number)
                <strong>Factura ref.:</strong> {{ $invoice->invoice_number }}<br>
            @endif
        </div>
    </div>
</div>

{{-- ═══ AVISO ═══ --}}
<div class="no-fiscal-notice">
    Documento informativo con precios orientativos &mdash; No tiene valor fiscal ni sustituye a la factura
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
            {{-- NIF/CIF omitted intentionally: no fiscal value document --}}
        </div>
    </div>
</div>

{{-- ═══ TABLA CON PRECIOS ═══ --}}
<table class="items">
    <thead>
        <tr>
            <th style="width:5%">#</th>
            <th style="width:45%">Descripción</th>
            <th class="right" style="width:15%">Cantidad</th>
            <th class="right" style="width:17%">Precio unit.</th>
            <th class="right" style="width:18%">Importe</th>
        </tr>
    </thead>
    <tbody>
        @forelse($invoice->items as $i => $item)
            @php
                $qty        = abs((float) $item->quantity);
                $unitPrice  = (float) $item->unit_price;
                $lineAmount = $qty * $unitPrice;
            @endphp
            <tr>
                <td class="center" style="color:#9ca3af">{{ $i + 1 }}</td>
                <td>
                    <div class="item-name">{{ $item->name }}</div>
                    @if($item->description)
                        <div class="item-desc">{{ $item->description }}</div>
                    @endif
                    @if($item->harvest?->plotPlanting?->grapeVariety)
                        <div class="item-desc">Variedad: {{ $item->harvest->plotPlanting->grapeVariety->name }}</div>
                    @endif
                    @if($item->wineLot?->name)
                        <div class="item-desc">Lote: {{ $item->wineLot->name }}</div>
                    @endif
                </td>
                <td class="right">{{ number_format($qty, 3) }}</td>
                <td class="right">{{ number_format($unitPrice, 4) }} €</td>
                <td class="right" style="font-weight:bold">{{ number_format($lineAmount, 2) }} €</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align:center; color:#9ca3af; padding:20pt;">Sin ítems</td>
            </tr>
        @endforelse
    </tbody>
</table>

{{-- ═══ TOTAL (sin IVA desglosado) ═══ --}}
<div class="totals-box">
    <div class="totals-spacer"></div>
    <div class="totals-data">
        <table class="totals-table">
            <tr>
                <td style="color:#6b7280">Base imponible</td>
                <td style="text-align:right">{{ number_format($subtotal, 2) }} €</td>
            </tr>
            <tr class="total-row">
                <td>Total orientativo</td>
                <td style="text-align:right">{{ number_format($subtotal, 2) }} €</td>
            </tr>
        </table>
        <p style="font-size:7.5pt; color:#9ca3af; text-align:center; margin-top:5pt;">
            Importes sin IVA. Consulte la factura para datos fiscales.
        </p>
    </div>
</div>

{{-- ═══ OBSERVACIONES ═══ --}}
@if($invoice->observations)
    <div class="obs-box">
        <div class="obs-title">Observaciones</div>
        {{ $invoice->observations }}
    </div>
@endif

{{-- ═══ PIE ═══ --}}
<div style="font-size:7.5pt; color:#9ca3af; text-align:center; border-top:0.5pt solid #e5e7eb; padding-top:8pt; margin-top:14pt;">
    Documento generado el {{ now()->format('d/m/Y H:i') }} · Agro365
    · Albarán valorado {{ $code }} · Documento informativo sin valor fiscal
</div>

</body>
</html>
