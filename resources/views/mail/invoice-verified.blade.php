<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #1f2937; background: #f9fafb; margin: 0; padding: 0; }
        .wrap { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: #15803d; padding: 28px 32px; }
        .header h1 { color: #fff; margin: 0; font-size: 22px; }
        .header p  { color: #bbf7d0; margin: 4px 0 0; font-size: 14px; }
        .body { padding: 28px 32px; }
        .badge { display: inline-block; background: #dcfce7; color: #166534; border: 1px solid #86efac; border-radius: 20px; padding: 4px 14px; font-size: 12px; font-weight: bold; margin-bottom: 20px; }
        .info { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 16px 20px; margin: 20px 0; }
        .info table { width: 100%; border-collapse: collapse; }
        .info td { padding: 5px 0; font-size: 14px; }
        .info td:first-child { color: #6b7280; width: 40%; }
        .info td:last-child { font-weight: bold; color: #111827; }
        .csv { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 14px 20px; margin: 20px 0; }
        .csv p { margin: 0 0 6px; font-size: 12px; color: #6b7280; font-weight: bold; text-transform: uppercase; }
        .csv code { font-size: 16px; font-family: monospace; color: #1e40af; letter-spacing: 1px; }
        .footer { padding: 20px 32px; background: #f9fafb; border-top: 1px solid #e5e7eb; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <h1>{{ config('app.name') }}</h1>
        <p>{{ __('Facturación electrónica — Verifactu') }}</p>
    </div>
    <div class="body">
        <div class="badge">✓ {{ __('Factura verificada por la AEAT') }}</div>

        <p style="font-size:15px; margin-bottom:8px;">
            {{ __('Hola') }}{{ $invoice->billing_first_name ? ', '.$invoice->billing_first_name : '' }},
        </p>
        <p style="font-size:14px; color:#4b5563; margin-bottom:20px;">{{ __('Tu factura ha sido registrada correctamente en el sistema Verifactu de la Agencia Tributaria.
            Puedes comprobar su autenticidad en la sede electrónica de la AEAT.') }}</p>

        <div class="info">
            <table>
                <tr>
                    <td>{{ __('Número de factura') }}</td>
                    <td>{{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td>{{ __('Fecha') }}</td>
                    <td>{{ $invoice->invoice_date?->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td>{{ __('Importe total') }}</td>
                    <td>{{ number_format($invoice->total_amount, 2, ',', '.') }} €</td>
                </tr>
                <tr>
                    <td>{{ __('Emisor') }}</td>
                    <td>{{ $invoice->user->name }}</td>
                </tr>
            </table>
        </div>

        @if($invoice->sif_uuid)
        <div class="csv">
            <p>{{ __('Código Seguro de Verificación (CSV)') }}</p>
            <code>{{ $invoice->sif_uuid }}</code>
        </div>
        @endif

        <p style="font-size:13px; color:#6b7280;">{{ __('Si tienes cualquier duda sobre esta factura, contacta directamente con el emisor.') }}</p>
    </div>
    <div class="footer">
        {{ __('Este mensaje ha sido enviado automáticamente por :app.', ['app' => config('app.name')]) }}
        {{ __('No respondas a este correo.') }}
    </div>
</div>
</body>
</html>
