<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $invoice->invoice_type === 'grape_purchase' ? 'Liquidación de Vendimia' : 'Factura' }} {{ $invoice->invoice_number ?? '' }}</title>
    <style>
        @page { margin: 15mm 16mm 20mm 16mm; }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            color: #1f2937;
            line-height: 1.5;
        }

        /* ── LOGO HEADER ── */
        .hdr {
            text-align: center;
            padding-bottom: 10pt;
            margin-bottom: 14pt;
            border-bottom: 2.5pt solid #15803d;
        }
        .hdr.grape { border-bottom-color: #92400e; }
        .hdr img   { height: 42pt; width: auto; }
        .hdr-sub   { font-size: 8pt; color: #6b7280; margin-top: 3pt; }

        /* ── EMISOR / TÍTULO ── */
        .issuer-lbl { font-size: 7pt; font-weight: bold; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5pt; margin-bottom: 3pt; }
        .issuer-nm  { font-size: 11pt; font-weight: bold; color: #111827; margin-bottom: 3pt; }
        .issuer-dt  { font-size: 8pt; color: #4b5563; line-height: 1.65; }
        .issuer-dt .ln { margin-bottom: 1pt; }

        .doc-ttl { font-size: 22pt; font-weight: bold; color: #111827; letter-spacing: -0.5pt; margin-bottom: 2pt; }
        .doc-num { font-size: 12pt; font-weight: bold; color: #15803d; margin-bottom: 8pt; }
        .doc-num.g { color: #92400e; }
        .doc-mt  { font-size: 8pt; color: #6b7280; }
        .doc-mt .ln { margin-bottom: 3pt; }
        .doc-mt strong { color: #374151; }

        /* ── BADGES ── */
        .bdg { display: inline-block; padding: 2pt 8pt; border-radius: 20pt; font-size: 7.5pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5pt; margin-top: 4pt; }
        .bdg-sent { background: #dbeafe; color: #1e40af; border: 1pt solid #93c5fd; }
        .bdg-paid { background: #dcfce7; color: #166534; border: 1pt solid #86efac; }
        .bdg-canc { background: #fee2e2; color: #991b1b; border: 1pt solid #fca5a5; }

        /* ── PARTES ── */
        .pty-box  { border: 1pt solid #e5e7eb; padding: 10pt 12pt; vertical-align: top; }
        .pty-role { font-size: 7pt; font-weight: bold; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5pt; margin-bottom: 4pt; padding-bottom: 3pt; border-bottom: 0.5pt solid #f3f4f6; }
        .pty-nm   { font-size: 10pt; font-weight: bold; color: #111827; margin-bottom: 3pt; }
        .pty-dt   { font-size: 8pt; color: #4b5563; line-height: 1.65; }
        .pty-dt .ln { margin-bottom: 1pt; }

        /* ── TABLA ÍTEMS ── */
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14pt;
            font-size: 8pt;
            table-layout: fixed;
        }
        table.items thead tr      { background: #15803d; color: white; }
        table.items thead tr.grp  { background: #92400e; }
        table.items thead th {
            padding: 6pt 7pt;
            text-align: left;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3pt;
        }
        table.items thead th.r { text-align: right; }
        table.items tbody tr.alt  { background: #f9fafb; }
        table.items tbody td {
            padding: 6pt 7pt;
            border-bottom: 0.5pt solid #e5e7eb;
            vertical-align: top;
            word-wrap: break-word;
        }
        table.items tbody td.r  { text-align: right; white-space: nowrap; }

        .i-nm  { font-weight: bold; color: #111827; }
        .i-sub { font-size: 7pt; color: #6b7280; margin-top: 2pt; }
        .wtag  { font-size: 7pt; color: #166534; background: #f0fdf4; border: 0.5pt solid #bbf7d0; padding: 0pt 3pt; margin-right: 2pt; }

        /* ── TOTALES ── */
        table.tot { width: 100%; border-collapse: collapse; font-size: 8.5pt; }
        table.tot td { padding: 4pt 10pt; }
        table.tot td.lbl  { color: #6b7280; }
        table.tot td.val  { text-align: right; font-weight: bold; color: #374151; }
        table.tot tr.sep  td { border-top: 0.5pt solid #e5e7eb; }
        table.tot tr.disc td.lbl { color: #dc2626; }
        table.tot tr.disc td.val { color: #dc2626; }
        table.tot tr.grand     { background: #15803d; }
        table.tot tr.grand.g   { background: #92400e; }
        table.tot tr.grand td  { font-size: 10pt; font-weight: bold; padding: 7pt 10pt; color: white; }
        table.tot tr.grand td.val { text-align: right; }

        /* ── DESGLOSE IVA ── */
        table.txg { width: 100%; border-collapse: collapse; font-size: 8pt; margin-top: 6pt; }
        table.txg thead tr { background: #f3f4f6; }
        table.txg thead th { padding: 4pt 6pt; font-size: 7pt; font-weight: bold; text-transform: uppercase; color: #6b7280; text-align: right; }
        table.txg thead th:first-child { text-align: left; }
        table.txg tbody td { padding: 3pt 6pt; text-align: right; border-bottom: 0.5pt solid #f3f4f6; color: #374151; }
        table.txg tbody td:first-child { text-align: left; }

        /* ── OBSERVACIONES ── */
        .obs   { background: #fffbeb; border-left: 3pt solid #f59e0b; padding: 8pt 12pt; font-size: 8pt; color: #374151; margin-bottom: 14pt; }
        .obs-t { font-weight: bold; color: #b45309; margin-bottom: 3pt; font-size: 8.5pt; }

        /* ── NOTA IRPF ── */
        .irpf-n { font-size: 7.5pt; color: #6b7280; line-height: 1.6; }

        /* ── FIRMAS ── */
        .sig-box  { border: 1pt solid #d1d5db; padding: 10pt 14pt; text-align: center; vertical-align: bottom; }
        .sig-line { border-top: 1pt solid #9ca3af; margin: 46pt 8pt 6pt 8pt; }
        .sig-lbl  { font-size: 7.5pt; color: #9ca3af; }
        .sig-nm   { font-size: 8.5pt; font-weight: bold; color: #374151; margin-top: 2pt; }

        /* ── PIE ── */
        .footer { font-size: 7pt; color: #9ca3af; text-align: center; border-top: 0.5pt solid #e5e7eb; padding-top: 7pt; margin-top: 12pt; line-height: 1.6; }

        /* ── SELLO PAGADO ── */
        .paid-stamp {
            position: absolute; top: 65pt; right: 16mm;
            border: 3pt solid #16a34a; color: #16a34a;
            padding: 4pt 14pt; font-size: 18pt; font-weight: bold;
            letter-spacing: 2pt; opacity: 0.25; text-transform: uppercase;
            transform: rotate(-20deg);
        }
    </style>
</head>
<body>

@php
    $profile  = $user->profile;
    $isGrape  = $invoice->invoice_type === 'grape_purchase';
    $isPaid   = $invoice->payment_status === 'paid';

    $logoPath = public_path('images/logo.png');
    $logoSrc  = file_exists($logoPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
        : null;

    $billingName = trim(implode(' ', array_filter([
        $invoice->billing_first_name ?? '',
        $invoice->billing_last_name  ?? '',
    ])));
    if (!$billingName) $billingName = $invoice->billing_company_name ?? '—';

    $issuerCity = trim(implode(' ', array_filter([
        $profile->postal_code ?? '',
        $profile->city        ?? '',
    ])));

    $docTitle  = $isGrape ? 'LIQUIDACIÓN' : 'FACTURA';
    $docSubtitle = $isGrape ? 'DE VENDIMIA' : null;
    $docNumber = $invoice->invoice_number ?? '—';

    $taxGroups = $invoice->items
        ->groupBy(fn($i) => number_format((float) $i->tax_rate, 2))
        ->map(fn($g, $r) => [
            'rate'   => (float) $r,
            'base'   => $g->sum(fn($i) => (float) $i->tax_base),
            'amount' => $g->sum(fn($i) => (float) $i->tax_amount),
        ])
        ->sortBy('rate')->values();
    $multipleRates = $taxGroups->count() > 1;
    $hasWineLots   = $invoice->items->filter(fn($i) => $i->wineLot)->isNotEmpty();

    $paymentLabels = [
        'transfer' => 'Transferencia bancaria',
        'cash'     => 'Efectivo',
        'card'     => 'Tarjeta',
        'check'    => 'Cheque',
        'other'    => 'Otro',
    ];
    $paymentLabel = $paymentLabels[$invoice->payment_type] ?? ($invoice->payment_type ? ucfirst($invoice->payment_type) : null);

    $statusLabels  = ['sent' => 'Enviada', 'paid' => 'Pagada', 'cancelled' => 'Cancelada'];
    $statusClasses = ['sent' => 'bdg-sent', 'paid' => 'bdg-paid', 'cancelled' => 'bdg-canc'];
@endphp

@if($isPaid)
    <div class="paid-stamp">PAGADA</div>
@endif

{{-- ══ LOGO ══ --}}
<div class="hdr {{ $isGrape ? 'grape' : '' }}">
    @if($logoSrc)
        <img src="{{ $logoSrc }}" alt="Agro360"/>
    @endif
    <div class="hdr-sub">Agro360 &middot; Plataforma de gestión vitivinícola</div>
</div>

{{-- ══ EMISOR + TÍTULO DEL DOCUMENTO ══ --}}
<table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:16pt;">
    <tr>
        <td width="54%" valign="top" style="padding-right:14pt;">
            <div class="issuer-lbl">Emitido por</div>
            <div class="issuer-nm">{{ $user->name }}</div>
            <div class="issuer-dt">
                @if($profile?->address)
                    <div class="ln">{{ $profile->address }}</div>
                @endif
                @if($issuerCity)
                    <div class="ln">{{ $issuerCity }}@if($profile?->province?->name) &mdash; {{ $profile->province->name }}@endif</div>
                @endif
                @if($profile?->phone)
                    <div class="ln">Tel.: {{ $profile->phone }}</div>
                @endif
                <div class="ln">{{ $user->email }}</div>
            </div>
        </td>
        <td width="46%" valign="top" align="right">
            <div class="doc-ttl">{{ $docTitle }}</div>
            @if($docSubtitle)
                <div style="font-size:11pt; font-weight:bold; color:#92400e; margin-bottom:4pt; letter-spacing:0.2pt;">{{ $docSubtitle }}</div>
            @endif
            <div class="doc-num {{ $isGrape ? 'g' : '' }}">{{ $docNumber }}</div>
            <div class="doc-mt">
                <div class="ln"><strong>Fecha:</strong> {{ $invoice->invoice_date?->format('d/m/Y') ?? now()->format('d/m/Y') }}</div>
                @if($invoice->delivery_note_date)
                    <div class="ln"><strong>F. albarán:</strong> {{ $invoice->delivery_note_date->format('d/m/Y') }}</div>
                @endif
                @if($invoice->delivery_note_code)
                    <div class="ln"><strong>Ref. albarán:</strong> {{ $invoice->delivery_note_code }}</div>
                @endif
                @if($paymentLabel)
                    <div class="ln"><strong>Forma de pago:</strong> {{ $paymentLabel }}</div>
                @endif
                @if($invoice->due_date)
                    <div class="ln"><strong>Vencimiento:</strong> {{ $invoice->due_date->format('d/m/Y') }}</div>
                @endif
            </div>
            @if(isset($statusLabels[$invoice->status]))
                <div class="bdg {{ $statusClasses[$invoice->status] }}">{{ $statusLabels[$invoice->status] }}</div>
            @endif
        </td>
    </tr>
</table>

{{-- ══ PARTES: A / DE ══ --}}
<table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:16pt;">
    <tr>
        {{-- A: destinatario --}}
        <td width="48%" valign="top" class="pty-box">
            <div class="pty-role">A:</div>
            @if($isGrape && $invoice->viticulturist)
                <div class="pty-nm">{{ $invoice->viticulturist->name }}</div>
                <div class="pty-dt">
                    <div class="ln">{{ $invoice->viticulturist->email }}</div>
                    @if($invoice->billing_company_document)
                        <div class="ln">NIF: {{ $invoice->billing_company_document }}</div>
                    @endif
                    @if($invoice->billing_address)
                        <div class="ln">{{ $invoice->billing_address }}</div>
                    @endif
                    @if($invoice->billing_postal_code || $invoice->billing_city)
                        <div class="ln">{{ trim(implode(' ', array_filter([$invoice->billing_postal_code ?? '', $invoice->billing_city ?? '']))) }}</div>
                    @endif
                </div>
            @else
                <div class="pty-nm">{{ $billingName }}</div>
                <div class="pty-dt">
                    @if($invoice->billing_company_name && $invoice->billing_company_name !== $billingName)
                        <div class="ln">{{ $invoice->billing_company_name }}</div>
                    @endif
                    @if($invoice->billing_company_document)
                        <div class="ln">NIF/CIF: {{ $invoice->billing_company_document }}</div>
                    @endif
                    @if($invoice->billing_email)
                        <div class="ln">{{ $invoice->billing_email }}</div>
                    @endif
                    @if($invoice->billing_phone)
                        <div class="ln">Tel.: {{ $invoice->billing_phone }}</div>
                    @endif
                    @if($invoice->billing_address)
                        <div class="ln">{{ $invoice->billing_address }}</div>
                    @endif
                    @if($invoice->billing_postal_code || $invoice->billing_city)
                        <div class="ln">
                            {{ trim(implode(' ', array_filter([$invoice->billing_postal_code ?? '', $invoice->billing_city ?? '']))) }}
                            @if($invoice->billing_state) &mdash; {{ $invoice->billing_state }}@endif
                        </div>
                    @endif
                </div>
            @endif
        </td>

        <td width="4%">&nbsp;</td>

        {{-- De: emisor --}}
        <td width="48%" valign="top" class="pty-box">
            <div class="pty-role">De:</div>
            <div class="pty-nm">{{ $user->name }}</div>
            <div class="pty-dt">
                <div class="ln">{{ $user->email }}</div>
                @if($profile?->phone)
                    <div class="ln">Tel.: {{ $profile->phone }}</div>
                @endif
                @if($profile?->address)
                    <div class="ln">{{ $profile->address }}</div>
                @endif
                @if($issuerCity)
                    <div class="ln">{{ $issuerCity }}@if($profile?->province?->name) &mdash; {{ $profile->province->name }}@endif</div>
                @endif
            </div>
        </td>
    </tr>
</table>

{{-- ══ TABLA DE ÍTEMS ══ --}}
@if($isGrape)

    {{-- LIQUIDACIÓN DE VENDIMIA --}}
    <table class="items">
        <thead>
            <tr class="grp">
                <th style="width:36%">Descripción / Recepción</th>
                <th class="r" style="width:12%">Kg</th>
                <th class="r" style="width:11%">€/kg</th>
                <th class="r" style="width:13%">Base imp.</th>
                <th class="r" style="width:9%">IRPF %</th>
                <th class="r" style="width:11%">Retención</th>
                <th class="r" style="width:8%">A pagar</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $idx => $item)
                @php
                    $base   = (float) $item->tax_base;
                    $irpf   = (float) $item->tax_amount;
                    $aPagar = $base - $irpf;
                @endphp
                <tr class="{{ $idx % 2 !== 0 ? 'alt' : '' }}">
                    <td>
                        <div class="i-nm">{{ $item->name }}</div>
                        @if($item->description && $item->description !== $item->name)
                            <div class="i-sub">{{ $item->description }}</div>
                        @endif
                        @if($item->sku)
                            <div class="i-sub">Ref: {{ $item->sku }}</div>
                        @endif
                    </td>
                    <td class="r">{{ number_format((float) $item->quantity, 3, ',', '.') }}</td>
                    <td class="r">{{ number_format((float) $item->unit_price, 4, ',', '.') }} €</td>
                    <td class="r">{{ number_format($base, 2, ',', '.') }} €</td>
                    <td class="r">{{ number_format((float) $item->tax_rate, 2, ',', '.') }}%</td>
                    <td class="r" style="color:#92400e;">{{ number_format($irpf, 2, ',', '.') }} €</td>
                    <td class="r" style="font-weight:bold;">{{ number_format($aPagar, 2, ',', '.') }} €</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center; color:#9ca3af; padding:16pt;">Sin líneas</td>
                </tr>
            @endforelse
        </tbody>
    </table>

@else

    {{-- FACTURA VENTA DE PRODUCTO --}}
    <table class="items">
        <thead>
            <tr>
                <th style="width:{{ $hasWineLots ? '25%' : '37%' }}">Producto</th>
                @if($hasWineLots)
                    <th style="width:15%">SKU / Lote</th>
                @else
                    <th style="width:10%">SKU</th>
                @endif
                <th class="r" style="width:7%">Cant.</th>
                <th class="r" style="width:11%">P. Unit.</th>
                <th class="r" style="width:7%">Dto.%</th>
                <th class="r" style="width:7%">IVA%</th>
                <th class="r" style="width:11%">Base imp.</th>
                <th class="r" style="width:9%">Cuota IVA</th>
                <th class="r" style="width:8%">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $idx => $item)
                @php $lot = $item->wineLot; @endphp
                <tr class="{{ $idx % 2 !== 0 ? 'alt' : '' }}">
                    <td>
                        <div class="i-nm">{{ $item->name }}</div>
                        @if($item->description && $item->description !== $item->name)
                            <div class="i-sub">{{ $item->description }}</div>
                        @endif
                        @if($lot)
                            <div class="i-sub">
                                @if($lot->vintage)<span class="wtag">{{ $lot->vintage }}</span>@endif
                                @if($lot->wine_type)<span class="wtag">{{ ucfirst($lot->wine_type) }}</span>@endif
                                @if($lot->alcohol)<span class="wtag">{{ $lot->alcohol }}%</span>@endif
                                @if($lot->aging_type)<span class="wtag">{{ ucfirst($lot->aging_type) }}</span>@endif
                                @if($lot->bottle_format)<span class="wtag">{{ number_format((float)$lot->bottle_format * 1000) }} ml</span>@endif
                            </div>
                        @endif
                    </td>
                    @if($hasWineLots)
                        <td>
                            @if($item->sku)
                                <div class="i-nm" style="font-size:8pt;">{{ $item->sku }}</div>
                            @endif
                            @if($lot?->sku && $lot->sku !== $item->sku)
                                <div class="i-sub">Lote: {{ $lot->sku }}</div>
                            @elseif($lot?->name)
                                <div class="i-sub">{{ $lot->name }}</div>
                            @endif
                        </td>
                    @else
                        <td>
                            @if($item->sku)
                                <span style="font-size:8pt; color:#6b7280;">{{ $item->sku }}</span>
                            @endif
                        </td>
                    @endif
                    <td class="r">{{ number_format((float) $item->quantity, 2, ',', '.') }}</td>
                    <td class="r">{{ number_format((float) $item->unit_price, 2, ',', '.') }} €</td>
                    <td class="r">
                        @if((float) $item->discount_percentage > 0)
                            {{ number_format((float) $item->discount_percentage, 0) }}%
                        @else
                            &mdash;
                        @endif
                    </td>
                    <td class="r">{{ number_format((float) $item->tax_rate, 0) }}%</td>
                    <td class="r">{{ number_format((float) $item->tax_base, 2, ',', '.') }} €</td>
                    <td class="r">{{ number_format((float) $item->tax_amount, 2, ',', '.') }} €</td>
                    <td class="r" style="font-weight:bold;">{{ number_format((float) $item->total, 2, ',', '.') }} €</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center; color:#9ca3af; padding:16pt;">Sin líneas</td>
                </tr>
            @endforelse
        </tbody>
    </table>

@endif

{{-- ══ TOTALES ══ --}}
<table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:16pt;">
    <tr>
        <td width="50%" valign="top" style="padding-right:20pt;">
            @if($isGrape)
                <div class="irpf-n">
                    <strong style="color:#92400e;">Nota:</strong> La retención IRPF se descuenta de la base imponible.
                    El importe &laquo;A pagar&raquo; es el neto a abonar al viticultor/proveedor.
                </div>
            @elseif($multipleRates)
                <div style="font-size:7.5pt; font-weight:bold; color:#6b7280; text-transform:uppercase; margin-bottom:4pt;">Desglose por tipo de IVA</div>
                <table class="txg">
                    <thead>
                        <tr>
                            <th>Tipo IVA</th>
                            <th>Base imp.</th>
                            <th>Cuota IVA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($taxGroups as $g)
                            <tr>
                                <td>{{ number_format($g['rate'], 0) }}%</td>
                                <td>{{ number_format($g['base'], 2, ',', '.') }} €</td>
                                <td>{{ number_format($g['amount'], 2, ',', '.') }} €</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </td>
        <td width="50%" valign="top">
            <table class="tot">
                @if($isGrape)
                    <tr>
                        <td class="lbl">Base imponible</td>
                        <td class="val">{{ number_format((float) $invoice->tax_base, 2, ',', '.') }} €</td>
                    </tr>
                    <tr>
                        <td class="lbl" style="color:#92400e;">Retención IRPF</td>
                        <td class="val" style="color:#92400e;">&minus; {{ number_format((float) $invoice->tax_amount, 2, ',', '.') }} €</td>
                    </tr>
                    <tr class="grand g">
                        <td class="lbl" style="color:white;">A PAGAR</td>
                        <td class="val">{{ number_format((float) $invoice->total_amount, 2, ',', '.') }} €</td>
                    </tr>
                @else
                    <tr>
                        <td class="lbl">Subtotal</td>
                        <td class="val">{{ number_format((float) $invoice->subtotal, 2, ',', '.') }} €</td>
                    </tr>
                    @if((float) ($invoice->discount_amount ?? 0) > 0)
                        <tr class="disc">
                            <td class="lbl">Descuento</td>
                            <td class="val">&minus; {{ number_format((float) $invoice->discount_amount, 2, ',', '.') }} €</td>
                        </tr>
                    @endif
                    <tr class="sep">
                        <td class="lbl">Base imponible</td>
                        <td class="val">{{ number_format((float) $invoice->tax_base, 2, ',', '.') }} €</td>
                    </tr>
                    <tr>
                        <td class="lbl">
                            IVA@if(!$multipleRates && $taxGroups->count() === 1) ({{ number_format($taxGroups->first()['rate'], 0) }}%)@endif
                        </td>
                        <td class="val">{{ number_format((float) $invoice->tax_amount, 2, ',', '.') }} €</td>
                    </tr>
                    <tr class="grand">
                        <td class="lbl" style="color:white;">TOTAL</td>
                        <td class="val">{{ number_format((float) $invoice->total_amount, 2, ',', '.') }} €</td>
                    </tr>
                @endif
            </table>
        </td>
    </tr>
</table>

{{-- ══ OBSERVACIONES ══ --}}
@if($invoice->observations || $invoice->observations_invoice)
    <div class="obs">
        <div class="obs-t">Observaciones</div>
        @if($invoice->observations)
            <div>{{ $invoice->observations }}</div>
        @endif
        @if($invoice->observations_invoice && $invoice->observations_invoice !== $invoice->observations)
            <div style="margin-top:3pt;">{{ $invoice->observations_invoice }}</div>
        @endif
    </div>
@endif

{{-- ══ FIRMAS ══ --}}
<table width="100%" cellspacing="0" cellpadding="0" style="margin-top:20pt;">
    <tr>
        <td width="45%" valign="bottom" class="sig-box">
            <div class="sig-line"></div>
            <div class="sig-lbl">Firma y sello del {{ $isGrape ? 'viticultor / proveedor' : 'cliente' }}</div>
            <div class="sig-nm">{{ $isGrape ? ($invoice->viticulturist?->name ?? $billingName) : $billingName }}</div>
            @if(!$isGrape)
                <div style="font-size:7pt; color:#9ca3af; margin-top:2pt;">Fecha de recepción: _______________</div>
            @endif
        </td>
        <td width="10%">&nbsp;</td>
        <td width="45%" valign="bottom" class="sig-box">
            <div class="sig-line"></div>
            <div class="sig-lbl">Firma y sello de la bodega</div>
            <div class="sig-nm">{{ $user->name }}</div>
        </td>
    </tr>
</table>

{{-- ══ PIE LEGAL ══ --}}
<div class="footer">
    Documento generado el {{ now()->format('d/m/Y \a \l\a\s H:i') }}
    &middot; Agro360 &middot; Plataforma de gestión vitivinícola
    @if($invoice->invoice_number)
        &middot; {{ $isGrape ? 'Liquidación' : 'Factura' }} {{ $invoice->invoice_number }}
    @endif
</div>

</body>
</html>
