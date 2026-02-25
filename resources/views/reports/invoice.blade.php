<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Factura {{ $invoice->invoice_number ?? 'Borrador' }}</title>
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
            border-bottom: 2.5pt solid #16a34a;
            padding-bottom: 12pt;
        }
        .header-left  { display: table-cell; width: 55%; vertical-align: top; }
        .header-right { display: table-cell; width: 45%; vertical-align: top; text-align: right; }

        .issuer-name {
            font-size: 15pt;
            font-weight: bold;
            color: #15803d;
            margin-bottom: 3pt;
        }
        .issuer-detail {
            font-size: 8.5pt;
            color: #4b5563;
            line-height: 1.6;
        }

        .invoice-title {
            font-size: 22pt;
            font-weight: bold;
            color: #111827;
            letter-spacing: -0.5pt;
        }
        .invoice-number {
            font-size: 11pt;
            font-weight: bold;
            color: #16a34a;
            margin-top: 2pt;
        }
        .invoice-meta {
            font-size: 8.5pt;
            color: #6b7280;
            margin-top: 6pt;
            line-height: 1.7;
        }
        .invoice-meta strong { color: #374151; }

        /* ── ESTADO BADGE ── */
        .status-badge {
            display: inline-block;
            padding: 2pt 8pt;
            border-radius: 20pt;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
            margin-top: 6pt;
        }
        .status-draft     { background: #fef9c3; color: #854d0e; border: 1pt solid #fde047; }
        .status-sent      { background: #dbeafe; color: #1e40af; border: 1pt solid #93c5fd; }
        .status-paid      { background: #dcfce7; color: #166534; border: 1pt solid #86efac; }
        .status-cancelled { background: #fee2e2; color: #991b1b; border: 1pt solid #fca5a5; }

        /* ── BLOQUE CLIENTE ── */
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
        .party-gap { display: table-cell; width: 4%; }
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
        .party-name {
            font-size: 10.5pt;
            font-weight: bold;
            color: #111827;
            margin-bottom: 2pt;
        }
        .party-detail {
            font-size: 8.5pt;
            color: #4b5563;
            line-height: 1.6;
        }

        /* ── TABLA DE ÍTEMS ── */
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14pt;
            font-size: 8.5pt;
        }
        table.items thead tr {
            background: #15803d;
            color: white;
        }
        table.items thead th {
            padding: 6pt 7pt;
            text-align: left;
            font-weight: bold;
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.3pt;
        }
        table.items thead th.right { text-align: right; }

        table.items tbody tr:nth-child(even) { background: #f9fafb; }
        table.items tbody tr:hover           { background: #f0fdf4; }

        table.items tbody td {
            padding: 6pt 7pt;
            border-bottom: 0.5pt solid #e5e7eb;
            vertical-align: top;
        }
        table.items tbody td.right { text-align: right; }
        table.items tbody td.center { text-align: center; }

        .item-name { font-weight: bold; color: #111827; }
        .item-desc { font-size: 7.5pt; color: #6b7280; margin-top: 1pt; }

        /* ── TOTALES ── */
        .totals-wrapper {
            display: table;
            width: 100%;
            margin-bottom: 14pt;
        }
        .totals-left  { display: table-cell; width: 55%; vertical-align: top; }
        .totals-right { display: table-cell; width: 45%; vertical-align: top; }

        table.totals {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }
        table.totals td {
            padding: 4pt 8pt;
        }
        table.totals td:last-child { text-align: right; font-weight: bold; }
        table.totals tr.subtotal-row td { color: #374151; }
        table.totals tr.discount-row td { color: #dc2626; }
        table.totals tr.tax-row td      { color: #374151; border-top: 0.5pt solid #e5e7eb; }
        table.totals tr.total-row {
            background: #15803d;
            color: white;
            border-radius: 4pt;
        }
        table.totals tr.total-row td {
            font-size: 11pt;
            font-weight: bold;
            padding: 7pt 8pt;
        }

        /* ── DATOS BANCARIOS ── */
        .bank-box {
            background: #f0fdf4;
            border: 1pt solid #bbf7d0;
            border-radius: 4pt;
            padding: 9pt 12pt;
            font-size: 8.5pt;
            line-height: 1.7;
        }
        .bank-box .bank-title {
            font-weight: bold;
            color: #15803d;
            font-size: 9pt;
            margin-bottom: 5pt;
        }
        .bank-box .bank-row { color: #374151; }
        .bank-box .bank-row strong { color: #111827; }

        /* ── OBSERVACIONES ── */
        .observations-box {
            background: #fffbeb;
            border-left: 3pt solid #f59e0b;
            padding: 8pt 12pt;
            font-size: 8.5pt;
            color: #374151;
            margin-bottom: 12pt;
            border-radius: 0 4pt 4pt 0;
        }
        .observations-box .obs-title {
            font-weight: bold;
            color: #b45309;
            margin-bottom: 4pt;
        }

        /* ── SELLO PAGADO ── */
        .paid-stamp {
            position: absolute;
            top: 80pt;
            right: 15mm;
            transform: rotate(-20deg);
            border: 3pt solid #16a34a;
            color: #16a34a;
            padding: 4pt 14pt;
            font-size: 18pt;
            font-weight: bold;
            letter-spacing: 2pt;
            opacity: 0.35;
            text-transform: uppercase;
        }

        .section-title {
            font-size: 8pt;
            font-weight: bold;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
            margin-bottom: 6pt;
        }
    </style>
</head>
<body>

@php
    $profile = $user->profile;
    $statusLabels = [
        'draft'     => 'Borrador',
        'sent'      => 'Enviada',
        'paid'      => 'Pagada',
        'cancelled' => 'Cancelada',
        'corrective'=> 'Rectificativa',
    ];
    $statusClasses = [
        'draft'     => 'status-draft',
        'sent'      => 'status-sent',
        'paid'      => 'status-paid',
        'cancelled' => 'status-cancelled',
    ];
@endphp

{{-- SELLO PAGADO --}}
@if($invoice->isPaid())
    <div class="paid-stamp">PAGADA</div>
@endif

{{-- ═══ CABECERA ═══ --}}
<div class="header">
    <div class="header-left">
        <div class="issuer-name">{{ $user->name }}</div>
        <div class="issuer-detail">
            @if($profile)
                @if($profile->address){{ $profile->address }}<br>@endif
                @if($profile->postal_code || $profile->city)
                    {{ implode(' ', array_filter([$profile->postal_code, $profile->city])) }}
                    @if($profile->province)
                        — {{ $profile->province->name }}
                    @endif
                    <br>
                @endif
                @if($profile->phone)Tel.: {{ $profile->phone }}<br>@endif
            @endif
            {{ $user->email }}
        </div>
    </div>
    <div class="header-right">
        <div class="invoice-title">FACTURA</div>
        @if($invoice->invoice_number)
            <div class="invoice-number">{{ $invoice->invoice_number }}</div>
        @endif
        <div class="invoice-meta">
            <strong>Fecha:</strong> {{ $invoice->invoice_date?->format('d/m/Y') ?? now()->format('d/m/Y') }}<br>
            @if($invoice->order_date)
                <strong>F. pedido:</strong> {{ $invoice->order_date->format('d/m/Y') }}<br>
            @endif
            @if($invoice->delivery_note_code)
                <strong>Albarán:</strong> {{ $invoice->delivery_note_code }}<br>
            @endif
        </div>
        <div class="status-badge {{ $statusClasses[$invoice->status] ?? 'status-draft' }}">
            {{ $statusLabels[$invoice->status] ?? $invoice->status }}
        </div>
    </div>
</div>

{{-- ═══ EMISOR / DESTINATARIO ═══ --}}
<div class="parties">
    <div class="party-box">
        <div class="party-label">Datos del emisor</div>
        <div class="party-name">{{ $user->name }}</div>
        <div class="party-detail">
            {{ $user->email }}
            @if($profile?->phone)<br>{{ $profile->phone }}@endif
        </div>
    </div>
    <div class="party-gap"></div>
    <div class="party-box">
        <div class="party-label">Facturar a</div>
        @php
            $billingName = implode(' ', array_filter([
                $invoice->billing_first_name,
                $invoice->billing_last_name,
            ])) ?: $invoice->client?->full_name;
        @endphp
        <div class="party-name">{{ $billingName }}</div>
        <div class="party-detail">
            @if($invoice->billing_company_name){{ $invoice->billing_company_name }}<br>@endif
            @if($invoice->billing_company_document)NIF: {{ $invoice->billing_company_document }}<br>@endif
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

{{-- ═══ TABLA DE ÍTEMS ═══ --}}
<table class="items">
    <thead>
        <tr>
            <th style="width:38%">Concepto</th>
            <th class="right" style="width:10%">Cant.</th>
            <th class="right" style="width:12%">P. unitario</th>
            <th class="right" style="width:9%">Dto.</th>
            <th class="right" style="width:12%">Base imp.</th>
            <th class="right" style="width:9%">IVA</th>
            <th class="right" style="width:10%">Total</th>
        </tr>
    </thead>
    <tbody>
        @forelse($invoice->items as $item)
            <tr>
                <td>
                    <div class="item-name">{{ $item->name }}</div>
                    @if($item->description)
                        <div class="item-desc">{{ $item->description }}</div>
                    @endif
                    @if($item->sku)
                        <div class="item-desc">Ref: {{ $item->sku }}</div>
                    @endif
                </td>
                <td class="right">{{ number_format($item->quantity, 2) }}</td>
                <td class="right">{{ number_format($item->unit_price, 2, ',', '.') }} €</td>
                <td class="right">
                    @if($item->discount_percentage > 0)
                        {{ number_format($item->discount_percentage, 0) }}%
                    @else
                        —
                    @endif
                </td>
                <td class="right">{{ number_format($item->subtotal, 2, ',', '.') }} €</td>
                <td class="right">{{ number_format($item->tax_rate, 0) }}%</td>
                <td class="right">{{ number_format($item->total, 2, ',', '.') }} €</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align:center; color:#9ca3af; padding: 20pt;">
                    Sin ítems
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

{{-- ═══ TOTALES + BANCO ═══ --}}
<div class="totals-wrapper">
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

        {{-- Forma de pago --}}
        @if($invoice->payment_type)
            <div style="margin-top:8pt; font-size:8.5pt; color:#374151;">
                <strong>Forma de pago:</strong>
                {{ match($invoice->payment_type) {
                    'transfer'  => 'Transferencia bancaria',
                    'cash'      => 'Efectivo',
                    'card'      => 'Tarjeta',
                    'check'     => 'Cheque',
                    default     => ucfirst($invoice->payment_type),
                } }}
                @if($invoice->payment_date)
                    · Pagado el {{ $invoice->payment_date->format('d/m/Y') }}
                @endif
            </div>
        @endif
    </div>

    <div class="totals-right">
        <table class="totals">
            <tr class="subtotal-row">
                <td>Subtotal</td>
                <td>{{ number_format($invoice->subtotal, 2, ',', '.') }} €</td>
            </tr>
            @if($invoice->discount_amount > 0)
                <tr class="discount-row">
                    <td>Descuento</td>
                    <td>− {{ number_format($invoice->discount_amount, 2, ',', '.') }} €</td>
                </tr>
            @endif
            <tr class="tax-row">
                <td>Base imponible</td>
                <td>{{ number_format($invoice->tax_base, 2, ',', '.') }} €</td>
            </tr>
            <tr class="tax-row">
                <td>IVA ({{ number_format($invoice->tax_rate, 0) }}%)</td>
                <td>{{ number_format($invoice->tax_amount, 2, ',', '.') }} €</td>
            </tr>
            <tr class="total-row">
                <td>TOTAL</td>
                <td>{{ number_format($invoice->total_amount, 2, ',', '.') }} €</td>
            </tr>
        </table>
    </div>
</div>

{{-- ═══ OBSERVACIONES ═══ --}}
@if($invoice->observations || $invoice->observations_invoice)
    <div class="observations-box">
        <div class="obs-title">Observaciones</div>
        @if($invoice->observations)
            <div>{{ $invoice->observations }}</div>
        @endif
        @if($invoice->observations_invoice)
            <div style="margin-top:3pt;">{{ $invoice->observations_invoice }}</div>
        @endif
    </div>
@endif

{{-- ═══ PIE LEGAL ═══ --}}
<div style="font-size:7.5pt; color:#9ca3af; text-align:center; border-top:0.5pt solid #e5e7eb; padding-top:8pt; margin-top:8pt;">
    Documento generado el {{ now()->format('d/m/Y H:i') }} · Agro365
    @if($invoice->invoice_number)
        · Factura {{ $invoice->invoice_number }}
    @endif
</div>

</body>
</html>
