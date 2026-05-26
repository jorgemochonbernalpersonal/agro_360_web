<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $invoice->invoice_number ?? __('Albarán') . ' ' . $invoice->delivery_note_code }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">

    <div style="background: linear-gradient(135deg, #4d7c0f 0%, #3f6212 100%); padding: 30px; border-radius: 10px 10px 0 0; text-align: center;">
        <h1 style="color: white; margin: 0; font-size: 22px; font-weight: 700;">
            {{ $invoice->invoice_number ? __('Factura') . ' ' . $invoice->invoice_number : __('Albarán') . ' ' . $invoice->delivery_note_code }}
        </h1>
        @if($invoice->invoice_number && $invoice->delivery_note_code)
            <p style="color: #d9f99d; margin: 6px 0 0; font-size: 13px;">{{ __('Albarán') }}: {{ $invoice->delivery_note_code }}</p>
        @endif
    </div>

    <div style="background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #e5e7eb; border-top: none;">

        <p style="font-size: 15px; margin-bottom: 20px;">
            {{ __('Estimado/a') }} <strong>{{ $invoice->client->full_name ?? ($invoice->billing_first_name . ' ' . $invoice->billing_last_name) }}</strong>,
        </p>

        <p style="font-size: 14px; color: #4b5563; margin-bottom: 24px;">
            {{ __('Adjunto encontrará los documentos correspondientes') }}
            @if($invoice->invoice_number)
                {{ __('a la factura') }} <strong>{{ $invoice->invoice_number }}</strong>
            @else
                {{ __('al albarán') }} <strong>{{ $invoice->delivery_note_code }}</strong>
            @endif
            {{ __('con fecha') }} {{ $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : now()->format('d/m/Y') }}.
        </p>

        {{-- Resumen --}}
        <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #4d7c0f;">
            <h3 style="margin-top: 0; color: #4d7c0f; font-size: 15px;">{{ __('Resumen') }}</h3>
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                @if($invoice->invoice_number)
                <tr>
                    <td style="padding: 5px 0; color: #6b7280; width: 45%;">{{ __('Nº Factura') }}</td>
                    <td style="padding: 5px 0; font-weight: bold;">{{ $invoice->invoice_number }}</td>
                </tr>
                @endif
                @if($invoice->delivery_note_code)
                <tr>
                    <td style="padding: 5px 0; color: #6b7280;">{{ __('Albarán') }}</td>
                    <td style="padding: 5px 0;">{{ $invoice->delivery_note_code }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding: 5px 0; color: #6b7280;">{{ __('Fecha') }}</td>
                    <td style="padding: 5px 0;">{{ ($invoice->invoice_date ?? now())->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; color: #6b7280;">{{ __('Base imponible') }}</td>
                    <td style="padding: 5px 0;">{{ number_format($invoice->subtotal, 2) }} €</td>
                </tr>
                @if((float)$invoice->tax_amount > 0)
                <tr>
                    <td style="padding: 5px 0; color: #6b7280;">{{ __('IVA') }}</td>
                    <td style="padding: 5px 0;">{{ number_format($invoice->tax_amount, 2) }} €</td>
                </tr>
                @endif
                <tr style="border-top: 1px solid #e5e7eb;">
                    <td style="padding: 8px 0 4px; font-weight: bold;">{{ __('Total') }}</td>
                    <td style="padding: 8px 0 4px; font-weight: bold; color: #4d7c0f; font-size: 15px;">
                        {{ number_format($invoice->total_amount, 2) }} €
                    </td>
                </tr>
            </table>
        </div>

        {{-- Items --}}
        @if($invoice->items->count() > 0)
        <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h3 style="margin-top: 0; color: #374151; font-size: 14px;">{{ __('Detalle') }}</h3>
            <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                <thead>
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <th style="text-align: left; padding: 6px 4px; color: #6b7280; font-weight: 600;">{{ __('Concepto') }}</th>
                        <th style="text-align: right; padding: 6px 4px; color: #6b7280; font-weight: 600;">{{ __('Cant.') }}</th>
                        <th style="text-align: right; padding: 6px 4px; color: #6b7280; font-weight: 600;">{{ __('Precio') }}</th>
                        <th style="text-align: right; padding: 6px 4px; color: #6b7280; font-weight: 600;">{{ __('Total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                    <tr style="border-bottom: 1px solid #f3f4f6;">
                        <td style="padding: 6px 4px;">{{ $item->name }}</td>
                        <td style="padding: 6px 4px; text-align: right;">{{ number_format($item->quantity, 2) }}</td>
                        <td style="padding: 6px 4px; text-align: right;">{{ number_format($item->unit_price, 4) }} €</td>
                        <td style="padding: 6px 4px; text-align: right; font-weight: 600;">{{ number_format($item->total, 2) }} €</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if($invoice->observations)
        <div style="background: #fef9c3; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #eab308; font-size: 13px; color: #713f12;">
            <strong>{{ __('Observaciones:') }}</strong> {{ $invoice->observations }}
        </div>
        @endif

        <p style="font-size: 13px; color: #6b7280;">
            {{ __('Los documentos PDF están adjuntos a este correo.') }}
            @if($senderName)
                <br>{{ __('Un saludo de') }} <strong>{{ $senderName }}</strong>.
            @endif
        </p>

        <p style="font-size: 12px; color: #9ca3af; margin-top: 30px; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 16px;">{{ __('Este correo ha sido enviado desde Agro365') }}</p>
    </div>

</body>
</html>
